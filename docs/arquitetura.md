# Jefin Finances: arquitetura, fluxos e regras

## 1. Contexto e stack

Monólito Laravel com views Blade e JavaScript compilado pelo laravel-mix. Há uma API JWT usada pelo app mobile, com escopo menor que o da web. Não há filas, cache de aplicação nem jobs agendados: a janela de Ocorrências é completada quando o usuário abre uma tela.

PHP 7.4 (FPM) · Laravel 6.20 · MySQL 5.7 · nginx 1.29 · Blade + jQuery + SweetAlert · laravel-mix 5 / Node 14 · tymon/jwt-auth · PHPUnit + SQLite `:memory:` · Docker Compose (web, app, db)

```mermaid
flowchart LR
  B["Navegador<br/>Blade + public/js/app.js"] -->|"HTTP, sessão + CSRF"| N["nginx<br/>container web :8000"]
  MB["App mobile"] -->|"HTTP /api, JWT"| N
  N -->|"FastCGI :9000"| P["PHP-FPM 7.4<br/>container app<br/>Laravel 6"]
  P -->|"PDO"| DB[("MySQL 5.7<br/>container db")]
  P -.->|"storage/logs<br/>storage/debugbar"| FS[("disco")]
  DDL["database/ddl/*.sql<br/>aplicados à mão"] -.->|"schema"| DB
```

O schema real vem dos arquivos em `database/ddl`, sem migrations. Os testes usam um schema próprio em `tests/migrations`.

## 2. Camadas da requisição

Os controllers são finos. As regras de negócio, as transações e a checagem de dono ficam nos models e nos módulos `app/Recorrencia` e `app/Projecao`. Escritas respondem JSON `{error, msg}`; falhas inesperadas são registradas no log e viram mensagem genérica.

```mermaid
flowchart TD
  RT["routes/web.php<br/>middleware auth (sessão)"] --> FR["FormRequest<br/>valida, 422 com errors"]
  FR --> CT["Controller<br/>monta filtros, chama o model,<br/>devolve view ou JSON {error, msg}"]
  CT -->|"index"| CJ["completarJanela(user)<br/>DespesaFixa e ProventoFixo"]
  CT --> MD["Models Eloquent<br/>Despesa, DespesaFixa, Proventos, ProventoFixo,<br/>Lancamento"]
  CJ --> RC
  MD --> RC["app/Recorrencia<br/>trait Recorrente, Vigencia, Ocorrencia"]
  CT --> PJ["app/Projecao/Projecao<br/>somente leitura"]
  PJ --> MD
  MD -->|"doUsuario: 404 se o registro é de outro usuário"| DB[("MySQL")]
  MD -->|"DB::transaction + lockForUpdate"| DB
  CT --> V["views Blade<br/>JS: AJAX + SweetAlert"]
```

## 3. Modelo de dados

Toda tabela de domínio pertence a um `user_id`. Ocorrências são linhas comuns de `despesas` e `proventos` com a chave da recorrência preenchida (`despesa_fixa_id`, `provento_fixo_id`); Despesas Variáveis e Proventos avulsos têm essa chave nula.

Uma Despesa é só Fixa ou Variável: `despesas_tipos`, `despesa_tipo_id` (agora nulo) e `dividas` continuam no banco, mas as telas não os usam mais (só a API ainda expõe os Tipos de Despesa).

```mermaid
erDiagram
  users ||--o{ despesas_fixas : "tem"
  users ||--o{ despesas : "tem"
  users ||--o{ proventos_fixos : "tem"
  users ||--o{ proventos : "tem"
  users ||--o{ lancamentos : "tem"
  despesas_fixas |o--o{ despesas : "Ocorrências"
  proventos_fixos |o--o{ proventos : "Ocorrências"
  despesas |o--o{ lancamentos : "pagamento"

  despesas_fixas {
    int id PK
    int user_id FK
    varchar nome_despesa
    decimal valor "Valor Previsto, 10,2"
    date mes_inicio "dia 1"
    date mes_fim "null = sem fim"
    tinyint dia_vencimento "1 a 31"
    date ocorrencias_ate "último mês gravado"
    tinyint ativo
    tinyint oculta "1 = Ocultação"
  }
  despesas {
    int id PK
    int user_id FK
    int despesa_fixa_id FK "null = Variável"
    varchar nome_despesa "Nome (k/N)"
    decimal valor_total
    date data_vencimento "define a Competência"
    tinyint pago
    tinyint ativo
    datetime created_at "Competência se não há vencimento"
  }
  proventos_fixos {
    int id PK
    int user_id FK
    varchar descricao
    decimal valor
    date mes_inicio
    date mes_fim
    date ocorrencias_ate
    tinyint ativo
    tinyint oculta "1 = Ocultação"
  }
  proventos {
    int id PK
    int user_id FK
    int provento_fixo_id FK "null = avulso"
    varchar descricao_provento
    decimal valor_provento
    date data_provento "Ocorrência no dia 1"
  }
  lancamentos {
    int id PK
    int user_id FK
    int despesa_id FK "null = outro lançamento"
    datetime data_lancamento
    decimal valor
    varchar descricao
  }
```

Valores monetários em `DECIMAL(10,2)`. As tabelas `valor_hora` e `dividas` existem no banco mas não são usadas pelo código.

**Ocultação.** `oculta = 1` numa fixa esconde ela e todas as suas Ocorrências de todo o app. Um global scope (`App\Recorrencia\SemOcorrenciasOcultas`) em `Despesa` e `Proventos` tira das consultas as linhas cuja recorrência está oculta; só a relação `ocorrencias()` da própria fixa o ignora, para Encerrar e Excluir continuarem funcionando. Fixa oculta não grava Ocorrências novas; ao desocultar, a janela se completa.

## 4. Módulo Recorrência

`Vigencia` é um objeto de valor puro: decide quais meses gravar e calcula Vencimento e parcela, sem tocar no banco. O trait `Recorrente` concentra gravar, Reajuste e Encerramento; cada model informa como criar, reajustar e excluir a sua Ocorrência.

```mermaid
classDiagram
  class Recorrente {
    <<trait>>
    +vigencia() Vigencia
    +gravarOcorrencias(ate) int
    +ocorrenciasAPartirDe(mes) Builder
    +reajustar(aPartirDe, valor, dia)
    +encerrar(fim)
    +completarJanela(user) int
    +fimDepoisDoAtual(fim) bool
    #criarOcorrencia(Ocorrencia)*
    #reajustarOcorrencia(ocorrencia, noMes)*
    #excluirOcorrencia(ocorrencia)*
    #colunaCompetencia()*
    #diaDaOcorrencia()*
    #ocorrenciaPreservada(ocorrencia) bool
  }
  class Vigencia {
    +int JANELA_MESES = 12
    +fimPorParcelas(inicio, parcelas)$ Carbon
    +totalParcelas() int
    +ocorrenciasAGravar(hoje, gravadasAte, ate) Ocorrencia[]
    +ocorrenciaNoMes(mes) Ocorrencia
    +vencimentoNoMes(mes) Carbon
  }
  class Ocorrencia {
    +Carbon mes
    +Carbon vencimento
    +int parcela
    +int totalParcelas
    +nome(base) string
  }
  class DespesaFixa {
    dia = dia_vencimento
    preservada = Despesa paga
  }
  class ProventoFixo {
    dia = 1
    preservada = nunca
  }
  Recorrente <|.. DespesaFixa
  Recorrente <|.. ProventoFixo
  Recorrente ..> Vigencia : usa
  Vigencia ..> Ocorrencia : cria
  DespesaFixa "1" --> "*" Despesa : ocorrencias()
  ProventoFixo "1" --> "*" Proventos : ocorrencias()
```

## 5. Gravação de Ocorrências

É chamada pelo `index` de Despesas, Despesas Fixas, Proventos, Proventos Fixos e Projeção, e no cadastro da fixa. É idempotente: abrir a tela de novo no mesmo mês não grava nada. Cada recorrência é travada na sua própria transação, para que duas abas abertas não gravem o mesmo mês duas vezes.

```mermaid
flowchart TD
  A["completarJanela(user)"] --> B["para cada recorrência ativa do usuário<br/>DB::transaction + lockForUpdate"]
  B --> C{"ocorrencias_ate<br/>preenchido?"}
  C -->|sim| D["de = ocorrencias_ate + 1 mês"]
  C -->|não| E["de = mes_inicio"]
  D --> F{"de antes do mês atual?"}
  E --> F
  F -->|sim| G["de = mês atual<br/>nunca grava retroativo"]
  F -->|não| H{"tem mes_fim?"}
  G --> H
  H -->|sim| I["até = mes_fim"]
  H -->|não| J["até = mês atual + 12"]
  I --> K["para cada mês de de até até:<br/>Vencimento = min(dia, dias do mês)<br/>parcela k/N quando há fim"]
  J --> K
  K --> L["criarOcorrencia<br/>Despesa: pago 0<br/>Provento: dia 1"]
  L --> M["ocorrencias_ate = último mês gravado"]
```

Como a gravação parte de `ocorrencias_ate` e não dos meses existentes, uma Ocorrência excluída com "só esta" não é recriada.

## 6. Alteração de Ocorrência com escopo

Na Despesa o escopo vem do campo `escopo` (`so_esta` ou `seguintes`); no Provento, de `update_subsequent`. "Esta e as seguintes" vira um Reajuste da fixa a partir da Competência da Ocorrência. Nome e ativo continuam valendo só para a Ocorrência editada.

```mermaid
sequenceDiagram
  actor U as Usuário
  participant C as DespesasController
  participant R as DespesasRequest
  participant D as Despesa
  participant F as DespesaFixa (Recorrente)
  participant DB as MySQL
  U->>C: PUT /despesas/update/:id com escopo
  C->>R: valida escopo
  C->>D: updateDespesa(id, dados, user)
  D->>DB: doUsuario: 404 se for de outro usuário
  alt so_esta, ou Despesa Variável
    D->>DB: grava nome, valor, ativo e vencimento
  else seguintes
    D->>D: recusaSeguintes: mês passado ou paga
    D->>D: recusa se o mês do Vencimento mudou
    D->>F: reajustar(competência, valor, dia se mudou)
    F->>DB: BEGIN e lockForUpdate da fixa
    F->>DB: grava meses anteriores pendentes com o valor antigo
    F->>DB: novo valor e dia na fixa
    loop Ocorrências com Competência a partir do mês
      F->>DB: pula as pagas, reescreve valor e Vencimento das demais
    end
    D->>DB: nome e ativo só nesta Ocorrência
    F->>DB: COMMIT
  end
  C-->>U: JSON error e msg
```

O dia só é repassado ao Reajuste quando difere do Vencimento calculado; assim, um dia 31 cortado para 30 num mês curto não muda o dia da fixa.

## 7. Encerramento

Tem duas entradas: a ação "Encerrar" na tela da fixa (`PUT …/encerrar/:id`, com o mês de fim) e a exclusão de uma Ocorrência com "esta e as seguintes", que chama `encerrar(competência − 1 mês)`.

```mermaid
flowchart TD
  S1["Tela da fixa: Encerrar(mes_fim)"] --> V{"mes_fim antes do mês atual<br/>ou depois do fim atual?"}
  V -->|sim| X["recusa: o Encerramento só encurta"]
  V -->|não| A
  S2["Excluir Ocorrência<br/>esta e as seguintes"] --> V2{"mês passado ou paga?"}
  V2 -->|sim| X2["recusa"]
  V2 -->|não| A["encerrar(fim)<br/>BEGIN + lockForUpdate"]
  A --> B["Ocorrências com Competência depois do fim"]
  B --> C{"preservada?<br/>Despesa paga"}
  C -->|sim| D["mantém e estende o fim até ela"]
  C -->|não| E["exclui a Ocorrência"]
  D --> G{"fim antes de mes_inicio?"}
  E --> G
  G -->|sim| H["exclui a recorrência"]
  G -->|não| I["mes_fim = fim<br/>ocorrencias_ate limitado ao fim"]
```

## 8. Pagamento de Despesa

O botão é um toggle. O `pago` da Despesa e o Lançamento mudam na mesma transação, e a Despesa é relida com trava para que dois cliques simultâneos não lancem dois pagamentos.

```mermaid
sequenceDiagram
  actor U as Usuário
  participant C as DespesasController
  participant D as Despesa
  participant L as Lancamento
  participant DB as MySQL
  U->>C: PUT /despesas/payDespesa/:id
  C->>D: pagamentoDespesa(id, user)
  D->>DB: doUsuario: 404 se for de outro usuário
  D->>DB: BEGIN e lockForUpdate da Despesa
  alt já paga
    D->>DB: pago = 0 e apaga os Lançamentos do dono
  else tem Lançamento sem pago (dado antigo)
    D->>DB: pago = 1, sem novo Lançamento
  else não paga
    D->>L: lancar(data agora, valor_total, nome)
    L->>DB: insere Lançamento e marca pago = 1
  end
  D->>DB: COMMIT
  C-->>U: JSON error e msg
```

Excluir uma Despesa paga leva junto os Lançamentos do dono (`excluirComLancamentos`, issue #16).

## 9. Projeção

Somente leitura sobre os registros gravados. Antes de ler, o controller completa a janela das recorrências, para que o último mês do Horizonte de 12 já tenha as fixas sem fim.

```mermaid
flowchart LR
  Q["GET /projecao?horizonte=N"] --> HV{"N em 3, 6 ou 12?"}
  HV -->|não| H6["N = 6"]
  HV -->|sim| CJ
  H6 --> CJ["completarJanela<br/>DespesaFixa e ProventoFixo"]
  CJ --> LOOP["para i de 0 a N<br/>mês = atual + i"]
  LOOP --> PV["Proventos<br/>soma valor_provento"]
  LOOP --> DF["Despesas com despesa_fixa_id<br/>soma valor_total"]
  LOOP --> DV["Despesas sem despesa_fixa_id<br/>lista e soma"]
  PV --> SM["Saldo do Mês = Proventos - Fixas - Variáveis"]
  DF --> SM
  DV --> SM
  SM --> SA["Saldo Acumulado<br/>começa em 0 no mês atual"]
  LOOP --> MC["Marcos: mes_inicio ou mes_fim<br/>de fixas ativas e não ocultas = este mês<br/>agrupados por lado e tipo; mais de 3 viram link"]
  LOOP --> DC["Datas Comemorativas do mês<br/>lista fixa, Carnaval e Páscoa calculados"]
  CJ --> AL["Alertas: Marcos e Variáveis<br/>com distância de 1 a N<br/>agrupados por mês, com total atualizado<br/>e resumo quando há mais de um"]
```

Competência das Despesas: `COALESCE(data_vencimento, created_at)`. O nome no Marco de término é o da Ocorrência gravada no mês de fim, por exemplo "Cartão (4/10)" após um Encerramento antecipado.

No Alerta de um Marco, o total atualizado é o de Despesas Fixas (ou de Proventos) gravado no mês em que o Marco faz efeito: o próprio mês, quando começa; o mês seguinte, quando termina (o mês de fim ainda conta a última Ocorrência). O resumo do mês soma os Alertas de ganho (entra) e de atenção (sai).

## 10. Regras de negócio

Cada regra tem um id estável para ser citado em issues e PRs. "Garantida em" aponta onde o código a impõe; "Teste" aponta a classe de Feature que a cobre.

| Id | Regra | Garantida em | Teste |
|---|---|---|---|
| RN-01 | Todo registro é do seu usuário. Ler, alterar, excluir, ocultar ou pagar um registro de outro usuário responde 404. | `Model::doUsuario (Despesa, Proventos, Lancamento, fixas)` | DonoDaDespesaTest, DonoDoLancamentoTest, OcorrenciaProventoFixoEscopoTest, OcultacaoTest |
| RN-02 | A Competência de uma Despesa é o mês do Vencimento; sem Vencimento, o mês de cadastro. | `Despesa::getDespesas, Despesa::competencia` | VencimentoDespesaTest |
| RN-03 | Ocorrências são gravadas antecipadamente até o mês atual + 12, só depois da última gravada e nunca em meses passados. | `Vigencia::ocorrenciasAGravar, Recorrente::completarJanela` | DespesaFixaVigenciaTest, ProventoFixoTest, ProjecaoTest |
| RN-04 | O Vencimento de cada mês é o dia da fixa, ou o último dia do mês quando ele é mais curto. | `Vigencia::vencimentoNoMes` | DespesaFixaVigenciaTest |
| RN-05 | Parcelas: fim = início + N − 1; cada Ocorrência recebe o nome "Nome (k/N)". Informar parcelas e mês de fim juntos responde 422. | `Vigencia::fimPorParcelas, Ocorrencia::nome, DespesasFixasRequest` | DespesaFixaVigenciaTest |
| RN-06 | Uma Despesa é só Fixa ou Variável: não há Tipo de Despesa nas telas, e Despesas e fixas são gravadas sem tipo. | `DespesasRequest, Despesa, DespesaFixa` | DespesaSemTipoTest |
| RN-07 | "Esta e as seguintes" é recusado num mês passado, numa Ocorrência paga e quando o mês do Vencimento muda. | `Despesa::recusaSeguintes, Despesa::updateDespesa, Proventos::updateProvento` | OcorrenciaDespesaFixaEscopoTest, OcorrenciaProventoFixoEscopoTest |
| RN-08 | Reajuste: os meses anteriores ainda não gravados ficam com o valor antigo; do mês em diante, sobrescreve também os ajustados à mão, menos os pagos. | `Recorrente::reajustar` | OcorrenciaDespesaFixaEscopoTest |
| RN-09 | Encerramento só encurta e não aceita mês passado. Ocorrências pagas são preservadas e estendem o fim até elas; fim antes do início exclui a recorrência. | `Recorrente::encerrar, fimDepoisDoAtual, requests das fixas` | OcorrenciaDespesaFixaEscopoTest, ProjecaoTest |
| RN-10 | Excluir uma Despesa Fixa apaga ela e todas as Ocorrências, só enquanto nenhuma foi paga; com alguma paga, use Encerrar. | `DespesaFixa::deleteDespesaFixa` | DespesasFixasTelaTest |
| RN-11 | Pagamento é atômico: `pago` e Lançamento mudam juntos. Excluir Despesa paga apaga os Lançamentos do dono. | `Despesa::pagamentoDespesa, Lancamento::lancar, excluirComLancamentos` | ConsistenciaDespesaLancamentoTest |
| RN-12 | Projeção: Horizonte em {3, 6, 12} (outro valor vira 6); Saldo Acumulado parte de zero; Marcos incluem o mês atual; Alertas só para distância ≥ 1. | `Projecao::horizonteValido, meses, alertas` | ProjecaoTest |
| RN-13 | Valores de Despesa, fixas, Lançamento e Provento até `DECIMAL(10,2)` (R$ 99.999.999,99). | `DDL valores_ate_99_milhoes.sql, provento_fixo.sql` | smoke no MySQL (PR #20) |
| RN-14 | O guia do usuário é público em `/guia` e tem link na tela de login. | `routes/web.php, auth/login.blade.php` | GuiaDoUsuarioTest (PR #23) |
| RN-15 | Ocultação: a fixa oculta e todas as suas Ocorrências, inclusive pagas, somem de listas, totais, Projeção, Marcos e Alertas, sem nada ser alterado; desocultar restaura tudo. | `SemOcorrenciasOcultas, Recorrente::ocultar, completarJanela` | OcultacaoTest |
| RN-16 | Comprometimento da Renda com Despesas Fixas = Valor Previsto das fixas vigentes ÷ Proventos do mês atual (sem Proventos, não é calculado). | `DespesasFixasController::index` | DespesasFixasTelaTest |
| RN-17 | A tela inicial depois do login é a Projeção; `/home` redireciona para ela. | `RouteServiceProvider::HOME, routes/web.php` | MenuTest |

## 11. Rotas de domínio (web)

Todas sob o middleware `auth`, exceto login, cadastro, recuperação de senha e `/guia`. Cada recurso segue o padrão `index · create · store · edit · update · delete`; a tabela lista as rotas que fogem dele.

| Método | Caminho | Faz |
|---|---|---|
| GET | `/despesas?mes&ano` | Despesas do mês; completa a janela antes |
| PUT | `/despesas/update/:id` | Edita; `escopo=so_esta\|seguintes` numa Ocorrência |
| DELETE | `/despesas/delete/:id` | Exclui com Lançamentos; `seguintes` encerra a fixa |
| PUT | `/despesas/payDespesa/:id` | Toggle de pagamento (RN-11) |
| PUT | `/despesasFixas/reajustar/:id` | Reajuste a partir de `a_partir_de` (≥ mês atual) |
| PUT | `/despesasFixas/encerrar/:id` | Encerramento em `mes_fim` |
| PUT | `/despesasFixas/ocultar/:id` | Ocultação: `oculta=1` oculta, `oculta=0` desoculta |
| DELETE | `/despesasFixas/delete/:id` | Exclui a fixa e todas as Ocorrências, se nenhuma estiver paga (RN-10) |
| PUT | `/proventosFixos/reajustar/:id` | Reajuste do Provento Fixo |
| PUT | `/proventosFixos/encerrar/:id` | Encerramento do Provento Fixo |
| PUT | `/proventosFixos/ocultar/:id` | Ocultação do Provento Fixo |
| PUT | `/proventos/update/:id` | Edita; `update_subsequent` vira Reajuste |
| DELETE | `/proventos/delete/:id` | `delete_subsequent` vira Encerramento |
| POST | `/proventos/copyProventos` | Copia os Proventos avulsos do usuário do mês anterior para o mês do filtro |
| GET | `/projecao?horizonte` | Projeção, Marcos, Alertas e Datas Comemorativas; tela inicial |
| GET | `/guia` | Guia do usuário, público |

A API (`routes/api.php`, middleware `verifyApi` com JWT) expõe usuários, Tipos de Despesa, Lançamentos (só leitura) e Proventos. Ela não tem Despesas, fixas nem Projeção.

## 12. Testes

Testes de Feature pelas rotas HTTP, com `RefreshDatabase` sobre SQLite em memória. A data é fixada com `Carbon::setTestNow`; as falhas são simuladas com eventos de model que lançam exceção.

> **Trava obrigatória.** `tests/TravaBancoDeTeste.php` aborta a suíte se a conexão resolvida não for SQLite `:memory:`. O `RefreshDatabase` apaga todas as tabelas da conexão, e isso já destruiu o banco de dev uma vez. Rode no container:
>
> ```sh
> docker exec jefin_finances_app sh -c 'cd /var/www && php artisan config:clear && vendor/bin/phpunit'
> ```

- Estado atual: 140 testes, 761 asserções.
- Não cobertos: as travas `lockForUpdate` (o SQLite as ignora), o JavaScript das telas e o dark mode (sem testes de navegador) e Usuários.
- Schema de teste: `tests/migrations/2026_11_21_000001_criar_schema_de_teste.php`. Ao mudar um DDL, atualize os dois.

## 13. Dívida técnica conhecida

| Gravidade | Item | Detalhe |
|---|---|---|
| alta | Usuários sem checagem de dono | O model de Usuários não aplica RN-01 nem registra falhas no log. |
| média | Schema por DDL manual | Sem migrations: cada ambiente aplica `database/ddl` na ordem certa, e o schema de teste é mantido à parte. Divergência entre os dois passa despercebida. |
| média | Concorrência e JS sem testes | As travas só funcionam no MySQL e não são exercitadas pela suíte; as telas não têm teste de navegador. A Ocultação depende de um global scope: consultas novas em `Despesa`/`Proventos` já o recebem, mas um `withoutGlobalScopes()` esquecido reexibe Ocorrências ocultas. |
| baixa | Debugbar com chave duplicada | `config/debugbar.php` define `storage.enabled` duas vezes; sem `DEBUGBAR_STORAGE_ENABLED=true`, requisições AJAX estouram o buffer do nginx (502). |
| baixa | Tabelas órfãs e guia manual | `valor_hora`, `dividas` e `despesas_tipos` (fora da API) não são usadas pelo código. O guia do usuário é HTML estático e precisa ser atualizado quando uma regra muda. |

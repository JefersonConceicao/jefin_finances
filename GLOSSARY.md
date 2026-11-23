# Jefin Finances

Controle financeiro pessoal: o que se gasta de forma recorrente, o que entra de gasto extra em cada mês, e uma visão dos meses à frente.

## Despesas

**Despesa**:
Um gasto do usuário que conta em um mês específico (sua Competência).
_Avoid_: Gasto, conta

**Despesa Fixa**:
Um gasto recorrente que se repete todo mês dentro da sua Vigência; serve de modelo para gerar a Despesa de cada mês. Parcelas (cartão, financiamento) são Despesas Fixas com fim. Pode ser excluída junto com todas as suas Ocorrências enquanto nenhuma delas foi paga; depois disso, só o Encerramento.
_Avoid_: Despesa recorrente

**Ocorrência**:
A Despesa (ou Provento) de um mês gerada por uma Despesa Fixa (ou Provento Fixo). Alterar ou excluir uma Ocorrência vale para "só esta" ou "esta e as seguintes".
_Avoid_: Instância, parcela, despesa gerada

**Despesa Variável**:
Uma Despesa avulsa, que não foi gerada a partir de uma Despesa Fixa. Fixa ou Variável é a única classificação de uma Despesa; não há categorias.
_Avoid_: Gasto extra, despesa avulsa, tipo de despesa, categoria

**Competência**:
O mês em que uma Despesa conta no orçamento: o mês do seu Vencimento, independente de quando ela foi cadastrada.
_Avoid_: Mês de referência, data da despesa

**Vencimento**:
A data em que o usuário pretende pagar uma Despesa. Numa Despesa Fixa é um dia do mês, aplicado a cada Ocorrência (ou o último dia, se o mês for mais curto).
_Avoid_: Data de pagamento, data limite

**Vigência**:
A janela de meses de Competência em que algo recorrente vale: um mês de início e um mês de fim opcional (sem fim, vale por tempo indeterminado).
_Avoid_: Período, duração

**Valor Previsto**:
O valor esperado de uma Despesa Fixa ou Provento Fixo a cada mês; o valor real de um mês pode diferir dele (ex.: conta de luz).
_Avoid_: Valor estimado, valor padrão

**Reajuste**:
Mudança do Valor Previsto de uma Despesa Fixa ou Provento Fixo que vale de um mês em diante, sem alterar os meses anteriores.
_Avoid_: Atualização, correção

**Encerramento**:
Definir o mês de fim da Vigência de uma Despesa Fixa ou Provento Fixo, no mês atual ou no futuro.
_Avoid_: Inativação, cancelamento

**Ocultação**:
Esconder uma Despesa Fixa ou Provento Fixo, junto com todas as suas Ocorrências, de todo o app (listas, totais, Projeção, Marcos e Alertas), como se nunca tivesse existido, sem excluir nem alterar nada; desfazer a Ocultação traz tudo de volta. Serve para simular na Projeção como ficaria sem aquele registro.
_Avoid_: Inativação, desativação, arquivamento, simulação

## Proventos

**Provento**:
Dinheiro que entra para o usuário em um mês.
_Avoid_: Receita, renda, entrada

**Provento Fixo**:
Um Provento recorrente (ex.: salário) que se repete todo mês dentro da sua Vigência; serve de modelo para gerar o Provento de cada mês.
_Avoid_: Provento anual, renda fixa

## Visão

**Projeção**:
A visão mês a mês, do mês atual até o Horizonte, do que vai entrar e sair, combinando o que já está registrado com o que as Despesas Fixas e Proventos Fixos vão gerar.
_Avoid_: Previsão, provisão

**Horizonte**:
Quantos meses à frente do mês atual a Projeção mostra.
_Avoid_: Janela, período

**Saldo do Mês**:
Proventos menos Despesas de um mês.
_Avoid_: Sobra, resultado, saldo restante

**Saldo Acumulado**:
A soma dos Saldos do Mês a partir do mês atual até um mês da Projeção; não inclui o dinheiro que o usuário já tem hoje.
_Avoid_: Saldo final, saldo total

**Marco**:
Um mês da Projeção em que uma Despesa Fixa ou Provento Fixo começa ou termina.
_Avoid_: Evento, mudança

**Alerta**:
Um aviso na Projeção sobre o impacto futuro de um Marco ou de uma Despesa Variável com Competência futura; é de ganho (o fluxo melhora) ou de atenção (o fluxo piora).
_Avoid_: Notificação, lembrete

**Comprometimento da Renda**:
Quanto dos Proventos de um mês é tomado pelas Despesas daquele mês, em percentual.
_Avoid_: Percentual de gastos, uso da renda

**Data Comemorativa**:
Uma data do calendário (nacional ou de Salvador/BA) que costuma trazer gastos variáveis extras, como São João ou Natal; aparece no mês da Projeção em que cai.
_Avoid_: Feriado, evento

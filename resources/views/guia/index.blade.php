<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Guia do Jefin Finances</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap">
<style>
/* Layout: guia de leitura com sumário fixo à esquerda no desktop; uma coluna no celular.
   Cores: tinta azul-ardósia, verde de provento, terracota suave de despesa. */
:root{
  --bg:#f5f7f8;
  --surface:#ffffff;
  --sunken:#eaeef0;
  --fg:#18232b;
  --muted:#5b6872;
  --line:#d5dde1;
  --accent:#1d5f74;
  --accent-soft:#dcebf0;
  --in:#2c7a4b;
  --in-soft:#e0f0e6;
  --out:#b0513c;
  --out-soft:#f6e3dd;
  --warn:#9a6a12;
  --warn-soft:#f6ecd6;
  --f-display:"Sora", "Segoe UI", system-ui, sans-serif;
  --f-body:"Inter", system-ui, -apple-system, "Segoe UI", sans-serif;
  --f-mono:"JetBrains Mono", ui-monospace, "SFMono-Regular", Menlo, monospace;
}
@media (prefers-color-scheme: dark){
  :root:not([data-theme="light"]){
    --bg:#11181d; --surface:#18222a; --sunken:#0d1317; --fg:#e4ebef; --muted:#97a6b0; --line:#2b3943;
    --accent:#7cc0d6; --accent-soft:#173440; --in:#6fcf97; --in-soft:#16301f; --out:#ec9a85; --out-soft:#3a201a;
    --warn:#e3b75a; --warn-soft:#352a12; color-scheme:dark;
  }
}
:root[data-theme="dark"]{
  --bg:#11181d; --surface:#18222a; --sunken:#0d1317; --fg:#e4ebef; --muted:#97a6b0; --line:#2b3943;
  --accent:#7cc0d6; --accent-soft:#173440; --in:#6fcf97; --in-soft:#16301f; --out:#ec9a85; --out-soft:#3a201a;
  --warn:#e3b75a; --warn-soft:#352a12; color-scheme:dark;
}

*{box-sizing:border-box}
body{margin:0}
[hidden]{display:none!important}
.back{display:inline-block; margin-bottom:18px; font-size:14px; text-decoration:none}
html{scroll-behavior:smooth}
@media (prefers-reduced-motion: reduce){html{scroll-behavior:auto}}
body{
  background:var(--bg); color:var(--fg); font-family:var(--f-body); font-size:16px; line-height:1.6;
  padding-left:max(16px, env(safe-area-inset-left, 0px)); padding-right:max(16px, env(safe-area-inset-right, 0px));
}
.wrap{max-width:1180px; margin:0 auto; display:grid; grid-template-columns:220px minmax(0,1fr); gap:56px; padding-block:48px 96px}
@media (max-width: 900px){ .wrap{grid-template-columns:minmax(0,1fr); gap:24px; padding-block:28px 64px} }

/* Sumário */
nav.toc{position:sticky; top:calc(env(safe-area-inset-top, 0px) + 24px); align-self:start; font-size:14px}
nav.toc .brand{font-family:var(--f-display); font-weight:700; font-size:15px; letter-spacing:.01em; margin-bottom:16px; display:flex; gap:8px; align-items:center}
nav.toc .brand span{display:inline-grid; place-items:center; width:26px; height:26px; border-radius:7px; background:var(--accent); color:var(--bg); font-size:13px}
nav.toc ol{list-style:none; margin:0; padding:0; display:grid; gap:2px; counter-reset:t}
nav.toc a{display:block; padding:6px 10px; border-radius:6px; color:var(--muted); text-decoration:none}
nav.toc a:hover, nav.toc a:focus-visible{background:var(--sunken); color:var(--fg); outline:none}
@media (max-width: 900px){
  nav.toc{position:static}
  nav.toc ol{display:flex; flex-wrap:wrap; gap:6px}
  nav.toc a{border:1px solid var(--line); padding:4px 10px; font-size:13px}
}

main{min-width:0}
section{padding-top:56px; scroll-margin-top:16px}
section:first-child{padding-top:0}
h1,h2,h3{font-family:var(--f-display); text-wrap:balance; line-height:1.2; margin:0}
h1{font-size:clamp(30px, 5vw, 44px); font-weight:700; letter-spacing:-.02em}
h2{font-size:clamp(22px, 3vw, 28px); font-weight:600; letter-spacing:-.01em; margin-bottom:12px}
h3{font-size:17px; font-weight:600; margin-bottom:6px}
p{margin:0 0 12px; max-width:68ch}
.lead{font-size:18px; color:var(--muted); max-width:62ch; margin-top:14px}
.eyebrow{font-size:12px; font-weight:600; letter-spacing:.08em; text-transform:uppercase; color:var(--accent); margin-bottom:10px}
.term{font-weight:600; color:var(--fg); border-bottom:1px dotted var(--muted)}
.money{font-family:var(--f-mono); font-variant-numeric:tabular-nums; white-space:nowrap}
.in{color:var(--in)} .out{color:var(--out)}
a{color:var(--accent)}

/* Três ideias do topo */
.pillars{display:grid; grid-template-columns:repeat(3, minmax(0,1fr)); gap:16px; margin-top:28px}
@media (max-width: 700px){ .pillars{grid-template-columns:minmax(0,1fr)} }
.pillar{background:var(--surface); border:1px solid var(--line); border-radius:10px; padding:18px}
.pillar .k{font-family:var(--f-mono); font-size:12px; color:var(--muted)}
.pillar h3{margin-top:4px}
.pillar p{font-size:14.5px; color:var(--muted); margin:0}

/* Diagrama */
.figure{background:var(--surface); border:1px solid var(--line); border-radius:12px; padding:20px; margin-top:18px; overflow-x:auto}
.figure pre.mermaid{margin:0; background:transparent; min-width:640px; text-align:center}
.caption{font-size:13.5px; color:var(--muted); margin-top:10px}

/* Passos */
ol.steps{list-style:none; padding:0; margin:18px 0 0; display:grid; gap:0; counter-reset:s}
ol.steps li{counter-increment:s; display:grid; grid-template-columns:40px minmax(0,1fr); gap:14px; padding:14px 0; border-top:1px solid var(--line)}
ol.steps li:last-child{border-bottom:1px solid var(--line)}
ol.steps li::before{content:counter(s); font-family:var(--f-display); font-weight:700; font-size:15px; width:32px; height:32px; border-radius:50%; display:grid; place-items:center; background:var(--accent-soft); color:var(--accent)}
ol.steps h3{margin:4px 0 2px}
ol.steps p{margin:0; color:var(--muted); font-size:15px}
.where{font-family:var(--f-mono); font-size:12.5px; color:var(--accent); background:var(--accent-soft); padding:1px 7px; border-radius:4px; white-space:nowrap}

/* Linha do tempo de Vigência */
.tl{overflow-x:auto; margin-top:16px; background:var(--surface); border:1px solid var(--line); border-radius:12px; padding:16px}
.tl-grid{display:grid; grid-template-columns:132px repeat(13, minmax(54px,1fr)); gap:4px; min-width:860px; font-size:12.5px}
.tl-grid .h{font-family:var(--f-mono); color:var(--muted); text-align:center; padding:4px 0}
.tl-grid .h.now{color:var(--accent); font-weight:500}
.tl-grid .row-l{font-weight:600; font-size:13px; display:flex; flex-direction:column; justify-content:center; line-height:1.25}
.tl-grid .row-l small{font-weight:400; color:var(--muted); font-size:11.5px}
.cell{border-radius:6px; padding:6px 4px; text-align:center; font-family:var(--f-mono); font-variant-numeric:tabular-nums; font-size:11.5px; line-height:1.3; min-height:40px; display:flex; flex-direction:column; justify-content:center}
.cell.out{background:var(--out-soft); color:var(--out)}
.cell.in{background:var(--in-soft); color:var(--in)}
.cell.empty{background:transparent; border:1px dashed var(--line); color:var(--muted)}
.cell.paid{outline:2px solid var(--out); outline-offset:-2px}
.cell.changed{background:var(--warn-soft); color:var(--warn)}
.cell small{display:block; font-size:10.5px; opacity:.85}
.legend{display:flex; flex-wrap:wrap; gap:14px; font-size:13px; color:var(--muted); margin-top:12px}
.legend i{display:inline-block; width:12px; height:12px; border-radius:3px; vertical-align:-1px; margin-right:6px}

/* Escopo */
.scope{display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:16px; margin-top:18px}
@media (max-width: 700px){ .scope{grid-template-columns:minmax(0,1fr)} }
.scope .card{background:var(--surface); border:1px solid var(--line); border-radius:10px; padding:18px}
.scope .card h3{display:flex; align-items:center; gap:8px}
.tag{font-family:var(--f-mono); font-size:11.5px; padding:2px 8px; border-radius:999px; background:var(--sunken); color:var(--muted); font-weight:500}
.scope dl{margin:10px 0 0; display:grid; grid-template-columns:auto minmax(0,1fr); gap:6px 12px; font-size:14.5px}
.scope dt{font-weight:600}
.scope dd{margin:0; color:var(--muted)}

.rules{margin-top:18px; border-left:3px solid var(--warn); background:var(--warn-soft); padding:14px 18px; border-radius:0 8px 8px 0}
.rules h3{color:var(--warn)}
.rules ul{margin:6px 0 0; padding-left:18px}
.rules li{margin:4px 0; max-width:68ch}

/* Tabela da Projeção */
.tbl{overflow-x:auto; margin-top:16px; border:1px solid var(--line); border-radius:12px; background:var(--surface)}
table{border-collapse:collapse; width:100%; min-width:560px; font-size:14px}
th,td{padding:10px 14px; text-align:right; border-bottom:1px solid var(--line); font-variant-numeric:tabular-nums}
th:first-child, td:first-child{text-align:left}
thead th{font-size:12px; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); font-weight:600; background:var(--sunken)}
tbody tr:last-child td{border-bottom:0}
td.mono{font-family:var(--f-mono)}
td .mk{display:inline-block; font-family:var(--f-body); font-size:11.5px; padding:1px 8px; border-radius:999px; margin-left:6px}
.mk.ganho{background:var(--in-soft); color:var(--in)}
.mk.atencao{background:var(--out-soft); color:var(--out)}

.alerts{display:grid; gap:8px; margin-top:16px}
.alert{display:grid; grid-template-columns:auto minmax(0,1fr); gap:12px; align-items:center; background:var(--surface); border:1px solid var(--line); border-radius:8px; padding:10px 14px; font-size:14.5px}
.alert .dot{width:10px; height:10px; border-radius:50%}
.alert.ganho .dot{background:var(--in)}
.alert.atencao .dot{background:var(--out)}

.formula{font-family:var(--f-mono); font-size:14px; background:var(--sunken); border-radius:8px; padding:12px 16px; margin:12px 0; overflow-x:auto; white-space:nowrap}

/* Outras telas */
.screens{display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:16px; margin-top:18px}
@media (max-width: 700px){ .screens{grid-template-columns:minmax(0,1fr)} }
.screen{border-top:2px solid var(--fg); padding-top:12px}
.screen p{font-size:14.5px; color:var(--muted); margin:0}

/* Glossário */
dl.gloss{display:grid; grid-template-columns:minmax(140px, 200px) minmax(0,1fr); gap:0; margin-top:16px; border-top:1px solid var(--line)}
dl.gloss dt, dl.gloss dd{padding:10px 0; border-bottom:1px solid var(--line); margin:0}
dl.gloss dt{font-weight:600; padding-right:16px}
dl.gloss dd{color:var(--muted); font-size:15px}
@media (max-width: 560px){ dl.gloss{grid-template-columns:minmax(0,1fr)} dl.gloss dt{border-bottom:0; padding-bottom:0} }

.note{font-size:14px; color:var(--muted); background:var(--sunken); border-radius:8px; padding:12px 16px; margin-top:16px; max-width:76ch}
</style>
</head>
<body>
<div class="wrap">
  <nav class="toc" aria-label="Sumário">
    <div class="brand"><span>J</span> Jefin Finances</div>
    <a class="back" href="{{ auth()->check() ? route('projecao.index') : route('auth.renderViewLogin') }}">&larr; {{ auth()->check() ? 'Voltar ao sistema' : 'Voltar ao login' }}</a>
    <ol>
      <li><a href="#inicio">A ideia</a></li>
      <li><a href="#fluxo">O caminho do dinheiro</a></li>
      <li><a href="#passos">Primeiros passos</a></li>
      <li><a href="#recorrencias">Fixas e Ocorrências</a></li>
      <li><a href="#alterar">Alterar um mês</a></li>
      <li><a href="#pagamento">Pagar uma Despesa</a></li>
      <li><a href="#projecao">Projeção</a></li>
      <li><a href="#telas">Outras telas</a></li>
      <li><a href="#glossario">Glossário</a></li>
    </ol>
  </nav>

  <main>
    <section id="inicio">
      <div class="eyebrow">Guia para quem está começando</div>
      <h1>Como o Jefin Finances organiza o seu dinheiro</h1>
      <p class="lead">O sistema responde a uma pergunta: quanto vai sobrar nos próximos meses? Para isso ele junta o que entra, o que sai todo mês e os gastos que aparecem de vez em quando.</p>

      <div class="pillars">
        <div class="pillar">
          <div class="k">1 · cadastre uma vez</div>
          <h3>O que se repete</h3>
          <p>Salário, aluguel, assinatura, parcelas do cartão. Você cadastra como <b>Provento Fixo</b> ou <b>Despesa Fixa</b> e o sistema preenche os meses.</p>
        </div>
        <div class="pillar">
          <div class="k">2 · registre quando surgir</div>
          <h3>O que é avulso</h3>
          <p>IPVA, presente, conserto. Vira uma <b>Despesa Variável</b> no mês do vencimento.</p>
        </div>
        <div class="pillar">
          <div class="k">3 · acompanhe</div>
          <h3>Os meses à frente</h3>
          <p>A <b>Projeção</b> mostra mês a mês quanto entra, quanto sai e o que muda no caminho.</p>
        </div>
      </div>
    </section>

    <section id="fluxo">
      <h2>O caminho do dinheiro</h2>
      <p>Tudo que você cadastra termina em um mês. Fixas geram um registro por mês (a <span class="term">Ocorrência</span>), avulsos entram direto no mês deles, e a Projeção lê esses meses.</p>
      <div class="figure">
<pre class="mermaid">
flowchart LR
  PF["Provento Fixo<br/>ex.: Salário"] -->|"gera um por mês"| P["Proventos<br/>do mês"]
  PA["Provento avulso<br/>ex.: freela"] --> P
  DF["Despesa Fixa<br/>ex.: Aluguel, Cartão 10x"] -->|"gera um por mês"| D["Despesas<br/>do mês"]
  DV["Despesa Variável<br/>ex.: IPVA"] --> D
  T["Tipos de Despesa<br/>Moradia, Transporte..."] -.->|"classifica"| DV
  D -->|"Declarar pagamento"| L["Lançamento<br/>(pago)"]
  P --> PR["Projeção"]
  D --> PR
  PR --> S["Saldo do Mês<br/>e Saldo Acumulado"]
  PR --> M["Marcos e Alertas"]
  L --> H["Home<br/>últimos lançamentos"]
</pre>
      </div>
      <p class="caption">Despesas pagas viram Lançamentos, que aparecem na Home. A Projeção conta todas as Despesas do mês, pagas ou não.</p>
    </section>

    <section id="passos">
      <h2>Primeiros passos</h2>
      <p>Uma ordem que funciona bem no primeiro uso. Todas as telas ficam no menu <b>Meu dinheiro</b>.</p>
      <ol class="steps">
        <li><div><h3>Crie sua conta e entre</h3><p>Pela tela de cadastro, ou com a conta Google. Cada usuário só vê os próprios dados.</p></div></li>
        <li><div><h3>Crie seus Tipos de Despesa</h3><p><span class="where">Despesas Tipos</span> Moradia, Transporte, Mercado… São as categorias das Despesas Variáveis. O tipo "Fixa" já existe e é reservado para as Despesas Fixas.</p></div></li>
        <li><div><h3>Cadastre o que entra todo mês</h3><p><span class="where">Proventos Fixos</span> Descrição, valor e mês de início. Sem mês de fim, vale por tempo indeterminado.</p></div></li>
        <li><div><h3>Cadastre o que sai todo mês</h3><p><span class="where">Despesas Fixas</span> Nome, valor, dia de vencimento e mês de início. Para parcelas, informe o número de parcelas ou o mês de fim: o sistema numera cada mês, como "Cartão (3/10)".</p></div></li>
        <li><div><h3>Registre os gastos avulsos</h3><p><span class="where">Despesas</span> Escolha o tipo e a data de vencimento. A Despesa conta no mês do vencimento, mesmo que você cadastre antes.</p></div></li>
        <li><div><h3>Marque o que pagou</h3><p><span class="where">Despesas</span> O botão de pagamento ao lado de cada Despesa declara que ela foi paga.</p></div></li>
        <li><div><h3>Olhe a Projeção</h3><p><span class="where">Projeção</span> Escolha 3, 6 ou 12 meses e veja o saldo de cada mês e os avisos do que muda.</p></div></li>
      </ol>
    </section>

    <section id="recorrencias">
      <h2>Fixas, Vigência e Ocorrências</h2>
      <p>Uma Despesa Fixa ou um Provento Fixo vale durante a sua <span class="term">Vigência</span>: um mês de início e, se houver, um mês de fim. O sistema grava antes a Ocorrência de cada mês, até <b>12 meses à frente</b> do mês atual. Por isso você pode ajustar um mês futuro antes de ele chegar.</p>
      <p>Quando o mês vira, a próxima tela que você abrir completa os meses que faltam. Um mês que você excluiu não volta.</p>

      <div class="tl" role="img" aria-label="Linha do tempo de novembro de 2026 a novembro de 2027 com três recorrências de exemplo">
        <div class="tl-grid">
          <div></div>
          <div class="h now">nov/26<br>hoje</div><div class="h">dez/26</div><div class="h">jan/27</div><div class="h">fev/27</div><div class="h">mar/27</div><div class="h">abr/27</div><div class="h">mai/27</div><div class="h">jun/27</div><div class="h">jul/27</div><div class="h">ago/27</div><div class="h">set/27</div><div class="h">out/27</div><div class="h">nov/27</div>

          <div class="row-l">Salário<small>Provento Fixo, sem fim</small></div>
          <div class="cell in">5.000</div><div class="cell in">5.000</div><div class="cell in">5.000</div><div class="cell in">5.000</div><div class="cell in">5.000</div><div class="cell in">5.000</div><div class="cell in">5.000</div><div class="cell in">5.000</div><div class="cell in">5.000</div><div class="cell in">5.000</div><div class="cell in">5.000</div><div class="cell in">5.000</div><div class="cell in">5.000</div>

          <div class="row-l">Aluguel<small>Despesa Fixa, sem fim, dia 10</small></div>
          <div class="cell out paid">1.800<small>paga</small></div><div class="cell out">1.800</div><div class="cell out">1.800</div><div class="cell out">1.800</div><div class="cell out">1.800</div><div class="cell out">1.800</div><div class="cell out">1.800</div><div class="cell out">1.800</div><div class="cell out">1.800</div><div class="cell out">1.800</div><div class="cell out">1.800</div><div class="cell out">1.800</div><div class="cell out">1.800</div>

          <div class="row-l">Cartão<small>Despesa Fixa, 6 parcelas</small></div>
          <div class="cell empty">–</div><div class="cell out">450<small>1/6</small></div><div class="cell out">450<small>2/6</small></div><div class="cell out">450<small>3/6</small></div><div class="cell out">450<small>4/6</small></div><div class="cell out">450<small>5/6</small></div><div class="cell out">450<small>6/6</small></div><div class="cell empty">–</div><div class="cell empty">–</div><div class="cell empty">–</div><div class="cell empty">–</div><div class="cell empty">–</div><div class="cell empty">–</div>
        </div>
        <div class="legend">
          <span><i style="background:var(--in-soft); border:1px solid var(--in)"></i>Ocorrência de Provento</span>
          <span><i style="background:var(--out-soft); border:1px solid var(--out)"></i>Ocorrência de Despesa</span>
          <span><i style="border:1px dashed var(--muted)"></i>fora da Vigência</span>
        </div>
      </div>
      <p class="caption">Exemplo com hoje em novembro de 2026. As fixas sem fim ficam gravadas até novembro de 2027. O cartão começa em dezembro e termina na 6ª parcela.</p>

      <h3 style="margin-top:22px">O dia do vencimento</h3>
      <p>Numa Despesa Fixa você escolhe um dia do mês. Se o mês for mais curto, vale o último dia: uma fixa do dia 31 vence em 28 de fevereiro.</p>

      <h3 style="margin-top:18px">Mudanças na fixa inteira</h3>
      <p>Na tela de edição da Despesa Fixa ou do Provento Fixo há duas ações:</p>
      <div class="scope">
        <div class="card">
          <h3>Reajustar</h3>
          <p style="margin:0; color:var(--muted); font-size:14.5px">Muda o <b>Valor Previsto</b> (e, na Despesa, o dia) a partir de um mês, que pode ser o atual ou um futuro. Os meses anteriores ficam como estavam. Exemplo: aluguel passa de <span class="money">R$ 1.800,00</span> para <span class="money">R$ 1.950,00</span> a partir de março.</p>
        </div>
        <div class="card">
          <h3>Encerrar</h3>
          <p style="margin:0; color:var(--muted); font-size:14.5px">Define o último mês da Vigência. Os meses depois dele são apagados e a janela não os recria. O Encerramento só encurta: para estender, cadastre uma nova fixa.</p>
        </div>
      </div>
    </section>

    <section id="alterar">
      <h2>Alterar ou excluir um mês</h2>
      <p>Ao editar ou excluir uma Ocorrência na tela de Despesas ou de Proventos, o sistema pergunta até onde a mudança vale.</p>
      <div class="scope">
        <div class="card">
          <h3>Só esta <span class="tag">um mês</span></h3>
          <dl>
            <dt>Editar</dt><dd>Muda só aquele mês. Bom para a conta de luz que veio diferente do previsto.</dd>
            <dt>Excluir</dt><dd>Some só aquele mês. A fixa continua nos outros e o mês excluído não é recriado.</dd>
          </dl>
        </div>
        <div class="card">
          <h3>Esta e as seguintes <span class="tag">daqui em diante</span></h3>
          <dl>
            <dt>Editar</dt><dd>Vira um <b>Reajuste</b>: o novo valor vale deste mês em diante, inclusive nos meses que você já tinha ajustado à mão.</dd>
            <dt>Excluir</dt><dd>Vira um <b>Encerramento</b>: a fixa termina no mês anterior.</dd>
          </dl>
        </div>
      </div>

      <div class="tl" role="img" aria-label="Exemplo de Reajuste a partir de fevereiro">
        <div class="tl-grid" style="grid-template-columns:132px repeat(6, minmax(70px,1fr)); min-width:560px">
          <div></div><div class="h now">nov/26</div><div class="h">dez/26</div><div class="h">jan/27</div><div class="h">fev/27</div><div class="h">mar/27</div><div class="h">abr/27</div>
          <div class="row-l">Antes<small>março ajustado à mão</small></div>
          <div class="cell out">1.800</div><div class="cell out">1.800</div><div class="cell out">1.800</div><div class="cell out">1.800</div><div class="cell changed">2.100<small>só esta</small></div><div class="cell out">1.800</div>
          <div class="row-l">Depois<small>fev "esta e as seguintes" → 1.950</small></div>
          <div class="cell out">1.800</div><div class="cell out">1.800</div><div class="cell out">1.800</div><div class="cell changed">1.950</div><div class="cell changed">1.950</div><div class="cell changed">1.950</div>
        </div>
      </div>

      <div class="rules">
        <h3>Quando "esta e as seguintes" é recusado</h3>
        <ul>
          <li><b>Mês passado.</b> Só vale a partir do mês atual. Um mês que já passou pode ser mudado só com "só esta".</li>
          <li><b>Despesa já paga.</b> Uma Ocorrência paga é histórico: Reajuste, Encerramento e troca de nome não mexem nela. Remova o pagamento antes, ou use Reajustar ou Encerrar na própria fixa.</li>
          <li><b>Troca de mês.</b> Para mover o vencimento para outro mês, use "só esta".</li>
        </ul>
      </div>
    </section>

    <section id="pagamento">
      <h2>Pagar uma Despesa</h2>
      <p>Na lista de Despesas, o botão ao lado de cada item declara o pagamento. A Despesa fica marcada como paga e o sistema cria um <span class="term">Lançamento</span> com a data e o valor, que aparece em "últimos lançamentos" na Home. Clicar de novo remove o pagamento e o Lançamento.</p>
      <p>No topo da lista aparecem o valor total do mês, quanto já foi pago e quanto falta pagar.</p>
      <div class="note">Uma Despesa conta no mês do seu vencimento (a <b>Competência</b>), e não no mês em que foi cadastrada ou paga. Um IPVA cadastrado em novembro com vencimento em 15/02/2027 conta em fevereiro.</div>
    </section>

    <section id="projecao">
      <h2>Projeção</h2>
      <p>A Projeção mostra o mês atual e mais 3, 6 ou 12 meses (o <span class="term">Horizonte</span>; o padrão é 6). Ela só lê o que está gravado, então os ajustes que você faz em um mês futuro aparecem nela.</p>
      <div class="formula">Saldo do Mês = Proventos − (Despesas Fixas + Despesas Variáveis)</div>
      <p>O <b>Saldo Acumulado</b> soma os Saldos do Mês a partir do mês atual. Ele não inclui o dinheiro que você já tem na conta hoje.</p>

      <div class="tbl">
        <table>
          <thead><tr><th>Mês</th><th>Proventos</th><th>Fixas</th><th>Variáveis</th><th>Saldo do Mês</th><th>Acumulado</th></tr></thead>
          <tbody>
            <tr><td>Novembro/2026 <span class="mk atencao" style="background:var(--accent-soft); color:var(--accent)">atual</span></td><td class="mono in">5.000,00</td><td class="mono">1.800,00</td><td class="mono">0,00</td><td class="mono">3.200,00</td><td class="mono">3.200,00</td></tr>
            <tr><td>Dezembro/2026 <span class="mk atencao">Cartão começa</span></td><td class="mono in">5.000,00</td><td class="mono">2.250,00</td><td class="mono">300,00</td><td class="mono">2.450,00</td><td class="mono">5.650,00</td></tr>
            <tr><td>Janeiro/2027</td><td class="mono in">5.000,00</td><td class="mono">2.250,00</td><td class="mono">0,00</td><td class="mono">2.750,00</td><td class="mono">8.400,00</td></tr>
            <tr><td>Fevereiro/2027</td><td class="mono in">5.000,00</td><td class="mono">2.250,00</td><td class="mono">1.200,00</td><td class="mono">1.550,00</td><td class="mono">9.950,00</td></tr>
          </tbody>
        </table>
      </div>
      <p class="caption">Exemplo com Salário, Aluguel e o Cartão da seção anterior, um presente de R$ 300 em dezembro e o IPVA em fevereiro.</p>

      <h3 style="margin-top:22px">Marcos e Alertas</h3>
      <p>Um <span class="term">Marco</span> é o mês em que uma fixa começa ou termina; ele aparece na linha daquele mês. Os <span class="term">Alertas</span> avisam, do mais próximo ao mais distante, o que vai mudar nos meses futuros:</p>
      <div class="alerts">
        <div class="alert atencao"><span class="dot"></span><span>em 1 mês começa Cartão: <span class="money out">-R$ 450,00</span></span></div>
        <div class="alert atencao"><span class="dot"></span><span>em 3 meses vence IPVA: <span class="money out">-R$ 1.200,00</span></span></div>
        <div class="alert ganho"><span class="dot"></span><span>em 6 meses termina Cartão (6/6): seu fluxo melhora em <span class="money in">R$ 450,00</span></span></div>
      </div>
      <p class="caption" style="margin-top:10px">Verde é ganho: uma Despesa Fixa que termina ou um Provento Fixo que começa. Terracota pede atenção: uma Despesa Fixa que começa, um Provento Fixo que termina ou uma Despesa Variável futura.</p>
    </section>

    <section id="telas">
      <h2>Outras telas</h2>
      <div class="screens">
        <div class="screen"><h3>Home</h3><p>Total já pago, total de proventos, há quantos dias você usa o sistema e os últimos lançamentos.</p></div>
        <div class="screen"><h3>Calculadora de Proventos</h3><p>Os Proventos de um mês, fixos e avulsos. O botão "Repetir proventos do mês anterior" copia os seus Proventos avulsos do mês anterior para o mês escolhido no filtro; os Proventos Fixos ficam de fora, porque já aparecem sozinhos. Para o que entra todo mês, prefira um Provento Fixo.</p></div>
        <div class="screen"><h3>Dívidas</h3><p>Controle à parte de dívidas parceladas: valor total, parcelas e "declarar pagamento" a cada parcela. As Dívidas não entram na Projeção. Para que uma parcela conte no saldo, cadastre-a também como Despesa Fixa com fim.</p></div>
        <div class="screen"><h3>Usuários e perfil</h3><p>Dados da conta e troca de senha.</p></div>
      </div>
    </section>

    <section id="glossario">
      <h2>Glossário</h2>
      <p>As palavras que o sistema usa, sempre com este sentido.</p>
      <dl class="gloss">
        <dt>Provento</dt><dd>Dinheiro que entra em um mês.</dd>
        <dt>Provento Fixo</dt><dd>Provento que se repete todo mês na Vigência, como o salário.</dd>
        <dt>Despesa</dt><dd>Um gasto que conta em um mês específico.</dd>
        <dt>Despesa Fixa</dt><dd>Gasto que se repete todo mês na Vigência. Parcelas são Despesas Fixas com fim.</dd>
        <dt>Despesa Variável</dt><dd>Despesa avulsa, que não veio de uma Despesa Fixa.</dd>
        <dt>Ocorrência</dt><dd>A Despesa ou o Provento de um mês gerado por uma fixa.</dd>
        <dt>Tipo de Despesa</dt><dd>A categoria de uma Despesa. "Fixa" é reservado às Despesas Fixas.</dd>
        <dt>Vencimento</dt><dd>A data em que você pretende pagar a Despesa.</dd>
        <dt>Competência</dt><dd>O mês em que a Despesa conta: o mês do Vencimento.</dd>
        <dt>Vigência</dt><dd>Mês de início e, se houver, mês de fim de uma fixa.</dd>
        <dt>Valor Previsto</dt><dd>O valor esperado de uma fixa a cada mês.</dd>
        <dt>Reajuste</dt><dd>Novo Valor Previsto a partir de um mês, sem mudar os anteriores.</dd>
        <dt>Encerramento</dt><dd>Definir o último mês da Vigência.</dd>
        <dt>Lançamento</dt><dd>O registro do pagamento de uma Despesa.</dd>
        <dt>Projeção</dt><dd>A visão mês a mês do que vai entrar e sair.</dd>
        <dt>Horizonte</dt><dd>Quantos meses à frente a Projeção mostra: 3, 6 ou 12.</dd>
        <dt>Saldo do Mês</dt><dd>Proventos menos Despesas de um mês.</dd>
        <dt>Saldo Acumulado</dt><dd>A soma dos Saldos do Mês desde o mês atual.</dd>
        <dt>Marco</dt><dd>Mês em que uma fixa começa ou termina.</dd>
        <dt>Alerta</dt><dd>Aviso de ganho ou de atenção sobre um mês futuro.</dd>
      </dl>
    </section>
  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/mermaid@10.9.1/dist/mermaid.min.js"></script>
<script>
    //O DIAGRAMA SEGUE O TEMA CLARO OU ESCURO DO SISTEMA OPERACIONAL
    mermaid.initialize({
        startOnLoad: true,
        theme: window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'neutral'
    });
</script>
</body>
</html>

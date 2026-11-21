# Ocorrências das recorrências são gravadas antecipadamente

Despesas Fixas e Proventos Fixos gravam suas Ocorrências de uma vez, para toda a Vigência, em vez de gerar cada mês só quando ele chega e calcular os meses futuros na hora. Assim a Projeção é apenas leitura dos registros, e cada mês futuro pode ser ajustado ou excluído individualmente; alterações e exclusões perguntam ao usuário se valem para "só esta" ou "esta e as seguintes" (o Reajuste e o Encerramento são esses dois casos).

## Consequences

- Recorrências sem fim gravam uma janela móvel de 12 meses à frente, completada quando o app é aberto. Só se acrescentam meses **após a última Ocorrência gravada**, para que uma Ocorrência excluída de propósito ("só esta") não seja recriada.
- "Esta e as seguintes" sobrescreve inclusive Ocorrências ajustadas manualmente; não se rastreia quais meses foram editados à mão.
- Não há geração retroativa: uma recorrência com início no passado só grava do mês atual em diante.
- Uma Despesa Fixa deixa de ficar "travada" após gerar Despesas; o histórico é preservado porque alterações só afetam a Ocorrência escolhida e as seguintes.

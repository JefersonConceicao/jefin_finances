-- Fim da ação "inativar" da Despesa Fixa (issue #7)
-- Banco: MySQL 5.7
-- A fixa inativa antiga vira uma fixa encerrada na última Ocorrência gravada (ou no mês de início),
-- e não grava mais Ocorrências. Para voltar a usá-la, cadastre uma nova Despesa Fixa.

UPDATE despesas_fixas
SET mes_fim = COALESCE(ocorrencias_ate, mes_inicio),
    ativo = 1
WHERE ativo = 0;

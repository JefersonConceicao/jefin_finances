-- Vencimento da Despesa (issue #4, ADR-0002)
-- Banco: MySQL 5.7
-- A Competência da Despesa passa a ser o mês do Vencimento.
-- Registros antigos ficam com NULL e continuam contando no mês de created_at.

ALTER TABLE despesas
    ADD COLUMN data_vencimento DATE NULL DEFAULT NULL AFTER valor_total;

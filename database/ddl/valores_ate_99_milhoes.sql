-- Limite de valor das Despesas, Despesas Fixas e Lançamentos (code review da spec #1)
-- Banco: MySQL 5.7
-- DECIMAL(6,2) só vai até R$ 9.999,99; um financiamento ou parcela maior falharia ao gravar.
-- O Lançamento copia o valor da Despesa paga, então acompanha o mesmo limite dos Proventos.

ALTER TABLE despesas MODIFY valor_total DECIMAL(10,2) NOT NULL;
ALTER TABLE despesas_fixas MODIFY valor DECIMAL(10,2) NOT NULL;
ALTER TABLE lancamentos MODIFY valor DECIMAL(10,2) NULL DEFAULT NULL;

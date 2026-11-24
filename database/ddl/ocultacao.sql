-- Ocultação de Despesa Fixa e Provento Fixo (issue #31)
-- Banco: MySQL 5.7
-- Oculta = 1: a recorrência e todas as suas Ocorrências somem de todo o app, sem excluir nem alterar nada.

ALTER TABLE despesas_fixas
    ADD COLUMN oculta TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 AFTER ativo;

ALTER TABLE proventos_fixos
    ADD COLUMN oculta TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 AFTER ativo;

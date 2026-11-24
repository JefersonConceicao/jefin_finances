-- Fim dos Tipos de Despesa (issue #26)
-- Banco: MySQL 5.7
-- Uma Despesa é só Fixa ou Variável. O tipo deixa de ser obrigatório;
-- a coluna, a FK e a tabela despesas_tipos ficam (nada é apagado).

ALTER TABLE despesas
    MODIFY despesa_tipo_id INT(10) UNSIGNED NULL DEFAULT NULL;

ALTER TABLE despesas_fixas
    MODIFY despesa_tipo_id INT(10) UNSIGNED NULL DEFAULT NULL;

-- Tipo de Despesa "Fixa" reservado (issue #5)
-- Banco: MySQL 5.7
-- Cada usuário tem um único tipo reservado, identificado pelo marcador e não pelo nome.

ALTER TABLE despesas_tipos
    ADD COLUMN reservado TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 AFTER ativo;

-- Reaproveita o tipo chamado exatamente "Fixa" (o de menor id) de cada usuário
UPDATE despesas_tipos t
JOIN (
    SELECT user_id, MIN(id) AS id
    FROM despesas_tipos
    WHERE LOWER(TRIM(nome)) = 'fixa'
    GROUP BY user_id
) x ON x.id = t.id
SET t.reservado = 1;

-- Cria o tipo reservado para quem tem Despesa Fixa e ainda não tem um
INSERT INTO despesas_tipos (nome, ativo, reservado, user_id)
SELECT DISTINCT 'Fixa', 1, 1, f.user_id
FROM despesas_fixas f
WHERE NOT EXISTS (
    SELECT 1 FROM despesas_tipos t WHERE t.user_id = f.user_id AND t.reservado = 1
);

-- Despesas Fixas e suas Ocorrências passam a usar o tipo reservado
UPDATE despesas_fixas f
JOIN despesas_tipos t ON t.user_id = f.user_id AND t.reservado = 1
SET f.despesa_tipo_id = t.id;

UPDATE despesas d
JOIN despesas_tipos t ON t.user_id = d.user_id AND t.reservado = 1
SET d.despesa_tipo_id = t.id
WHERE d.despesa_fixa_id IS NOT NULL;

-- Despesa Fixa com Vigência e Ocorrências gravadas (issue #6, ADR-0001)
-- Banco: MySQL 5.7

ALTER TABLE despesas_fixas
    ADD COLUMN mes_inicio DATE NULL DEFAULT NULL AFTER ativo,
    ADD COLUMN mes_fim DATE NULL DEFAULT NULL AFTER mes_inicio,
    ADD COLUMN dia_vencimento TINYINT(2) UNSIGNED NULL DEFAULT NULL AFTER mes_fim,
    ADD COLUMN ocorrencias_ate DATE NULL DEFAULT NULL AFTER dia_vencimento;

-- Fixas antigas: começam no mês em que foram cadastradas, sem fim, vencendo no dia do cadastro
UPDATE despesas_fixas
SET mes_inicio = DATE_FORMAT(created_at, '%Y-%m-01'),
    dia_vencimento = DAY(created_at)
WHERE mes_inicio IS NULL;

-- A última Ocorrência já gravada (Competência pelo Vencimento, ou cadastro nos registros antigos)
UPDATE despesas_fixas f
JOIN (
    SELECT despesa_fixa_id, DATE_FORMAT(MAX(COALESCE(data_vencimento, created_at)), '%Y-%m-01') AS ate
    FROM despesas
    WHERE despesa_fixa_id IS NOT NULL
    GROUP BY despesa_fixa_id
) d ON d.despesa_fixa_id = f.id
SET f.ocorrencias_ate = d.ate;

ALTER TABLE despesas_fixas
    MODIFY mes_inicio DATE NOT NULL,
    MODIFY dia_vencimento TINYINT(2) UNSIGNED NOT NULL;

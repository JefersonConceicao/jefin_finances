-- DDL para o módulo de despesas fixas (custo de vida basal)
-- Banco: MySQL 5.7
-- Sem uso de migrations

CREATE TABLE IF NOT EXISTS despesas_fixas (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT(10) UNSIGNED NOT NULL,
    nome_despesa VARCHAR(100) NOT NULL,
    despesa_tipo_id INT(10) UNSIGNED NOT NULL,
    valor DECIMAL(6,2) NOT NULL, -- mesmo limite de despesas.valor_total
    ativo TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    created_at DATETIME NULL DEFAULT NULL,
    updated_at DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_despesas_fixas_user_ativo (user_id, ativo),
    KEY idx_despesas_fixas_tipo (despesa_tipo_id),
    CONSTRAINT fk_despesas_fixas_user
        FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_despesas_fixas_tipo
        FOREIGN KEY (despesa_tipo_id) REFERENCES despesas_tipos(id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Vincula a despesa mensal gerada à despesa fixa de origem
ALTER TABLE despesas
    ADD COLUMN despesa_fixa_id INT(10) UNSIGNED NULL DEFAULT NULL AFTER despesa_tipo_id,
    ADD KEY idx_despesas_despesa_fixa (despesa_fixa_id),
    ADD CONSTRAINT fk_despesas_despesa_fixa
        FOREIGN KEY (despesa_fixa_id) REFERENCES despesas_fixas(id)
        ON DELETE SET NULL;

-- Módulo valor-hora descontinuado
-- DROP TABLE IF EXISTS valor_hora;

-- Provento Fixo com Ocorrências (issue #8, ADR-0001)
-- Banco: MySQL 5.7

CREATE TABLE IF NOT EXISTS proventos_fixos (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT(10) UNSIGNED NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    ativo TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    mes_inicio DATE NOT NULL,
    mes_fim DATE NULL DEFAULT NULL,
    ocorrencias_ate DATE NULL DEFAULT NULL,
    created_at DATETIME NULL DEFAULT NULL,
    updated_at DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_proventos_fixos_user_ativo (user_id, ativo),
    CONSTRAINT fk_proventos_fixos_user
        FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Liga o Provento (Ocorrência) ao Provento Fixo de origem.
-- O limite antigo (DECIMAL(6,2), até R$ 9.999,99) não comporta um salário maior; passa a DECIMAL(10,2).
ALTER TABLE proventos
    MODIFY valor_provento DECIMAL(10,2) NULL DEFAULT NULL,
    ADD COLUMN provento_fixo_id INT(10) UNSIGNED NULL DEFAULT NULL AFTER user_id,
    ADD KEY idx_proventos_provento_fixo (provento_fixo_id),
    ADD CONSTRAINT fk_proventos_provento_fixo
        FOREIGN KEY (provento_fixo_id) REFERENCES proventos_fixos(id)
        ON DELETE SET NULL;

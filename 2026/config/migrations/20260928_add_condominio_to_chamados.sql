ALTER TABLE chamados
    ADD COLUMN Condominio_idCondominio INT NULL DEFAULT NULL AFTER morador_idMorador,
    ADD INDEX fk_chamados_condominio_idx (Condominio_idCondominio ASC),
    ADD CONSTRAINT fk_chamados_condominio
        FOREIGN KEY (Condominio_idCondominio)
        REFERENCES condominio (idCondominio)
        ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE chamados c
JOIN (
    SELECT mu.Morador_idMorador,
           MIN(u.Condominio_idCondominio) AS Condominio_idCondominio
    FROM moradorunidade mu
    JOIN unidade u ON u.idUnidade = mu.Unidade_idUnidade
    WHERE mu.dataFim IS NULL
    GROUP BY mu.Morador_idMorador
    HAVING COUNT(DISTINCT u.Condominio_idCondominio) = 1
) vinculo ON vinculo.Morador_idMorador = c.morador_idMorador
SET c.Condominio_idCondominio = vinculo.Condominio_idCondominio
WHERE c.Condominio_idCondominio IS NULL;
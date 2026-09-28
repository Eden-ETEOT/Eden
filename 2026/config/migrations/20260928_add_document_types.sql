CREATE TABLE IF NOT EXISTS tipoDocumento (
    codigo VARCHAR(50) NOT NULL,
    nome   VARCHAR(100) NOT NULL,
    ativo  TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (codigo)
);

INSERT IGNORE INTO tipoDocumento (codigo, nome) VALUES
('ata', 'Ata'),
('convencao', CONVERT(0x436F6E76656EC3A7C3A36F USING utf8mb4)),
('contrato', 'Contrato'),
('emergencia', CONVERT(0x456D657267C3AA6E636961 USING utf8mb4)),
('financeiro', 'Financeiro'),
('laudo', 'Laudo'),
('manual', 'Manual'),
('outro', 'Outro'),
('regimento', 'Regimento'),
('seguro', 'Seguro');

INSERT IGNORE INTO tipoDocumento (codigo, nome)
SELECT DISTINCT tipo, CONCAT(UPPER(SUBSTRING(tipo, 1, 1)), SUBSTRING(tipo, 2))
FROM documentos;

ALTER TABLE documentos
    ADD INDEX tipo (tipo ASC),
    ADD CONSTRAINT documentos_tipo_ibfk
        FOREIGN KEY (tipo)
        REFERENCES tipoDocumento (codigo)
        ON DELETE RESTRICT ON UPDATE CASCADE;
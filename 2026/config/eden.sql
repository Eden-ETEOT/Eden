CREATE SCHEMA IF NOT EXISTS eden DEFAULT CHARACTER SET utf8mb4;
USE eden;


CREATE TABLE IF NOT EXISTS plano (
    idPlano         INT           NOT NULL AUTO_INCREMENT,
    nome            VARCHAR(50)   NOT NULL,
    descricao       TEXT          NULL DEFAULT NULL,
    valor           DECIMAL(10,2) NOT NULL,
    maxApartamentos INT           NOT NULL,
    funcionalidades TEXT          NOT NULL DEFAULT '',
    ativo           TINYINT(1)    NOT NULL DEFAULT 1,
    PRIMARY KEY (idPlano)
);


CREATE TABLE IF NOT EXISTS condominio (
    idCondominio  INT          NOT NULL AUTO_INCREMENT,
    CNPJ          CHAR(18)     NOT NULL,
    nome          VARCHAR(100) NOT NULL,
    foto          VARCHAR(500) NULL DEFAULT NULL,
    logradouro    VARCHAR(100) NOT NULL,
    numero        INT          NOT NULL,
    bairro        VARCHAR(100) NOT NULL,
    cidade        VARCHAR(100) NOT NULL,
    UF            CHAR(2)      NOT NULL,
    CEP           CHAR(9)      NOT NULL,
    telefone      VARCHAR(15)  NULL,
    email         VARCHAR(100) NULL,
    dataCadastro  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    ativo         TINYINT(1)   NOT NULL DEFAULT 1,
    Plano_idPlano INT          NOT NULL,
    PRIMARY KEY (idCondominio),
    UNIQUE INDEX CNPJ (CNPJ ASC),
    INDEX Plano_idPlano (Plano_idPlano ASC),
    CONSTRAINT condominio_ibfk_1
        FOREIGN KEY (Plano_idPlano)
        REFERENCES plano (idPlano)
);


CREATE TABLE IF NOT EXISTS areacomum (
    idAreaComum             INT           NOT NULL AUTO_INCREMENT,
    nome                    VARCHAR(100)  NOT NULL,
    descricao               TEXT          NULL DEFAULT NULL,
    capacidade              INT           NULL DEFAULT NULL,
    ativo                   TINYINT(1)    NOT NULL DEFAULT 1,
    Condominio_idCondominio INT           NOT NULL,
    PRIMARY KEY (idAreaComum),
    INDEX Condominio_idCondominio (Condominio_idCondominio ASC),
    CONSTRAINT areacomum_ibfk_1
        FOREIGN KEY (Condominio_idCondominio)
        REFERENCES condominio (idCondominio)
);


CREATE TABLE IF NOT EXISTS tipoDocumento (
    codigo VARCHAR(50) NOT NULL,
    nome   VARCHAR(100) NOT NULL,
    ativo  TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (codigo)
);


CREATE TABLE IF NOT EXISTS documentos (
    idDocumento             INT          NOT NULL AUTO_INCREMENT,
    nome                    VARCHAR(100) NOT NULL,
    tipo                    VARCHAR(50)  NOT NULL,
    caminho                 VARCHAR(500) NULL DEFAULT NULL,
    dataUpload              DATETIME     NULL DEFAULT CURRENT_TIMESTAMP(),
    publico                 TINYINT(1)   NOT NULL DEFAULT 1,
    Condominio_idCondominio INT          NOT NULL,
    PRIMARY KEY (idDocumento),
    INDEX Condominio_idCondominio (Condominio_idCondominio ASC),
    INDEX tipo (tipo ASC),
    CONSTRAINT documento_ibfk_1
        FOREIGN KEY (Condominio_idCondominio)
        REFERENCES condominio (idCondominio),
    CONSTRAINT documentos_tipo_ibfk
        FOREIGN KEY (tipo)
        REFERENCES tipoDocumento (codigo)
        ON DELETE RESTRICT ON UPDATE CASCADE
);


CREATE TABLE IF NOT EXISTS usuario (
    idUsuario   INT          NOT NULL AUTO_INCREMENT,
    email       VARCHAR(100) NOT NULL,
    senha       VARCHAR(255) NOT NULL,
    CPF         CHAR(14)     NOT NULL,
    telefone    VARCHAR(15)  NULL DEFAULT NULL,
    nome        VARCHAR(100) NOT NULL,
    foto        VARCHAR(500) NULL DEFAULT NULL,
    ativo       TINYINT(1)   NOT NULL DEFAULT 1,
    dataCriacao DATETIME     NULL DEFAULT CURRENT_TIMESTAMP(),
    PRIMARY KEY (idUsuario),
    UNIQUE INDEX email (email ASC),
    UNIQUE INDEX CPF (CPF ASC)
);


CREATE TABLE IF NOT EXISTS funcionario (
    idFuncionario INT          NOT NULL AUTO_INCREMENT,
    idUsuario     INT          NOT NULL,
    funcao        VARCHAR(100) NOT NULL,
    tipoVinculo   VARCHAR(100) NOT NULL,
    PRIMARY KEY (idFuncionario),
    INDEX idUsuario (idUsuario ASC),
    CONSTRAINT funcionario_ibfk_1
        FOREIGN KEY (idUsuario)
        REFERENCES usuario (idUsuario)
);


CREATE TABLE IF NOT EXISTS funcionariocondominio (
    idFuncionarioCondominio   INT  NOT NULL AUTO_INCREMENT,
    Funcionario_idFuncionario INT  NOT NULL,
    Condominio_idCondominio   INT  NOT NULL,
    dataAdmissao              DATE NOT NULL,
    dataDesligamento          DATE NULL DEFAULT NULL,
    inicioExpediente          TIME NULL DEFAULT NULL,
    fimExpediente             TIME NULL DEFAULT NULL,
    PRIMARY KEY (idFuncionarioCondominio),
    INDEX Funcionario_idFuncionario (Funcionario_idFuncionario ASC),
    INDEX Condominio_idCondominio (Condominio_idCondominio ASC),
    CONSTRAINT funcionariocondominio_ibfk_1
        FOREIGN KEY (Funcionario_idFuncionario)
        REFERENCES funcionario (idFuncionario),
    CONSTRAINT funcionariocondominio_ibfk_2
        FOREIGN KEY (Condominio_idCondominio)
        REFERENCES condominio (idCondominio)
);


CREATE TABLE IF NOT EXISTS morador (
    idMorador      INT          NOT NULL AUTO_INCREMENT,
    idUsuario      INT          NOT NULL,
    tipoMorador    ENUM('proprietario', 'inquilino', 'dependente') NOT NULL,
    dataNascimento DATE         NOT NULL,
    PRIMARY KEY (idMorador),
    INDEX idUsuario (idUsuario ASC),
    CONSTRAINT morador_ibfk_1
        FOREIGN KEY (idUsuario)
        REFERENCES usuario (idUsuario)
);


CREATE TABLE IF NOT EXISTS unidade (
    idUnidade               INT          NOT NULL AUTO_INCREMENT,
    numResid                VARCHAR(10)  NOT NULL,
    andar                   INT          NOT NULL,
    bloco                   VARCHAR(10)  NOT NULL,
    metragem                DECIMAL(8,2) NULL,
    qrCodePermanente        VARCHAR(255) NULL DEFAULT NULL,
    dataGeracaoQR           DATETIME     NULL DEFAULT NULL,
    ativo                   TINYINT(1)   NOT NULL DEFAULT 1,
    Condominio_idCondominio INT          NOT NULL,
    PRIMARY KEY (idUnidade),
    UNIQUE INDEX qrCodePermanente (qrCodePermanente ASC),
    INDEX Condominio_idCondominio (Condominio_idCondominio ASC),
    CONSTRAINT unidade_ibfk_1
        FOREIGN KEY (Condominio_idCondominio)
        REFERENCES condominio (idCondominio)
);


CREATE TABLE IF NOT EXISTS convite (
    idConvite         INT          NOT NULL AUTO_INCREMENT,
    token             CHAR(32)     NOT NULL,
    Unidade_idUnidade INT          NOT NULL,
    tipoMorador       ENUM('proprietario', 'inquilino', 'dependente') NOT NULL,
    emailEsperado     VARCHAR(100)  NULL,
    criadoPor         INT          NOT NULL,
    dataCriacao       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    dataExpiracao     DATETIME     NOT NULL,
    status            ENUM('pendente', 'usado', 'cancelado') NOT NULL DEFAULT 'pendente',
    dataUso           DATETIME     NULL,
    PRIMARY KEY (idConvite),
    UNIQUE INDEX token (token ASC),
    INDEX Unidade_idUnidade (Unidade_idUnidade ASC),
    INDEX criadoPor (criadoPor ASC),
    CONSTRAINT convite_ibfk_1
        FOREIGN KEY (Unidade_idUnidade)
        REFERENCES unidade (idUnidade),
    CONSTRAINT convite_ibfk_2
        FOREIGN KEY (criadoPor)
        REFERENCES usuario (idUsuario)
);



CREATE TABLE IF NOT EXISTS moradorunidade (
    idMoradorUnidade  INT  NOT NULL AUTO_INCREMENT,
    Morador_idMorador INT  NOT NULL,
    Unidade_idUnidade INT  NOT NULL,
    dataInicio        DATE NOT NULL,
    dataFim           DATE NULL DEFAULT NULL,
    PRIMARY KEY (idMoradorUnidade),
    INDEX Morador_idMorador (Morador_idMorador ASC),
    INDEX Unidade_idUnidade (Unidade_idUnidade ASC),
    CONSTRAINT moradorunidade_ibfk_1
        FOREIGN KEY (Morador_idMorador)
        REFERENCES morador (idMorador),
    CONSTRAINT moradorunidade_ibfk_2
        FOREIGN KEY (Unidade_idUnidade)
        REFERENCES unidade (idUnidade)
);


CREATE TABLE IF NOT EXISTS sindico (
    idSindico               INT NOT NULL AUTO_INCREMENT,
    idUsuario               INT NOT NULL,
    Condominio_idCondominio INT NOT NULL,
    PRIMARY KEY (idSindico),
    INDEX idUsuario (idUsuario ASC),
    INDEX Condominio_idCondominio (Condominio_idCondominio ASC),
    CONSTRAINT sindico_ibfk_1
        FOREIGN KEY (idUsuario)
        REFERENCES usuario (idUsuario),
    CONSTRAINT sindico_ibfk_2
        FOREIGN KEY (Condominio_idCondominio)
        REFERENCES condominio (idCondominio)
);


CREATE TABLE IF NOT EXISTS prioridade (
    idPrioridade INT         NOT NULL AUTO_INCREMENT,
    ordem        INT         NOT NULL,
    nome         VARCHAR(45) NOT NULL,
    descricao    TEXT        NOT NULL,
    PRIMARY KEY (idPrioridade)
);


CREATE TABLE IF NOT EXISTS categoria (
    idCategoria INT         NOT NULL AUTO_INCREMENT,
    nome        VARCHAR(45) NOT NULL,
    descricao   TEXT        NOT NULL,
    tipo        VARCHAR(45) NOT NULL,
    PRIMARY KEY (idCategoria)
);


CREATE TABLE IF NOT EXISTS chamados (
    idChamados               INT         NOT NULL AUTO_INCREMENT,
    dataPedida               DATETIME    NOT NULL,
    dataRealizada            DATETIME    NULL DEFAULT NULL,
    titulo                   VARCHAR(45) NOT NULL,
    descricao                TEXT        NOT NULL,
    privado                  TINYINT(1)  NOT NULL DEFAULT 0,
    status                   ENUM('analise', 'andamento', 'cancelada', 'resolvida') NOT NULL DEFAULT 'analise',
    prioridade_idPrioridade  INT         NOT NULL,
    funcionario_idFuncionario INT        NULL DEFAULT NULL,
    categoria_idCategoria    INT         NOT NULL,
    morador_idMorador        INT         NULL DEFAULT NULL,
    Condominio_idCondominio  INT         NULL DEFAULT NULL,
    PRIMARY KEY (idChamados),
    INDEX fk_chamados_prioridade1_idx (prioridade_idPrioridade ASC),
    INDEX fk_chamados_funcionario1_idx (funcionario_idFuncionario ASC),
    INDEX fk_chamados_categoria1_idx (categoria_idCategoria ASC),
    INDEX fk_chamados_morador1_idx (morador_idMorador ASC),
    INDEX fk_chamados_condominio_idx (Condominio_idCondominio ASC),
    CONSTRAINT fk_chamados_prioridade1
        FOREIGN KEY (prioridade_idPrioridade)
        REFERENCES prioridade (idPrioridade)
        ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT fk_chamados_funcionario1
        FOREIGN KEY (funcionario_idFuncionario)
        REFERENCES funcionario (idFuncionario)
        ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT fk_chamados_categoria1
        FOREIGN KEY (categoria_idCategoria)
        REFERENCES categoria (idCategoria)
        ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT fk_chamados_morador1
        FOREIGN KEY (morador_idMorador)
        REFERENCES morador (idMorador)
        ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT fk_chamados_condominio
        FOREIGN KEY (Condominio_idCondominio)
        REFERENCES condominio (idCondominio)
        ON DELETE SET NULL ON UPDATE CASCADE
);


CREATE TABLE IF NOT EXISTS mensagemChamado (
    idMensagemChamado   INT      NOT NULL AUTO_INCREMENT,
    chamados_idChamados INT      NOT NULL,
    usuario_idUsuario   INT      NOT NULL,
    conteudo            TEXT     NOT NULL,
    dataEnvio           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    PRIMARY KEY (idMensagemChamado),
    INDEX fk_mensagemChamado_chamados1_idx (chamados_idChamados ASC),
    INDEX fk_mensagemChamado_usuario1_idx (usuario_idUsuario ASC),
    CONSTRAINT fk_mensagemChamado_chamados1
        FOREIGN KEY (chamados_idChamados)
        REFERENCES chamados (idChamados)
        ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT fk_mensagemChamado_usuario1
        FOREIGN KEY (usuario_idUsuario)
        REFERENCES usuario (idUsuario)
        ON DELETE NO ACTION ON UPDATE NO ACTION
);


CREATE TABLE IF NOT EXISTS chamadoAnexo (
    idChamadoAnexo      INT          NOT NULL AUTO_INCREMENT,
    caminho             VARCHAR(500) NULL DEFAULT NULL,
    nomeArquivo         VARCHAR(100) NOT NULL,
    dataUpload          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    chamados_idChamados INT          NOT NULL,
    PRIMARY KEY (idChamadoAnexo),
    INDEX fk_ChamadoAnexo_chamados1_idx (chamados_idChamados ASC),
    CONSTRAINT fk_ChamadoAnexo_chamados1
        FOREIGN KEY (chamados_idChamados)
        REFERENCES chamados (idChamados)
        ON DELETE NO ACTION ON UPDATE NO ACTION
);

CREATE TABLE IF NOT EXISTS resetSenha (
    idResetSenha   INT          NOT NULL AUTO_INCREMENT,
    idUsuario      INT          NOT NULL,
    codigo         CHAR(6)      NOT NULL,
    dataCriacao    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    dataExpiracao  DATETIME     NOT NULL,
    verificado     TINYINT(1)   NOT NULL DEFAULT 0,
    usado          TINYINT(1)   NOT NULL DEFAULT 0,
    PRIMARY KEY (idResetSenha),
    INDEX idUsuario (idUsuario ASC),
    CONSTRAINT resetSenha_ibfk_1
        FOREIGN KEY (idUsuario)
        REFERENCES usuario (idUsuario)
        ON DELETE CASCADE
);

-- 10 registros por tabela, respeitando as FK do schema (BancoV1-2026.sql).
-- ==============================================================
-- PLANO (10)
-- ==============================================================
-- ==============================================================
-- SEEDS — só fazem sentido em banco novo (IDs literais 1-10).
-- ==============================================================

INSERT INTO plano (nome, descricao, valor, maxApartamentos, funcionalidades, ativo) VALUES
('Plano Básico',   'Plano inicial do condomínio',          0.00,   50,  'Gestão de moradores e ocorrências', 1),
('Plano Essencial','Para condomínios em crescimento',      99.90,  100, 'Ocorrências e áreas comuns', 1),
('Plano Conforto', 'Recursos extras de segurança',        149.90,  150, 'Ocorrências, segurança e documentos', 1),
('Plano Padrão',   'Pacote completo para condomínios médios', 199.90, 200, 'Gestão completa', 1),
('Plano Premium',  'Todos os recursos disponíveis',       299.90,  300, 'Gestão completa sem limites', 1),
('Plano Master',   'Para grandes condomínios',            399.90,  400, 'Suporte prioritário e gestão total', 1),
('Plano Empresarial', 'Condomínios e torres comerciais',  499.90,  500, 'Gestão empresarial', 1),
('Plano Familiar', 'Ideal para moradores familiares',     249.90,  250, 'Ocorrências e reservas', 1),
('Plano Comercial','Condomínios mistos',                  349.90,  350, 'Gestão mista completo', 1),
('Plano Personalizado', 'Sob consulta',                   599.90, 1000, 'Tudo personalizado', 1);

-- ==============================================================
-- CONDOMINIO (10)
-- ==============================================================
INSERT INTO condominio (CNPJ, nome, foto, logradouro, numero, bairro, cidade, UF, CEP, telefone, email, Plano_idPlano, ativo) VALUES
('12.345.678/0001-90', 'Residencial Jardim das Flores', NULL, 'Rua das Flores', 100, 'Centro', 'São Paulo', 'SP', '01001-000', '(11) 3000-0001', 'jardim@condo.com.br', 1, 1),
('23.456.789/0001-01', 'Condomínio Solaris',            NULL, 'Av. do Sol', 250, 'Jardim Paulista', 'São Paulo', 'SP', '01401-001', '(11) 3000-0002', 'solaris@condo.com.br', 1, 1),
('34.567.890/0001-12', 'Edifício Aurora',               NULL, 'Rua Aurora', 45, 'Vila Nova', 'Campinas', 'SP', '13010-100', '(19) 3255-0003', 'aurora@condo.com.br', 2, 1),
('45.678.901/0001-23', 'Residencial Vista Verde',       NULL, 'Rua Verde', 320, 'Parque Verde', 'Campinas', 'SP', '13040-200', '(19) 3255-0004', 'vista@condo.com.br', 2, 1),
('56.789.012/0001-34', 'Condomínio Horizonte Azul',     NULL, 'Av. Horizonte', 500, 'Centro', 'Osasco', 'SP', '06010-000', '(11) 3655-0005', 'horizonte@condo.com.br', 3, 1),
('67.890.123/0001-45', 'Torre das Palmeiras',           NULL, 'Rua das Palmeiras', 88, 'Alto da Boa Vista', 'Santos', 'SP', '11055-100', '(13) 3222-0006', 'palmeiras@condo.com.br', 3, 1),
('78.901.234/0001-56', 'Residencial Bella Vista',       NULL, 'Rua Bela Vista', 70, 'Centro', 'Guarulhos', 'SP', '07010-000', '(11) 2440-0007', 'bellavista@condo.com.br', 4, 1),
('89.012.345/0001-67', 'Condomínio Parque Central',     NULL, 'Av. Central', 900, 'Parque Central', 'São Bernardo do Campo', 'SP', '09750-000', '(11) 4358-0008', 'parque@condo.com.br', 4, 1),
('90.123.456/0001-78', 'Edifício Montreal',             NULL, 'Rua Montreal', 150, 'Centro', 'Santo André', 'SP', '09010-000', '(11) 4436-0009', 'montreal@condo.com.br', 5, 1),
('01.234.567/0001-89', 'Residencial Gran Ville',        NULL, 'Av. Gran Ville', 610, 'Gran Ville', 'São José dos Campos', 'SP', '12230-000', '(12) 3925-0010', 'granville@condo.com.br', 5, 1);

-- ==============================================================
-- AREA COMUM (10)
-- ==============================================================
INSERT INTO areacomum (nome, descricao, capacidade, ativo, Condominio_idCondominio) VALUES
('Piscina',          'Piscina adulto com raia',    50, 1, 1),
('Churrasqueira',    'Espaço com churrasqueira',   30, 1, 2),
('Salão de Festas',  'Salão com cozinha industrial', 80, 1, 3),
('Academia',         'Academia equipada',          25, 1, 4),
('Playground',       'Playground infantil',        40, 1, 5),
('Quadra Esportiva', 'Quadra poliesportiva',       20, 1, 6),
('Salão de Jogos',   'Jogos e sinuca',             35, 1, 7),
('Espaço Gourmet',   'Espaço gastronômico',        25, 1, 8),
('Brinquedoteca',    'Brinquedoteca infantil',     15, 1, 9),
('Sauna',            'Sauna a vapor',              10, 1, 10);

-- ==============================================================
-- DOCUMENTOS (10)
-- ==============================================================
INSERT INTO tipoDocumento (codigo, nome) VALUES
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

INSERT INTO documentos (nome, tipo, caminho, publico, Condominio_idCondominio) VALUES
('Regimento Interno',           'regimento',   NULL, 1, 1),
('Ata de Assembleia 2026',      'ata',         NULL, 1, 2),
('Convenção do Condomínio',     'convencao',   NULL, 1, 3),
('Prestação de Contas 2025',    'financeiro',  NULL, 1, 4),
('Manual do Morador',           'manual',      NULL, 1, 5),
('Laudo de Vistoria',           'laudo',       NULL, 1, 6),
('Orçamento 2026',              'financeiro',  NULL, 0, 7),
('Seguro do Condomínio',        'seguro',      NULL, 0, 8),
('Contrato de Manutenção',      'contrato',    NULL, 0, 9),
('Plano de Emergência',         'emergencia',  NULL, 1, 10);

-- ==============================================================
-- USUARIO (10) -- admin (id 1) com senha "1234"; demais também "1234"
-- ==============================================================
INSERT INTO usuario (idUsuario, email, senha, CPF, telefone, nome, foto, ativo) VALUES
(1, 'Sindico@eden.tcc', '$2y$12$LTbqMIsF5Y91xyxIz82UxuSlT/KznYnKS7W8vtk17QN3Rko0U/gGO', '999.888.777-66', NULL, 'Síndico', NULL, 1),
(2, 'gui.ferreira365@gmail.com', '$2y$10$NdpuEjcVhiuKMxIfG/Zm8uilPCrTCjCwaK.gN.TXh7ICOFdio0i5y', '000.000.000-00', NULL, 'adm', NULL, 1),
(3, 'maria.souza@teste.com',      '$2y$10$NdpuEjcVhiuKMxIfG/Zm8uilPCrTCjCwaK.gN.TXh7ICOFdio0i5y', '111.222.333-44', '(11) 98888-0001', 'Maria Souza', NULL, 1),
(4, 'joao.pereira@teste.com',     '$2y$10$NdpuEjcVhiuKMxIfG/Zm8uilPCrTCjCwaK.gN.TXh7ICOFdio0i5y', '222.333.444-55', '(11) 98888-0002', 'João Pereira', NULL, 1),
(5, 'ana.oliveira@teste.com',     '$2y$10$NdpuEjcVhiuKMxIfG/Zm8uilPCrTCjCwaK.gN.TXh7ICOFdio0i5y', '333.444.555-66', '(11) 98888-0003', 'Ana Oliveira', NULL, 1),
(6, 'carlos.santos@teste.com',    '$2y$10$NdpuEjcVhiuKMxIfG/Zm8uilPCrTCjCwaK.gN.TXh7ICOFdio0i5y', '444.555.666-77', '(11) 98888-0004', 'Carlos Santos', NULL, 1),
(7, 'julia.lima@teste.com',       '$2y$10$NdpuEjcVhiuKMxIfG/Zm8uilPCrTCjCwaK.gN.TXh7ICOFdio0i5y', '555.666.777-88', '(11) 98888-0005', 'Julia Lima', NULL, 1),
(8, 'pedro.almeida@teste.com',    '$2y$10$NdpuEjcVhiuKMxIfG/Zm8uilPCrTCjCwaK.gN.TXh7ICOFdio0i5y', '666.777.888-99', '(11) 98888-0006', 'Pedro Almeida', NULL, 1),
(9, 'fernanda.costa@teste.com',   '$2y$10$NdpuEjcVhiuKMxIfG/Zm8uilPCrTCjCwaK.gN.TXh7ICOFdio0i5y', '777.888.999-00', '(11) 98888-0007', 'Fernanda Costa', NULL, 1),
(10, 'lucas.martins@teste.com',    '$2y$10$NdpuEjcVhiuKMxIfG/Zm8uilPCrTCjCwaK.gN.TXh7ICOFdio0i5y', '888.999.000-11', '(11) 98888-0008', 'Lucas Martins', NULL, 1),
(11, 'beatriz.rocha@teste.com',    '$2y$10$NdpuEjcVhiuKMxIfG/Zm8uilPCrTCjCwaK.gN.TXh7ICOFdio0i5y', '999.000.111-22', '(11) 98888-0009', 'Beatriz Rocha', NULL, 1),
(101, 'usuario@usuario.usuario', '$2y$12$KPAZuSTcGOWcW8DGFzHom.MISJ5LJecsulzfBGRUxbe7Za028/ABq', '100.100.100-10', NULL, 'Usuario Comum', NULL, 1),
(102, 'sindico@sindico.sindico', '$2y$12$rbRQ1hlfFvikTgcT3r9pjuIXDFx.j/ZDH25.emIpAaSfooR13Xx8S', '200.200.200-20', NULL, 'Sindico Teste', NULL, 1),
(103, 'funcionario@funcionario.funcionario', '$2y$12$IJIwvkLgzyVOmrfwIV6VQecIWmG5dPsz4ykQidGhXdHCKLpFlr9Jq', '300.300.300-30', NULL, 'Funcionario Teste', NULL, 1);

-- ==============================================================
-- FUNCIONARIO (10)
-- ==============================================================
INSERT INTO funcionario (idUsuario, funcao, tipoVinculo) VALUES
(1,  'Administrador',          'efetivo'),
(2,  'Porteiro',               'CLT'),
(3,  'Zelador',                'CLT'),
(4,  'Síndico Profissional',   'terceirizado'),
(5,  'Técnico de Manutenção',  'terceirizado'),
(6,  'Jardineiro',             'CLT'),
(7,  'Segurança',              'terceirizado'),
(8,  'Recepcionista',          'CLT'),
(9,  'Auxiliar de Limpeza',    'CLT'),
(10, 'Eletricista',            'terceirizado'),
(103, 'Zelador',                  'CLT');

-- ==============================================================
-- FUNCIONARIOCONDOMINIO (10)
-- ==============================================================
INSERT INTO funcionariocondominio (Funcionario_idFuncionario, Condominio_idCondominio, dataAdmissao, dataDesligamento, inicioExpediente, fimExpediente) VALUES
(1, 1, '2024-01-01', NULL, '08:00:00', '18:00:00'),
(2, 2, '2024-02-01', NULL, '06:00:00', '14:00:00'),
(3, 3, '2024-03-01', NULL, '07:00:00', '16:00:00'),
(4, 4, '2024-04-01', NULL, '08:30:00', '17:30:00'),
(5, 5, '2024-05-01', NULL, '09:00:00', '18:00:00'),
(6, 6, '2024-06-01', NULL, '06:00:00', '15:00:00'),
(7, 7, '2024-07-01', NULL, '22:00:00', '06:00:00'),
(8, 8, '2024-08-01', NULL, '08:00:00', '17:00:00'),
(9, 9, '2024-09-01', '2025-09-01', '07:00:00', '16:00:00'),
(10, 10, '2025-01-01', NULL, '08:00:00', '17:00:00'),
(11, 1, '2026-01-01', NULL, '08:00:00', '18:00:00');

-- ==============================================================
-- MORADOR (10)
-- ==============================================================
INSERT INTO morador (idUsuario, tipoMorador, dataNascimento) VALUES
(2, 'proprietario', '1985-01-15'),
(3, 'inquilino',    '1990-11-02'),
(4, 'proprietario', '1982-03-08'),
(5, 'dependente',   '1980-07-25'),
(6, 'proprietario', '1995-05-20'),
(7, 'inquilino',    '1988-09-12'),
(8, 'proprietario', '1979-12-30'),
(9, 'dependente',   '2001-02-14'),
(10, 'inquilino',   '1992-06-05'),
(1,  'proprietario','1990-10-10');

-- ==============================================================
-- UNIDADE (10)
-- ==============================================================
INSERT INTO unidade (numResid, andar, bloco, metragem, qrCodePermanente, dataGeracaoQR, ativo, Condominio_idCondominio) VALUES
('101', 1, 'A', 62.50, 'eden-u-001', NOW(), 1, 1),
('102', 1, 'A', 58.00, 'eden-u-002', NOW(), 1, 2),
('201', 2, 'B', 75.00, 'eden-u-003', NOW(), 1, 3),
('202', 2, 'B', 80.50, 'eden-u-004', NOW(), 1, 4),
('301', 3, 'A', 65.00, 'eden-u-005', NOW(), 1, 5),
('302', 3, 'A', 60.00, 'eden-u-006', NOW(), 1, 6),
('401', 4, 'C', 90.00, 'eden-u-007', NOW(), 1, 7),
('402', 4, 'C', 85.00, 'eden-u-008', NOW(), 1, 8),
('501', 5, 'B', 70.00, 'eden-u-009', NOW(), 1, 9),
('502', 5, 'B', 72.00, 'eden-u-010', NOW(), 1, 10);

-- ==============================================================
-- MORADORUNIDADE (10) -- todas ativas (dataFim NULL)
-- ==============================================================
INSERT INTO moradorunidade (Morador_idMorador, Unidade_idUnidade, dataInicio, dataFim) VALUES
(1, 1, '2024-01-01', NULL),
(2, 2, '2024-02-01', NULL),
(3, 3, '2024-03-01', NULL),
(4, 4, '2024-04-01', NULL),
(5, 5, '2024-05-01', NULL),
(6, 6, '2024-06-01', NULL),
(7, 7, '2024-07-01', NULL),
(8, 8, '2024-08-01', NULL),
(9, 9, '2024-09-01', NULL),
(10, 10, '2024-10-01', NULL);

-- ==============================================================
-- SINDICO (10)
-- ==============================================================
INSERT INTO sindico (idUsuario, Condominio_idCondominio) VALUES
(2,  1),
(3,  2),
(4,  3),
(5,  4),
(6,  5),
(7,  6),
(8,  7),
(9,  8),
(10, 9),
(1, 10),
(102, 1);

-- ==============================================================
-- PRIORIDADE (10)
-- ==============================================================
INSERT INTO prioridade (ordem, nome, descricao) VALUES
(0,  'Indefinida', 'Prioridade ainda não definida'),
(2,  'Baixa',      'Pode aguardar programação'),
(3,  'Média',      'Resolver em breve'),
(7,  'Alta',       'Resolver rapidamente'),
(10, 'Urgente',    'Agravar com urgência');

-- ==============================================================
-- CATEGORIA (10)
-- ==============================================================
INSERT INTO categoria (nome, descricao, tipo) VALUES
('Vazamento',            'Vazamentos e infiltrações', 'manutencao'),
('Problema Elétrico',    'Falhas e riscos elétricos', 'manutencao'),
('Limpeza',              'Limpeza de áreas comuns',   'limpeza'),
('Segurança',            'Questões de segurança',     'seguranca'),
('Elevador',             'Problemas no elevador',     'manutencao'),
('Hidráulica',           'Questões hidráulicas',      'manutencao'),
('Estrutural',           'Questões estruturais',      'manutencao'),
('Paisagismo',           'Jardins e áreas verdes',    'manutencao'),
('Convivência',          'Reclamações de convivência','social'),
('Outros',               'Outros assuntos',           'diversos');

-- ==============================================================
-- CHAMADOS (10)
-- ==============================================================
INSERT INTO chamados (dataPedida, dataRealizada, titulo, descricao, privado, status, prioridade_idPrioridade, funcionario_idFuncionario, categoria_idCategoria, morador_idMorador) VALUES
('2026-09-01 09:10:00', NULL, 'Vazamento no banheiro',        'Água escorrendo do banheiro do 101', 0, 'analise',   7,  5,  1, 1),
('2026-09-01 10:00:00', NULL, 'Luz faltando no corredor',     'Lâmpada do corredor não acende',     0, 'andamento', 5,  10, 2, 2),
('2026-08-30 14:00:00', '2026-09-02 11:00:00', 'Limpeza da área comum', 'Necessário limpeza no hall',    0, 'resolvida', 2,  8,  3, 3),
('2026-09-03 08:30:00', NULL, 'Suspeita de arrombamento',     'Porta do 401 com sinais de arrombamento', 1, 'analise',  9,  7,  4, 4),
('2026-09-04 18:00:00', NULL, 'Elevador preso',               'Elevador B preso no 3º andar',        1, 'andamento', 10, 5,  5, 5),
('2026-09-05 07:00:00', NULL, 'Cano vazando na cozinha',      'Cano da cozinha do 302',              0, 'cancelada', 7,  NULL, 6, 6),
('2026-09-06 15:00:00', NULL, 'Trinca no muro',               'Trinca aparente no muro externo',     0, 'analise',   6,  5,  7, 7),
('2026-09-07 09:00:00', '2026-09-07 16:30:00', 'Jardim precisa de poda', 'Podas periódicas do jardim',  1, 'resolvida', 1,  6,  8, 8),
('2026-09-07 22:00:00', NULL, 'Som alto no apartamento',      'Barulho excessivo no 402',            0, 'andamento', 4,  NULL, 9, 9),
('2026-09-08 06:00:00', '2026-09-08 10:00:00', 'Troca de lâmpada no hall', 'Lâmpada queimada no hall',  0, 'resolvida', 3,  10, 2, 10);

-- ==============================================================
-- MENSAGEMCHAMADO (10)
-- ==============================================================
INSERT INTO mensagemChamado (chamados_idChamados, usuario_idUsuario, conteudo, dataEnvio) VALUES
(1, 2, 'Quando podem enviar o encanador?',  '2026-09-01 09:20:00'),
(1, 5, 'Encanador acionado.',              '2026-09-01 10:00:00'),
(2, 3, 'Lâmpada do corredor 2 queimada.',  '2026-09-01 10:10:00'),
(3, 4, 'Solicitação de limpeza recebida.', '2026-08-30 14:05:00'),
(4, 5, 'Segurança foi comunicada.',        '2026-09-03 08:45:00'),
(5, 6, 'Executando resgate no elevador.',  '2026-09-04 18:10:00'),
(6, 7, 'Cancelado pelo morador.',          '2026-09-05 07:30:00'),
(7, 8, 'Engenharia agendada.',             '2026-09-06 15:30:00'),
(8, 9, 'Poda realizada.',                  '2026-09-07 16:35:00'),
(9, 10, 'Notificação enviada ao morador.', '2026-09-07 22:05:00');

-- ==============================================================
-- CHAMADOANEXO (10)
-- ==============================================================
INSERT INTO chamadoAnexo (caminho, nomeArquivo, dataUpload, chamados_idChamados) VALUES
(NULL, 'foto-vazamento.jpg',   '2026-09-01 09:15:00', 1),
(NULL, 'foto-corredor.jpg',    '2026-09-01 10:05:00', 2),
(NULL, 'checklist-limpeza.pdf','2026-08-30 14:07:00', 3),
(NULL, 'foto-porta.jpg',       '2026-09-03 08:40:00', 4),
(NULL, 'boletim-elevador.pdf', '2026-09-04 18:12:00', 5),
(NULL, 'foto-cano.jpg',        '2026-09-05 07:05:00', 6),
(NULL, 'foto-muro.jpg',        '2026-09-06 15:10:00', 7),
(NULL, 'foto-jardim.jpg',      '2026-09-07 09:05:00', 8),
(NULL, 'audio-barulho.mp3',    '2026-09-07 22:02:00', 9),
(NULL, 'foto-hall.jpg',        '2026-09-08 06:05:00', 10);


-- ==============================================================
-- CONVITE (10)
-- ==============================================================
INSERT INTO convite (token, Unidade_idUnidade, tipoMorador, criadoPor, dataCriacao, dataExpiracao, status, dataUso) VALUES
('641820cf65dd705ceabcb08164e0e097', 1, 'proprietario', 2, NOW(), DATE_ADD(NOW(), INTERVAL 1 HOUR), 'pendente', NULL),
('f1da07d7ee5201bcd3266494895e629b', 2, 'inquilino', 3, NOW(), DATE_ADD(NOW(), INTERVAL 1 HOUR), 'pendente', NULL),
('8c76de424df986d67edbf50c3cea2e9e', 3, 'proprietario', 4, NOW(), DATE_ADD(NOW(), INTERVAL 1 HOUR), 'usado', NULL),
('c26baded1bf988dc919f569f27dd4128', 4, 'dependente', 5, NOW(), DATE_ADD(NOW(), INTERVAL 1 HOUR), 'pendente', NULL),
('d0e00175268d3b3dbb7f742d7b63263e', 5, 'proprietario', 6, NOW(), DATE_ADD(NOW(), INTERVAL 24 HOUR), 'pendente', NULL),
('4fa7fd9a6522212b1c132523d73459cb', 6, 'inquilino', 7, NOW(), DATE_ADD(NOW(), INTERVAL 24 HOUR), 'pendente', NULL),
('26cfd48d48d2f900b289ebc3578e95f8', 7, 'proprietario', 8, NOW(), DATE_ADD(NOW(), INTERVAL 24 HOUR), 'usado', NULL),
('dedb5edb616ef0d8ddc3be6bc391f3a0', 8, 'dependente', 9, NOW(), DATE_ADD(NOW(), INTERVAL 48 HOUR), 'pendente', NULL),
('5b6a0c70f5ec97256ea63938b59750e5', 9, 'proprietario', 10, NOW(), DATE_ADD(NOW(), INTERVAL 48 HOUR), 'pendente', NULL),
('d6345e0bc548161942245dfab340d597', 10, 'inquilino', 1, NOW(), DATE_ADD(NOW(), INTERVAL 48 HOUR), 'pendente', NULL);

-- ==============================================================

-- Backfill: condomínio dos chamados via vínculo ativo (equivale à migration).
UPDATE chamados c
JOIN (
    SELECT mu.Morador_idMorador, MIN(u.Condominio_idCondominio) AS Condominio_idCondominio
    FROM moradorunidade mu
    JOIN unidade u ON u.idUnidade = mu.Unidade_idUnidade
    WHERE mu.dataFim IS NULL
    GROUP BY mu.Morador_idMorador
    HAVING COUNT(DISTINCT u.Condominio_idCondominio) = 1
) vinculo ON vinculo.Morador_idMorador = c.morador_idMorador
SET c.Condominio_idCondominio = vinculo.Condominio_idCondominio
WHERE c.Condominio_idCondominio IS NULL;

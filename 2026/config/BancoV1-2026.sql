CREATE SCHEMA IF NOT EXISTS eden DEFAULT CHARACTER SET utf8mb4;
USE eden;


CREATE TABLE IF NOT EXISTS plano (
    idPlano         INT           NOT NULL AUTO_INCREMENT,
    nome            VARCHAR(50)   NOT NULL,
    descricao       TEXT          NULL DEFAULT NULL,
    valor           DECIMAL(10,2) NOT NULL,
    maxApartamentos INT           NOT NULL,
    funcionalidades TEXT          NOT NULL,
    ativo           TINYINT(1)    NOT NULL DEFAULT 1,
    PRIMARY KEY (idPlano)
);


CREATE TABLE IF NOT EXISTS condominio (
    idCondominio  INT          NOT NULL AUTO_INCREMENT,
    CNPJ          CHAR(18)     NOT NULL,
    nome          VARCHAR(100) NOT NULL,
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
    CONSTRAINT documento_ibfk_1
        FOREIGN KEY (Condominio_idCondominio)
        REFERENCES condominio (idCondominio)
);


CREATE TABLE IF NOT EXISTS usuario (
    idUsuario   INT          NOT NULL AUTO_INCREMENT,
    email       VARCHAR(100) NOT NULL,
    senha       VARCHAR(255) NOT NULL,
    CPF         CHAR(14)     NOT NULL,
    telefone    VARCHAR(15)  NULL DEFAULT NULL,
    nome        VARCHAR(100) NOT NULL,
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
    idSindico INT NOT NULL AUTO_INCREMENT,
    idUsuario INT NOT NULL,
    PRIMARY KEY (idSindico),
    INDEX idUsuario (idUsuario ASC),
    CONSTRAINT sindico_ibfk_1
        FOREIGN KEY (idUsuario)
        REFERENCES usuario (idUsuario)
);


CREATE TABLE IF NOT EXISTS prioridade (
    idprioridade INT         NOT NULL AUTO_INCREMENT,
    ordem        INT         NOT NULL,
    nome         VARCHAR(45) NOT NULL,
    descricao    TEXT        NOT NULL,
    PRIMARY KEY (idprioridade)
);


CREATE TABLE IF NOT EXISTS categoria (
    idcategoria INT         NOT NULL AUTO_INCREMENT,
    nome        VARCHAR(45) NOT NULL,
    descricao   TEXT        NOT NULL,
    tipo        VARCHAR(45) NOT NULL,
    PRIMARY KEY (idcategoria)
);


CREATE TABLE IF NOT EXISTS chamados (
    idchamados                INT         NOT NULL AUTO_INCREMENT,
    dataPedida                DATETIME    NOT NULL,
    dataRealizada             DATETIME    NULL DEFAULT NULL,
    titulo                    VARCHAR(45) NOT NULL,
    descricao                 TEXT        NOT NULL,
    privado                   TINYINT(1)  NOT NULL DEFAULT 0,
    status                    ENUM('analise', 'andamento', 'cancelada', 'resolvida') NOT NULL DEFAULT 'analise',
    prioridade_idprioridade   INT         NOT NULL,
    funcionario_idFuncionario INT         NOT NULL,
    categoria_idcategoria     INT         NOT NULL,
    morador_idMorador         INT         NOT NULL,
    PRIMARY KEY (idchamados),
    INDEX fk_chamados_prioridade1_idx (prioridade_idprioridade ASC),
    INDEX fk_chamados_funcionario1_idx (funcionario_idFuncionario ASC),
    INDEX fk_chamados_categoria1_idx (categoria_idcategoria ASC),
    INDEX fk_chamados_morador1_idx (morador_idMorador ASC),
    CONSTRAINT fk_chamados_prioridade1
        FOREIGN KEY (prioridade_idprioridade)
        REFERENCES prioridade (idprioridade)
        ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT fk_chamados_funcionario1
        FOREIGN KEY (funcionario_idFuncionario)
        REFERENCES funcionario (idFuncionario)
        ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT fk_chamados_categoria1
        FOREIGN KEY (categoria_idcategoria)
        REFERENCES categoria (idcategoria)
        ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT fk_chamados_morador1
        FOREIGN KEY (morador_idMorador)
        REFERENCES morador (idMorador)
        ON DELETE NO ACTION ON UPDATE NO ACTION
);


CREATE TABLE IF NOT EXISTS mensagemChamado (
    idmensagemChamado   INT      NOT NULL AUTO_INCREMENT,
    chamados_idchamados INT      NOT NULL,
    usuario_idUsuario   INT      NOT NULL,
    conteudo            TEXT     NOT NULL,
    dataEnvio           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    PRIMARY KEY (idmensagemChamado),
    INDEX fk_mensagemChamado_chamados1_idx (chamados_idchamados ASC),
    INDEX fk_mensagemChamado_usuario1_idx (usuario_idUsuario ASC),
    CONSTRAINT fk_mensagemChamado_chamados1
        FOREIGN KEY (chamados_idchamados)
        REFERENCES chamados (idchamados)
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
    chamados_idchamados INT          NOT NULL,
    PRIMARY KEY (idChamadoAnexo),
    INDEX fk_ChamadoAnexo_chamados1_idx (chamados_idchamados ASC),
    CONSTRAINT fk_ChamadoAnexo_chamados1
        FOREIGN KEY (chamados_idchamados)
        REFERENCES chamados (idchamados)
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
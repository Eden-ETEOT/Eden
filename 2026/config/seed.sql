-- Dados de semente (seed) do banco eden
-- Aplicar apenas em bancos novos: mysql -u root --skip-password eden < seed.sql

USE eden;

INSERT INTO usuario (email, senha, CPF, telefone, nome, ativo)
VALUES (
    'gui.ferreira365@gmail.com',
    '$2y$10$NdpuEjcVhiuKMxIfG/Zm8uilPCrTCjCwaK.gN.TXh7ICOFdio0i5y', -- hash de "1234"
    '000.000.000-00',
    NULL,
    'adm',
    1
);

INSERT INTO plano
(nome, descricao, valor, maxApartamentos, funcionalidades, ativo)
VALUES
(
  'Plano Básico',
  'Plano inicial do condomínio',
  0.00,
  50,
  'Gestão de moradores e ocorrências',
  1
);

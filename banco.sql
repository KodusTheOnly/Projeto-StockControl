CREATE SCHEMA cadastro_usuarios;
USE cadastro_usuarios;
SELECT * FROM usuarios;
CREATE TABLE usuarios (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  senha_hash VARCHAR(255) NOT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
);
-- Adicionando na tabela a coluna PERFIL, com 'ADMIN' e 'OPERADOR'
ALTER TABLE usuarios
  ADD COLUMN perfil ENUM('ADMIN','OPERADOR') NOT NULL DEFAULT 'OPERADOR';
-- Criando tabela de Tokens de recuperação
CREATE TABLE senha_tokens (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NOT NULL,
  token CHAR(64) NOT NULL UNIQUE,
  expira_em DATETIME NOT NULL,
  usado_em DATETIME NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (usuario_id),
  CONSTRAINT fk_tokens_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    ON DELETE CASCADE
);

-- ================== ESTOQUE DE PRODUTOS ==================
CREATE DATABASE IF NOT EXISTS estoque_produtos
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;
USE estoque_produtos;

CREATE TABLE IF NOT EXISTS produtos (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome VARCHAR(120) NOT NULL,
  categoria VARCHAR(100) NOT NULL,
  fornecedor VARCHAR(160) NOT NULL,
  lote VARCHAR(100) NOT NULL,
  validade DATE NOT NULL,
  quantidade INT UNSIGNED NOT NULL,
  qr_code_habilitado TINYINT(1) NOT NULL DEFAULT 0,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_produtos_nome_lote (nome, lote)
);

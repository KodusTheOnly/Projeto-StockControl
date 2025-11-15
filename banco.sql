-- BANCO DE DADOS: StockControl
CREATE SCHEMA StockControl;
USE StockControl;
-- MÓDULO DE AUTENTICAÇÃO E USUÁRIOS
-- Tabela de usuários do sistema
CREATE TABLE usuarios (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  senha_hash VARCHAR(255) NOT NULL,
  perfil ENUM('ADMIN','OPERADOR') NOT NULL DEFAULT 'OPERADOR',
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX idx_email (email),
  INDEX idx_perfil (perfil)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de tokens para recuperação de senha
CREATE TABLE senha_tokens (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NOT NULL,
  token CHAR(64) NOT NULL UNIQUE,
  expira_em DATETIME NOT NULL,
  usado_em DATETIME NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_usuario (usuario_id),
  INDEX idx_token (token),
  INDEX idx_expira (expira_em),
  CONSTRAINT fk_senha_tokens_usuario 
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- MÓDULO DE GESTÃO DE PRODUTOS E ESTOQUE
-- Tabela de produtos (informações gerais)
CREATE TABLE produtos (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome VARCHAR(120) NOT NULL,
  categoria VARCHAR(100) NOT NULL,
  fornecedor_padrao VARCHAR(160) NOT NULL,
  qr_code_habilitado TINYINT(1) NOT NULL DEFAULT 0,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_produtos_nome (nome),
  INDEX idx_categoria (categoria),
  INDEX idx_qr_code (qr_code_habilitado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Tabela de lotes (cada entrada de estoque)
CREATE TABLE lotes (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  produto_id INT UNSIGNED NOT NULL,
  lote VARCHAR(100) NOT NULL,
  fornecedor VARCHAR(160) NOT NULL,
  validade DATE NOT NULL,
  quantidade INT UNSIGNED NOT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_lotes_produto_lote (produto_id, lote),
  INDEX idx_produto (produto_id),
  INDEX idx_validade (validade),
  CONSTRAINT fk_lotes_produto 
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- MÓDULO DE GESTÃO DE FILIAIS
-- Tabela de filiais (RF08)
CREATE TABLE filiais (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome VARCHAR(150) NOT NULL,
  endereco VARCHAR(255) DEFAULT NULL,
  telefone VARCHAR(30) DEFAULT NULL,
  responsavel VARCHAR(120) DEFAULT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX idx_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*
RELACIONAMENTOS:
  1. usuarios (1) ←→ (N) senha_tokens
    - Um usuário pode ter vários tokens de recuperação
    - CASCADE: Se usuário for excluído, tokens também são

  2. produtos (1) ←→ (N) lotes
    - Um produto pode ter vários lotes no estoque
    - CASCADE: Se produto for excluído, lotes também são

  3. filiais
    - Tabela independente para gestão de filiais do supermercado

ÍNDICES:
  - Criados em campos de busca frequente (email, perfil, categoria, nome)
  - Otimizam consultas e melhoram performance

CHARSET:
  - utf8mb4: Suporte completo a caracteres especiais e emojis
*/

-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS diretoriomenotech CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE diretoriomenotech;

-- Tabela de profissionais
CREATE TABLE IF NOT EXISTS profissionais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    especialidade VARCHAR(255) NOT NULL,
    cidade VARCHAR(255) NOT NULL,
    estado VARCHAR(2) NOT NULL,
    instagram VARCHAR(255),
    telefone VARCHAR(20),
    whatsapp VARCHAR(20),
    site VARCHAR(255),
    foto VARCHAR(255),
    biografia TEXT,
    conselho_tipo VARCHAR(20),
    conselho_numero VARCHAR(30),
    rqe VARCHAR(30),
    atendimento VARCHAR(20),
    endereco VARCHAR(255),
    bairro VARCHAR(120),
    cep VARCHAR(10),
    certificado VARCHAR(255) DEFAULT 'Selo_MenoTech_Principal_Vinho.png',
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de admin
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    UNIQUE KEY username_unique (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir usuário admin padrão (senha: admin123)
-- Ou execute o arquivo setup_admin.php para configurar automaticamente
INSERT INTO admin (username, password) VALUES ('admin', '$2y$10$YourHashHere');

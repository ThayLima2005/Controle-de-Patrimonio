-- Tabela usuario
CREATE TABLE IF NOT EXISTS usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(11) UNIQUE NOT NULL,
    cargo VARCHAR(50),
    nome_usuario VARCHAR(50) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela departamento
CREATE TABLE IF NOT EXISTS departamento (
    departamento_id INT AUTO_INCREMENT PRIMARY KEY,
    nome_departamento VARCHAR(100) NOT NULL,
    responsavel VARCHAR(100),
    telefone VARCHAR(15),
    email VARCHAR(100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela fornecedor
CREATE TABLE IF NOT EXISTS fornecedor (
    fornecedor_id INT AUTO_INCREMENT PRIMARY KEY,
    razao_social VARCHAR(150) NOT NULL,
    cnpj VARCHAR(14) UNIQUE NOT NULL,
    cidade VARCHAR(50),
    cep VARCHAR(8),
    uf CHAR(2),
    bairro VARCHAR(50),
    endereco VARCHAR(150),
    numero VARCHAR(10),
    telefone VARCHAR(15),
    email VARCHAR(100),
    inscricao_municipal VARCHAR(15),
    inscricao_estadual VARCHAR(15)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela patrimonio
CREATE TABLE IF NOT EXISTS patrimonio (
    id_patrimonio INT AUTO_INCREMENT PRIMARY KEY,
    departamento_id INT,
    fornecedor_id INT,
    descricao TEXT NOT NULL,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
    garantia INT,
    marca VARCHAR(50),
    status_2 VARCHAR(50),
    num_patrimonio VARCHAR(50) UNIQUE NOT NULL,
    data_aquisicao DATE,
    valor_aquisicao DECIMAL(10, 2),
    nota_fiscal VARCHAR(20),
    FOREIGN KEY (departamento_id) REFERENCES departamento(departamento_id),
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedor(fornecedor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela transferencia
CREATE TABLE IF NOT EXISTS transferencia (
    id_transferencia INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    departamento_id INT NOT NULL COMMENT 'Departamento de origem',
    patrimonio_id INT NOT NULL,
    data_transferencia DATETIME DEFAULT CURRENT_TIMESTAMP,
    departamento_destino INT NOT NULL COMMENT 'Departamento destino',
    observacao TEXT,
    departamento_anterior VARCHAR(100) COMMENT 'Mantido como histórico',
    responsavel VARCHAR(100),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    FOREIGN KEY (departamento_id) REFERENCES departamento(departamento_id),
    FOREIGN KEY (patrimonio_id) REFERENCES patrimonio(id_patrimonio),
    FOREIGN KEY (departamento_destino) REFERENCES departamento(departamento_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
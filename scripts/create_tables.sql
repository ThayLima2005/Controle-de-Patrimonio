<<<<<<< HEAD
-- Tabela usuario (unificada com usuarios)
CREATE TABLE IF NOT EXISTS usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
=======
-- Tabela usuario
CREATE TABLE IF NOT EXISTS usuario (
    id_usuario SERIAL PRIMARY KEY,
>>>>>>> bd487f09ead85e150ce6fcd6d1e227f374995a36
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(11) UNIQUE NOT NULL,
    cargo VARCHAR(50),
    nome_usuario VARCHAR(50) UNIQUE NOT NULL,
<<<<<<< HEAD
    senha VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
=======
    senha VARCHAR(100) NOT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
>>>>>>> bd487f09ead85e150ce6fcd6d1e227f374995a36
);

-- Tabela departamento
CREATE TABLE IF NOT EXISTS departamento (
<<<<<<< HEAD
    departamento_id INT AUTO_INCREMENT PRIMARY KEY,
=======
    departamento_id SERIAL PRIMARY KEY,
>>>>>>> bd487f09ead85e150ce6fcd6d1e227f374995a36
    nome_departamento VARCHAR(100) NOT NULL,
    responsavel VARCHAR(100),
    telefone VARCHAR(15),
    email VARCHAR(100)
);

-- Tabela fornecedor
CREATE TABLE IF NOT EXISTS fornecedor (
<<<<<<< HEAD
    fornecedor_id INT AUTO_INCREMENT PRIMARY KEY,
=======
    fornecedor_id SERIAL PRIMARY KEY,
>>>>>>> bd487f09ead85e150ce6fcd6d1e227f374995a36
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
);

-- Tabela patrimonio
CREATE TABLE IF NOT EXISTS patrimonio (
<<<<<<< HEAD
    id_patrimonio INT AUTO_INCREMENT PRIMARY KEY,
    departamento_id INT,
    fornecedor_id INT,
    descricao TEXT NOT NULL,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
    garantia INT,
=======
    id_patrimonio SERIAL PRIMARY KEY,
    departamento_id INT REFERENCES departamento(departamento_id),
    fornecedor_id INT REFERENCES fornecedor(fornecedor_id),
    descricao TEXT NOT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    garantia INTEGER,
>>>>>>> bd487f09ead85e150ce6fcd6d1e227f374995a36
    marca VARCHAR(50),
    status_2 VARCHAR(50),
    num_patrimonio VARCHAR(50) UNIQUE NOT NULL,
    data_aquisicao DATE,
    valor_aquisicao DECIMAL(10, 2),
<<<<<<< HEAD
    nota_fiscal VARCHAR(20),
    FOREIGN KEY (departamento_id) REFERENCES departamento(departamento_id),
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedor(fornecedor_id)
);

-- Tabela transferencia
CREATE TABLE IF NOT EXISTS transferencia (
    id_transferencia INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    departamento_id INT NOT NULL COMMENT 'Departamento de origem',
    patrimonio_id INT NOT NULL,
    data_transferencia DATETIME DEFAULT CURRENT_TIMESTAMP,
    departamento_destino INT NOT NULL COMMENT 'Agora como INT, referenciando departamento_id',
    observacao TEXT,
    departamento_anterior VARCHAR(100) COMMENT 'Mantido como histórico',
    responsavel VARCHAR(100),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    FOREIGN KEY (departamento_id) REFERENCES departamento(departamento_id),
    FOREIGN KEY (patrimonio_id) REFERENCES patrimonio(id_patrimonio),
    FOREIGN KEY (departamento_destino) REFERENCES departamento(departamento_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
=======
    nota_fiscal VARCHAR(20) -- Novo campo para armazenar o número da nota fiscal
);
-- Tabela transferencia
CREATE TABLE IF NOT EXISTS transferencia (
    id_transferencia SERIAL PRIMARY KEY,
    id_usuario INT REFERENCES usuario(id_usuario),
    departamento_id INT REFERENCES departamento(departamento_id),
    patrimonio_id INT REFERENCES patrimonio(id_patrimonio),
    data_transferencia TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    departamento_destino VARCHAR(100),
    observacao TEXT,
    departamento_anterior VARCHAR(100),
    responsavel VARCHAR(100)
);
>>>>>>> bd487f09ead85e150ce6fcd6d1e227f374995a36

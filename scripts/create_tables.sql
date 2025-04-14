-- Tabela usuario
CREATE TABLE IF NOT EXISTS usuario (
    id_usuario SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(11) UNIQUE NOT NULL,
    cargo VARCHAR(50),
    nome_usuario VARCHAR(50) UNIQUE NOT NULL,
    senha VARCHAR(100) NOT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela departamento
CREATE TABLE IF NOT EXISTS departamento (
    departamento_id SERIAL PRIMARY KEY,
    nome_departamento VARCHAR(100) NOT NULL,
    responsavel VARCHAR(100),
    telefone VARCHAR(15),
    email VARCHAR(100)
);

-- Tabela fornecedor
CREATE TABLE IF NOT EXISTS fornecedor (
    fornecedor_id SERIAL PRIMARY KEY,
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
    id_patrimonio SERIAL PRIMARY KEY,
    departamento_id INT REFERENCES departamento(departamento_id),
    fornecedor_id INT REFERENCES fornecedor(fornecedor_id),
    descricao TEXT NOT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    garantia INTEGER,
    marca VARCHAR(50),
    status_2 VARCHAR(50),
    num_patrimonio VARCHAR(50) UNIQUE NOT NULL,
    data_aquisicao DATE,
    valor_aquisicao DECIMAL(10, 2),
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

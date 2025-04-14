const express = require('express');
const bodyParser = require('body-parser');
const path = require('path');
const cors = require('cors');
const { Client } = require('pg');

const app = express();
const port = 3003;

// Conectar ao banco de dados PostgreSQL
const client = new Client({
  user: 'postgres', // Seu usuário do PostgreSQL
  host: 'localhost',
  database: 'patrimonio', // Nome da base de dados
  password: '261217', // Sua senha do PostgreSQL
  port:5434,
});

client.connect()
    .then(() => console.log('Conectado ao banco de dados...'))
    .catch((err) => console.error('Erro ao conectar ao banco de dados', err));

// Middleware
app.use(cors());
app.use(bodyParser.json());

// Servir a página de login como a página inicial
app.get('/', (req, res) => {
  res.sendFile(path.join(__dirname, 'view', 'login.html'));
});

// Servir arquivos estáticos (incluindo index.html e outros arquivos)
app.use(express.static(path.join(__dirname, 'view')));

// Rota de login
app.post('/login', async (req, res) => {
  const { email, password } = req.body;
  try {
    const result = await client.query('SELECT * FROM usuario WHERE nome_usuario = $1 AND senha = $2', [email, password]);
    if (result.rows.length > 0) {
      res.json({ success: true });
    } else {
      res.status(401).json({ success: false, message: 'Credenciais inválidas' });
    }
  } catch (err) {
    console.error(err);
    res.status(500).json({ success: false, message: 'Erro no servidor' });
  }
});

// Rota de registro para novos usuários
app.post('/register', async (req, res) => {
  const { nome, cpf, cargo, email, password } = req.body;
  const dataCadastro = new Date();

  if (!nome || !cpf || !cargo || !email || !password) {
    return res.status(400).json({ success: false, message: 'Todos os campos são obrigatórios.' });
  }

  try {
    const userExists = await client.query('SELECT * FROM usuario WHERE nome_usuario = $1', [email]);
    if (userExists.rows.length > 0) {
      return res.status(409).json({ success: false, message: 'O nome de usuário já existe.' });
    }

    const result = await client.query(
        'INSERT INTO usuario (nome, cpf, cargo, nome_usuario, senha, data_cadastro) VALUES ($1, $2, $3, $4, $5, $6) RETURNING *',
        [nome, cpf, cargo, email, password, dataCadastro]
    );
    res.status(201).json({ success: true, data: result.rows[0] });
  } catch (err) {
    console.error('Erro ao registrar usuário:', err);
    res.status(500).json({ success: false, message: 'Erro ao registrar usuário.' });
  }
});

// Rotas para operações com fornecedor
app.get('/fornecedor', async (req, res) => {
  try {
    const result = await client.query('SELECT * FROM fornecedor');
    res.json(result.rows);
  } catch (err) {
    console.error(err);
    res.status(500).json({ success: false, message: 'Erro ao listar fornecedores' });
  }
});

app.post('/fornecedor', async (req, res) => {
  const { razao_social, cnpj, cidade, cep, uf, bairro, endereco, numero, telefone, email, inscricao_municipal, inscricao_estadual } = req.body;
  try {
    const result = await client.query(
        'INSERT INTO fornecedor (razao_social, cnpj, cidade, cep, uf, bairro, endereco, numero, telefone, email, inscricao_municipal, inscricao_estadual) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12) RETURNING *',
        [razao_social, cnpj, cidade, cep, uf, bairro, endereco, numero, telefone, email, inscricao_municipal, inscricao_estadual]
    );
    res.json({ success: true, data: result.rows[0] });
  } catch (err) {
    console.error(err);
    res.status(500).json({ success: false, message: 'Erro ao adicionar fornecedor' });
  }
});

app.put('/fornecedor/:id', async (req, res) => {
  const { id } = req.params;
  const { razao_social, cnpj, cidade, cep, uf, bairro, endereco, numero, telefone, email, inscricao_municipal, inscricao_estadual } = req.body;
  try {
    const result = await client.query(
        'UPDATE fornecedor SET razao_social = $1, cnpj = $2, cidade = $3, cep = $4, uf = $5, bairro = $6, endereco = $7, numero = $8, telefone = $9, email = $10, inscricao_municipal = $11, inscricao_estadual = $12 WHERE id_fornecedor = $13 RETURNING *',
        [razao_social, cnpj, cidade, cep, uf, bairro, endereco, numero, telefone, email, inscricao_municipal, inscricao_estadual, id]
    );
    if (result.rowCount > 0) {
      res.json({ success: true, data: result.rows[0] });
    } else {
      res.status(404).json({ success: false, message: 'Fornecedor não encontrado' });
    }
  } catch (err) {
    console.error(err);
    res.status(500).json({ success: false, message: 'Erro ao atualizar fornecedor' });
  }
});

app.delete('/fornecedor/:id', async (req, res) => {
  const { id } = req.params;
  try {
    const result = await client.query('DELETE FROM fornecedor WHERE id_fornecedor = $1 RETURNING *', [id]);
    if (result.rowCount > 0) {
      res.json({ success: true });
    } else {
      res.status(404).json({ success: false, message: 'Fornecedor não encontrado' });
    }
  } catch (err) {
    console.error(err);
    res.status(500).json({ success: false, message: 'Erro ao excluir fornecedor' });
  }
});

// Rotas para operações com departamento
app.post('/departamento', async (req, res) => {
  const { nome_departamento, responsavel, telefone, email } = req.body;
  try {
    const result = await client.query(
        'INSERT INTO departamento (nome_departamento, responsavel, telefone, email) VALUES ($1, $2, $3, $4) RETURNING *',
        [nome_departamento, responsavel, telefone, email]
    );
    res.json({ success: true, data: result.rows[0] });
  } catch (err) {
    console.error(err);
    res.status(500).json({ success: false, message: 'Erro ao adicionar departamento' });
  }
});

app.get('/departamento', async (req, res) => {
  try {
    const result = await client.query('SELECT * FROM departamento');
    res.json({ departamentos: result.rows });
  } catch (err) {
    console.error(err);
    res.status(500).json({ success: false, message: 'Erro ao listar departamentos' });
  }
});

app.put('/departamento/:id', async (req, res) => {
  const { id } = req.params;
  const { nome_departamento, responsavel, telefone, email } = req.body;
  try {
    const result = await client.query(
        'UPDATE departamento SET nome_departamento = $1, responsavel = $2, telefone = $3, email = $4 WHERE departamento_id = $5 RETURNING *',
        [nome_departamento, responsavel, telefone, email, id]
    );
    if (result.rowCount > 0) {
      res.json({ success: true, data: result.rows[0] });
    } else {
      res.status(404).json({ success: false, message: 'Departamento não encontrado' });
    }
  } catch (err) {
    console.error(err);
    res.status(500).json({ success: false, message: 'Erro ao atualizar departamento' });
  }
});

app.delete('/departamento/:id', async (req, res) => {
  const { id } = req.params;
  try {
    const result = await client.query('DELETE FROM departamento WHERE departamento_id = $1 RETURNING *', [id]);
    if (result.rowCount > 0) {
      res.json({ success: true });
    } else {
      res.status(404).json({ success: false, message: 'Departamento não encontrado' });
    }
  } catch (err) {
    console.error(err);
    res.status(500).json({ success: false, message: 'Erro ao excluir departamento' });
  }
});

// Rotas para operações com patrimônio
app.get('/patrimonio', async (req, res) => {
  try {
    const result = await client.query('SELECT * FROM patrimonio');
    res.json({ patrimonios: result.rows });
  } catch (err) {
    console.error(err);
    res.status(500).json({ success: false, message: 'Erro ao listar patrimônios' });
  }
});

app.post('/patrimonio', async (req, res) => {
  const { descricao, departamento_id, fornecedor_id, data_aquisicao, valor_aquisicao, garantia, marca, status_2, num_patrimonio, nota_fiscal } = req.body;
  const dataCadastro = new Date();
  try {
    const result = await client.query(
        'INSERT INTO patrimonio (descricao, departamento_id, fornecedor_id, data_aquisicao, valor_aquisicao, garantia, marca, status_2, num_patrimonio, data_cadastro, nota_fiscal) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11) RETURNING *',
        [descricao, departamento_id, fornecedor_id, data_aquisicao, valor_aquisicao, garantia, marca, status_2, num_patrimonio, dataCadastro, nota_fiscal]
    );
    res.json({ success: true, data: result.rows[0] });
  } catch (err) {
    console.error(err);
    res.status(500).json({ success: false, message: 'Erro ao adicionar patrimônio' });
  }
});

app.put('/patrimonio/:id', async (req, res) => {
  const { id } = req.params;
  const { descricao, departamento_id, fornecedor_id, data_aquisicao, valor_aquisicao, garantia, marca, status_2, num_patrimonio, nota_fiscal } = req.body;
  try {
    const result = await client.query(
        'UPDATE patrimonio SET descricao = $1, departamento_id = $2, fornecedor_id = $3, data_aquisicao = $4, valor_aquisicao = $5, garantia = $6, marca = $7, status_2 = $8, num_patrimonio = $9, nota_fiscal = $10 WHERE id_patrimonio = $11 RETURNING *',
        [descricao, departamento_id, fornecedor_id, data_aquisicao, valor_aquisicao, garantia, marca, status_2, num_patrimonio, nota_fiscal, id]
    );
    if (result.rowCount > 0) {
      res.json({ success: true, data: result.rows[0] });
    } else {
      res.status(404).json({ success: false, message: 'Patrimônio não encontrado' });
    }
  } catch (err) {
    console.error(err);
    res.status(500).json({ success: false, message: 'Erro ao atualizar patrimônio' });
  }
});

app.delete('/patrimonio/:id', async (req, res) => {
  const { id } = req.params;
  try {
    // Primeiro, deletar dependências na tabela 'transferencia'
    await client.query('DELETE FROM transferencia WHERE patrimonio_id = $1', [id]);

    // Em seguida, deletar o patrimônio
    const result = await client.query('DELETE FROM patrimonio WHERE id_patrimonio = $1 RETURNING *', [id]);

    if (result.rowCount > 0) {
      console.log(`Patrimônio ${id} excluído com sucesso.`);
      res.json({ success: true });
    } else {
      console.log(`Patrimônio com ID ${id} não encontrado.`);
      res.status(404).json({ success: false, message: 'Patrimônio não encontrado' });
    }
  } catch (err) {
    console.error('Erro ao excluir o patrimônio:', err);
    res.status(500).json({ success: false, message: 'Erro ao excluir patrimônio' });
  }
});

app.get('/transfer', async (req, res) => {
  try {
    const result = await client.query('SELECT * FROM transferencia');
    res.json(result.rows);
  } catch (err) {
    console.error(err);
    res.status(500).json({ success: false, message: 'Erro ao listar transferências' });
  }
});

app.post('/transfer', async (req, res) => {
  try {
    const {
      id_usuario,
      departamento_id,
      patrimonio_id,
      data_transferencia,
      departamento_destino,
      observacao,
      departamento_anterior,
      responsavel
    } = req.body;

    // Inserção na tabela `transferencia` com as colunas corretas
    const result = await client.query(
        'INSERT INTO transferencia (id_usuario, departamento_id, patrimonio_id, data_transferencia, departamento_destino, observacao, departamento_anterior, responsavel) VALUES ($1, $2, $3, $4, $5, $6, $7, $8) RETURNING *',
        [id_usuario, departamento_id, patrimonio_id, data_transferencia, departamento_destino, observacao, departamento_anterior, responsavel]
    );

    res.status(201).json({ success: true, data: result.rows[0] });
  } catch (error) {
    console.error('Erro ao adicionar transferência:', error);
    res.status(500).json({ success: false, message: error.message });
  }
});

app.put('/transfer/:id', async (req, res) => {
  const { id } = req.params;
  const { departamento_atual, departamento_destino, patrimonio_id, responsavel, data_transferencia, observacao } = req.body;
  try {
    const result = await client.query(
        'UPDATE transferencia SET departamento_atual = $1, departamento_destino = $2, patrimonio_id = $3, responsavel = $4, data_transferencia = $5, observacao = $6 WHERE id_transferencia = $7 RETURNING *',
        [departamento_atual, departamento_destino, patrimonio_id, responsavel, data_transferencia, observacao, id]
    );
    if (result.rowCount > 0) {
      res.json({ success: true, data: result.rows[0] });
    } else {
      res.status(404).json({ success: false, message: 'Transferência não encontrada' });
    }
  } catch (err) {
    console.error(err);
    res.status(500).json({ success: false, message: 'Erro ao atualizar transferência' });
  }
});

app.get('/transfer', async (req, res) => {
    const { startDate, endDate } = req.query;
    try {
        let query = 'SELECT * FROM transferencia';
        const params = [];

        if (startDate || endDate) {
            query += ' WHERE 1=1';
            if (startDate) {
                params.push(startDate);
                query += ` AND data_transferencia >= $${params.length}`;
            }
            if (endDate) {
                params.push(endDate);
                query += ` AND data_transferencia <= $${params.length}`;
            }
        }

        const result = await client.query(query, params);
        res.json(result.rows);
    } catch (err) {
        console.error('Erro ao buscar transferências:', err);
        res.status(500).json({ success: false, message: 'Erro ao buscar transferências' });
    }
});


app.delete('/transfer/:id', async (req, res) => {
  const { id } = req.params;
  try {
    const result = await client.query('DELETE FROM transferencia WHERE id_transferencia = $1 RETURNING *', [id]);
    if (result.rowCount > 0) {
      res.json({ success: true });
    } else {
      res.status(404).json({ success: false, message: 'Transferência não encontrada' });
    }
  } catch (err) {
    console.error(err);
    res.status(500).json({ success: false, message: 'Erro ao excluir transferência' });
  }
});

// Rotas para buscar dados de departamentos, patrimônios e usuários
app.get('/departamentos', async (req, res) => {
  try {
    const result = await client.query('SELECT * FROM departamento');
    res.json(result.rows);
  } catch (err) {
    console.error('Erro ao buscar departamentos:', err);
    res.status(500).json({ success: false, message: 'Erro ao buscar departamentos' });
  }
});

app.get('/patrimonios', async (req, res) => {
  try {
    const result = await client.query('SELECT * FROM patrimonio');
    res.json(result.rows);
  } catch (err) {
    console.error('Erro ao buscar patrimônios:', err);
    res.status(500).json({ success: false, message: 'Erro ao buscar patrimônios' });
  }
});

app.get('/usuarios', async (req, res) => {
  try {
    const result = await client.query('SELECT * FROM usuario');
    res.json(result.rows);
  } catch (err) {
    console.error('Erro ao buscar usuários:', err);
    res.status(500).json({ success: false, message: 'Erro ao buscar usuários' });
  }
});

// Rotas para servir outras páginas HTML
app.get('/index.html', (req, res) => {
  res.sendFile(path.join(__dirname, 'view', 'index.html'));
});

app.get('/acquisition.html', (req, res) => {
  res.sendFile(path.join(__dirname, 'view', 'acquisition.html'));
});

app.get('/departamento.html', (req, res) => {
  res.sendFile(path.join(__dirname, 'view', 'departamento.html'));
});

app.get('/fornecedor.html', (req, res) => {
  res.sendFile(path.join(__dirname, 'view', 'fornecedor.html'));
});

app.get('/patrimonio.html', (req, res) => {
  res.sendFile(path.join(__dirname, 'view', 'patrimonio.html'));
});

app.get('/transfer.html', (req, res) => {
  res.sendFile(path.join(__dirname, 'view', 'transfer.html'));
});

// Iniciar o servidor
app.listen(port, () => {
  console.log(`Servidor rodando em http://localhost:${port}`);
});
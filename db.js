const { Client } = require('pg');
const fs = require('fs');
const path = require('path');

// Configuração da conexão com o PostgreSQL
const client = new Client({
  user: 'postgres',      // Coloque seu usuário do PostgreSQL
  host: 'localhost',        // Host do seu PostgreSQL (geralmente localhost)
  database: 'patrimonio',   // Coloque o nome do seu banco de dados
  password: '261217',    // Coloque sua senha do PostgreSQL
  port: 5432,               // Porta padrão do PostgreSQL
});

async function createTables() {
  try {
    await client.connect();  // Conecta ao PostgreSQL
    console.log('Conectado ao banco de dados...');

    // Lê o arquivo SQL contendo a criação das tabelas
    const sql = fs.readFileSync(path.join(__dirname, 'scripts/create_tables.sql')).toString();
    
    // Executa o script SQL
    await client.query(sql);
    console.log('Tabelas criadas com sucesso!');
    
  } catch (error) {
    console.error('Erro ao criar tabelas:', error);
  } finally {
    await client.end();  // Fecha a conexão
  }
}

// Executa a função para criar as tabelas
createTables();

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Patrimônios</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            padding: 20px;
        }
        .btn-toolbar {
            justify-content: space-between;
        }
    </style>
</head>
<body>

<div class="container">
    <h1 class="text-center">Gerenciamento de Patrimônios</h1>

    <!-- Formulário para adicionar um patrimônio -->
    <form id="patrimonioForm">
        <div class="form-group">
            <label for="descricao">Descrição</label>
            <input type="text" class="form-control" id="descricao" placeholder="Descrição do patrimônio" required>
        </div>
        <div class="form-group">
            <label for="marca">Marca</label>
            <input type="text" class="form-control" id="marca" placeholder="Marca do patrimônio">
        </div>
        <div class="form-group">
            <label for="num_patrimonio">Número do Patrimônio</label>
            <div class="input-group">
                <input type="text" class="form-control" id="num_patrimonio" placeholder="Número do patrimônio" required readonly>
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="button" onclick="gerarCodigoPatrimonio()">Gerar Código</button>
                </div>
            </div>
            <small class="form-text text-muted">Clique no botão para gerar um código automaticamente</small>
        </div>
        <div class="form-group">
            <label for="data_aquisicao">Data de Aquisição</label>
            <input type="date" class="form-control" id="data_aquisicao" required>
        </div>
        <div class="form-group">
            <label for="valor_aquisicao">Valor de Aquisição</label>
            <input type="number" step="0.01" class="form-control" id="valor_aquisicao" placeholder="Valor em R$" required>
        </div>
        <div class="form-group">
            <label for="garantia">Garantia (em meses)</label>
            <input type="number" class="form-control" id="garantia" placeholder="Garantia em meses" required>
        </div>
        <div class="form-group">
            <label for="nota_fiscal">Nota Fiscal</label>
            <input type="text" class="form-control" id="nota_fiscal" placeholder="Número da nota fiscal">
        </div>
        <div class="form-group">
            <label for="status_2">Status</label>
            <select class="form-control" id="status_2" required>
                <option value="">Selecione o status</option>
                <option value="Novo">Novo</option>
                <option value="Usado">Usado</option>
            </select>
        </div>
        <div class="form-group">
            <label for="fornecedor_id">Fornecedor</label>
            <select class="form-control" id="fornecedor_id" required>
                <!-- Opções serão carregadas dinamicamente -->
            </select>
        </div>
        <div class="form-group">
            <label for="departamento_id">Departamento</label>
            <select class="form-control" id="departamento_id" required>
                <!-- Opções serão carregadas dinamicamente -->
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Adicionar Patrimônio</button>
    </form>

    <hr>

    <!-- Tabela para listar os patrimônios -->
    <h2 class="text-center">Lista de Patrimônios</h2>
    <table class="table table-bordered table-hover">
        <thead>
        <tr>
            <th>ID</th>
            <th>Descrição</th>
            <th>Marca</th>
            <th>Número Patrimônio</th>
            <th>Data de Aquisição</th>
            <th>Valor de Aquisição</th>
            <th>Garantia</th>
            <th>Nota Fiscal</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>
        </thead>
        <tbody id="patrimonioTableBody">
        <!-- Linhas geradas dinamicamente -->
        </tbody>
    </table>
</div>

<script>
    // Função para gerar código de patrimônio aleatório
    function gerarCodigoPatrimonio() {
        const prefixo = "PAT-"; // Prefixo personalizável
        const numeroAleatorio = Math.floor(100000 + Math.random() * 900000); // Gera número de 6 dígitos
        const codigo = prefixo + numeroAleatorio;
        
        document.getElementById('num_patrimonio').value = codigo;
    }

    // Gerar código automaticamente ao carregar a página
    document.addEventListener('DOMContentLoaded', function() {
        gerarCodigoPatrimonio();
        loadFornecedores();
        loadDepartamentos();
        loadPatrimonios();
    });

    // Função para carregar os patrimônios do backend
    async function loadPatrimonios() {
        try {
            const response = await fetch('http://localhost:3003/patrimonio');
            if (!response.ok) throw new Error('Erro ao carregar patrimônios.');
            const data = await response.json();
            const patrimonios = data.patrimonios || [];
            const tableBody = document.getElementById('patrimonioTableBody');
            tableBody.innerHTML = '';

            patrimonios.forEach(patrimonio => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${patrimonio.id_patrimonio}</td>
                    <td>${patrimonio.descricao}</td>
                    <td>${patrimonio.marca}</td>
                    <td>${patrimonio.num_patrimonio}</td>
                    <td>${patrimonio.data_aquisicao}</td>
                    <td>R$ ${parseFloat(patrimonio.valor_aquisicao).toFixed(2)}</td>
                    <td>${patrimonio.garantia} meses</td>
                    <td>${patrimonio.nota_fiscal}</td>
                    <td>${patrimonio.status_2}</td>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick="editPatrimonio(${patrimonio.id_patrimonio})">Editar</button>
                        <button class="btn btn-danger btn-sm" onclick="deletePatrimonio(${patrimonio.id_patrimonio})">Excluir</button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
        } catch (error) {
            alert(error.message);
        }
    }

    // Função para adicionar um patrimônio
    document.getElementById('patrimonioForm').addEventListener('submit', async function(event) {
        event.preventDefault();
        const patrimonio = {
            descricao: document.getElementById('descricao').value,
            marca: document.getElementById('marca').value,
            num_patrimonio: document.getElementById('num_patrimonio').value,
            data_aquisicao: document.getElementById('data_aquisicao').value,
            valor_aquisicao: parseFloat(document.getElementById('valor_aquisicao').value),
            garantia: parseInt(document.getElementById('garantia').value),
            nota_fiscal: document.getElementById('nota_fiscal').value,
            status_2: document.getElementById('status_2').value,
            fornecedor_id: parseInt(document.getElementById('fornecedor_id').value),
            departamento_id: parseInt(document.getElementById('departamento_id').value)
        };

        try {
            const response = await fetch('http://localhost:3003/patrimonio', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(patrimonio)
            });

            if (!response.ok) throw new Error('Erro ao adicionar patrimônio.');
            alert('Patrimônio adicionado com sucesso!');
            document.getElementById('patrimonioForm').reset();
            gerarCodigoPatrimonio(); // Gera novo código após cadastro
            loadPatrimonios();
        } catch (error) {
            alert(error.message);
        }
    });

    // Função para excluir um patrimônio
    async function deletePatrimonio(id_patrimonio) {
        if (confirm('Tem certeza que deseja excluir este patrimônio?')) {
            try {
                const response = await fetch(`http://localhost:3003/patrimonio/${id_patrimonio}`, { method: 'DELETE' });
                if (!response.ok) throw new Error('Erro ao excluir patrimônio.');
                alert('Patrimônio excluído com sucesso!');
                loadPatrimonios();
            } catch (error) {
                alert(error.message);
            }
        }
    }

    // Função para editar um patrimônio
    async function editPatrimonio(id_patrimonio) {
        const descricao = prompt('Nova descrição:');
        if (!descricao) return;

        try {
            const response = await fetch(`http://localhost:3003/patrimonio/${id_patrimonio}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ descricao })
            });

            if (!response.ok) throw new Error('Erro ao editar patrimônio.');
            alert('Patrimônio editado com sucesso!');
            loadPatrimonios();
        } catch (error) {
            alert(error.message);
        }
    }

    // Função para carregar fornecedores
    async function loadFornecedores() {
        try {
            const response = await fetch('http://localhost:3003/fornecedor');
            if (!response.ok) throw new Error('Erro ao carregar fornecedores.');
            const fornecedores = await response.json();
            const fornecedorSelect = document.getElementById('fornecedor_id');
            fornecedorSelect.innerHTML = '<option value="">Selecione um fornecedor</option>';

            fornecedores.forEach(fornecedor => {
                const option = document.createElement('option');
                option.value = fornecedor.id_fornecedor;
                option.textContent = fornecedor.razao_social;
                fornecedorSelect.appendChild(option);
            });
        } catch (error) {
            alert(error.message);
        }
    }

    // Função para carregar departamentos
    async function loadDepartamentos() {
        try {
            const response = await fetch('http://localhost:3003/departamento');
            if (!response.ok) throw new Error('Erro ao carregar departamentos.');
            const data = await response.json();
            const departamentos = data.departamentos || [];
            const departamentoSelect = document.getElementById('departamento_id');
            departamentoSelect.innerHTML = '<option value="">Selecione um departamento</option>';

            departamentos.forEach(departamento => {
                const option = document.createElement('option');
                option.value = departamento.departamento_id;
                option.textContent = departamento.nome_departamento;
                departamentoSelect.appendChild(option);
            });
        } catch (error) {
            alert(error.message);
        }
    }
</script>
</body>
</html>

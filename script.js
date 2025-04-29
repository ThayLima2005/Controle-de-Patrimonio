    const API_URL = 'http://localhost:3003';

    // Carregar dados ao iniciar a página
    document.addEventListener('DOMContentLoaded', function () {
        if (document.getElementById('departamento-list')) {
            loadDepartamentos();
        } else if (document.getElementById('patrimonio-list')) {
            loadPatrimonios();
        } else if (document.getElementById('transfer-list')) {
            loadTransfers();
        }
    });

    // Função para adicionar um departamento
    document.getElementById('add-departamento-form')?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const departamentoId = document.getElementById('departamento-id').value;
        const nome = document.getElementById('nome_departamento').value;
        const responsavel = document.getElementById('responsavel').value;
        const telefone = document.getElementById('telefone').value;
        const email = document.getElementById('email').value;

        const url = departamentoId ? `${API_URL}/departamento/${departamentoId}` : `${API_URL}/departamento`;
        const method = departamentoId ? 'PUT' : 'POST';

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    nome_departamento: nome,
                    responsavel: responsavel,
                    telefone: telefone,
                    email: email
                })
            });

            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Erro ao processar request');

            alert(departamentoId ? 'Departamento atualizado com sucesso!' : 'Departamento adicionado com sucesso!');
            document.getElementById('add-departamento-form').reset();
            document.getElementById('departamento-id').value = '';
            loadDepartamentos(); // Atualiza a lista de departamentos
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao enviar dados. Verifique o console para mais detalhes.');
        }
    });

    // Função para carregar a lista de departamentos
    async function loadDepartamentos() {
        try {
            const response = await fetch(`${API_URL}/departamento`);
            if (!response.ok) throw new Error('Erro ao carregar departamentos.');

            const result = await response.json();
            const departamentoList = document.getElementById('departamento-list');
            departamentoList.innerHTML = '';
            result.departamentos.forEach(departamento => {
                departamentoList.innerHTML += `
                    <tr>
                        <td>${departamento.departamento_id}</td>
                        <td>${departamento.nome_departamento}</td>
                        <td>${departamento.responsavel}</td>
                        <td>${departamento.telefone}</td>
                        <td>${departamento.email}</td>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick="editDepartamento(${departamento.departamento_id})">Editar</button>
                            <button class="btn btn-danger btn-sm" onclick="deleteDepartamento(${departamento.departamento_id})">Excluir</button>
                        </td>
                    </tr>
                `;
            });
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao carregar departamentos. Verifique o console para mais detalhes.');
        }
    }

    // Função para editar departamento
    async function editDepartamento(id) {
        try {
            const response = await fetch(`${API_URL}/departamento/${id}`);
            if (!response.ok) throw new Error('Erro ao carregar departamento.');

            const data = await response.json();
            document.getElementById('departamento-id').value = data.departamento_id;
            document.getElementById('nome_departamento').value = data.nome_departamento;
            document.getElementById('responsavel').value = data.responsavel;
            document.getElementById('telefone').value = data.telefone;
            document.getElementById('email').value = data.email;
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao carregar departamento. Verifique o console para mais detalhes.');
        }
    }

    // Função para deletar departamento
    async function deleteDepartamento(id) {
        if (confirm('Tem certeza que deseja excluir este departamento?')) {
            try {
                const response = await fetch(`${API_URL}/departamento/${id}`, {
                    method: 'DELETE'
                });

                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'Erro ao excluir departamento.');

                alert('Departamento excluído com sucesso!');
                loadDepartamentos(); // Atualiza a lista de departamentos
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao excluir departamento. Verifique o console para mais detalhes.');
            }
        }
    }

    // Função para carregar a lista de patrimônios
    async function loadPatrimonios() {
        try {
            const response = await fetch(`${API_URL}/patrimonio`);
            if (!response.ok) throw new Error('Erro ao carregar patrimônios.');

            const result = await response.json();
            const patrimonioList = document.getElementById('patrimonio-list');
            patrimonioList.innerHTML = '';
            result.patrimonios.forEach(patrimonio => {
                patrimonioList.innerHTML += `
                    <tr>
                        <td>${patrimonio.id_patrimonio}</td>
                        <td>${patrimonio.descricao}</td>
                        <td>${patrimonio.departamento_id}</td>
                        <td>${patrimonio.fornecedor_id}</td>
                        <td>${patrimonio.data_aquisicao}</td>
                        <td>${patrimonio.valor_aquisicao}</td>
                        <td>${patrimonio.garantia}</td>
                        <td>${patrimonio.marca}</td>
                        <td>${patrimonio.status_2}</td>
                        <td>${patrimonio.num_patrimonio}</td>
                        <td>${patrimonio.nota_fiscal}</td>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick="editPatrimonio(${patrimonio.id_patrimonio})">Editar</button>
                            <button class="btn btn-danger btn-sm" onclick="deletePatrimonio(${patrimonio.id_patrimonio})">Excluir</button>
                        </td>
                    </tr>
                `;
            });
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao carregar patrimônios. Verifique o console para mais detalhes.');
        }
    }

    // Função para adicionar um patrimônio
    document.getElementById('add-patrimonio-form')?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const patrimonioId = document.getElementById('patrimonio-id').value;
        const descricao = document.getElementById('descricao').value;
        const departamento_id = document.getElementById('departamento_id').value;
        const fornecedor_id = document.getElementById('fornecedor_id').value;
        const data_aquisicao = document.getElementById('data_aquisicao').value;
        const valor_aquisicao = document.getElementById('valor_aquisicao').value;
        const garantia = document.getElementById('garantia').value;
        const marca = document.getElementById('marca').value;
        const status_2 = document.getElementById('status_2').value;
        const num_patrimonio = document.getElementById('num_patrimonio').value;
        const nota_fiscal = document.getElementById('nota_fiscal').value;

        const url = patrimonioId ? `${API_URL}/patrimonio/${patrimonioId}` : `${API_URL}/patrimonio`;
        const method = patrimonioId ? 'PUT' : 'POST';

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    descricao: descricao,
                    departamento_id: departamento_id,
                    fornecedor_id: fornecedor_id,
                    data_aquisicao: data_aquisicao,
                    valor_aquisicao: valor_aquisicao,
                    garantia: garantia,
                    marca: marca,
                    status_2: status_2,
                    num_patrimonio: num_patrimonio,
                    nota_fiscal: nota_fiscal
                })
            });

            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Erro ao processar request');

            alert(patrimonioId ? 'Patrimônio atualizado com sucesso!' : 'Patrimônio adicionado com sucesso!');
            document.getElementById('add-patrimonio-form').reset();
            document.getElementById('patrimonio-id').value = '';
            loadPatrimonios(); // Atualiza a lista de patrimônios
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao enviar dados. Verifique o console para mais detalhes.');
        }
    });

    // Função para editar patrimônio
    async function editPatrimonio(id) {
        try {
            const response = await fetch(`${API_URL}/patrimonio/${id}`);
            if (!response.ok) throw new Error('Erro ao carregar patrimônio.');

            const data = await response.json();
            document.getElementById('patrimonio-id').value = data.id_patrimonio;
            document.getElementById('descricao').value = data.descricao;
            document.getElementById('departamento_id').value = data.departamento_id;
            document.getElementById('fornecedor_id').value = data.fornecedor_id;
            document.getElementById('data_aquisicao').value = data.data_aquisicao;
            document.getElementById('valor_aquisicao').value = data.valor_aquisicao;
            document.getElementById('garantia').value = data.garantia;
            document.getElementById('marca').value = data.marca;
            document.getElementById('status_2').value = data.status_2;
            document.getElementById('num_patrimonio').value = data.num_patrimonio;
            document.getElementById('nota_fiscal').value = data.nota_fiscal;
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao carregar patrimônio. Verifique o console para mais detalhes.');
        }
    }

    // Função para deletar patrimônio
    async function deletePatrimonio(id) {
        if (confirm('Tem certeza que deseja excluir este patrimônio?')) {
            try {
                const response = await fetch(`${API_URL}/patrimonio/${id}`, {
                    method: 'DELETE'
                });

                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'Erro ao excluir patrimônio.');

                alert('Patrimônio excluído com sucesso!');
                loadPatrimonios(); // Atualiza a lista de patrimônios
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao excluir patrimônio. Verifique o console para mais detalhes.');
            }
        }
    }

    // Função para carregar a lista de transferências
    document.addEventListener('DOMContentLoaded', function() {
        const loadTransferList = () => {
            fetch('http://localhost:3003/transfer')
                .then(response => response.json())
                .then(data => {
                    const transferList = document.getElementById('transfer-list');
                    transferList.innerHTML = '';
    
                    data.forEach(transfer => {
                        // Buscar os dados complementares para substituir IDs
                        const departamentoAtualNome = getDepartamentoNome(transfer.departamento_atual);
                        const departamentoDestinoNome = getDepartamentoNome(transfer.departamento_destino);
                        const responsavelNome = getResponsavelNome(transfer.responsavel);
    
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${transfer.id_transferencia != null ? transfer.id_transferencia : 'N/A'}</td>
                            <td>${departamentoAtualNome != null ? departamentoAtualNome : 'N/A'}</td>
                            <td>${departamentoDestinoNome != null ? departamentoDestinoNome : 'N/A'}</td>
                            <td>${transfer.patrimonio_id != null ? transfer.patrimonio_id : 'N/A'}</td>
                            <td>${responsavelNome != null ? responsavelNome : 'N/A'}</td>
                            <td>${transfer.data_transferencia != null ? transfer.data_transferencia : 'N/A'}</td>
                            <td>${transfer.observacao != null ? transfer.observacao : 'N/A'}</td>
                        `;
                        transferList.appendChild(row);
                    });
                })
                .catch(error => console.error('Erro ao carregar transferências:', error));
        };
    
        // Função para buscar o nome do departamento com base no ID
        const getDepartamentoNome = (departamentoId) => {
            return fetch(`http://localhost:3003/departamentos/${departamentoId}`)
                .then(response => response.json())
                .then(data => data.nome_departamento || 'N/A')
                .catch(() => 'N/A');
        };
    
        // Função para buscar o nome do responsável com base no ID
        const getResponsavelNome = (responsavelId) => {
            return fetch(`http://localhost:3003/usuarios/${responsavelId}`)
                .then(response => response.json())
                .then(data => data.nome || 'N/A')
                .catch(() => 'N/A');
        };
    
        loadTransferList();
    });    

    // Função para adicionar uma transferência
    document.getElementById('add-transfer-form')?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const transferId = document.getElementById('transfer-id').value;
        const transferData = {
            departamento_atual: document.getElementById('departamento_id').value,
            departamento_destino: document.getElementById('departamento_destino').value,
            patrimonio_id: document.getElementById('patrimonio_id').value,
            responsavel: document.getElementById('responsavel').value,
            data_transferencia: document.getElementById('data_transferencia').value,
            observacao: document.getElementById('observacao').value
        };

        const url = transferId ? `${API_URL}/transfers/${transferId}` : `${API_URL}/transfers`;
        const method = transferId ? 'PUT' : 'POST';

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(transferData)
            });

            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Erro ao processar request');

            alert(transferId ? 'Transferência atualizada com sucesso!' : 'Transferência adicionada com sucesso!');
            document.getElementById('add-transfer-form').reset();
            document.getElementById('transfer-id').value = '';
            loadTransfers(); // Atualiza a lista de transferências
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao enviar dados. Verifique o console para mais detalhes.');
        }
    });

    // Função para editar transferência
    async function editTransfer(id) {
        try {
            const response = await fetch(`${API_URL}/transfers/${id}`);
            if (!response.ok) throw new Error('Erro ao carregar transferência.');

            const data = await response.json();
            document.getElementById('transfer-id').value = data.id;
            document.getElementById('departamento_id').value = data.departamento_atual;
            document.getElementById('departamento_destino').value = data.departamento_destino;
            document.getElementById('patrimonio_id').value = data.patrimonio_id;
            document.getElementById('responsavel').value = data.responsavel;
            document.getElementById('data_transferencia').value = data.data_transferencia;
            document.getElementById('observacao').value = data.observacao;
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao carregar transferência. Verifique o console para mais detalhes.');
        }
    }

    // Função para deletar transferência
    async function deleteTransfer(id) {
        if (confirm('Tem certeza que deseja excluir esta transferência?')) {
            try {
                const response = await fetch(`${API_URL}/transfers/${id}`, {
                    method: 'DELETE'
                });

                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'Erro ao excluir transferência.');

                alert('Transferência excluída com sucesso!');
                loadTransfers(); // Atualiza a lista de transferências
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao excluir transferência. Verifique o console para mais detalhes.');
            }
        }
    }
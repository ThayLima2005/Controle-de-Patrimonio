<?php
session_start();

<<<<<<< HEAD
// Verifica se o usuário está logado
=======
// Check if user is logged in
>>>>>>> bd487f09ead85e150ce6fcd6d1e227f374995a36
if (!isset($_SESSION['usuario_logado'])) {
    header('Location: login.php');
    exit();
}

<<<<<<< HEAD
// Conexão com o banco de dados
require_once __DIR__ . '/config.php';

try {
    // Obtém todas as transferências
    $stmtTransferencias = $pdo->query("
        SELECT t.*, 
               u.nome AS usuario, 
               p.descricao AS patrimonio_descricao,
               p.num_patrimonio,
               d1.nome_departamento AS departamento_origem,
               d2.nome_departamento AS departamento_destino_nome
        FROM transferencia t
        JOIN usuario u ON t.id_usuario = u.id_usuario
        JOIN patrimonio p ON t.patrimonio_id = p.id_patrimonio
        JOIN departamento d1 ON t.departamento_id = d1.departamento_id
        LEFT JOIN departamento d2 ON t.departamento_destino = d2.departamento_id
        ORDER BY t.data_transferencia DESC
    ");
    $transferencias = $stmtTransferencias->fetchAll(PDO::FETCH_ASSOC);

    // Obtém todos os patrimônios
    $stmtPatrimonios = $pdo->query("
        SELECT p.id_patrimonio, p.descricao, p.num_patrimonio, d.nome_departamento
        FROM patrimonio p
        JOIN departamento d ON p.departamento_id = d.departamento_id
        ORDER BY p.descricao
    ");
    $patrimonios = $stmtPatrimonios->fetchAll(PDO::FETCH_ASSOC);

    // Obtém todos os departamentos
    $stmtDepartamentos = $pdo->query("SELECT departamento_id, nome_departamento FROM departamento ORDER BY nome_departamento");
=======
// Database connection
require_once 'config.php';

try {
    // Get all assets
    $stmtPatrimonios = $pdo->query("
        SELECT p.*, f.razao_social AS fornecedor, d.nome AS departamento
        FROM patrimonios p
        LEFT JOIN fornecedores f ON p.fornecedor_id = f.id
        LEFT JOIN departamentos d ON p.departamento_id = d.id
        ORDER BY p.id DESC
    ");
    $patrimonios = $stmtPatrimonios->fetchAll(PDO::FETCH_ASSOC);

    // Get all suppliers
    $stmtFornecedores = $pdo->query("SELECT id, razao_social FROM fornecedores ORDER BY razao_social");
    $fornecedores = $stmtFornecedores->fetchAll(PDO::FETCH_ASSOC);

    // Get all departments
    $stmtDepartamentos = $pdo->query("SELECT id, nome FROM departamentos ORDER BY nome");
>>>>>>> bd487f09ead85e150ce6fcd6d1e227f374995a36
    $departamentos = $stmtDepartamentos->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro ao carregar dados: " . $e->getMessage());
}

<<<<<<< HEAD
// Processa o formulário de transferência
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $erro = [];
    
    // Valida os campos
    if (empty($_POST['patrimonio_id'])) {
        $erro[] = 'Patrimônio é obrigatório.';
    }
    if (empty($_POST['departamento_destino'])) {
        $erro[] = 'Departamento destino é obrigatório.';
    }
    if (empty($_POST['responsavel'])) {
        $erro[] = 'Responsável é obrigatório.';
    }

    if (empty($erro)) {
        try {
            // Inicia transação
            $pdo->beginTransaction();

            // 1. Obtém informações atuais do patrimônio
            $stmtPatrimonio = $pdo->prepare("
                SELECT p.*, d.nome_departamento 
                FROM patrimonio p
                JOIN departamento d ON p.departamento_id = d.departamento_id
                WHERE p.id_patrimonio = ?
            ");
            $stmtPatrimonio->execute([$_POST['patrimonio_id']]);
            $patrimonio = $stmtPatrimonio->fetch(PDO::FETCH_ASSOC);

            if (!$patrimonio) {
                throw new Exception("Patrimônio não encontrado");
            }

            // 2. Registra a transferência
            $dadosTransferencia = [
                'id_usuario' => $_SESSION['usuario_id'],
                'departamento_id' => $patrimonio['departamento_id'],
                'patrimonio_id' => $_POST['patrimonio_id'],
                'departamento_destino' => $_POST['departamento_destino'],
                'observacao' => $_POST['observacao'] ?? null,
                'departamento_anterior' => $patrimonio['nome_departamento'],
                'responsavel' => $_POST['responsavel']
            ];

            $stmtTransferencia = $pdo->prepare("
                INSERT INTO transferencia 
                (id_usuario, departamento_id, patrimonio_id, data_transferencia, departamento_destino, observacao, departamento_anterior, responsavel)
                VALUES 
                (:id_usuario, :departamento_id, :patrimonio_id, NOW(), :departamento_destino, :observacao, :departamento_anterior, :responsavel)
            ");

            $stmtTransferencia->execute($dadosTransferencia);

            // 3. Atualiza o departamento do patrimônio
            $stmtUpdatePatrimonio = $pdo->prepare("
                UPDATE patrimonio SET departamento_id = ? WHERE id_patrimonio = ?
            ");
            $stmtUpdatePatrimonio->execute([$_POST['departamento_destino'], $_POST['patrimonio_id']]);

            // Confirma a transação
            $pdo->commit();

            $_SESSION['mensagem'] = 'Transferência realizada com sucesso!';
            header('Location: transferencia.php');
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            $erro[] = "Erro ao processar transferência: " . $e->getMessage();
        }
    }
}
?>

=======
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $dados = [
            'descricao' => $_POST['descricao'],
            'marca' => $_POST['marca'],
            'num_patrimonio' => $_POST['num_patrimonio'],
            'data_aquisicao' => $_POST['data_aquisicao'],
            'valor_aquisicao' => str_replace(',', '.', $_POST['valor_aquisicao']),
            'garantia' => $_POST['garantia'],
            'nota_fiscal' => $_POST['nota_fiscal'],
            'status_2' => $_POST['status_2'],
            'fornecedor_id' => $_POST['fornecedor_id'],
            'departamento_id' => $_POST['departamento_id']
        ];

        $stmt = $pdo->prepare("
            INSERT INTO patrimonios 
            (descricao, marca, num_patrimonio, data_aquisicao, valor_aquisicao, garantia, nota_fiscal, status_2, fornecedor_id, departamento_id)
            VALUES 
            (:descricao, :marca, :num_patrimonio, :data_aquisicao, :valor_aquisicao, :garantia, :nota_fiscal, :status_2, :fornecedor_id, :departamento_id)
        ");

        if ($stmt->execute($dados)) {
            $_SESSION['mensagem'] = 'Patrimônio cadastrado com sucesso!';
            header('Location: patrimonio.php');
            exit();
        }

    } catch (PDOException $e) {
        $erro = "Erro ao cadastrar patrimônio: " . $e->getMessage();
    }
}
?>
>>>>>>> bd487f09ead85e150ce6fcd6d1e227f374995a36
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<<<<<<< HEAD
    <title>Gerenciamento de Transferências</title>
=======
    <title>Gerenciamento de Patrimônios</title>
>>>>>>> bd487f09ead85e150ce6fcd6d1e227f374995a36
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
        }
<<<<<<< HEAD
        .table-responsive {
            margin-top: 20px;
        }
        .card {
            margin-bottom: 20px;
        }
        .table thead th {
            background-color: #007bff;
            color: white;
=======
        .btn-toolbar {
            justify-content: space-between;
>>>>>>> bd487f09ead85e150ce6fcd6d1e227f374995a36
        }
    </style>
</head>
<body>

<div class="container">
    <?php if (isset($_SESSION['mensagem'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['mensagem'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['mensagem']); ?>
    <?php endif; ?>

<<<<<<< HEAD
    <?php if (!empty($erro)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul>
                <?php foreach ($erro as $mensagem): ?>
                    <li><?= htmlspecialchars($mensagem) ?></li>
                <?php endforeach; ?>
            </ul>
=======
    <?php if (isset($erro)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $erro ?>
>>>>>>> bd487f09ead85e150ce6fcd6d1e227f374995a36
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

<<<<<<< HEAD
    <h1 class="text-center mb-4">Gerenciamento de Transferências</h1>

    <!-- Formulário para nova transferência -->
    <div class="card border-primary">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Nova Transferência</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="transferencia.php">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="patrimonio_id" class="form-label">Patrimônio*</label>
                        <select class="form-select" id="patrimonio_id" name="patrimonio_id" required>
                            <option value="">Selecione um patrimônio</option>
                            <?php foreach ($patrimonios as $patrimonio): ?>
                                <option value="<?= $patrimonio['id_patrimonio'] ?>">
                                    <?= htmlspecialchars($patrimonio['num_patrimonio'] . ' - ' . $patrimonio['descricao'] . ' (' . $patrimonio['nome_departamento'] . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="departamento_destino" class="form-label">Departamento Destino*</label>
                        <select class="form-select" id="departamento_destino" name="departamento_destino" required>
                            <option value="">Selecione um departamento</option>
                            <?php foreach ($departamentos as $departamento): ?>
                                <option value="<?= $departamento['departamento_id'] ?>">
                                    <?= htmlspecialchars($departamento['nome_departamento']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="responsavel" class="form-label">Responsável pela Transferência*</label>
                        <input type="text" class="form-control" id="responsavel" name="responsavel" required>
                    </div>
                    <div class="col-md-6">
                        <label for="observacao" class="form-label">Observações</label>
                        <input type="text" class="form-control" id="observacao" name="observacao">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Registrar Transferência</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Histórico de transferências -->
    <div class="card border-primary">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Histórico de Transferências</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Data/Hora</th>
                            <th>Patrimônio</th>
                            <th>De</th>
                            <th>Para</th>
                            <th>Responsável</th>
                            <th>Usuário</th>
                            <th>Observações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($transferencias) > 0): ?>
                            <?php foreach ($transferencias as $transferencia): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($transferencia['data_transferencia'])) ?></td>
                                    <td><?= htmlspecialchars($transferencia['num_patrimonio'] . ' - ' . $transferencia['patrimonio_descricao']) ?></td>
                                    <td><?= htmlspecialchars($transferencia['departamento_anterior']) ?></td>
                                    <td><?= htmlspecialchars($transferencia['departamento_destino_nome'] ?? $transferencia['departamento_destino']) ?></td>
                                    <td><?= htmlspecialchars($transferencia['responsavel']) ?></td>
                                    <td><?= htmlspecialchars($transferencia['usuario']) ?></td>
                                    <td><?= htmlspecialchars($transferencia['observacao'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">Nenhuma transferência registrada</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
=======
    <h1 class="text-center">Gerenciamento de Patrimônios</h1>

    <!-- Formulário para adicionar um patrimônio -->
    <form method="POST" action="patrimonio.php">
        <div class="form-group mb-3">
            <label for="descricao" class="form-label">Descrição</label>
            <input type="text" class="form-control" id="descricao" name="descricao" placeholder="Descrição do patrimônio" required>
        </div>
        <div class="form-group mb-3">
            <label for="marca" class="form-label">Marca</label>
            <input type="text" class="form-control" id="marca" name="marca" placeholder="Marca do patrimônio">
        </div>
        <div class="form-group mb-3">
            <label for="num_patrimonio" class="form-label">Número do Patrimônio</label>
            <input type="text" class="form-control" id="num_patrimonio" name="num_patrimonio" placeholder="Número do patrimônio" required>
        </div>
        <div class="form-group mb-3">
            <label for="data_aquisicao" class="form-label">Data de Aquisição</label>
            <input type="date" class="form-control" id="data_aquisicao" name="data_aquisicao" required>
        </div>
        <div class="form-group mb-3">
            <label for="valor_aquisicao" class="form-label">Valor de Aquisição</label>
            <input type="text" class="form-control" id="valor_aquisicao" name="valor_aquisicao" placeholder="Valor em R$" required>
        </div>
        <div class="form-group mb-3">
            <label for="garantia" class="form-label">Garantia (em meses)</label>
            <input type="number" class="form-control" id="garantia" name="garantia" placeholder="Garantia em meses" required>
        </div>
        <div class="form-group mb-3">
            <label for="nota_fiscal" class="form-label">Nota Fiscal</label>
            <input type="text" class="form-control" id="nota_fiscal" name="nota_fiscal" placeholder="Número da nota fiscal">
        </div>
        <div class="form-group mb-3">
            <label for="status_2" class="form-label">Status</label>
            <select class="form-control" id="status_2" name="status_2" required>
                <option value="">Selecione o status</option>
                <option value="Novo">Novo</option>
                <option value="Usado">Usado</option>
            </select>
        </div>
        <div class="form-group mb-3">
            <label for="fornecedor_id" class="form-label">Fornecedor</label>
            <select class="form-control" id="fornecedor_id" name="fornecedor_id" required>
                <option value="">Selecione um fornecedor</option>
                <?php foreach ($fornecedores as $fornecedor): ?>
                    <option value="<?= $fornecedor['id'] ?>"><?= htmlspecialchars($fornecedor['razao_social']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group mb-3">
            <label for="departamento_id" class="form-label">Departamento</label>
            <select class="form-control" id="departamento_id" name="departamento_id" required>
                <option value="">Selecione um departamento</option>
                <?php foreach ($departamentos as $departamento): ?>
                    <option value="<?= $departamento['id'] ?>"><?= htmlspecialchars($departamento['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Adicionar Patrimônio</button>
    </form>

    <hr>

    <!-- Tabela para listar os patrimônios -->
    <h2 class="text-center">Lista de Patrimônios</h2>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-primary">
                <tr>
                    <th>ID</th>
                    <th>Descrição</th>
                    <th>Marca</th>
                    <th>Nº Patrimônio</th>
                    <th>Data Aquisição</th>
                    <th>Valor (R$)</th>
                    <th>Garantia</th>
                    <th>Nota Fiscal</th>
                    <th>Status</th>
                    <th>Fornecedor</th>
                    <th>Departamento</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($patrimonios) > 0): ?>
                    <?php foreach ($patrimonios as $patrimonio): ?>
                        <tr>
                            <td><?= $patrimonio['id'] ?></td>
                            <td><?= htmlspecialchars($patrimonio['descricao']) ?></td>
                            <td><?= htmlspecialchars($patrimonio['marca']) ?></td>
                            <td><?= htmlspecialchars($patrimonio['num_patrimonio']) ?></td>
                            <td><?= date('d/m/Y', strtotime($patrimonio['data_aquisicao'])) ?></td>
                            <td>R$ <?= number_format($patrimonio['valor_aquisicao'], 2, ',', '.') ?></td>
                            <td><?= $patrimonio['garantia'] ?> meses</td>
                            <td><?= htmlspecialchars($patrimonio['nota_fiscal']) ?></td>
                            <td><?= htmlspecialchars($patrimonio['status_2']) ?></td>
                            <td><?= htmlspecialchars($patrimonio['fornecedor'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($patrimonio['departamento'] ?? 'N/A') ?></td>
                            <td>
                                <a href="editar_patrimonio.php?id=<?= $patrimonio['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                                <a href="excluir_patrimonio.php?id=<?= $patrimonio['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="12" class="text-center">Nenhum patrimônio cadastrado</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
>>>>>>> bd487f09ead85e150ce6fcd6d1e227f374995a36
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
<<<<<<< HEAD
    // Validação do formulário
    document.querySelector('form').addEventListener('submit', function(e) {
        const patrimonio = document.getElementById('patrimonio_id').value;
        const departamento = document.getElementById('departamento_destino').value;
        
        if (patrimonio === '' || departamento === '') {
            e.preventDefault();
            alert('Por favor, preencha todos os campos obrigatórios.');
=======
    // Format currency input
    document.getElementById('valor_aquisicao').addEventListener('blur', function() {
        let value = this.value.replace(/[^\d,]/g, '');
        value = value.replace(',', '.');
        if (value && !isNaN(value)) {
            this.value = parseFloat(value).toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
>>>>>>> bd487f09ead85e150ce6fcd6d1e227f374995a36
        }
    });
</script>
</body>
</html>
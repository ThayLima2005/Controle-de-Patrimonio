<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_logado'])) {
    header('Location: login.php');
    exit();
}

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
    $departamentos = $stmtDepartamentos->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro ao carregar dados: " . $e->getMessage());
}

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

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Transferências</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
        }
        .table-responsive {
            margin-top: 20px;
        }
        .card {
            margin-bottom: 20px;
        }
        .table thead th {
            background-color: #007bff;
            color: white;
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

    <?php if (!empty($erro)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul>
                <?php foreach ($erro as $mensagem): ?>
                    <li><?= htmlspecialchars($mensagem) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

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
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Validação do formulário
    document.querySelector('form').addEventListener('submit', function(e) {
        const patrimonio = document.getElementById('patrimonio_id').value;
        const departamento = document.getElementById('departamento_destino').value;
        
        if (patrimonio === '' || departamento === '') {
            e.preventDefault();
            alert('Por favor, preencha todos os campos obrigatórios.');
        }
    });
</script>
</body>
</html>
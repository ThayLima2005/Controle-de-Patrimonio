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
    // Obtém todas as transferências (consulta corrigida)
    $sqlTransferencias = "
        SELECT 
            t.*,
            u.nome AS usuario,
            p.descricao AS patrimonio_descricao,
            p.num_patrimonio,
            de.nome_departamento AS departamento_origem,
            para.nome_departamento AS departamento_destino_nome
        FROM transferencia t
        LEFT JOIN usuario u ON t.id_usuario = u.id_usuario
        LEFT JOIN patrimonio p ON t.patrimonio_id = p.id_patrimonio
        LEFT JOIN departamento de ON t.departamento_id = de.departamento_id
        LEFT JOIN departamento para ON t.departamento_destino = para.departamento_id
        ORDER BY t.data_transferencia DESC
    ";
    $transferencias = $pdo->query($sqlTransferencias)->fetchAll(PDO::FETCH_ASSOC);

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

// Processa o formulário de transferência (código corrigido)
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

            // 2. Registra a transferência (sintaxe corrigida)
            $stmtTransferencia = $pdo->prepare("
                INSERT INTO transferencia SET
                id_usuario = :id_usuario,
                departamento_id = :departamento_id,
                patrimonio_id = :patrimonio_id,
                data_transferencia = NOW(),
                departamento_destino = :departamento_destino,
                observacao = :observacao,
                departamento_anterior = :departamento_anterior,
                responsavel = :responsavel
            ");

            $stmtTransferencia->execute([
                ':id_usuario' => $_SESSION['usuario_logado']['id'],
                ':departamento_id' => $patrimonio['departamento_id'],
                ':patrimonio_id' => $_POST['patrimonio_id'],
                ':departamento_destino' => $_POST['departamento_destino'],
                ':observacao' => $_POST['observacao'] ?? null,
                ':departamento_anterior' => $patrimonio['nome_departamento'],
                ':responsavel' => $_POST['responsavel']
            ]);

            // 3. Atualiza o departamento do patrimônio
            $stmtUpdatePatrimonio = $pdo->prepare("
                UPDATE patrimonio SET departamento_id = ? WHERE id_patrimonio = ?
            ");
            $stmtUpdatePatrimonio->execute([$_POST['departamento_destino'], $_POST['patrimonio_id']]);

            // Confirma a transação
            $pdo->commit();

            $_SESSION['mensagem'] = 'Transferência realizada com sucesso!';
            header('Location: ' . $_SERVER['PHP_SELF']);
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
        .alert{
            margin-top: 60px;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <a class="navbar-brand" href="index.php">Patrimônio 360</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto44444431">
            <li class="nav-item">
                <a class="nav-link" href="index.php">Página Inicial</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="patrimonio.php">Gerenciar Patrimônio</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="transfer.php">Registrar Transferência</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="fornecedor.php">Gerenciar Fornecedor</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="departamento.php">Gerenciar Departamento</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="movimento.php">Movimento de Transferências</a>
            </li>
        </ul>
        <span class="navbar-text me-3">Bem-vindo, <?= htmlspecialchars($_SESSION['usuario_logado']['nome'] ?? 'Usuário') ?></span>
        <a class="btn btn-outline-light" href="logout.php">Logoff</a>
    </div>
</nav>

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
            <ul><br><br><br>
                <?php foreach ($erro as $mensagem): ?>
                    <li><?= htmlspecialchars($mensagem) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?><br><br><br><br>

    <h1 class="text-center mb-4">Gerenciamento de Transferências</h1>

    <!-- Formulário para nova transferência -->
    <div class="card border-primary">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Nova Transferência</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
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

    <!-- Histórico de transferências (exibição corrigida) -->
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
                            <th>Observações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($transferencias)): ?>
                            <?php foreach ($transferencias as $t): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($t['data_transferencia'])) ?></td>
                                    <td><?= htmlspecialchars($t['num_patrimonio'] . ' - ' . $t['patrimonio_descricao']) ?></td>
                                    <td><?= htmlspecialchars($t['departamento_origem'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($t['departamento_destino_nome'] ?? $t['departamento_destino']) ?></td>
                                    <td><?= htmlspecialchars($t['responsavel']) ?></td>
                                    <td><?= htmlspecialchars($t['observacao'] ?? '') ?></td>
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
        const responsavel = document.getElementById('responsavel').value;
        
        if (patrimonio === '' || departamento === '' || responsavel === '') {
            e.preventDefault();
            alert('Por favor, preencha todos os campos obrigatórios.');
        }
    });
</script>
</body>
</html>
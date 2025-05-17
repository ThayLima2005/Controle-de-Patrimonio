<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_logado'])) {
    header('Location: login.php');
    exit();
}

// Conexão com o banco de dados
require_once 'config.php';

try {
    // Obtém filtros de data dos parâmetros GET
    $startDate = isset($_GET['startDate']) ? $_GET['startDate'] : null;
    $endDate = isset($_GET['endDate']) ? $_GET['endDate'] : null;

    // Prepara a consulta base
    $sql = "SELECT t.id_transferencia, 
                   d1.nome_departamento AS departamento_atual, 
                   d2.nome_departamento AS departamento_destino,
                   p.num_patrimonio AS numero_patrimonio,
                   u.nome AS responsavel,
                   t.data_transferencia,
                   t.observacao
            FROM transferencia t
            LEFT JOIN departamento d1 ON t.departamento_id = d1.departamento_id
            LEFT JOIN departamento d2 ON t.departamento_destino = d2.departamento_id
            LEFT JOIN patrimonio p ON t.patrimonio_id = p.id_patrimonio
            LEFT JOIN usuario u ON t.id_usuario = u.id_usuario";

    // Adiciona filtros de data se fornecidos
    $params = [];
    if ($startDate || $endDate) {
        $conditions = [];
        if ($startDate) {
            $conditions[] = "t.data_transferencia >= :start_date";
            $params[':start_date'] = $startDate;
        }
        if ($endDate) {
            $conditions[] = "t.data_transferencia <= :end_date";
            $params[':end_date'] = $endDate;
        }
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    $sql .= " ORDER BY t.data_transferencia DESC";

    // Prepara e executa a consulta
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $transfers = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Exibe mensagem de erro detalhada em desenvolvimento
    error_log("Erro ao carregar transferências: " . $e->getMessage());
    echo "<div class='alert alert-danger'>Ocorreu um erro ao tentar carregar as transferências. Tente novamente mais tarde.</div>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimento de Transferências</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
            margin: 0;
        }
        .main-content {
            min-height: 100%;
            padding-bottom: 50px;
        }
        .table { 
            margin-top: 20px;
        }
        footer {
            background-color: #343a40;
            color: white;
            text-align: center;
            padding: 10px 0;
            position: relative;
            bottom: 0;
            width: 100%;
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
<!-- Conteúdo principal -->
<div class="container mt-4 main-content">
    <h2 class="mb-4">Movimento de Transferências</h2>

    <!-- Filtro de data -->
    <form method="get" action="movimento.php" class="row g-3" onsubmit="return validateForm()">
        <div class="col-md-4">
            <label for="startDate" class="form-label">Data Início</label>
            <input type="date" class="form-control" id="startDate" name="startDate" 
                   value="<?= htmlspecialchars($startDate ?? '') ?>">
        </div>
        <div class="col-md-4">
            <label for="endDate" class="form-label">Data Fim</label>
            <input type="date" class="form-control" id="endDate" name="endDate"
                   value="<?= htmlspecialchars($endDate ?? '') ?>">
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-primary me-2">Filtrar</button>
            <a href="movimento.php" class="btn btn-secondary">Limpar</a>
        </div>
    </form>

    <!-- Tabela de transferências -->
    <div class="table-responsive mt-4">
        <table class="table table-hover table-bordered">
            <thead class="table-primary">
                <tr>
                    <th>ID</th>
                    <th>Departamento Atual</th>
                    <th>Departamento Destino</th>
                    <th>Nº Patrimônio</th>
                    <th>Responsável</th>
                    <th>Data da Transferência</th>
                    <th>Observação</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($transfers) > 0): ?>
                    <?php foreach ($transfers as $transfer): ?>
                        <tr>
                            <td><?= htmlspecialchars($transfer['id_transferencia']) ?></td>
                            <td><?= htmlspecialchars($transfer['departamento_atual']) ?></td>
                            <td><?= htmlspecialchars($transfer['departamento_destino']) ?></td>
                            <td><?= htmlspecialchars($transfer['numero_patrimonio']) ?></td>
                            <td><?= htmlspecialchars($transfer['responsavel']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($transfer['data_transferencia'])) ?></td>
                            <td><?= htmlspecialchars($transfer['observacao']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">Nenhuma transferência encontrada</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Rodapé -->
<footer>
    <p>&copy; <?= date('Y') ?> Controle de Patrimônio. Todos os direitos reservados.</p>
</footer>

<!-- Scripts JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Validação do formulário para verificar se a data final não é anterior à data inicial
    function validateForm() {
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;

        if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
            alert('A Data Fim não pode ser anterior à Data Início.');
            return false;
        }
        return true;
    }
</script>

</body>
</html>
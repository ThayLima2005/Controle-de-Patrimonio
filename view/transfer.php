<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['usuario_logado'])) {
    header('Location: login.php');
    exit();
}

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
    $departamentos = $stmtDepartamentos->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro ao carregar dados: " . $e->getMessage());
}

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
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Patrimônios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
    <?php if (isset($_SESSION['mensagem'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['mensagem'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['mensagem']); ?>
    <?php endif; ?>

    <?php if (isset($erro)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $erro ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

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
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Format currency input
    document.getElementById('valor_aquisicao').addEventListener('blur', function() {
        let value = this.value.replace(/[^\d,]/g, '');
        value = value.replace(',', '.');
        if (value && !isNaN(value)) {
            this.value = parseFloat(value).toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    });
</script>
</body>
</html>
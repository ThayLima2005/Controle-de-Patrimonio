<?php
session_start();
require_once 'config.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_logado'])) {
    header("Location: login.php");
    exit();
}

// Funções para carregar dados
function carregarPatrimonios($pdo) {
    $stmt = $pdo->query("
        SELECT p.*, f.razao_social, d.nome_departamento 
        FROM patrimonio p
        LEFT JOIN fornecedor f ON p.fornecedor_id = f.fornecedor_id
        LEFT JOIN departamento d ON p.departamento_id = d.departamento_id
        ORDER BY p.id_patrimonio DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function carregarFornecedores($pdo) {
    $stmt = $pdo->query("SELECT fornecedor_id, razao_social FROM fornecedor ORDER BY razao_social");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function carregarDepartamentos($pdo) {
    $stmt = $pdo->query("SELECT departamento_id, nome_departamento FROM departamento ORDER BY nome_departamento");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Processar formulário de adição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar campos obrigatórios
    $required = ['descricao', 'num_patrimonio', 'data_aquisicao', 'valor_aquisicao', 
                'garantia', 'status_2', 'fornecedor_id', 'departamento_id'];
    
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['erro'] = "O campo $field é obrigatório!";
            header("Location: patrimonio.php");
            exit();
        }
    }
    
    // Tratar campos opcionais
    $marca = !empty($_POST['marca']) ? $_POST['marca'] : null;
    $nota_fiscal = !empty($_POST['nota_fiscal']) ? $_POST['nota_fiscal'] : null;
    
    // Converter valores numéricos
    $fornecedor_id = (int)$_POST['fornecedor_id'];
    $departamento_id = (int)$_POST['departamento_id'];
    $valor_aquisicao = (float)$_POST['valor_aquisicao'];
    $garantia = (int)$_POST['garantia'];
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO patrimonio (
                descricao, marca, num_patrimonio, data_aquisicao, 
                valor_aquisicao, garantia, nota_fiscal, status_2, 
                fornecedor_id, departamento_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_POST['descricao'],
            $marca,
            $_POST['num_patrimonio'],
            $_POST['data_aquisicao'],
            $valor_aquisicao,
            $garantia,
            $nota_fiscal,
            $_POST['status_2'],
            $fornecedor_id,
            $departamento_id
        ]);
        
        $_SESSION['mensagem'] = "Patrimônio adicionado com sucesso!";
        header("Location: patrimonio.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['erro'] = "Erro ao adicionar patrimônio: " . $e->getMessage();
        header("Location: patrimonio.php");
        exit();
    }
}

// Processar exclusão
if (isset($_GET['excluir'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM patrimonio WHERE id_patrimonio = ?");
        $stmt->execute([$_GET['excluir']]);
        $_SESSION['mensagem'] = "Patrimônio excluído com sucesso!";
        header("Location: patrimonio.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['erro'] = "Erro ao excluir patrimônio: " . $e->getMessage();
        header("Location: patrimonio.php");
        exit();
    }
}

// Carregar dados
$patrimonios = carregarPatrimonios($pdo);
$fornecedores = carregarFornecedores($pdo);
$departamentos = carregarDepartamentos($pdo);
?>

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
        <div class="alert alert-success"><?= $_SESSION['mensagem'] ?></div>
        <?php unset($_SESSION['mensagem']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['erro'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['erro'] ?></div>
        <?php unset($_SESSION['erro']); ?>
    <?php endif; ?>

    <h1 class="text-center">Gerenciamento de Patrimônios</h1>

    <!-- Formulário para adicionar um patrimônio -->
    <form method="POST" action="patrimonio.php">
        <div class="form-group">
            <label for="descricao">Descrição</label>
            <input type="text" class="form-control" name="descricao" id="descricao" placeholder="Descrição do patrimônio" required>
        </div>
        <div class="form-group">
            <label for="marca">Marca</label>
            <input type="text" class="form-control" name="marca" id="marca" placeholder="Marca do patrimônio">
        </div>
        <div class="form-group">
            <label for="num_patrimonio">Número do Patrimônio</label>
            <input type="text" class="form-control" name="num_patrimonio" id="num_patrimonio" placeholder="Número do patrimônio" required>
        </div>
        <div class="form-group">
            <label for="data_aquisicao">Data de Aquisição</label>
            <input type="date" class="form-control" name="data_aquisicao" id="data_aquisicao" required>
        </div>
        <div class="form-group">
            <label for="valor_aquisicao">Valor de Aquisição</label>
            <input type="number" step="0.01" class="form-control" name="valor_aquisicao" id="valor_aquisicao" placeholder="Valor em R$" required>
        </div>
        <div class="form-group">
            <label for="garantia">Garantia (em meses)</label>
            <input type="number" class="form-control" name="garantia" id="garantia" placeholder="Garantia em meses" required>
        </div>
        <div class="form-group">
            <label for="nota_fiscal">Nota Fiscal</label>
            <input type="text" class="form-control" name="nota_fiscal" id="nota_fiscal" placeholder="Número da nota fiscal">
        </div>
        <div class="form-group">
            <label for="status_2">Status</label>
            <select class="form-control" name="status_2" id="status_2" required>
                <option value="">Selecione o status</option>
                <option value="Novo">Novo</option>
                <option value="Usado">Usado</option>
            </select>
        </div>
        <div class="form-group">
            <label for="fornecedor_id">Fornecedor</label>
            <select class="form-control" name="fornecedor_id" id="fornecedor_id" required>
                <option value="">Selecione um fornecedor</option>
                <?php foreach ($fornecedores as $fornecedor): ?>
                    <option value="<?= $fornecedor['fornecedor_id'] ?>"><?= htmlspecialchars($fornecedor['razao_social']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="departamento_id">Departamento</label>
            <select class="form-control" name="departamento_id" id="departamento_id" required>
                <option value="">Selecione um departamento</option>
                <?php foreach ($departamentos as $departamento): ?>
                    <option value="<?= $departamento['departamento_id'] ?>"><?= htmlspecialchars($departamento['nome_departamento']) ?></option>
                <?php endforeach; ?>
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
            <?php foreach ($patrimonios as $patrimonio): ?>
            <tr>
                <td><?= $patrimonio['id_patrimonio'] ?></td>
                <td><?= htmlspecialchars($patrimonio['descricao']) ?></td>
                <td><?= htmlspecialchars($patrimonio['marca']) ?></td>
                <td><?= htmlspecialchars($patrimonio['num_patrimonio']) ?></td>
                <td><?= date('d/m/Y', strtotime($patrimonio['data_aquisicao'])) ?></td>
                <td>R$ <?= number_format($patrimonio['valor_aquisicao'], 2, ',', '.') ?></td>
                <td><?= $patrimonio['garantia'] ?> meses</td>
                <td><?= htmlspecialchars($patrimonio['nota_fiscal']) ?></td>
                <td><?= htmlspecialchars($patrimonio['status_2']) ?></td>
                <td><?= htmlspecialchars($patrimonio['razao_social'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($patrimonio['nome_departamento'] ?? 'N/A') ?></td>
                <td>
                    <a href="editar_patrimonio.php?id=<?= $patrimonio['id_patrimonio'] ?>" class="btn btn-warning btn-sm">Editar</a>
                    <a href="patrimonio.php?excluir=<?= $patrimonio['id_patrimonio'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este patrimônio?')">Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
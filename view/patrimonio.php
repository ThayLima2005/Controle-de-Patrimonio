<?php
session_start();
require_once 'config.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_logado'])) {
    header("Location: login.php");
    exit();
}

// Inicializa variáveis de mensagem
$mensagem = '';
$tipoMensagem = '';

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

// Processar adição/edição de patrimônio
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required = ['descricao', 'num_patrimonio', 'data_aquisicao', 'valor_aquisicao', 
                'garantia', 'status_2', 'fornecedor_id', 'departamento_id'];
    
    // Validar campos obrigatórios
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $mensagem = "O campo $field é obrigatório!";
            $tipoMensagem = 'danger';
            break;
        }
    }
    
    if (empty($mensagem)) {
        // Tratar campos opcionais
        $marca = !empty($_POST['marca']) ? $_POST['marca'] : null;
        $nota_fiscal = !empty($_POST['nota_fiscal']) ? $_POST['nota_fiscal'] : null;
        
        // Converter valores numéricos
        $fornecedor_id = (int)$_POST['fornecedor_id'];
        $departamento_id = (int)$_POST['departamento_id'];
        $valor_aquisicao = (float)$_POST['valor_aquisicao'];
        $garantia = (int)$_POST['garantia'];
        
        try {
            if (isset($_POST['editar'])) {
                // Atualizar patrimônio existente
                $id_patrimonio = (int)$_POST['id_patrimonio'];
                
                $stmt = $pdo->prepare("
                    UPDATE patrimonio SET
                        descricao = ?,
                        marca = ?,
                        num_patrimonio = ?,
                        data_aquisicao = ?,
                        valor_aquisicao = ?,
                        garantia = ?,
                        nota_fiscal = ?,
                        status_2 = ?,
                        fornecedor_id = ?,
                        departamento_id = ?
                    WHERE id_patrimonio = ?
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
                    $departamento_id,
                    $id_patrimonio
                ]);
                
                $mensagem = "Patrimônio atualizado com sucesso!";
                $tipoMensagem = 'success';
            } else {
                // Adicionar novo patrimônio
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
                
                $mensagem = "Patrimônio adicionado com sucesso!";
                $tipoMensagem = 'success';
            }
        } catch (PDOException $e) {
            $mensagem = "Erro ao salvar patrimônio: " . $e->getMessage();
            $tipoMensagem = 'danger';
        }
    }
}

// Processar exclusão
if (isset($_GET['excluir'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM patrimonio WHERE id_patrimonio = ?");
        $stmt->execute([$_GET['excluir']]);
        $mensagem = "Patrimônio excluído com sucesso!";
        $tipoMensagem = 'success';
    } catch (PDOException $e) {
        $mensagem = "Erro ao excluir patrimônio: " . $e->getMessage();
        $tipoMensagem = 'danger';
    }
}

// Carregar dados para edição
$patrimonioEditar = null;
if (isset($_GET['editar'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM patrimonio 
            WHERE id_patrimonio = ?
        ");
        $stmt->execute([$_GET['editar']]);
        $patrimonioEditar = $stmt->fetch();
    } catch (PDOException $e) {
        $mensagem = "Erro ao carregar patrimônio: " . $e->getMessage();
        $tipoMensagem = 'danger';
    }
}

// Carregar listas
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 56px;
        }
        .navbar {
            margin-bottom: 20px;
        }
        .card {
            background-color: #f8f9fa;
        }
        .table thead th {
            background-color: #007bff;
            color: #fff;
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
        <ul class="navbar-nav me-auto">
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

<div class="container mt-5">
    <?php if (!empty($mensagem)): ?>
        <div class="alert alert-<?= $tipoMensagem ?> alert-dismissible fade show">
            <?= $mensagem ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <h2><?= isset($_GET['editar']) ? 'Editar' : 'Adicionar' ?> Patrimônio</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <form method="POST">
                        <?php if (isset($patrimonioEditar)): ?>
                            <input type="hidden" name="id_patrimonio" value="<?= $patrimonioEditar['id_patrimonio'] ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label class="form-label">Descrição*</label>
                            <input type="text" class="form-control" name="descricao" required
                                   value="<?= htmlspecialchars($patrimonioEditar['descricao'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Marca</label>
                            <input type="text" class="form-control" name="marca"
                                   value="<?= htmlspecialchars($patrimonioEditar['marca'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Número do Patrimônio*</label>
                            <input type="text" class="form-control" name="num_patrimonio" required
                                   value="<?= htmlspecialchars($patrimonioEditar['num_patrimonio'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Data de Aquisição*</label>
                            <input type="date" class="form-control" name="data_aquisicao" required
                                   value="<?= htmlspecialchars($patrimonioEditar['data_aquisicao'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Valor de Aquisição*</label>
                            <input type="number" step="0.01" class="form-control" name="valor_aquisicao" required
                                   value="<?= htmlspecialchars($patrimonioEditar['valor_aquisicao'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Garantia (meses)*</label>
                            <input type="number" class="form-control" name="garantia" required
                                   value="<?= htmlspecialchars($patrimonioEditar['garantia'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Nota Fiscal</label>
                            <input type="text" class="form-control" name="nota_fiscal"
                                   value="<?= htmlspecialchars($patrimonioEditar['nota_fiscal'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Status*</label>
                            <select class="form-control" name="status_2" required>
                                <option value="Novo" <?= ($patrimonioEditar['status_2'] ?? '') == 'Novo' ? 'selected' : '' ?>>Novo</option>
                                <option value="Usado" <?= ($patrimonioEditar['status_2'] ?? '') == 'Usado' ? 'selected' : '' ?>>Usado</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Fornecedor*</label>
                            <select class="form-control" name="fornecedor_id" required>
                                <option value="">Selecione um fornecedor</option>
                                <?php foreach ($fornecedores as $fornecedor): ?>
                                    <option value="<?= $fornecedor['fornecedor_id'] ?>" 
                                        <?= ($patrimonioEditar['fornecedor_id'] ?? '') == $fornecedor['fornecedor_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($fornecedor['razao_social']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Departamento*</label>
                            <select class="form-control" name="departamento_id" required>
                                <option value="">Selecione um departamento</option>
                                <?php foreach ($departamentos as $departamento): ?>
                                    <option value="<?= $departamento['departamento_id'] ?>" 
                                        <?= ($patrimonioEditar['departamento_id'] ?? '') == $departamento['departamento_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($departamento['nome_departamento']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" name="<?= isset($_GET['editar']) ? 'editar' : 'adicionar' ?>">
                            <?= isset($_GET['editar']) ? 'Atualizar' : 'Cadastrar' ?> Patrimônio
                        </button>
                        
                        <?php if (isset($_GET['editar'])): ?>
                            <a href="patrimonio.php" class="btn btn-secondary">Cancelar</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <h2>Lista de Patrimônios</h2>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>ID</th>
                            <th>Descrição</th>
                            <th>Nº Patrimônio</th>
                            <th>Valor (R$)</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($patrimonios) > 0): ?>
                            <?php foreach ($patrimonios as $patrimonio): ?>
                                <tr>
                                    <td><?= $patrimonio['id_patrimonio'] ?></td>
                                    <td><?= htmlspecialchars($patrimonio['descricao']) ?></td>
                                    <td><?= htmlspecialchars($patrimonio['num_patrimonio']) ?></td>
                                    <td>R$ <?= number_format($patrimonio['valor_aquisicao'], 2, ',', '.') ?></td>
                                    <td>
                                        <a href="patrimonio.php?editar=<?= $patrimonio['id_patrimonio'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                        <a href="patrimonio.php?excluir=<?= $patrimonio['id_patrimonio'] ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Tem certeza que deseja excluir este patrimônio?')">
                                            Excluir
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">Nenhum patrimônio cadastrado</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_logado'])) {
    header('Location: login.php');
    exit();
}

// Configurações do banco de dados (ajuste conforme seu ambiente)
define('DB_HOST', 'localhost');
define('DB_NAME', 'controle_patrimonio');
define('DB_USER', 'root');
define('DB_PASS', '123456');
define('DB_PORT', '3307'); // Adicione se estiver usando porta diferente

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";port=".DB_PORT, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao conectar com o banco de dados: " . $e->getMessage());
}

// Inicializa variáveis de mensagem
$mensagem = '';
$tipoMensagem = '';

// Processar adição de novo departamento
if (isset($_POST['adicionar'])) {
    $nome = trim($_POST['nome_departamento']);
    $responsavel = trim($_POST['responsavel']);
    $telefone = trim($_POST['telefone']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

    if (empty($nome) || empty($responsavel) || empty($telefone) || !$email) {
        $mensagem = 'Preencha todos os campos corretamente!';
        $tipoMensagem = 'danger';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO departamento (nome_departamento, responsavel, telefone, email) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nome, $responsavel, $telefone, $email]);
            $mensagem = 'Departamento adicionado com sucesso!';
            $tipoMensagem = 'success';
        } catch (PDOException $e) {
            $mensagem = 'Erro ao cadastrar departamento: ' . $e->getMessage();
            $tipoMensagem = 'danger';
        }
    }
}

// Processar exclusão de departamento
if (isset($_GET['excluir'])) {
    $id = filter_var($_GET['excluir'], FILTER_VALIDATE_INT);
    if ($id !== false) {
        try {
            // Verificar se o departamento está vinculado a patrimônios
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM patrimonio WHERE departamento_id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->fetchColumn() > 0) {
                $mensagem = 'Não é possível excluir: departamento está vinculado a patrimônios!';
                $tipoMensagem = 'danger';
            } else {
                $stmt = $pdo->prepare("DELETE FROM departamento WHERE departamento_id = ?");
                $stmt->execute([$id]);
                $mensagem = 'Departamento excluído com sucesso!';
                $tipoMensagem = 'success';
            }
        } catch (PDOException $e) {
            $mensagem = 'Erro ao excluir departamento: ' . $e->getMessage();
            $tipoMensagem = 'danger';
        }
    } else {
        $mensagem = 'ID inválido para exclusão!';
        $tipoMensagem = 'danger';
    }
}

// Processar edição de departamento
if (isset($_POST['editar'])) {
    $id = filter_var($_POST['departamento_id'], FILTER_VALIDATE_INT);
    $nome = trim($_POST['nome_departamento']);
    $responsavel = trim($_POST['responsavel']);
    $telefone = trim($_POST['telefone']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

    if ($id === false || empty($nome) || empty($responsavel) || empty($telefone) || !$email) {
        $mensagem = 'Dados inválidos para edição!';
        $tipoMensagem = 'danger';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE departamento SET nome_departamento = ?, responsavel = ?, telefone = ?, email = ? WHERE departamento_id = ?");
            $stmt->execute([$nome, $responsavel, $telefone, $email, $id]);
            $mensagem = 'Departamento atualizado com sucesso!';
            $tipoMensagem = 'success';
        } catch (PDOException $e) {
            $mensagem = 'Erro ao atualizar departamento: ' . $e->getMessage();
            $tipoMensagem = 'danger';
        }
    }
}

// Obter lista de departamentos para exibição
try {
    $stmt = $pdo->query("SELECT * FROM departamento ORDER BY nome_departamento");
    $departamentos = $stmt->fetchAll();
} catch (PDOException $e) {
    $mensagem = 'Erro ao carregar departamentos: ' . $e->getMessage();
    $tipoMensagem = 'danger';
    $departamentos = [];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Departamentos</title>
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
        .form-control {
            min-width: 250px;
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
<div class="container mt-5">
    <?php if (!empty($mensagem)): ?>
        <div class="alert alert-<?= $tipoMensagem ?> alert-dismissible fade show">
            <?= $mensagem ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <h2><?= isset($_GET['editar']) ? 'Editar' : 'Adicionar' ?> Departamento</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <form method="POST">
                        <?php if (isset($_GET['editar']) && !empty($_GET['editar'])):
                            $departamentoEditar = null;
                            try {
                                $stmt = $pdo->prepare("SELECT * FROM departamento WHERE departamento_id = ?");
                                $stmt->execute([$_GET['editar']]);
                                $departamentoEditar = $stmt->fetch();
                            } catch (PDOException $e) {
                                $mensagem = 'Erro ao carregar departamento: ' . $e->getMessage();
                                $tipoMensagem = 'danger';
                            }
                            if ($departamentoEditar): ?>
                                <input type="hidden" name="departamento_id" value="<?= $departamentoEditar['departamento_id'] ?>">
                        <?php endif; 
                        endif; ?>
                        
                        <div class="mb-3">
                            <label class="form-label">Nome do Departamento*</label>
                            <input type="text" class="form-control" name="nome_departamento" required
                                value="<?= $departamentoEditar['nome_departamento'] ?? '' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Responsável*</label>
                            <input type="text" class="form-control" name="responsavel" required
                                value="<?= $departamentoEditar['responsavel'] ?? '' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Telefone*</label>
                            <input type="text" class="form-control phone-mask" name="telefone" required
                                value="<?= $departamentoEditar['telefone'] ?? '' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email*</label>
                            <input type="email" class="form-control" name="email" required
                                value="<?= $departamentoEditar['email'] ?? '' ?>">
                        </div>
                        <button type="submit" class="btn btn-primary" name="<?= isset($_GET['editar']) ? 'editar' : 'adicionar' ?>">
                            <?= isset($_GET['editar']) ? 'Atualizar' : 'Cadastrar' ?> Departamento
                        </button>
                        
                        <?php if (isset($_GET['editar'])): ?>
                            <a href="departamento.php" class="btn btn-secondary">Cancelar</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <h2>Lista de Departamentos</h2>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Responsável</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($departamentos) > 0): ?>
                            <?php foreach ($departamentos as $departamento): ?>
                                <tr>
                                    <td><?= $departamento['departamento_id'] ?></td>
                                    <td><?= htmlspecialchars($departamento['nome_departamento']) ?></td>
                                    <td><?= htmlspecialchars($departamento['responsavel']) ?></td>
                                    <td>
                                        <a href="departamento.php?editar=<?= $departamento['departamento_id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                        <a href="departamento.php?excluir=<?= $departamento['departamento_id'] ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Tem certeza que deseja excluir este departamento?')">
                                            Excluir
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">Nenhum departamento cadastrado</td>
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
// Máscara para telefone
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.phone-mask').forEach(el => {
        el.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '')
                .replace(/(\d{2})(\d)/, '($1) $2')
                .replace(/(\d{4,5})(\d)/, '$1-$2')
                .substr(0, 15);
        });
    });
});
</script>
</body>
</html>
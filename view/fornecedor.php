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

// Processar adição de novo fornecedor
if (isset($_POST['adicionar'])) {
    $razao_social = trim($_POST['razao_social']);
    $cnpj = preg_replace('/[^0-9]/', '', $_POST['cnpj']);
    $telefone = trim($_POST['telefone']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    
    // Validação básica
    if (empty($razao_social) || strlen($cnpj) != 14 || !$email) {
        $mensagem = 'Preencha todos os campos corretamente! CNPJ deve ter 14 dígitos.';
        $tipoMensagem = 'danger';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO fornecedor 
                (razao_social, cnpj, telefone, email, cidade, cep, uf, bairro, endereco, numero, inscricao_municipal, inscricao_estadual) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $razao_social,
                $cnpj,
                $telefone,
                $email,
                $_POST['cidade'] ?? null,
                preg_replace('/[^0-9]/', '', $_POST['cep'] ?? ''),
                $_POST['uf'] ?? null,
                $_POST['bairro'] ?? null,
                $_POST['endereco'] ?? null,
                $_POST['numero'] ?? null,
                $_POST['inscricao_municipal'] ?? null,
                $_POST['inscricao_estadual'] ?? null
            ]);
            
            $mensagem = 'Fornecedor adicionado com sucesso!';
            $tipoMensagem = 'success';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Erro de duplicidade (CNPJ único)
                $mensagem = 'Erro: CNPJ já cadastrado!';
            } else {
                $mensagem = 'Erro ao cadastrar fornecedor: ' . $e->getMessage();
            }
            $tipoMensagem = 'danger';
        }
    }
}

// Processar exclusão de fornecedor
if (isset($_GET['excluir'])) {
    $id = filter_var($_GET['excluir'], FILTER_VALIDATE_INT);
    if ($id !== false) {
        try {
            // Verificar se o fornecedor está vinculado a patrimônios
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM patrimonio WHERE fornecedor_id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->fetchColumn() > 0) {
                $mensagem = 'Não é possível excluir: fornecedor está vinculado a patrimônios!';
                $tipoMensagem = 'danger';
            } else {
                $stmt = $pdo->prepare("DELETE FROM fornecedor WHERE fornecedor_id = ?");
                $stmt->execute([$id]);
                $mensagem = 'Fornecedor excluído com sucesso!';
                $tipoMensagem = 'success';
            }
        } catch (PDOException $e) {
            $mensagem = 'Erro ao excluir fornecedor: ' . $e->getMessage();
            $tipoMensagem = 'danger';
        }
    } else {
        $mensagem = 'ID inválido para exclusão!';
        $tipoMensagem = 'danger';
    }
}

// Processar edição de fornecedor
if (isset($_POST['editar'])) {
    $id = filter_var($_POST['fornecedor_id'], FILTER_VALIDATE_INT);
    $razao_social = trim($_POST['razao_social']);
    $cnpj = preg_replace('/[^0-9]/', '', $_POST['cnpj']);
    $telefone = trim($_POST['telefone']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    
    if ($id === false || empty($razao_social) || strlen($cnpj) != 14 || !$email) {
        $mensagem = 'Dados inválidos para edição!';
        $tipoMensagem = 'danger';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE fornecedor SET 
                razao_social = ?, 
                cnpj = ?, 
                telefone = ?, 
                email = ?,
                cidade = ?,
                cep = ?,
                uf = ?,
                bairro = ?,
                endereco = ?,
                numero = ?,
                inscricao_municipal = ?,
                inscricao_estadual = ?
                WHERE fornecedor_id = ?");
            
            $stmt->execute([
                $razao_social,
                $cnpj,
                $telefone,
                $email,
                $_POST['cidade'] ?? null,
                preg_replace('/[^0-9]/', '', $_POST['cep'] ?? ''),
                $_POST['uf'] ?? null,
                $_POST['bairro'] ?? null,
                $_POST['endereco'] ?? null,
                $_POST['numero'] ?? null,
                $_POST['inscricao_municipal'] ?? null,
                $_POST['inscricao_estadual'] ?? null,
                $id
            ]);
            
            $mensagem = 'Fornecedor atualizado com sucesso!';
            $tipoMensagem = 'success';
        } catch (PDOException $e) {
            $mensagem = 'Erro ao atualizar fornecedor: ' . $e->getMessage();
            $tipoMensagem = 'danger';
        }
    }
}

// Obter lista de fornecedores para exibição
try {
    $stmt = $pdo->query("SELECT * FROM fornecedor ORDER BY razao_social");
    $fornecedores = $stmt->fetchAll();
} catch (PDOException $e) {
    $mensagem = 'Erro ao carregar fornecedores: ' . $e->getMessage();
    $tipoMensagem = 'danger';
    $fornecedores = [];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Fornecedores</title>
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
        .form-section {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">Controle de Patrimônio</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Página Inicial</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="transferencia.php">Transferências</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="fornecedor.php">Fornecedores</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="departamento.php">Departamentos</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <?php if (!empty($mensagem)): ?>
        <div class="alert alert-<?= $tipoMensagem ?> alert-dismissible fade show">
            <?= $mensagem ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="form-section">
        <h2><?= isset($_GET['editar']) ? 'Editar' : 'Adicionar' ?> Fornecedor</h2>
        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <?php if (isset($_GET['editar']) && !empty($_GET['editar'])):
                        $fornecedorEditar = null;
                        try {
                            $stmt = $pdo->prepare("SELECT * FROM fornecedor WHERE fornecedor_id = ?");
                            $stmt->execute([$_GET['editar']]);
                            $fornecedorEditar = $stmt->fetch();
                        } catch (PDOException $e) {
                            $mensagem = 'Erro ao carregar fornecedor: ' . $e->getMessage();
                            $tipoMensagem = 'danger';
                        }
                        if ($fornecedorEditar): ?>
                            <input type="hidden" name="fornecedor_id" value="<?= $fornecedorEditar['fornecedor_id'] ?>">
                    <?php endif; 
                    endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Razão Social*</label>
                                <input type="text" class="form-control" name="razao_social" required
                                    value="<?= $fornecedorEditar['razao_social'] ?? '' ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">CNPJ*</label>
                                <input type="text" class="form-control cnpj-mask" name="cnpj" required
                                    value="<?= $fornecedorEditar['cnpj'] ?? '' ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Telefone*</label>
                                <input type="text" class="form-control phone-mask" name="telefone" required
                                    value="<?= $fornecedorEditar['telefone'] ?? '' ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email*</label>
                                <input type="email" class="form-control" name="email" required
                                    value="<?= $fornecedorEditar['email'] ?? '' ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">CEP</label>
                                <input type="text" class="form-control cep-mask" name="cep"
                                    value="<?= $fornecedorEditar['cep'] ?? '' ?>">
                            </div>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label">Endereço</label>
                                        <input type="text" class="form-control" name="endereco"
                                            value="<?= $fornecedorEditar['endereco'] ?? '' ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Número</label>
                                        <input type="text" class="form-control" name="numero"
                                            value="<?= $fornecedorEditar['numero'] ?? '' ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Bairro</label>
                                <input type="text" class="form-control" name="bairro"
                                    value="<?= $fornecedorEditar['bairro'] ?? '' ?>">
                            </div>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label">Cidade</label>
                                        <input type="text" class="form-control" name="cidade"
                                            value="<?= $fornecedorEditar['cidade'] ?? '' ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">UF</label>
                                        <select class="form-select" name="uf">
                                            <option value="">Selecione</option>
                                            <?php
                                            $ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
                                            foreach ($ufs as $uf): ?>
                                                <option value="<?= $uf ?>" <?= (($fornecedorEditar['uf'] ?? '') == $uf) ? 'selected' : '' ?>>
                                                    <?= $uf ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Inscrição Municipal</label>
                                <input type="text" class="form-control" name="inscricao_municipal"
                                    value="<?= $fornecedorEditar['inscricao_municipal'] ?? '' ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Inscrição Estadual</label>
                                <input type="text" class="form-control" name="inscricao_estadual"
                                    value="<?= $fornecedorEditar['inscricao_estadual'] ?? '' ?>">
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" name="<?= isset($_GET['editar']) ? 'editar' : 'adicionar' ?>">
                        <?= isset($_GET['editar']) ? 'Atualizar' : 'Cadastrar' ?> Fornecedor
                    </button>
                    
                    <?php if (isset($_GET['editar'])): ?>
                        <a href="fornecedor.php" class="btn btn-secondary">Cancelar</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <div class="form-section">
        <h2>Lista de Fornecedores</h2>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-primary">
                    <tr>
                        <th>ID</th>
                        <th>Razão Social</th>
                        <th>CNPJ</th>
                        <th>Telefone</th>
                        <th>Email</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($fornecedores) > 0): ?>
                        <?php foreach ($fornecedores as $fornecedor): ?>
                            <tr>
                                <td><?= $fornecedor['fornecedor_id'] ?></td>
                                <td><?= htmlspecialchars($fornecedor['razao_social']) ?></td>
                                <td class="cnpj-mask"><?= $fornecedor['cnpj'] ?></td>
                                <td class="phone-mask"><?= $fornecedor['telefone'] ?></td>
                                <td><?= htmlspecialchars($fornecedor['email']) ?></td>
                                <td>
                                    <a href="fornecedor.php?editar=<?= $fornecedor['fornecedor_id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                    <a href="fornecedor.php?excluir=<?= $fornecedor['fornecedor_id'] ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Tem certeza que deseja excluir este fornecedor?')">
                                        Excluir
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Nenhum fornecedor cadastrado</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Máscaras para os campos
document.addEventListener('DOMContentLoaded', function() {
    // Máscara para telefone
    document.querySelectorAll('.phone-mask').forEach(el => {
        el.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '')
                .replace(/(\d{2})(\d)/, '($1) $2')
                .replace(/(\d{4,5})(\d)/, '$1-$2')
                .substr(0, 15);
        });
    });
    
    // Máscara para CNPJ
    document.querySelectorAll('.cnpj-mask').forEach(el => {
        el.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '')
                .replace(/(\d{2})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d)/, '$1/$2')
                .replace(/(\d{4})(\d)/, '$1-$2')
                .substr(0, 18);
        });
    });
    
    // Máscara para CEP
    document.querySelectorAll('.cep-mask').forEach(el => {
        el.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '')
                .replace(/(\d{5})(\d)/, '$1-$2')
                .substr(0, 9);
        });
    });
});
</script>
</body>
</html>
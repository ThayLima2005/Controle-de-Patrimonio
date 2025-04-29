<?php
<<<<<<< HEAD
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
=======
// Configurações do banco de dados
$host = 'localhost';
$dbname = 'controle_patrimonio';
$username = 'root';
$password = '1234';

// Conexão com o banco de dados
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

// Processar operações CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Adicionar novo fornecedor
    if (isset($_POST['razao_social'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO fornecedores 
                (razao_social, cnpj, cidade, cep, uf, bairro, endereco, numero, telefone, email, inscricao_municipal, inscricao_estadual) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $_POST['razao_social'],
                $_POST['cnpj'],
                $_POST['cidade'],
                $_POST['cep'],
                $_POST['uf'],
                $_POST['bairro'],
                $_POST['endereco'],
                $_POST['numero'],
                $_POST['telefone'],
                $_POST['email'],
                $_POST['inscricao_municipal'],
                $_POST['inscricao_estadual']
            ]);
            
            header("Location: fornecedor.php?success=1");
            exit();
        } catch (PDOException $e) {
            $error = "Erro ao cadastrar fornecedor: " . $e->getMessage();
        }
    }
    
    // Editar fornecedor
    if (isset($_POST['edit_id'])) {
        try {
            $stmt = $pdo->prepare("UPDATE fornecedores SET 
                razao_social = ?, cnpj = ?, cidade = ?, cep = ?, uf = ?, bairro = ?, 
                endereco = ?, numero = ?, telefone = ?, email = ?, 
                inscricao_municipal = ?, inscricao_estadual = ? 
                WHERE id_fornecedor = ?");
            
            $stmt->execute([
                $_POST['razao_social'],
                $_POST['cnpj'],
                $_POST['cidade'],
                $_POST['cep'],
                $_POST['uf'],
                $_POST['bairro'],
                $_POST['endereco'],
                $_POST['numero'],
                $_POST['telefone'],
                $_POST['email'],
                $_POST['inscricao_municipal'],
                $_POST['inscricao_estadual'],
                $_POST['edit_id']
            ]);
            
            header("Location: fornecedor.php?success=1");
            exit();
        } catch (PDOException $e) {
            $error = "Erro ao atualizar fornecedor: " . $e->getMessage();
>>>>>>> bd487f09ead85e150ce6fcd6d1e227f374995a36
        }
    }
}

<<<<<<< HEAD
// Obter lista de fornecedores para exibição
try {
    $stmt = $pdo->query("SELECT * FROM fornecedor ORDER BY razao_social");
    $fornecedores = $stmt->fetchAll();
} catch (PDOException $e) {
    $mensagem = 'Erro ao carregar fornecedores: ' . $e->getMessage();
    $tipoMensagem = 'danger';
    $fornecedores = [];
=======
// Excluir fornecedor
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM fornecedores WHERE id_fornecedor = ?");
        $stmt->execute([$_GET['delete']]);
        
        header("Location: fornecedor.php?success=1");
        exit();
    } catch (PDOException $e) {
        $error = "Erro ao excluir fornecedor: " . $e->getMessage();
    }
}

// Buscar fornecedores para listagem
try {
    $stmt = $pdo->query("SELECT * FROM fornecedores");
    $fornecedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Erro ao carregar fornecedores: " . $e->getMessage();
}

// Buscar fornecedor para edição
$editing = null;
if (isset($_GET['edit'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM fornecedores WHERE id_fornecedor = ?");
        $stmt->execute([$_GET['edit']]);
        $editing = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Erro ao carregar fornecedor para edição: " . $e->getMessage();
    }
>>>>>>> bd487f09ead85e150ce6fcd6d1e227f374995a36
}
?>

<!DOCTYPE html>
<<<<<<< HEAD
<html lang="pt-BR">
=======
<html lang="pt-br">
>>>>>>> bd487f09ead85e150ce6fcd6d1e227f374995a36
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Fornecedores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<<<<<<< HEAD
=======
    <link href="styles.css" rel="stylesheet">
>>>>>>> bd487f09ead85e150ce6fcd6d1e227f374995a36
    <style>
        body {
            padding-top: 56px;
        }
<<<<<<< HEAD
        .navbar {
            margin-bottom: 20px;
        }
        .card {
            background-color: #f8f9fa;
        }
=======

        .navbar {
            margin-bottom: 20px;
        }

        .card {
            background-color: #f8f9fa;
        }

>>>>>>> bd487f09ead85e150ce6fcd6d1e227f374995a36
        .table thead th {
            background-color: #007bff;
            color: #fff;
        }
<<<<<<< HEAD
        .form-section {
            margin-bottom: 30px;
        }
=======
>>>>>>> bd487f09ead85e150ce6fcd6d1e227f374995a36
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
<<<<<<< HEAD
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
=======
    <a class="navbar-brand" href="index.php">Controle de Patrimônio</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="index.php">Página Inicial</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="fornecedor.php">Gerenciar Fornecedores</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="departamento.php">Gerenciar Departamentos</a>
            </li>
        </ul>
>>>>>>> bd487f09ead85e150ce6fcd6d1e227f374995a36
    </div>
</nav>

<div class="container mt-5">
<<<<<<< HEAD
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
=======
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php elseif (isset($_GET['success'])): ?>
        <div class="alert alert-success">Operação realizada com sucesso!</div>
    <?php endif; ?>

    <h2 class="mb-4"><?= $editing ? 'Editar' : 'Adicionar' ?> Fornecedor</h2>
    <div class="card border-light shadow-lg rounded-3 mb-4">
        <div class="card-body">
            <form method="POST">
                <?php if ($editing): ?>
                    <input type="hidden" name="edit_id" value="<?= $editing['id_fornecedor'] ?>">
                <?php endif; ?>
                
                <div class="mb-3">
                    <label for="razao_social" class="form-label">Razão Social</label>
                    <input type="text" class="form-control" id="razao_social" name="razao_social" 
                           value="<?= $editing ? htmlspecialchars($editing['razao_social']) : '' ?>" required>
                </div>
                <div class="mb-3">
                    <label for="cnpj" class="form-label">CNPJ</label>
                    <input type="text" class="form-control" id="cnpj" name="cnpj" 
                           value="<?= $editing ? htmlspecialchars($editing['cnpj']) : '' ?>" required>
                </div>
                <div class="mb-3">
                    <label for="cidade" class="form-label">Cidade</label>
                    <input type="text" class="form-control" id="cidade" name="cidade" 
                           value="<?= $editing ? htmlspecialchars($editing['cidade']) : '' ?>" required>
                </div>
                <div class="mb-3">
                    <label for="cep" class="form-label">CEP</label>
                    <input type="text" class="form-control" id="cep" name="cep" 
                           value="<?= $editing ? htmlspecialchars($editing['cep']) : '' ?>" required>
                </div>
                <div class="mb-3">
                    <label for="uf" class="form-label">UF</label>
                    <input type="text" class="form-control" id="uf" name="uf" 
                           value="<?= $editing ? htmlspecialchars($editing['uf']) : '' ?>" required>
                </div>
                <div class="mb-3">
                    <label for="bairro" class="form-label">Bairro</label>
                    <input type="text" class="form-control" id="bairro" name="bairro" 
                           value="<?= $editing ? htmlspecialchars($editing['bairro']) : '' ?>" required>
                </div>
                <div class="mb-3">
                    <label for="endereco" class="form-label">Endereço</label>
                    <input type="text" class="form-control" id="endereco" name="endereco" 
                           value="<?= $editing ? htmlspecialchars($editing['endereco']) : '' ?>" required>
                </div>
                <div class="mb-3">
                    <label for="numero" class="form-label">Número</label>
                    <input type="text" class="form-control" id="numero" name="numero" 
                           value="<?= $editing ? htmlspecialchars($editing['numero']) : '' ?>" required>
                </div>
                <div class="mb-3">
                    <label for="telefone" class="form-label">Telefone</label>
                    <input type="text" class="form-control" id="telefone" name="telefone" 
                           value="<?= $editing ? htmlspecialchars($editing['telefone']) : '' ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?= $editing ? htmlspecialchars($editing['email']) : '' ?>" required>
                </div>
                <div class="mb-3">
                    <label for="inscricao_municipal" class="form-label">Inscrição Municipal</label>
                    <input type="text" class="form-control" id="inscricao_municipal" name="inscricao_municipal" 
                           value="<?= $editing ? htmlspecialchars($editing['inscricao_municipal']) : '' ?>" required>
                </div>
                <div class="mb-3">
                    <label for="inscricao_estadual" class="form-label">Inscrição Estadual</label>
                    <input type="text" class="form-control" id="inscricao_estadual" name="inscricao_estadual" 
                           value="<?= $editing ? htmlspecialchars($editing['inscricao_estadual']) : '' ?>" required>
                </div>
                <button type="submit" class="btn btn-primary"><?= $editing ? 'Salvar Alterações' : 'Adicionar Fornecedor' ?></button>
                <?php if ($editing): ?>
                    <a href="fornecedor.php" class="btn btn-secondary">Cancelar</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <h2 class="mb-4">Lista de Fornecedores</h2>
    <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>Razão Social</th>
                <th>CNPJ</th>
                <th>Cidade</th>
                <th>CEP</th>
                <th>UF</th>
                <th>Bairro</th>
                <th>Endereço</th>
                <th>Número</th>
                <th>Telefone</th>
                <th>Email</th>
                <th>Inscrição Municipal</th>
                <th>Inscrição Estadual</th>
                <th>Ações</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($fornecedores as $fornecedor): ?>
                <tr>
                    <td><?= $fornecedor['id_fornecedor'] ?></td>
                    <td><?= htmlspecialchars($fornecedor['razao_social']) ?></td>
                    <td><?= htmlspecialchars($fornecedor['cnpj']) ?></td>
                    <td><?= htmlspecialchars($fornecedor['cidade']) ?></td>
                    <td><?= htmlspecialchars($fornecedor['cep']) ?></td>
                    <td><?= htmlspecialchars($fornecedor['uf']) ?></td>
                    <td><?= htmlspecialchars($fornecedor['bairro']) ?></td>
                    <td><?= htmlspecialchars($fornecedor['endereco']) ?></td>
                    <td><?= htmlspecialchars($fornecedor['numero']) ?></td>
                    <td><?= htmlspecialchars($fornecedor['telefone']) ?></td>
                    <td><?= htmlspecialchars($fornecedor['email']) ?></td>
                    <td><?= htmlspecialchars($fornecedor['inscricao_municipal']) ?></td>
                    <td><?= htmlspecialchars($fornecedor['inscricao_estadual']) ?></td>
                    <td>
                        <a href="fornecedor.php?edit=<?= $fornecedor['id_fornecedor'] ?>" class="btn btn-warning btn-sm">Editar</a>
                        <a href="fornecedor.php?delete=<?= $fornecedor['id_fornecedor'] ?>" class="btn btn-danger btn-sm" 
                           onclick="return confirm('Tem certeza que deseja excluir este fornecedor?')">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<footer class="bg-dark text-white text-center py-4 mt-5">
    <p>&copy; 2025 Controle de Patrimônio. Todos os direitos reservados.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
>>>>>>> bd487f09ead85e150ce6fcd6d1e227f374995a36
</body>
</html>
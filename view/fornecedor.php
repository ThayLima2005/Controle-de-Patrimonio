<?php
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
        }
    }
}

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
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Fornecedores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
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
    </div>
</nav>

<div class="container mt-5">
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
</body>
</html>
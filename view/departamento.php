<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Departamentos</title>
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
                <a class="nav-link" href="transfer.php">Registrar Transferência</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="fornecedor.php">Gerenciar Fornecedores</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="departamento.php">Gerenciar Departamentos</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container mt-5">
    <?php
    // Configurações do banco de dados
    $host = 'localhost';
    $dbname = 'controle_patrimonio';
    $username = 'root';
    $password = '1234';
    
    // Inicializa variáveis de mensagem
    $mensagem = '';
    $tipoMensagem = '';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
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
                $stmt = $pdo->prepare("INSERT INTO departamentos (nome_departamento, responsavel, telefone, email) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nome, $responsavel, $telefone, $email]);
                $mensagem = 'Departamento adicionado com sucesso!';
                $tipoMensagem = 'success';
            }
        }
        
        // Processar exclusão de departamento
        if (isset($_GET['excluir'])) {
            $id = filter_var($_GET['excluir'], FILTER_VALIDATE_INT);
            if ($id !== false) {
                $stmt = $pdo->prepare("DELETE FROM departamentos WHERE departamento_id = ?");
                $stmt->execute([$id]);
                $mensagem = 'Departamento excluído com sucesso!';
                $tipoMensagem = 'success';
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
                $stmt = $pdo->prepare("UPDATE departamentos SET nome_departamento = ?, responsavel = ?, telefone = ?, email = ? WHERE departamento_id = ?");
                $stmt->execute([$nome, $responsavel, $telefone, $email, $id]);
                $mensagem = 'Departamento atualizado com sucesso!';
                $tipoMensagem = 'success';
            }
        }
    } catch (PDOException $e) {
        $mensagem = 'Erro ao conectar com o banco de dados: ' . htmlspecialchars($e->getMessage());
        $tipoMensagem = 'danger';
    }

    // Exibir mensagens se existirem
    if (!empty($mensagem)) {
        echo "<div class='alert alert-{$tipoMensagem} alert-dismissible fade show' role='alert'>
                {$mensagem}
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
    }
    ?>

    <h2 class="mb-4">Adicionar Departamento</h2>
    <div class="card border-light shadow-lg rounded-3 mb-4">
        <div class="card-body">
            <form id="add-departamento-form" method="POST" action="">
                <div class="mb-3">
                    <label for="nome_departamento" class="form-label">Nome do Departamento</label>
                    <input type="text" class="form-control" id="nome_departamento" name="nome_departamento" required>
                </div>
                <div class="mb-3">
                    <label for="responsavel" class="form-label">Responsável</label>
                    <input type="text" class="form-control" id="responsavel" name="responsavel" required>
                </div>
                <div class="mb-3">
                    <label for="telefone" class="form-label">Telefone</label>
                    <input type="text" class="form-control" id="telefone" name="telefone" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <button type="submit" class="btn btn-primary" name="adicionar">Adicionar Departamento</button>
            </form>
        </div>
    </div>

    <h2 class="mb-4">Lista de Departamentos</h2>
    <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Responsável</th>
                <th>Telefone</th>
                <th>Email</th>
                <th>Ações</th>
            </tr>
            </thead>
            <tbody id="departamento-list">
                <?php
                try {
                    // Listar todos os departamentos
                    $stmt = $pdo->query("SELECT * FROM departamentos");
                    $departamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($departamentos) > 0) {
                        foreach ($departamentos as $departamento) {
                            $id = htmlspecialchars($departamento['departamento_id']);
                            $nome = htmlspecialchars($departamento['nome_departamento']);
                            $responsavel = htmlspecialchars($departamento['responsavel']);
                            $telefone = htmlspecialchars($departamento['telefone']);
                            $email = htmlspecialchars($departamento['email']);
                            
                            echo "<tr>
                                <td>{$id}</td>
                                <td>{$nome}</td>
                                <td>{$responsavel}</td>
                                <td>{$telefone}</td>
                                <td>{$email}</td>
                                <td>
                                    <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editModal{$id}'>Editar</button>
                                    <a href='?excluir={$id}' class='btn btn-danger btn-sm' onclick='return confirm(\"Tem certeza que deseja excluir este departamento?\")'>Excluir</a>
                                </td>
                            </tr>";
                            
                            // Modal de edição para cada departamento
                            echo "
                            <div class='modal fade' id='editModal{$id}' tabindex='-1' aria-labelledby='editModalLabel{$id}' aria-hidden='true'>
                                <div class='modal-dialog'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <h5 class='modal-title' id='editModalLabel{$id}'>Editar Departamento</h5>
                                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                        </div>
                                        <div class='modal-body'>
                                            <form method='POST' action=''>
                                                <input type='hidden' name='departamento_id' value='{$id}'>
                                                <div class='mb-3'>
                                                    <label for='nome_departamento_edit_{$id}' class='form-label'>Nome do Departamento</label>
                                                    <input type='text' class='form-control' id='nome_departamento_edit_{$id}' name='nome_departamento' value='{$nome}' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='responsavel_edit_{$id}' class='form-label'>Responsável</label>
                                                    <input type='text' class='form-control' id='responsavel_edit_{$id}' name='responsavel' value='{$responsavel}' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='telefone_edit_{$id}' class='form-label'>Telefone</label>
                                                    <input type='text' class='form-control' id='telefone_edit_{$id}' name='telefone' value='{$telefone}' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='email_edit_{$id}' class='form-label'>Email</label>
                                                    <input type='email' class='form-control' id='email_edit_{$id}' name='email' value='{$email}' required>
                                                </div>
                                                <button type='submit' class='btn btn-primary' name='editar'>Salvar Alterações</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center'>Nenhum departamento cadastrado</td></tr>";
                    }
                } catch (PDOException $e) {
                    echo "<tr><td colspan='6' class='text-center text-danger'>Erro ao carregar departamentos: ".htmlspecialchars($e->getMessage())."</td></tr>";
                }
                ?>
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
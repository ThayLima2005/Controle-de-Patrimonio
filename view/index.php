<<<<<<< HEAD
<?php
session_start();
require_once 'config.php'; // Arquivo com as configurações de conexão ao banco de dados

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_logado'])) {
    header("Location: login.php");
    exit();
}
?>

=======
>>>>>>> bd487f09ead85e150ce6fcd6d1e227f374995a36
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Patrimônio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
<<<<<<< HEAD
            background-color: #f8f9fa;
=======
>>>>>>> bd487f09ead85e150ce6fcd6d1e227f374995a36
        }

        .container {
            flex: 1;
<<<<<<< HEAD
            margin-top: 100px; /* Aumenta o espaço acima da área do conteúdo */
=======
            margin-top: 100px;
>>>>>>> bd487f09ead85e150ce6fcd6d1e227f374995a36
        }

        .navbar {
            margin-bottom: 20px;
        }

        .btn-custom {
            background-color: #0062cc;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 15px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .btn-custom:hover {
            background-color: #0056b3;
            transform: translateY(-3px);
        }

        footer {
            background-color: #343a40;
            color: white;
            text-align: center;
            padding: 20px 0;
            position: relative;
            bottom: 0;
            width: 100%;
            margin-top: auto;
        }
<<<<<<< HEAD

        h2 {
            font-size: 2rem;
            font-weight: 600;
            color: #007bff;
        }

        p {
            font-size: 1.2rem;
            color: #555;
        }
    </style>
</head>
<body>
=======
    </style>
</head>
<body>
<?php
// Inicia a sessão (para controle de login)
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_logado'])) {
    header('Location: login.php');
    exit();
}

// Inclui o cabeçalho (se quiser modularizar)
// include 'header.php';
?>

>>>>>>> bd487f09ead85e150ce6fcd6d1e227f374995a36
<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <a class="navbar-brand" href="index.php">Controle de Patrimônio</a>
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
        <a class="btn btn-outline-light" href="logout.php">Logoff</a>
    </div>
</nav>

<div class="container">
    <h2 class="mb-4">Bem-vindo ao Controle Patrimonial - Faculdade IDEAU</h2>
    <p class="mb-4">Escolha uma das opções abaixo para gerenciar o controle de patrimônio:</p>
    <div class="row">
        <div class="col-md-4 mb-3">
            <a href="patrimonio.php" class="btn btn-custom w-100">Gerenciar Patrimônio</a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="transfer.php" class="btn btn-custom w-100">Registrar Transferência</a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="fornecedor.php" class="btn btn-custom w-100">Gerenciar Fornecedor</a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="departamento.php" class="btn btn-custom w-100">Gerenciar Departamento</a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="movimento.php" class="btn btn-custom w-100">Movimento de Transferências</a>
        </div>
    </div>
</div>

<footer>
<<<<<<< HEAD
    <p>&copy; <?php echo date("Y"); ?> Controle de Patrimônio. Todos os direitos reservados.</p>
=======
    <p>&copy; <?php echo date('Y'); ?> Controle de Patrimônio. Todos os direitos reservados.</p>
>>>>>>> bd487f09ead85e150ce6fcd6d1e227f374995a36
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
<<<<<<< HEAD
</html>
=======
</html>
>>>>>>> bd487f09ead85e150ce6fcd6d1e227f374995a36

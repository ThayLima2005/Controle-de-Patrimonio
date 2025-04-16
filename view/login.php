<?php
session_start();

// Verifica se o usuário já está logado
if (isset($_SESSION['usuario_logado'])) {
    header('Location: index.php');
    exit();
}

// Conexão com o banco de dados (substitua com suas credenciais)
$host = 'localhost';
$dbname = 'controle_patrimonio';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// Processamento do formulário de login
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['usuario_logado'] = [
            'id' => $usuario['id'],
            'nome' => $usuario['nome'],
            'email' => $usuario['email'],
            'cargo' => $usuario['cargo']
        ];
        header('Location: index.php');
        exit();
    } else {
        $erro = 'Email ou senha incorretos';
    }
}

// Processamento do formulário de cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $cpf = filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_STRING);
    $cargo = filter_input(INPUT_POST, 'cargo', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'new-email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['new-password'];
    $confirmar_senha = $_POST['confirm-password'];

    if ($senha !== $confirmar_senha) {
        $erro = 'As senhas não coincidem';
    } else {
        // Verifica se o email já está cadastrado
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $erro = 'Este email já está cadastrado';
        } else {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, cpf, cargo, email, senha) VALUES (?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$nome, $cpf, $cargo, $email, $senha_hash])) {
                $_SESSION['mensagem'] = 'Conta criada com sucesso! Faça login para continuar.';
                header('Location: login.php');
                exit();
            } else {
                $erro = 'Erro ao cadastrar usuário';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Controle de Patrimônio</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="styles.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    .container {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
    }

    .card-wrapper {
      width: 100%;
      max-width: 500px;
      margin-top: 1rem;
    }

    .card {
      background-color: #ffffff;
      border: none;
      border-radius: 15px;
      padding: 2rem;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .card-title {
      color: #007bff;
    }

    .btn-custom {
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 25px;
      padding: 10px;
      transition: background-color 0.3s ease, transform 0.3s ease;
    }

    .btn-custom:hover {
      background-color: #0056b3;
      transform: translateY(-3px);
    }

    .btn-custom:focus {
      box-shadow: 0 0 5px rgba(0,123,255, .5);
    }

    #register-section {
      display: none;
    }

    footer {
      background-color: #343a40;
      color: white;
      text-align: center;
      padding: 20px 0;
      width: 100%;
      position: absolute;
      bottom: 0;
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

  <?php if ($erro): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?= $erro ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <div class="card-wrapper" id="login-section">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title text-center mb-4">Login</h3>
        <form method="POST" action="login.php">
          <input type="hidden" name="login" value="1">
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Digite seu email" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Senha</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Digite sua senha" required>
          </div>
          <button type="submit" class="btn btn-custom w-100">Entrar</button>
        </form>
        <p class="text-center mt-3">
          Não tem uma conta? <a href="#" id="show-register">Crie uma agora</a>
        </p>
      </div>
    </div>
  </div>

  <div class="card-wrapper" id="register-section">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title text-center mb-4">Criar Conta</h3>
        <form method="POST" action="login.php">
          <input type="hidden" name="register" value="1">
          <div class="mb-3">
            <label for="nome" class="form-label">Nome</label>
            <input type="text" class="form-control" id="nome" name="nome" placeholder="Digite seu nome completo" required>
          </div>
          <div class="mb-3">
            <label for="cpf" class="form-label">CPF</label>
            <input type="text" class="form-control" id="cpf" name="cpf" placeholder="Digite seu CPF" required>
          </div>
          <div class="mb-3">
            <label for="cargo" class="form-label">Cargo</label>
            <input type="text" class="form-control" id="cargo" name="cargo" placeholder="Digite seu cargo" required>
          </div>
          <div class="mb-3">
            <label for="new-email" class="form-label">Email</label>
            <input type="email" class="form-control" id="new-email" name="new-email" placeholder="Digite seu email" required>
          </div>
          <div class="mb-3">
            <label for="new-password" class="form-label">Senha</label>
            <input type="password" class="form-control" id="new-password" name="new-password" placeholder="Crie uma senha" required>
          </div>
          <div class="mb-3">
            <label for="confirm-password" class="form-label">Confirmar Senha</label>
            <input type="password" class="form-control" id="confirm-password" name="confirm-password" placeholder="Confirme sua senha" required>
          </div>
          <button type="submit" class="btn btn-custom w-100">Criar Conta</button>
        </form>
        <p class="text-center mt-3">
          Já tem uma conta? <a href="#" id="show-login">Entre agora</a>
        </p>
      </div>
    </div>
  </div>
</div>

<footer>
  <p>&copy; <?= date('Y') ?> Controle de Patrimônio. Todos os direitos reservados.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.getElementById('show-register').addEventListener('click', function(event) {
    event.preventDefault();
    document.getElementById('login-section').style.display = 'none';
    document.getElementById('register-section').style.display = 'block';
    window.scrollTo({ top: document.getElementById('register-section').offsetTop, behavior: 'smooth' });
  });

  document.getElementById('show-login').addEventListener('click', function(event) {
    event.preventDefault();
    document.getElementById('register-section').style.display = 'none';
    document.getElementById('login-section').style.display = 'block';
    window.scrollTo({ top: document.getElementById('login-section').offsetTop, behavior: 'smooth' });
  });

  // Mostra a seção de cadastro se houver erro no cadastro
  <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])): ?>
    document.getElementById('login-section').style.display = 'none';
    document.getElementById('register-section').style.display = 'block';
  <?php endif; ?>
</script>
</body>
</html>
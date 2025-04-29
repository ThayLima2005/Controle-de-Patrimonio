<?php
session_start();
require_once 'config.php';

// Redireciona se já estiver logado
if (isset($_SESSION['usuario_logado'])) {
    header("Location: index.php");
    exit();
}

// Processar login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM usuario WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            // Login bem-sucedido - agora armazenamos como array
            $_SESSION['usuario_logado'] = [
                'id' => $usuario['id_usuario'],
                'nome' => $usuario['nome'],
                'cargo' => $usuario['cargo'],
                'email' => $usuario['email']
            ];
            
            header("Location: index.php");
            exit();
        } else {
            $erro_login = "Email ou senha incorretos!";
        }
    } catch (PDOException $e) {
        $erro_login = "Erro ao conectar com o banco de dados: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
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
    }
  </style>
</head>
<body>
<div class="container">
  <div class="card-wrapper" id="login-section">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title text-center mb-4">Login</h3>
        <?php if (isset($erro_login)): ?>
          <div class="alert alert-danger"><?php echo $erro_login; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="login.php">
          <input type="hidden" name="login" value="1">
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Digite seu email" required>
          </div>
          <div class="mb-3">
            <label for="senha" class="form-label">Senha</label>
            <input type="password" class="form-control" id="senha" name="senha" placeholder="Digite sua senha" required>
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
        <?php if (isset($erro_registro)): ?>
          <div class="alert alert-danger"><?php echo $erro_registro; ?></div>
        <?php endif; ?>
        <?php if (isset($sucesso_registro)): ?>
          <div class="alert alert-success"><?php echo $sucesso_registro; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="login.php">
          <input type="hidden" name="registro" value="1">
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
            <label for="email_registro" class="form-label">Email</label>
            <input type="email" class="form-control" id="email_registro" name="email" placeholder="Digite seu email" required>
          </div>
          <div class="mb-3">
            <label for="senha_registro" class="form-label">Senha</label>
            <input type="password" class="form-control" id="senha_registro" name="senha" placeholder="Crie uma senha" required>
          </div>
          <div class="mb-3">
            <label for="confirmar_senha" class="form-label">Confirmar Senha</label>
            <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" placeholder="Confirme sua senha" required>
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
  <p>&copy; <?php echo date("Y"); ?> Controle de Patrimônio. Todos os direitos reservados.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.getElementById('show-register').addEventListener('click', function(event) {
    event.preventDefault();
    document.getElementById('login-section').style.display = 'none';
    document.getElementById('register-section').style.display = 'block';
  });

  document.getElementById('show-login').addEventListener('click', function(event) {
    event.preventDefault();
    document.getElementById('register-section').style.display = 'none';
    document.getElementById('login-section').style.display = 'block';
  });

  // Mostrar seção de registro se houver erro no registro
  <?php if (isset($erro_registro) || isset($sucesso_registro)): ?>
    document.getElementById('login-section').style.display = 'none';
    document.getElementById('register-section').style.display = 'block';
  <?php endif; ?>
</script>
</body>
</html>

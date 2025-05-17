<?php
session_start();
require_once 'config.php';

// Processar registro de novo usuário
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registro'])) {
    $nome = trim($_POST['nome']);
    $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']);
    $cargo = trim($_POST['cargo']);
    $email = trim($_POST['email']);
    $nome_usuario = trim($_POST['nome_usuario']);
    $senha = $_POST['senha'] ?? null; // Garante que existe, mesmo que null
    $confirmar_senha = $_POST['confirmar_senha'] ?? null;

    // Validações básicas
    if (empty($nome) || empty($cpf) || empty($cargo) || empty($email) || empty($senha)) {
        $erro_registro = "Todos os campos são obrigatórios!";
    } elseif (strlen($cpf) != 11) {
        $erro_registro = "CPF inválido! Deve conter 11 dígitos.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro_registro = "Email inválido!";
    } elseif ($senha !== $confirmar_senha) {
        $erro_registro = "As senhas não coincidem!";
    } elseif (strlen($senha) < 6) {
        $erro_registro = "A senha deve ter pelo menos 6 caracteres!";
    } else {
        try {
            // Verificar se usuário já existe
            $stmt = $pdo->prepare("SELECT id_usuario FROM usuario WHERE email = ? OR cpf = ? OR nome_usuario = ?");
            $stmt->execute([$email, $cpf, $nome_usuario]);
            
            if ($stmt->rowCount() > 0) {
                $erro_registro = "Email ou CPF já cadastrados!";
            } else {
                // Criptografar a senha (agora garantido que $senha existe)
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                
                // Inserir novo usuário - versão mais segura
                $stmt = $pdo->prepare("INSERT INTO usuario (nome, cpf, cargo, nome_usuario, email, senha) VALUES (?, ?, ?, ?, ?, ?)");
                $result = $stmt->execute([$nome, $cpf, $cargo, $nome_usuario, $email, $senha_hash]);
                
                if ($result) {
                    $sucesso_registro = "Usuário cadastrado com sucesso! Faça login.";
                    // Limpar campos do formulário
                    $_POST = array();
                } else {
                    $erro_registro = "Erro ao cadastrar usuário. Tente novamente.";
                }
            }
        } catch (PDOException $e) {
            $erro_registro = "Erro ao cadastrar usuário: " . $e->getMessage();
            // Log do erro para debug
            error_log("Erro no cadastro: " . $e->getMessage());
        }
    }
}

// Processar login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    try {
        // Consulta para buscar usuário por email
        $stmt = $pdo->prepare("SELECT * FROM usuario WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            // Verificar senha (suporta hash e texto plano temporariamente)
            if (password_verify($senha, $usuario['senha']) || $usuario['senha'] === $senha) {
                // Login bem-sucedido
                $_SESSION['usuario_logado'] = [
                    'id' => $usuario['id_usuario'],
                    'nome' => $usuario['nome'],
                    'cargo' => $usuario['cargo'],
                    'email' => $usuario['email']
                ];
                
                // Se a senha estiver em texto plano, atualize para hash
                if ($usuario['senha'] === $senha) {
                    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                    $pdo->prepare("UPDATE usuario SET senha = ? WHERE id_usuario = ?")
                        ->execute([$senha_hash, $usuario['id_usuario']]);
                }
                
                header("Location: index.php");
                exit();
            }
        }
        
        // Se chegou aqui, as credenciais estão erradas
        $erro_login = "Email ou senha incorretos!";
        
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
            <input type="email" class="form-control" id="email" name="email" 
                   value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" 
                   placeholder="Digite seu email" required>
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
            <label for="nome" class="form-label">Nome Completo</label>
            <input type="text" class="form-control" id="nome" name="nome" 
                   value="<?= isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : '' ?>" 
                   placeholder="Digite seu nome completo" required>
          </div>
          <div class="mb-3">
            <label for="nome_usuario" class="form-label">Nome de Usuário</label>
            <input type="text" class="form-control" id="nome_usuario" name="nome_usuario" value="<?= isset($_POST['nome_usuario']) ? htmlspecialchars($_POST['nome_usuario']) : '' ?>" placeholder="Escolha um nome de usuário" required>
          </div>
          <div class="mb-3">
            <label for="cpf" class="form-label">CPF (somente números)</label>
            <input type="text" class="form-control" id="cpf" name="cpf" 
                   value="<?= isset($_POST['cpf']) ? htmlspecialchars($_POST['cpf']) : '' ?>" 
                   placeholder="Digite seu CPF" required>
          </div>
          <div class="mb-3">
            <label for="cargo" class="form-label">Cargo</label>
            <input type="text" class="form-control" id="cargo" name="cargo" 
                   value="<?= isset($_POST['cargo']) ? htmlspecialchars($_POST['cargo']) : '' ?>" 
                   placeholder="Digite seu cargo" required>
          </div>
          <div class="mb-3">
            <label for="email_registro" class="form-label">Email</label>
            <input type="email" class="form-control" id="email_registro" name="email" 
                   value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" 
                   placeholder="Digite seu email" required>
          </div>
          <div class="mb-3">
            <label for="senha_registro" class="form-label">Senha (mínimo 6 caracteres)</label>
            <input type="password" class="form-control" id="senha_registro" name="senha" 
                   placeholder="Crie uma senha" required minlength="6">
          </div>
          <div class="mb-3">
            <label for="confirmar_senha" class="form-label">Confirmar Senha</label>
            <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" 
                   placeholder="Confirme sua senha" required minlength="6">
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
  // Alternar entre login e registro
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

  // Formatar CPF automaticamente
  document.getElementById('cpf').addEventListener('input', function(e) {
    let cpf = this.value.replace(/\D/g, '');
    
    if (cpf.length > 3) cpf = cpf.substring(0, 3) + '.' + cpf.substring(3);
    if (cpf.length > 6) cpf = cpf.substring(0, 7) + '.' + cpf.substring(7);
    if (cpf.length > 9) cpf = cpf.substring(0, 11) + '-' + cpf.substring(11);
    
    this.value = cpf.substring(0, 14);
  });

  // Mostrar seção de registro se houver erro/sucesso no registro
  <?php if (isset($erro_registro) || isset($sucesso_registro)): ?>
    document.getElementById('login-section').style.display = 'none';
    document.getElementById('register-section').style.display = 'block';
  <?php endif; ?>
</script>
</body>
</html>
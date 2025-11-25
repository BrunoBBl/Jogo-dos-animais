<?php
// 1. INICIA A SESSÃO (CRUCIAL! Deve ser a primeira coisa no script)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Inclui o arquivo de conexão
// É NECESSÁRIO que este arquivo defina a variável $conn (conexão PDO)
INCLUDE __DIR__ . "/connection.php";

// Inicializa variáveis para os campos do formulário e mensagens de erro
$arrayEmail = "";
$mensagem_erro = ""; // Variável para armazenar mensagens de erro

// Captura os dados POST
$clientArray = filter_input_array(INPUT_POST, FILTER_DEFAULT);

if ($clientArray) {
    // 2. VALIDAÇÃO INICIAL
    $arrayEmail = $clientArray["emails"] ?? ""; // Note: usando 'emails' do HTML do Bootstrap
    $senha_digitada = $clientArray["passwords"] ?? ""; // Note: usando 'passwords' do HTML do Bootstrap

    if (empty($arrayEmail) || empty($senha_digitada)) {
        $mensagem_erro = "Preencha todos os campos."; 
    } else if (!filter_var($arrayEmail, FILTER_VALIDATE_EMAIL)) {
        $mensagem_erro = "E-mail inválido.";
    } else {
        
        // 3. CONSULTA AO BANCO DE DADOS (Prepared Statement)
        $sql = "SELECT id, nome, senha FROM usuarios WHERE email = :email";
        
        try {
            $query = $conn->prepare($sql);
            $query->bindParam(':email', $arrayEmail);
            $query->execute(); 
            
            if ($query->rowCount() == 1) {
                $usuario = $query->fetch(PDO::FETCH_ASSOC);
                $hash_armazenado = $usuario['senha'];
                
                // 4. VERIFICAÇÃO DE SENHA
                if (password_verify($senha_digitada, $hash_armazenado)) {
                    
                    // LOGIN BEM-SUCEDIDO
                    $_SESSION['usuario_logado'] = $usuario['id'];
                    $_SESSION['nome_usuario'] = $usuario['nome'];

                    // REDIRECIONA para a página index.php
                    header("Location: index.php");
                    exit(); 
                    
                } else {
                    // Senha incorreta
                    $mensagem_erro = "E-mail ou senha incorretos.";
                }
                
            } else {
                // E-mail não encontrado
                $mensagem_erro = "E-mail ou senha incorretos.";
            }
            
        } catch (PDOException $e) {
            // Em ambiente de produção, esta mensagem deve ser mais genérica.
            $mensagem_erro = "Erro interno do servidor. Tente novamente mais tarde.";
            error_log("Erro de PDO no login: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Links Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Link para o CSS de estilo customizado (se o 'glass-bg' for Frutiger Aero, ele estaria aqui) -->
         <link rel="stylesheet" type="text/css" href="css/style.css">
    <title>LoginPage</title>
</head>

<!-- Body com classes Bootstrap para fundo escuro e centralização -->
<body class=" justify-content-center align-items-center" style="height: 100vh;">
    <!-- Elemento para o efeito de fundo (como o Frutiger Aero glass-bg) -->
    <div class="glass-bg"></div> 

    <!-- O formulário de login -->
    <form name="loginForm" action="./login.php" method="POST" class="p-4 border rounded shadow bg-white position-absolute top-50 start-50 translate-middle" style="width: 350px;">
        <h1 class="mb-4 text-center">Login</h1>

        <?php if ($mensagem_erro): ?>
            <!-- Alerta Bootstrap para exibir erros -->
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($mensagem_erro); ?>
            </div>
        <?php endif; ?>

        <div class="mb-3">
            <label for="emails" class="form-label">Email address</label>
            <!-- O input precisa do atributo 'name="emails"' para o PHP capturar -->
            <input type="email" class="form-control" id="emails" name="emails" 
                   value="<?= htmlspecialchars($arrayEmail); ?>" 
                   placeholder="Insira seu email" required>
        </div>

        <div class="mb-3">
            <label for="passwords" class="form-label">Password</label>
            <!-- O input precisa do atributo 'name="passwords"' para o PHP capturar -->
            <input type="password" class="form-control" id="passwords" name="passwords" 
                   placeholder="Senha" required>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="rememberMe">
            <label class="form-check-label" for="rememberMe">Lembrar-me</label>
        </div>

        <button type="submit" class="btn btn-primary w-100 mt-3">Entrar</button>
        
        <!-- Links Adicionais (Opcional, seguindo o padrão Bootstrap) -->
        <div class="text-center mt-3">
            <a href="#" class="text-decoration-none small d-block">Esqueceu a senha?</a>
            <a href="register.php" class="text-decoration-none small d-block">Criar uma conta</a>
        </div>
    </form>
</body>
</html>
<?php
// Inclui o arquivo de conexão com o banco de dados
// Certifique-se de que este arquivo define a variável $conn (objeto PDO)
INCLUDE __DIR__ . "/connection.php";

// Inicializa variáveis para preencher o formulário em caso de erro
$nome = "";
$email = "";
$mensagem = "";
$status_mensagem = ""; // 'success' ou 'danger' para o alerta Bootstrap

// Captura os dados enviados via POST
$clientArray = filter_input_array(INPUT_POST, FILTER_DEFAULT);

if ($clientArray) {
    // 1. Persistência de dados em caso de erro
    $nome = $clientArray["nome"] ?? "";
    $email = $clientArray["email"] ?? "";
    $senha = $clientArray["senha"] ?? "";

    // 2. Validação básica (Campos vazios)
    if (empty($nome) || empty($email) || empty($senha)) {
        $mensagem = "Preencha todos os campos.";
        $status_mensagem = "danger";
    } 
    // 3. Validação de formato de E-mail
    else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = "Formato de e-mail inválido.";
        $status_mensagem = "danger";
    } 
    // 4. Se a validação inicial passar, tenta inserir
    else {
        
        // CUIDADO DE SEGURANÇA: NUNCA armazene a senha em texto puro!
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        // 5. Verifica se o e-mail já está cadastrado (Útil por causa da UNIQUE KEY)
        $sql_check = "SELECT id FROM usuarios WHERE email = :email";
        $query_check = $conn->prepare($sql_check);
        $query_check->bindParam(':email', $email);
        $query_check->execute();

        if ($query_check->rowCount() > 0) {
            $mensagem = "Este e-mail já está cadastrado.";
            $status_mensagem = "danger";
        } else {
            // 6. SQL para Inserção (Prepared Statement)
            $sql_insert = "INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)";

            try {
                $query = $conn->prepare($sql_insert);
                
                // 7. Bind dos Parâmetros
                $query->bindParam(':nome', $nome);
                $query->bindParam(':email', $email);
                $query->bindParam(':senha', $senha_hash); // Usa o hash da senha!
                
                // 8. Execução
                $query->execute();
                
                // 9. Sucesso
                $mensagem = "Usuário cadastrado com sucesso! Você pode fazer login agora.";
                $status_mensagem = "success";
                
                // Limpa os campos após o sucesso
                $nome = "";
                $email = "";
            } catch (PDOException $e) {
                // Trata erro de banco de dados (ex: problema de conexão ou permissão)
                $mensagem = "Erro ao cadastrar. Tente novamente.";
                $status_mensagem = "danger";
                error_log("Erro de PDO no registro: " . $e->getMessage());
            }
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
    <!-- Link para o CSS de estilo customizado (loginstyle.css - para o efeito Frutiger Aero/glass-bg) -->
    <link rel="stylesheet" href="css/style.css"> 
    <title>Cadastro de Usuário</title>
</head>

<!-- Body com classes Bootstrap para fundo escuro e centralização -->
<body class=" justify-content-center align-items-center" style="height: 100vh;">
    <!-- Elemento para o efeito de fundo (glass-bg) -->
    <div class="glass-bg"></div> 

    <!-- O formulário de cadastro, com o mesmo estilo e centralização do login -->
    <form name="cadastro" action="./register.php" method="POST" class="p-4 border rounded shadow bg-white position-absolute top-50 start-50 translate-middle" style="width: 350px;">
        <h1 class="mb-4 text-center">Crie sua Conta</h1>

        <?php if (!empty($mensagem)): ?>
            <!-- Alerta Bootstrap para exibir mensagens de sucesso/erro -->
            <div class="alert alert-<?= $status_mensagem; ?>" role="alert">
                <?= htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <div class="mb-3">
            <label for="nome" class="form-label">Nome Completo</label>
            <!-- Usando .form-control e value para persistência -->
            <input class="form-control" type="text" id="nome" name="nome" value="<?= htmlspecialchars($nome); ?>" placeholder="Seu nome completo" required>
        </div>
        
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <!-- Usando .form-control e value para persistência -->
            <input class="form-control" type="email" id="email" name="email" value="<?= htmlspecialchars($email); ?>" placeholder="Seu melhor e-mail" required>
        </div>

        <div class="mb-3">
            <label for="senha" class="form-label">Senha</label>
            <!-- Usando .form-control -->
            <input class="form-control" type="password" id="senha" name="senha" placeholder="Crie uma senha segura" required>
        </div>

        <button type="submit" class="btn btn-primary w-100 mt-3">Cadastrar</button>
        
        <!-- Link para Voltar ao Login -->
        <div class="text-center mt-3">
            <a href="login.php" class="text-decoration-none small d-block">Já tenho uma conta (Fazer Login)</a>
        </div>
    </form>
</body>
</html>
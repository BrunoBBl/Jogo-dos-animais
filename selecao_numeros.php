<?php
// 1. INICIA A SESSÃO
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. VERIFICAÇÃO DE LOGIN
if (!isset($_SESSION['usuario_logado'])) {
    header("Location: login.php");
    exit();
}

$nome_usuario = $_SESSION['nome_usuario'] ?? "Usuário";
$mensagem = "";
$status_mensagem = "";

// Inicializa o carrinho na sessão se ainda não existir
if (!isset($_SESSION['carrinho_numeros']) || !is_array($_SESSION['carrinho_numeros'])) {
    $_SESSION['carrinho_numeros'] = [];
}
// Cria uma referência para facilitar o uso
$carrinho = &$_SESSION['carrinho_numeros'];

// Captura os dados POST
$clientArray = filter_input_array(INPUT_POST, FILTER_DEFAULT);

if ($clientArray && isset($clientArray['numeros'])) {
    $novos_numeros_selecionados = $clientArray['numeros'];
    $numeros_adicionados = 0;
    
    foreach ($novos_numeros_selecionados as $num) {
        // Garante que o número seja um inteiro entre 1 e 100
        $num = (int)$num;
        if ($num >= 1 && $num <= 100) {
            if (!in_array($num, $carrinho)) {
                $carrinho[] = $num;
                $numeros_adicionados++;
            }
        }
    }
    
    // Remove duplicatas e garante que a lista esteja ordenada
    sort($carrinho);
    
    if ($numeros_adicionados > 0) {
        $mensagem = "✅ " . $numeros_adicionados . " número(s) adicionado(s) ao carrinho com sucesso!";
        $status_mensagem = "success";
    } else {
        $mensagem = "🔔 Os números selecionados já estavam no carrinho ou a seleção é inválida.";
        $status_mensagem = "info";
    }
}

$itens_carrinho = count($carrinho);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="loginstyle.css"> 
    <title>Seleção de Números</title>
    <style>
        html, body { height: 100%; }
        /* Estilo para a grade de checkboxes */
        .number-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(60px, 1fr)); /* 60px é a largura mínima para cada número */
            gap: 10px;
        }
        .number-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            text-align: center;
            transition: all 0.2s;
            cursor: pointer;
        }
        /* Estilo quando o checkbox está marcado */
        .number-checkbox:checked + .number-label .number-box {
            background-color: #0d6efd;
            color: white;
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.5);
            transform: scale(1.05);
        }
        /* Oculta o checkbox nativo */
        .number-checkbox {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }
    </style>
</head>

<body class="bg-dark d-flex flex-column">
    <div class="glass-bg"></div> 

    <!-- Navbar igual ao index.php -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">Painel de Controle</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home me-1"></i> Menu Principal
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="selecao_numeros.php">
                            <i class="fas fa-dice me-1"></i> Seleção de Números
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="carrinho.php">
                            <i class="fas fa-shopping-cart me-1"></i> Carrinho
                            <?php if ($itens_carrinho > 0): ?>
                                <span class="badge bg-danger ms-1"><?= $itens_carrinho; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <span class="navbar-text text-white me-3">
                        Bem-vindo(a), <?= htmlspecialchars($nome_usuario); ?>!
                    </span>
                    <a href="logout.php" class="btn btn-outline-light">
                        <i class="fas fa-sign-out-alt me-1"></i> Sair
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Conteúdo Principal -->
    <main class="container my-5 flex-grow-1">
        <div class="card p-4 shadow-lg bg-white">
            <h2 class="card-title mb-4 text-center">Selecione seus Números (R$ 2,00 cada)</h2>
            
            <?php if (!empty($mensagem)): ?>
                <!-- Exibe mensagem de sucesso ou informação -->
                <div class="alert alert-<?= $status_mensagem; ?>" role="alert">
                    <?= htmlspecialchars($mensagem); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="selecao_numeros.php">
                <div class="number-grid mb-4">
                    <?php for ($i = 1; $i <= 100; $i++): ?>
                        <?php 
                            $checked = in_array($i, $carrinho);
                            $class_ativo = $checked ? 'bg-primary text-white shadow' : 'bg-light';
                        ?>
                        <label class="number-label">
                            <!-- Checkbox oculto. O nome é 'numeros[]' para receber um array de valores -->
                            <input type="checkbox" 
                                   name="numeros[]" 
                                   value="<?= $i; ?>" 
                                   class="number-checkbox"
                                   <?= $checked ? 'checked disabled' : ''; ?>>
                                   
                            <div class="number-box <?= $class_ativo; ?>">
                                <!-- Exibe o número e a indicação de estado -->
                                <strong><?= $i; ?></strong>
                                <?php if ($checked): ?>
                                    <span class="badge bg-success position-absolute top-0 end-0 mt-1 me-1">✓</span>
                                <?php endif; ?>
                            </div>
                        </label>
                    <?php endfor; ?>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <!-- Informação de Itens no Carrinho -->
                    <p class="mb-0 fs-5">
                        Itens Atuais no Carrinho: <span class="badge bg-secondary"><?= $itens_carrinho; ?></span>
                    </p>
                    <!-- Botão de Ação -->
                    <button type="submit" class="btn btn-success btn-lg" <?= $itens_carrinho >= 100 ? 'disabled' : ''; ?>>
                        <i class="fas fa-cart-plus me-2"></i> Adicionar Selecionados ao Carrinho
                    </button>
                </div>
            </form>
            
            <div class="text-center mt-3">
                <a href="carrinho.php" class="btn btn-outline-primary mt-3">Ver Carrinho Agora</a>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>
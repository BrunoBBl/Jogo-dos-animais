<?php
// 1. INICIA A SESSÃO
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. VERIFICAÇÃO DE LOGIN
// Se o ID do usuário não estiver na sessão, redireciona para a página de login.
if (!isset($_SESSION['usuario_logado'])) {
    header("Location: login.php");
    exit();
}

$nome_usuario = $_SESSION['nome_usuario'] ?? "Usuário";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Links Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Link para o CSS de estilo customizado (Mantenha o glass-bg e outros estilos) -->
    <link rel="stylesheet" href="loginstyle.css"> 
    <title>Menu Principal | Dashboard</title>
    <style>
        /* Ajuste para garantir que o body ocupe 100% da altura e o fundo escuro funcione */
        html, body {
            height: 100%;
        }
    </style>
</head>

<body class="bg-dark d-flex flex-column">
    <!-- Elemento para o efeito de fundo (glass-bg), se estiver definido no loginstyle.css -->
    <div class="glass-bg"></div> 

    <!-- Navbar/Menu Principal (Fixa no topo) -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">Painel de Controle</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <!-- Menu Principal (Home) -->
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="index.php">
                            <i class="fas fa-home me-1"></i> Menu Principal
                        </a>
                    </li>
                    <!-- Consultar -->
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-search me-1"></i> Consultar
                        </a>
                    </li>
					 <li class="nav-item">
                        <a class="nav-link" href="selecao_numeros.php">
                            <i class="fas fa-search me-1"></i> Selecao de Numeros
                        </a>
                    </li>
                    <!-- Adicione mais links aqui -->
                </ul>
                
                <!-- Informação do Usuário e Botão Sair (alinhado à direita) -->
                <div class="d-flex align-items-center">
                    <span class="navbar-text text-white me-3">
                        Bem-vindo(a), <?= htmlspecialchars($nome_usuario); ?>!
                    </span>
                    <!-- Sair (Logout) -->
                    <a href="logout.php" class="btn btn-outline-light">
                        <i class="fas fa-sign-out-alt me-1"></i> Sair
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Conteúdo Principal do Dashboard -->
    <section class="py-5 bg-dark text-white">
            <div class="container">
                
                <h2>Olá <?= htmlspecialchars($nome_usuario); ?>, Como Jogar</h2>
                <span>No nosso site funciona assim: cada número custa R$2 e paga R$120 se sair nos 5 prêmios.
Quanto mais números você compra, menor fica o retorno por real apostado.
Se comprar só 1 ou 2 números, o risco é alto, mas se acertar o lucro é gigante (de R$2 para R$120 ou R$240).
Conforme você vai marcando mais números, a chance de ganhar alguma coisa aumenta, mas o lucro médio cai um pouco</span>

                <div class="col-12 mt-3">
                    <img class="img-fluid w-75" src="/img/JOGO-DO-BICHO.webp" alt="">
                </div>
            </div>
        </section>
        <article class="container py-4 bg-dark text-white">
    <h2 class="mb-4">Últimos números sorteados:</h2>

    <div class="row g-4"> 
        
        <div class="col-md-4">
            <div class="card" style="width: 100%;">
                <img src="/img/prêmio.webp" class="card-img-top" alt="prêmio">
                <div class="card-body bg-secondary text-light">
                    <p class="card-text">
                        Maior prêmio da semana:<br>
                        Número: 44<br>
                        Valor: R$240
                    </p>
                </div>
            </div>
        </div>   
        <div class="col-md-4">
            <div class="card" style="width: 100%;">
                <img src="/img/prêmio.webp" class="card-img-top" alt="prêmio">
                <div class="card-body bg-secondary text-light">
                    <p class="card-text">
                        Segundo maior prêmio da semana:<br>
                        Número: 99<br>
                        Valor: R$120
                    </p>
                </div>
            </div>
        </div>       
        <div class="col-md-4">
            <div class="card" style="width: 100%;">
                <img src="/img/prêmio.webp" class="card-img-top" alt="prêmio">
                <div class="card-body bg-secondary text-light">
                    <p class="card-text">
                        Terceiro maior prêmio da semana:<br>
                        Número: 2<br>
                        Valor: R$60
                    </p>
                </div>
            </div>
        </div>
    </div>
</article>


    <!-- Adicione o JavaScript do Bootstrap (necessário para o Navbar responsivo) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Adicione Font Awesome para ícones (opcional, mas recomendado para aparência) -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>
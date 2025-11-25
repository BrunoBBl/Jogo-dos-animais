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

// INCLUI A CONEXÃO (Necessário para salvar no banco)
// Certifique-se de que connection.php cria a variável $conn (PDO)
INCLUDE __DIR__ . "/connection.php";

$usuario_id = $_SESSION['usuario_logado'];
$nome_usuario = $_SESSION['nome_usuario'] ?? "Usuário";
$mensagem = "";
$status_mensagem = "";

// Inicializa o carrinho
$carrinho = &$_SESSION['carrinho_numeros'];
if (!is_array($carrinho)) {
    $carrinho = [];
}

// Preço fixo (poderia vir do banco também)
$preco_unitario = 2.00;

// --- LÓGICA DE LIMPEZA ---
if (isset($_POST['limpar_carrinho'])) {
    $carrinho = [];
    $_SESSION['carrinho_numeros'] = [];
    $mensagem = "🗑️ O carrinho foi esvaziado com sucesso!";
    $status_mensagem = "warning";
}

// --- LÓGICA DE FINALIZAR COMPRA (TRANSACAO) ---
if (isset($_POST['finalizar_compra']) && count($carrinho) > 0) {
    try {
        // Inicia uma transação no banco (Tudo ou nada)
        // Isso é crucial: se falhar ao salvar os itens, a venda não é registrada.
        $conn->beginTransaction();

        $qtd_itens = count($carrinho);
        $valor_total_compra = $qtd_itens * $preco_unitario;

        // 1. Inserir na tabela 'transacoes' (Histórico Geral)
        $sql_transacao = "INSERT INTO transacoes (usuario_id, valor_total) VALUES (:uid, :total)";
        $stmt = $conn->prepare($sql_transacao);
        $stmt->bindParam(':uid', $usuario_id);
        $stmt->bindParam(':total', $valor_total_compra);
        $stmt->execute();
        
        // Pega o ID da transação que acabou de ser criada para vincular os itens
        $transacao_id = $conn->lastInsertId();

        // 2. Inserir cada item na tabela 'itens_transacao' (Detalhes)
        $sql_item = "INSERT INTO itens_transacao (transacao_id, numero_escolhido, valor_unitario) VALUES (:tid, :num, :val)";
        $stmt_item = $conn->prepare($sql_item);

        foreach ($carrinho as $numero) {
            $stmt_item->bindParam(':tid', $transacao_id);
            $stmt_item->bindParam(':num', $numero);
            $stmt_item->bindParam(':val', $preco_unitario);
            $stmt_item->execute();
        }

        // 3. Atualizar a tabela de LUCRO (caixa_loja)
        // Somamos o valor dessa venda ao saldo acumulado na tabela de lucro
        // Assumimos que o ID do caixa é 1 (criado no script SQL anterior)
        $sql_caixa = "UPDATE caixa_loja SET saldo_acumulado = saldo_acumulado + :valor WHERE id = 1";
        $stmt_caixa = $conn->prepare($sql_caixa);
        $stmt_caixa->bindParam(':valor', $valor_total_compra);
        $stmt_caixa->execute();

        // Se chegou até aqui sem erros, confirma todas as alterações no banco
        $conn->commit();

        // Limpa o carrinho e exibe sucesso
        $carrinho = [];
        $_SESSION['carrinho_numeros'] = [];
        $mensagem = "🎉 Compra realizada com sucesso! Transação #$transacao_id registrada.";
        $status_mensagem = "success";

    } catch (Exception $e) {
        // Se der qualquer erro, desfaz todas as alterações no banco
        $conn->rollBack();
        $mensagem = "❌ Erro ao processar compra: " . $e->getMessage();
        $status_mensagem = "danger";
    }
}

// 4. CÁLCULO TOTAL (Visualização)
$itens_carrinho = count($carrinho);
$valor_total = $itens_carrinho * $preco_unitario;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="loginstyle.css"> 
    <title>Meu Carrinho</title>
    <style>
        html, body { height: 100%; }
        /* Estilo para a lista de números */
        .numero-item {
            display: inline-block;
            background-color: #0d6efd;
            color: white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            line-height: 40px;
            text-align: center;
            font-weight: bold;
            margin: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>

<body class="bg-dark d-flex flex-column">
    <div class="glass-bg"></div> 

    <!-- Navbar -->
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
                        <a class="nav-link" href="selecao_numeros.php">
                            <i class="fas fa-dice me-1"></i> Seleção de Números
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="carrinho.php">
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
            <h2 class="card-title mb-4 text-center">Seu Carrinho de Compras</h2>
            
            <?php if (!empty($mensagem)): ?>
                <div class="alert alert-<?= $status_mensagem; ?>" role="alert">
                    <?= htmlspecialchars($mensagem); ?>
                </div>
            <?php endif; ?>

            <?php if ($itens_carrinho === 0 && empty($mensagem)): ?>
                <div class="alert alert-info text-center">
                    O seu carrinho está vazio. Adicione números na página de Seleção.
                </div>
                <div class="text-center mt-4">
                     <a href="selecao_numeros.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-cart-plus me-2"></i> Ir para Seleção de Números
                    </a>
                </div>
            <?php elseif ($itens_carrinho > 0): ?>
                <!-- Detalhes do Carrinho -->
                <div class="row mb-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="p-3 bg-light rounded shadow-sm">
                            <h5 class="mb-2">Resumo do Pedido</h5>
                            <p class="mb-1">Total de Números: <strong><?= $itens_carrinho; ?></strong></p>
                            <p class="mb-1">Preço Unitário: <strong>R$ <?= number_format($preco_unitario, 2, ',', '.'); ?></strong></p>
                            <hr class="my-2">
                            <h4 class="text-success">Valor Total: 
                                <strong>R$ <?= number_format($valor_total, 2, ',', '.'); ?></strong>
                            </h4>
                        </div>
                    </div>
                    <div class="col-md-6 d-flex flex-column justify-content-between">
                        <!-- Botão de Limpar Carrinho -->
                        <form method="POST" action="carrinho.php" onsubmit="return confirm('Tem certeza que deseja esvaziar o carrinho?');" class="mb-2">
                            <input type="hidden" name="limpar_carrinho" value="1">
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="fas fa-trash-alt me-2"></i> Esvaziar Carrinho
                            </button>
                        </form>
                        
                        <!-- Botão de FINALIZAR COMPRA -->
                        <form method="POST" action="carrinho.php">
                            <input type="hidden" name="finalizar_compra" value="1">
                            <button type="submit" class="btn btn-success w-100 btn-lg shadow fw-bold">
                                 <i class="fas fa-check-circle me-2"></i> Finalizar e Pagar
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Lista de Números Selecionados -->
                <h5 class="mt-4 mb-3">Números Selecionados:</h5>
                <div class="border p-3 rounded bg-light text-center">
                    <?php 
                        foreach ($carrinho as $numero) {
                            echo '<span class="numero-item">' . htmlspecialchars($numero) . '</span>';
                        }
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>
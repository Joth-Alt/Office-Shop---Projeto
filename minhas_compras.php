<?php
session_start();
include('conexao.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];

// Busca os pedidos do usuário
$sql = "SELECT * FROM pedidos WHERE id_usuario = ? ORDER BY data_p DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

// Função para formatar tempo em segundos para formato legível
function formatarTempo($segundos) {
    if ($segundos <= 0) return 'Entregue';
    
    $minutos = ceil($segundos / 60);
    if ($minutos < 60) {
        return "{$minutos} min";
    } else {
        $horas = floor($minutos / 60);
        $minutos_resto = $minutos % 60;
        return "{$horas}h {$minutos_resto}min";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Office Shop - Minhas Compras</title>
    
    <link rel="stylesheet" href="css/basic.css" />
    <link rel="stylesheet" href="css/background.css" />
    <link rel="stylesheet" href="css/modal.css" /> 
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="icon" href="imagems/logos/logo.png" type="image/png"> 

    <style>
        /* Estilos da sidebar, top-nav, etc., virão do basic.css/background.css/modal.css */

        /* Container Principal para o Conteúdo de Compras */
        .compras-container {
            padding: 20px;
        }

        /* Título da Seção */
        .compras-container h1 {
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 10px;
            margin-bottom: 25px;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Estilo para cada Pedido */
        .pedido {
            background: var(--card-bg-color, #fff);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            padding: 25px;
            margin-bottom: 30px;
            border-left: 5px solid var(--primary-color, #df2356);
            transition: transform 0.2s;
        }

        .pedido:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.12);
        }

        .pedido h2 {
            margin: 0 0 15px;
            color: var(--text-color);
            font-size: 1.5em;
            border-bottom: 1px dashed #eee;
            padding-bottom: 10px;
        }

        .info {
            margin: 8px 0;
            color: var(--secondary-text-color, #555);
            font-size: 0.95em;
        }
        
        .info strong {
            color: var(--text-color);
        }

        /* Estilos do Status */
        .status {
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 6px;
            display: inline-block;
            color: white;
            min-width: 90px;
            text-align: center;
            font-size: 0.85em;
            letter-spacing: 0.5px;
        }

        /* Cores dos Status */
        .Preparando { background: #f0ad4e; }
        .A_caminho { background: #5a5fe4ff; }
        .Entregue { background: #df2356; }
        
        /* Tabela de Itens */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: var(--table-bg-color, #f9f9f9);
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid var(--border-color, #ddd);
            text-align: left;
        }

        th {
            background: var(--header-bg-color, #eee);
            color: var(--text-color);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85em;
        }
        
        td {
             font-size: 0.9em;
             color: var(--secondary-text-color, #555);
        }

        /* Linha Total da Tabela */
        table tr:last-child th,
        table tr:last-child td {
            border-bottom: none;
            font-weight: bold;
            background: var(--total-bg-color, #e9e9e9);
            color: var(--primary-color, #333);
        }
        
        .sem-pedido {
            text-align: center;
            color: var(--secondary-text-color, #777);
            font-size: 1.1em;
            padding: 50px 20px;
            background: var(--card-bg-color, #fff);
            border-radius: 12px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            margin-top: 30px;
        }

        /* Estilo para informações de tempo */
        .tempo-info {
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 8px;
            margin: 10px 0;
            border-left: 3px solid #007bff;
        }

        .tempo-restante {
            font-weight: bold;
            color: #df2356;
        }

        .endereco-info {
            background: #fff3cd;
            padding: 10px 15px;
            border-radius: 8px;
            margin: 10px 0;
            border-left: 3px solid #ffc107;
            font-size: 0.9em;
        }
    </style>
</head>

<body> 
<div class="bg-pattern"></div>

    <aside class="sidebar">
        <nav class="menu-principal">
            <a href="index.php"><i class="fas fa-home"></i> <span class="txt">Home</span></a>
            <a href="favoritos.php"><i class="fas fa-heart"></i> <span class="txt">Favoritos</span></a>
            <a href="perfil.php"><i class="fas fa-user"></i> <span class="txt">Perfil</span></a>
            <a href="contato.php"><i class="fas fa-envelope"></i> <span class="txt">Contato</span></a>
            <a href="minhas_compras.php"><i class="fas fa-box"></i> <span class="txt">Minhas Compras</span></a>
        </nav>

        <nav class="menu-config">
            <a href="#"><i class="fas fa-cog"></i> <span class="txt">Configurações</span></a>
            <a href="#" id="themeToggle"><i class="fas fa-adjust"></i> <span class="txt">Tema</span></a>

            <div class="dropdown">
                <a href="#" class="dropbtn"><i class="fas fa-language"></i> <span class="txt">Tradução</span></a>
                <div class="dropdown-content">
                    <button onclick="setLanguage('pt')">🇧🇷 Português</button>
                    <button onclick="setLanguage('en')">🇺🇸 English</button>
                    <button onclick="setLanguage('es')">🇪🇦 Español</button>
                </div>
            </div>
            <a href="#" id="openCart"><i class="fas fa-shopping-cart"></i> <span class="txt">Carrinho</span> <span class="cart-count">. 0</span></a>
        </nav>
    </aside>

    <main class="content">
        <header class="top-nav">
            <div class="search-container">
                 <h1><i class="fas fa-shopping-bag"></i> Minhas Compras</h1>
            </div>
        </header>

        <section class="compras-container">
            
            <div id="lista-pedidos" class="lista-pedidos">
            <?php
            if ($result->num_rows > 0):
                while ($pedido = $result->fetch_assoc()):
                    $id_pedido = $pedido['id_pedido'];
                    $status = str_replace(' ', '_', $pedido['status']);
                    $status_display = $pedido['status'];
                    $endereco = $pedido['endereco_entrega'];
                    $endereco_partida = $pedido['endereco_partida'] ?? "Av. Monsenhor Theodomiro Lobo, 100 - Parque Res. Maria Elmira, Caçapava - SP, 12285-050";
                    $forma_pag = $pedido['forma_pag'];
                    $data = date("d/m/Y H:i", strtotime($pedido['data_p']));
                    $inicio = date("d/m/Y H:i:s", strtotime($pedido['inicio_entrega']));
                    $final = $pedido['final_entrega'] ? date("d/m/Y H:i:s", strtotime($pedido['final_entrega'])) : "—";
                    $tempo_estimado = $pedido['tempo_estimado'] ?? 0;
                    $tempo_restante = $pedido['tempo_restante'] ?? 0;

                    // Busca os itens do pedido
                    include('conexao.php'); 
                    
                    $sql_itens = "SELECT ip.*, p.n_produto 
                                  FROM itens_pedido ip 
                                  JOIN produto p ON p.id_produto = ip.id_produto
                                  WHERE ip.id_pedido = ?";
                    $stmt_itens = $conn->prepare($sql_itens);
                    $stmt_itens->bind_param("i", $id_pedido);
                    $stmt_itens->execute();
                    $res_itens = $stmt_itens->get_result();

                    $total = 0;
            ?>
                <div class="pedido">
                    <h2>Pedido #<?= $id_pedido ?></h2>
                    <div class="info"><strong>Status:</strong> <span class="status <?= $status ?>"><?= $status_display ?></span></div>
                    
                    <?php if ($pedido['status'] == 'A caminho'): ?>
                    <?php endif; ?>
                    
                    <div class="info"><strong>📅 Data do Pedido:</strong> <?= $data ?></div>
                    <div class="info"><strong>💳 Forma de Pagamento:</strong> <?= ucfirst($forma_pag) ?></div>
                    
                    <div class="endereco-info">
                        <div class="info"><strong>📍 Endereço de Partida:</strong> <?= htmlspecialchars($endereco_partida) ?></div>
                        <div class="info"><strong>🏠 Endereço de Entrega:</strong> <?= htmlspecialchars($endereco) ?></div>
                    </div>
                    
                    <?php if ($pedido['status'] == 'Preparando' || $pedido['status'] == 'A caminho'): ?>
                    <?php endif; ?>
                    
                    <div class="info"><strong>🟢 Início da Entrega:</strong> <?= $inicio ?></div>
                    <div class="info"><strong>🔴 Final da Entrega:</strong> <?= $final ?></div>
                    

                    <table>
                        <tr>
                            <th>Produto</th>
                            <th>Qtd</th>
                            <th>Preço Unit. (R$)</th>
                            <th>Subtotal (R$)</th>
                        </tr>
                        <?php while ($item = $res_itens->fetch_assoc()): 
                            $subtotal = $item['quantidade'] * $item['preco'];
                            $total += $subtotal;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($item['n_produto']) ?></td>
                            <td><?= $item['quantidade'] ?></td>
                            <td><?= number_format($item['preco'], 2, ',', '.') ?></td>
                            <td><?= number_format($subtotal, 2, ',', '.') ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <tr>
                            <th colspan="3" style="text-align: right;">Total do Pedido:</th>
                            <th>R$ <?= number_format($total, 2, ',', '.') ?></th>
                        </tr>
                    </table>
                </div>
            <?php
                    $stmt_itens->close();
                endwhile;
            else:
            ?>
                <div class="sem-pedido">📦 Você ainda não fez nenhuma compra</div>
            <?php endif; ?>
            </div>
        </section>
        
    </main>

    <div id="cart-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="cart-title"><i class="fas fa-shopping-cart"></i> Seu Carrinho</h2>
                <span class="close-btn">&times;</span>
            </div>
            
            <div id="cart-items" class="cart-items-list">
                <p id="empty-cart-message" style="text-align: center; color: #555; margin-top: 20px;">Seu carrinho está vazio. Adicione alguns produtos!</p>
            </div>

            <div class="cart-footer">
                <div class="cart-total">
                    <span>Total:</span>
                    <span id="cart-total-value">R$ 0,00</span>
                </div>
                <button class="checkout-btn">Finalizar Compra</button>
            </div>
        </div>
    </div>
    
    <div id="login-modal" class="modal">
        <div class="modal-content login-content">
            <span class="close-login-btn">&times;</span>
            <div class="login-form-container">
                <img src="D:\site organizado\imagems\logos\logo.png" alt="Mascote Login" class="login-mascote">
                
                <h2 class="login-title">Login</h2>

                <form>
                    <label for="email">Email ou telefone</label>
                    <input type="text" id="email" placeholder="Email ou telefone" required>
                    
                    <label for="password">Senha</label>
                    <div class="password-container">
                        <input type="password" id="password" placeholder="Digite sua senha" required>
                        <i class="fas fa-eye password-toggle"></i>
                    </div>
                    
                    <div class="login-options">
                        <label class="remember-me">
                            <input type="checkbox" id="remember-me">
                            Lembrar-me
                        </label>
                        <a href="#" class="forgot-password">Esqueceu a senha?</a>
                    </div>

                    <button type="submit" class="sign-in-btn">Entrar</button>
                </form>
                
                <button class="google-sign-in-btn">
                    <i class="fab fa-google"></i> Ou entre com Google
                </button>
            </div>
        </div>
    </div>
    
    <script src="home.js"></script> 
    
    <script>
    // Script para atualizar o status do pedido
    function atualizarStatusPedidos() {
        fetch('atualiza_status.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Status atualizados com sucesso');
                    // Recarrega a página para mostrar status atualizados
                    location.reload();
                }
            })
            .catch(error => console.error("Erro ao atualizar status:", error));
    }

    // Atualiza a cada 5 segundos
    setInterval(atualizarStatusPedidos, 5000);

    // Atualiza os contadores de tempo em tempo real (para pedidos "A caminho")
    function atualizarContadoresTempo() {
        document.querySelectorAll('[id^="tempo-restante-"]').forEach(element => {
            const tempoAtual = parseInt(element.dataset.tempo) || 0;
            
            if (tempoAtual > 0) {
                const novoTempo = tempoAtual - 1;
                element.dataset.tempo = novoTempo;
                element.textContent = formatarTempo(novoTempo);
                
                // Se o tempo chegou a zero, recarrega a página para atualizar o status
                if (novoTempo <= 0) {
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                }
            }
        });
    }

    // Atualiza contadores a cada segundo
    setInterval(atualizarContadoresTempo, 1000);

    function formatarTempo(segundos) {
        if (segundos <= 0) return 'Entregue!';
        
        const minutos = Math.ceil(segundos / 60);
        if (minutos < 60) {
            return `${minutos} min`;
        } else {
            const horas = Math.floor(minutos / 60);
            const minutosResto = minutos % 60;
            return `${horas}h ${minutosResto}min`;
        }
    }

    // Funções setLanguage e ThemeToggle devem estar no seu 'home.js'
    window.setLanguage = function(lang) {
        console.log(`Idioma definido para: ${lang}`);
        alert(`Idioma da interface mudado para: ${lang}`);
    };

    // Tema
    window.toggleTheme = function() {
        document.body.classList.toggle('dark-mode');
        const theme = document.body.classList.contains('dark-mode') ? 'dark' : 'light';
        localStorage.setItem('theme', theme);
    };

    // Aplicar tema salvo
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-mode');
    }

    // Event listener para o botão de tema
    document.getElementById('themeToggle')?.addEventListener('click', (e) => {
        e.preventDefault();
        toggleTheme();
    });
    </script>
</body>
</html>
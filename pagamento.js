// ====================================================================
// pagamento.js: Lógica Exclusiva da Tela de Pagamento (ATUALIZADA)
// ====================================================================

document.addEventListener('DOMContentLoaded', () => {
    // Configurações e Elementos DOM
    const DELIVERY_TAX = 20.00;
    const CARRINHO_API_URL = 'carrinho_api.php'; // API para ler/limpar o carrinho de sessão
    const cartItemsSummary = document.getElementById('cart-items-summary');
    const subtotalValue = document.getElementById('subtotal-value');
    const deliveryTaxSpan = document.getElementById('delivery-tax');
    const finalTotalValue = document.getElementById('final-total-value');
    const cartCountSpan = document.querySelector('.cart-count');
    
    // ==========================================================
    // CONVERSOR AUTOMÁTICO DE LINKS .html → .php
    // ==========================================================
    document.querySelectorAll('a[href]').forEach(link => {
        if (link.getAttribute('href').includes('.html')) {
            link.setAttribute('href', link.getAttribute('href').replace('.html', '.php'));
        }
    });

    // ==========================================================
    // Funções Auxiliares 
    // ==========================================================
    function formatPrice(value) {
        return 'R$ ' + parseFloat(value).toFixed(2).replace('.', ',');
    }
    
    // --- FUNÇÃO: BUSCA O CARRINHO VIA API PHP ---
    async function getCartFromAPI() {
        try {
            const response = await fetch(`${CARRINHO_API_URL}?action=get`);
            if (!response.ok) throw new Error('Falha na resposta da API do carrinho.');
            
            const data = await response.json();

            if (!data.success) throw new Error(data.message || 'Erro ao carregar dados do carrinho.');
            
            const cart = data.carrinho || [];
            
            // Mapeamento e validação dos dados do carrinho
            return cart.map(item => ({
                ...item,
                price: parseFloat(item.price) || 0,
                quantity: parseInt(item.quantity) || 1
            }));
        } catch (error) {
            console.error('Erro ao buscar carrinho via API:', error);
            return []; 
        }
    }
    
    // ==========================================================
    // 1. LÓGICA DE TEMA E TRADUÇÃO (Para a Sidebar)
    // ==========================================================
    window.toggleTheme = function() {
        const body = document.body;
        body.classList.toggle('dark-mode');
        const theme = body.classList.contains('dark-mode') ? 'dark' : 'light';
        localStorage.setItem('theme', theme);
    };

    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-mode');
    }
    
    window.setLanguage = function(lang) {
        console.log(`Idioma definido para: ${lang}`);
        alert(`Idioma da interface mudado para: ${lang}`);
    };

    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', (e) => {
            e.preventDefault();
            toggleTheme();
        });
    }

    // ==========================================================
    // 2. REMOÇÃO DE ITEM (Atualiza a tela e chama a API)
    // ==========================================================
    function removeFromCart(itemId) {
        // Envia requisição POST para remover o item da Sessão PHP
        fetch(CARRINHO_API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=remove&id=${itemId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log(`🗑️ Item ${itemId} removido via API.`);
                renderCheckoutSummary(); 
            } else {
                alert(data.message || 'Erro ao remover item do carrinho.');
            }
        })
        .catch(error => console.error('Erro de rede/API ao remover:', error));
    }

    // ----------------------------------------------------
    // DELEGAÇÃO DE EVENTOS PARA OS BOTÕES REMOVE
    // ----------------------------------------------------
    if (cartItemsSummary) {
        cartItemsSummary.addEventListener('click', function(e) {
            const removeButton = e.target.closest('.remove-btn');
            if (removeButton) {
                e.preventDefault();
                const itemId = removeButton.dataset.id;
                removeFromCart(itemId);
            }
        });
    }

    // ==========================================================
    // 3. FUNÇÃO PRINCIPAL: RENDERIZAÇÃO E CÁLCULO (Assíncrona)
    // ==========================================================
    async function renderCheckoutSummary() {
        const cart = await getCartFromAPI();

        cartItemsSummary.innerHTML = ''; 
        let subtotal = 0;
        let totalItems = 0;
        
        if (deliveryTaxSpan) {
            deliveryTaxSpan.textContent = formatPrice(DELIVERY_TAX);
        }

        if (cart.length === 0) {
            cartItemsSummary.innerHTML = '<p style="text-align: center; color: #df2356; margin-top: 40px; font-weight: 600;">Nenhum item para checkout. Volte para a Home.</p>';
            if (subtotalValue) subtotalValue.textContent = formatPrice(0);
            if (finalTotalValue) finalTotalValue.textContent = formatPrice(DELIVERY_TAX);
            if (cartCountSpan) cartCountSpan.textContent = '. 0';
            return;
        }

        cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;
            totalItems += item.quantity;

            const itemElement = document.createElement('div');
            itemElement.className = 'cart-item';
            
            itemElement.innerHTML = `
                <img src="${item.img || 'imagems/placeholder.png'}" alt="${item.name}">
                <div class="item-details">
                    <h4>${item.name}</h4>
                    <p>${item.details || 'Produto padrão'}</p>
                    <p>Qtd: ${item.quantity}</p>
                    <a href="#" class="remove-btn" data-id="${item.id}">REMOVER</a> 
                </div>
                <div class="item-actions">
                    <span class="item-price">${formatPrice(itemTotal)}</span>
                </div>
            `;
            
            cartItemsSummary.appendChild(itemElement);
        });

        const finalTotal = subtotal + DELIVERY_TAX;
        
        if (subtotalValue) subtotalValue.textContent = formatPrice(subtotal);
        if (finalTotalValue) finalTotalValue.textContent = formatPrice(finalTotal);
        if (cartCountSpan) cartCountSpan.textContent = `. ${totalItems}`;
    }

    renderCheckoutSummary();
});

// ===============================================
// LÓGICA DE BUSCA DE CEP (VIA CEP) - MANTIDA
// ===============================================

const cepInput = document.getElementById('cep'); 
const searchButton = document.getElementById('button'); 
const logradouroEl = document.getElementById("logradouro");
const bairroEl = document.getElementById("bairro");
const localidadeEl = document.getElementById("localidade");
const ufEl = document.getElementById("uf");

function buscaCEP() {
    const cepValue = cepInput.value.replace(/\D/g, ''); 

    if (!cepValue || cepValue.length !== 8) {
        alert("Por favor, digite um CEP válido com 8 dígitos.");
        bairroEl.innerText = "-";
        localidadeEl.innerText = "-";
        logradouroEl.innerText = "Aguardando CEP...";
        ufEl.innerText = "-";
        return; 
    }

    const url = 'https://viacep.com.br/ws/' + cepValue + '/json/';
    logradouroEl.innerText = "Buscando...";

    fetch(url)
      .then(response => response.json())
      .then(data => {
        if (data.erro) {
            alert("Erro: CEP não encontrado ou inválido.");
            bairroEl.innerText = "CEP não encontrado";
            localidadeEl.innerText = "-";
            logradouroEl.innerText = "CEP Inválido";
            ufEl.innerText = "-";
            return;
        }
        
        bairroEl.innerText = data.bairro;
        localidadeEl.innerText = data.localidade;
        logradouroEl.innerText = data.logradouro;
        ufEl.innerText = data.uf;
      })
      .catch(error => {
          console.error("Erro na requisição:", error);
          alert("Ocorreu um erro ao buscar o CEP. Tente novamente.");
          logradouroEl.innerText = "Erro na busca";
      });
}

if (searchButton) { 
    searchButton.addEventListener('click', buscaCEP);
}

if (cepInput) {
    cepInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault(); 
            buscaCEP();
        }
    });
}

// ===============================================
// LÓGICA DO MODAL DE PAGAMENTO (ATUALIZADA)
// ===============================================

// Variáveis de controle do Modal
const checkoutForm = document.getElementById('checkout-form');
const paymentModal = document.getElementById('paymentModal'); 
const closePaymentModal = document.getElementById('closePaymentModal');
const paymentSelectionForm = document.getElementById('payment-selection-form');

// Variáveis para elementos de conteúdo dinâmico
const contentPix = document.getElementById('contentPix');
const contentBoleto = document.getElementById('contentBoleto');
const contentCard = document.getElementById('contentCard');
const paymentMethods = document.querySelectorAll('input[name="paymentMethod"]');


// --- FUNÇÕES DE CONTROLE DO MODAL ---

// 1. Abrir o Modal ao clicar em FINALIZAR COMPRA
if (checkoutForm) {
    checkoutForm.addEventListener('submit', function(e) {
        e.preventDefault(); 
        paymentModal.style.display = 'flex'; 
        
        const initialMethod = document.querySelector('input[name="paymentMethod"]:checked');
        if (initialMethod) {
            displayPaymentContent(initialMethod.value);
        }
    });
}

// 2. Fechar o Modal ao clicar no 'X'
if (closePaymentModal) {
    closePaymentModal.addEventListener('click', function() {
        paymentModal.style.display = 'none';
    });
}

// 3. Fechar o Modal ao clicar fora
window.onclick = function(event) {
    if (event.target == paymentModal) {
        paymentModal.style.display = 'none';
    }
};

// --- FUNÇÕES DE CONTEÚDO DINÂMICO ---

function displayPaymentContent(method) {
    // 1. Esconde todos os blocos
    if (contentPix) contentPix.style.display = 'none';
    if (contentBoleto) contentBoleto.style.display = 'none';
    if (contentCard) contentCard.style.display = 'none';

    // 2. Mostra apenas o bloco correspondente
    switch (method) {
        case 'pix':
            if (contentPix) contentPix.style.display = 'block';
            break;
        case 'boleto':
            if (contentBoleto) contentBoleto.style.display = 'block';
            break;
        case 'card':
            if (contentCard) contentCard.style.display = 'block'; 
            break;
    }
}

// 3. Adiciona o listener para detectar a mudança nos rádio buttons
paymentMethods.forEach(radio => {
    radio.addEventListener('change', (event) => {
        displayPaymentContent(event.target.value);
    });
});


// 4. Lógica de Confirmação Final, PROCESSAR PEDIDO no BD e REDIRECIONAR
if (paymentSelectionForm) {
    paymentSelectionForm.addEventListener('submit', function(e) {
        e.preventDefault(); 
        
        const selectedMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
        
        // 1. Coleta os dados de endereço do formulário principal
        const logradouro = document.getElementById("logradouro").textContent;
        const bairro = document.getElementById("bairro").textContent;
        const localidade = document.getElementById("localidade").textContent;
        const uf = document.getElementById("uf").textContent;
        const numero = document.getElementById("numero").value;
        const cep = document.getElementById('cep').value.replace(/\D/g, '');

        if (!numero || logradouro === 'Aguardando CEP...' || logradouro === 'Erro na busca' || logradouro === 'CEP Inválido' || cep.length !== 8) {
             alert('Por favor, preencha o CEP e o Número do endereço corretamente antes de finalizar.');
             return;
        }

        const endereco_completo = `${logradouro}, ${numero} - ${bairro}, ${localidade}/${uf} CEP: ${cep}`;

        // 2. Monta os dados para enviar ao PHP
        const orderData = {
            forma_pagamento: selectedMethod,
            endereco_completo: endereco_completo
            // Os itens do carrinho serão lidos da SESSÃO no lado do PHP
        };

        // Exibe o alerta de processamento
        alert(`Pagamento confirmado! Método: ${selectedMethod}. Processando e Finalizando...`);
        
        // Oculta o modal
        paymentModal.style.display = 'none';

        // 3. Chama o endpoint para processar o pedido e zerar o carrinho no BD
        fetch('processa_pedido.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(orderData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('✅ Pedido processado com sucesso! ID:', data.id_pedido);
                alert(`Pedido #${data.id_pedido} finalizado e registrado no banco de dados!`);
                
                // >>> MUDANÇA AQUI: Redirecionamento SÓ no sucesso <<<
                window.location.href = 'index.php';
                
            } else {
                console.error('⚠️ Erro no processamento do BD:', data.message);
                alert('Houve um erro ao registrar seu pedido. ' + data.message);
                // Em caso de falha, re-renderiza o carrinho para mostrar que não foi zerado
                renderCheckoutSummary(); 
            }
        })
        .catch(error => {
            console.error('⚠️ Erro de rede ao processar pedido:', error);
            alert('Ocorreu um erro de rede. Tente novamente. Verifique o console para detalhes.');
            // Em caso de falha de rede, re-renderiza o carrinho para mostrar que não foi zerado
            renderCheckoutSummary(); 
        });
        // >>> REMOVIDO O BLOCO .finally() <<<
    });
}
// ====================================================================
// home.js: Lógica COMPLETA e UNIFICADA (Tema, Voz, Carrinho(PHP), Favoritos(Local), Modais)
// ====================================================================

// --- CONSTANTES GLOBAIS ---
const FAVORITES_STORAGE_KEY = 'productFavorites';
const CARRINHO_API_URL = 'carrinho_api.php'; // Ponto de comunicação com o PHP

// --- ELEMENTOS GLOBAIS (Serão definidos em DOMContentLoaded) ---
let cartItemsContainer, cartTotalValue, cartCountElement, emptyCartMessage, checkoutButton;
let loginModal, cartModal;

// --- FUNÇÕES AUXILIARES DE PREÇO (Defensiva contra formato 19.99 e 19,99) ---
function cleanAndParsePrice(priceString) {
    if (typeof priceString === 'number') return priceString;
    if (typeof priceString !== 'string') return 0;
    
    let cleaned = priceString.replace('R$', '').trim();
    
    // Tenta detectar a presença de vírgula ou ponto no final para decidir o que é decimal
    
    if (cleaned.includes(',')) {
        // Se tem vírgula (formato brasileiro): Remove pontos (milhar), troca vírgula por ponto (decimal)
        cleaned = cleaned.replace(/\./g, '').replace(/\s/g, ''); 
        cleaned = cleaned.replace(',', '.');
    } else {
        // Se NÃO tem vírgula, e tem ponto (formato americano): Remove espaços. Assume ponto é decimal.
        cleaned = cleaned.replace(/,/g, '').replace(/\s/g, ''); 
    }
    
    const parsedValue = parseFloat(cleaned);
    
    return parsedValue || 0;
}

function formatPrice(value) {
    // Formata o valor final para exibição com vírgula (R$ 1.999,90)
    // O valor do 'value' aqui deve ser um float: 19.99
    return 'R$ ' + parseFloat(value || 0).toFixed(2).replace('.', ',');
}

// --- FUNÇÕES DE FAVORITOS (MANTIDO EM LocalStorage) ---
function getFavorites() {
    try {
        const favsString = localStorage.getItem(FAVORITES_STORAGE_KEY) || localStorage.getItem('favoritos');
        return favsString ? JSON.parse(favsString) : [];
    } catch (e) {
        console.error('Erro ao ler favoritos', e);
        return [];
    }
}

function saveFavorites(favorites) {
    try {
        localStorage.setItem(FAVORITES_STORAGE_KEY, JSON.stringify(favorites));
    } catch (e) {
        console.error('Erro ao salvar favoritos', e);
    }
}


// ======================================
// AÇÕES DE PRODUTOS (CARRINHO: VIA AJAX/PHP)
// ======================================

/**
 * 1. Adiciona ou Incrementa um item no carrinho via AJAX.
 * @param {HTMLElement} button - O botão 'Adicionar ao Carrinho' ou equivalente.
 */
window.addToCart = function(button) {
    // A função busca o produto, pode ser o pai .produto ou o próprio botão se tiver os data-atributos
    let productElement = button.closest('.produto');
    
    // Se não encontrou o .produto, tenta usar o próprio botão (caso seja o do modal de detalhes)
    if (!productElement) {
        productElement = button;
    }

    const productId = productElement.dataset.productId;
    const productName = productElement.dataset.name;
    // O preço pode vir do data-price do botão (modal) ou do pai (.produto)
    const rawPrice = productElement.dataset.price;
    const productPrice = cleanAndParsePrice(rawPrice); 
    const productImg = productElement.dataset.img;

    if (!productId || !productName || isNaN(productPrice) || productPrice <= 0) {
        console.error("❌ ERRO GRAVE: Informações do produto faltando ou inválidas.", { productId, productName, productPrice, productImg, rawPrice });
        alert("Erro: Informações do produto incompletas. Verifique o console e o HTML (data-atributos).");
        return;
    }
    
    // Feedback visual
    button.disabled = true;
    const originalText = button.innerHTML;
    // Se for o botão do modal (que não tem ícone), podemos usar texto simples
    const loadingText = button.classList.contains('add-to-cart-btn') ? 'Adicionando...' : '<i class="fas fa-spinner fa-spin"></i> Adicionando...';
    button.innerHTML = loadingText;


    // Envia os dados para o carrinho_api.php
    fetch(CARRINHO_API_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        // Envia productPrice (que AGORA é um float com ponto como separador decimal: 19.99)
        body: `action=add&product_id=${productId}&name=${encodeURIComponent(productName)}&price=${productPrice}&img=${encodeURIComponent(productImg)}`
    })
    .then(response => response.json())
    .then(data => {
        button.disabled = false;
        button.innerHTML = originalText;
        if (data.success) {
            console.log(`✅ ${productName} adicionado/incrementado.`);
            updateCartDisplay();
            // Abre o modal do carrinho após adicionar
            if (cartModal) cartModal.style.display = 'flex';
        } else {
            console.error('Erro PHP:', data.message);
            alert('Erro ao adicionar produto: ' + (data.message || 'Verifique o console.'));
        }
    })
    .catch(error => {
        button.disabled = false;
        button.innerHTML = originalText;
        console.error('Erro de rede/API:', error);
        alert('Erro de conexão ao adicionar ao carrinho.');
    });
};

/**
 * 2. Atualiza a quantidade de um item no PHP/Sessão (chamado por +/-)
 */
window.updateQuantity = function(id, change) {
    // Busca o carrinho atual para obter a quantidade atual e calcular a nova
    fetch(`${CARRINHO_API_URL}?action=get`)
    .then(res => res.json())
    .then(data => {
        if (!data.success) return console.error('Erro ao buscar carrinho para atualização');
        
        const item = data.carrinho.find(i => i.id == id); 
        if (!item) return;

        let newQuantity = item.quantity + change;

        if (newQuantity <= 0) {
            window.removeItem(id); // Remove se a quantidade for zero ou menos
            return;
        }

        // Envia a nova quantidade para o PHP
        fetch(CARRINHO_API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=update_quantity&product_id=${id}&quantity=${newQuantity}` 
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCartDisplay(); // Recarrega o modal
            } else {
                alert(data.message || 'Erro ao atualizar quantidade.');
            }
        })
        .catch(error => console.error('Erro de rede:', error));
    });
};

/**
 * 3. Remove um item completamente do PHP/Sessão.
 */
window.removeItem = function(id) {
    if (!confirm('Tem certeza que deseja remover este item?')) return;
    
    fetch(CARRINHO_API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=remove&product_id=${id}` 
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log(`🗑️ Item ${id} removido.`);
            updateCartDisplay(); // Recarrega o modal
        } else {
            alert(data.message || 'Erro ao remover item.');
        }
    })
    .catch(error => console.error('Erro de rede:', error));
};

/**
 * 4. Puxa os dados atualizados do carrinho do PHP e atualiza o display.
 */
function updateCartDisplay() {
    if (!cartItemsContainer || !cartTotalValue) return; 

    fetch(`${CARRINHO_API_URL}?action=get`)
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            console.error("Erro ao buscar carrinho da API:", data.message);
            data.carrinho = []; 
        }
        
        const cart = data.carrinho || [];
        let total = 0;
        let totalItems = 0;
        cartItemsContainer.innerHTML = '';

        const isCartEmpty = cart.length === 0;
        if (emptyCartMessage) emptyCartMessage.style.display = isCartEmpty ? 'block' : 'none';

        if (!isCartEmpty) {
            cart.forEach(item => {
                // Garantindo que item.price é um número (limpa-se qualquer string do PHP)
                const price = cleanAndParsePrice(item.price);
                const subtotal = price * item.quantity;
                total += subtotal;
                totalItems += item.quantity;

                const itemElement = document.createElement('div');
                itemElement.classList.add('cart-item');
                
                itemElement.innerHTML = `
                    <img src="${item.img || ''}" alt="${item.name}">
                    <div class="item-info">
                        <span class="item-name">${item.name}</span>
                        <span class="item-price">${formatPrice(subtotal)}</span>
                    </div>
                    <div class="item-actions">
                        <button class="quantity-btn minus-btn" onclick="updateQuantity('${item.id}', -1)">-</button>
                        <span class="item-quantity">${item.quantity}</span>
                        <button class="quantity-btn plus-btn" onclick="updateQuantity('${item.id}', 1)">+</button>
                        <button class="remove-btn" onclick="removeItem('${item.id}')"><i class="fas fa-trash"></i></button>
                    </div>
                `;
                cartItemsContainer.appendChild(itemElement);
            });
        }

        cartTotalValue.textContent = formatPrice(total);
        if (cartCountElement) cartCountElement.textContent = `. ${totalItems}`;
        if (checkoutButton) checkoutButton.disabled = isCartEmpty;
    })
    .catch(error => console.error('Erro ao buscar carrinho:', error));
}


// ======================================
// AÇÕES DE PRODUTOS (Favoritos: MANTIDO LocalStorage)
// ======================================

window.toggleFavorite = function(button) {
    // Tenta encontrar o produto container
    let productElement = button.closest('.produto');
    // Se não encontrou, tenta o botão de adicionar ao carrinho do modal (que tem os data-atributos)
    if (!productElement) {
        productElement = button.closest('.detalhes-acoes') ? button.closest('.detalhes-acoes').querySelector('.add-to-cart-btn') : null;
    }
    
    if (!productElement) {
        console.error('toggleFavorite: Elemento base do produto não encontrado.');
        return;
    }

    const productId = productElement.dataset.productId;
    const productName = productElement.dataset.name || '';
    const productPrice = cleanAndParsePrice(productElement.dataset.price || 0);
    const productImg = productElement.dataset.img || '';

    if (!productId) {
        console.error('toggleFavorite: produto sem id');
        return;
    }

    let favorites = getFavorites();
    const isFav = favorites.some(f => f.id === productId);
    
    // Encontra todos os botões de favoritar relacionados a este produto (na página principal e no modal)
    const allFavButtons = document.querySelectorAll(`.produto[data-product-id="${productId}"] .favoritar, #detalhes-favoritar`);

    if (isFav) {
        favorites = favorites.filter(f => f.id !== productId);
        allFavButtons.forEach(btn => btn.classList.remove('favoritado', 'active'));
        console.log(`❌ ${productName} removido dos favoritos.`);
    } else {
        favorites.push({ id: productId, name: productName, price: productPrice, img: productImg });
        allFavButtons.forEach(btn => btn.classList.add('favoritado', 'active'));
        console.log(`❤️ ${productName} adicionado aos favoritos.`);
    }

    saveFavorites(favorites);

    if (typeof window.displayFavorites === 'function') {
        window.displayFavorites();
    }
    updateFavoriteVisuals();
};

window.displayFavorites = function() {
    const listContainer = document.getElementById('favoritos-list');
    if (!listContainer) return;

    const favorites = getFavorites();
    listContainer.innerHTML = '';

    const emptyMessage = document.getElementById('empty-favorites-message');
    if (emptyMessage) emptyMessage.style.display = favorites.length === 0 ? 'block' : 'none';

    favorites.forEach(item => {
        const productCard = document.createElement('div');
        productCard.classList.add('produto', 'favorito-item');
        productCard.setAttribute('data-product-id', item.id);
        productCard.setAttribute('data-name', item.name);
        productCard.setAttribute('data-price', item.price);
        productCard.setAttribute('data-img', item.img);

        productCard.innerHTML = `
            <div class="imagem-container">
                <img src="${item.img || ''}" alt="${item.name}">
            </div>
            <div class="nome-e-preco">
                <span class="nome">${item.name}</span>
                <span class="preco">${formatPrice(item.price)}</span>
            </div>
            <div class="acoes">
                <a href="produtos/produto-detalhe.php?id=${encodeURIComponent(item.id)}" class="comprar open-detalhes-modal" data-product-id="${item.id}">Ver Detalhes</a>
                <button class="add-to-cart-btn" onclick="addToCart(this)"><i class="fas fa-cart-plus"></i> Adicionar</button>
                <button class="favoritar favoritado" onclick="toggleFavorite(this)"><i class="fas fa-heart"></i></button>
            </div>
        `;
        listContainer.appendChild(productCard);
    });

    listContainer.querySelectorAll('.favoritar').forEach(btn => btn.classList.add('favoritado', 'active'));
};

function updateFavoriteVisuals() {
    const favorites = getFavorites();
    const products = document.querySelectorAll('.produto');

    products.forEach(product => {
        const productId = product.dataset.productId;
        const favButton = product.querySelector('.favoritar');

        if (favButton) {
            const isFav = favorites.some(f => f.id === productId);
            if (isFav) {
                favButton.classList.add('favoritado', 'active');
            } else {
                favButton.classList.remove('favoritado', 'active');
            }
        }
    });
}


// ======================================
// TEMA E UTILITÁRIOS
// ======================================
window.toggleTheme = function() {
    const body = document.body;
    body.classList.toggle('dark-mode');
    const theme = body.classList.contains('dark-mode') ? 'dark' : 'light';
    try { localStorage.setItem('theme', theme); } catch (e) { /* ignore */ }
};

window.setLanguage = function(lang) {
    console.log(`Idioma definido para: ${lang}`);
};

window.filtrarCategoria = function(categoria) {
    console.log(`Filtrando por: ${categoria}`);
    window.location.href = `busca.php?q=${encodeURIComponent(categoria)}`;
};

// ======================================
// BUSCA E CONFIGURAÇÕES (GLOBAL)
// ======================================

// Definição da função performSearch no escopo global
window.performSearch = (query) => {
    const searchResultsDiv = document.getElementById('search-results');
    
    // Coleta dados dos produtos 
    const productsData = Array.from(document.querySelectorAll('.produto')).map(productElement => {
        const linkElement = productElement.querySelector('.comprar');
        const url = linkElement ? linkElement.getAttribute('href') : '#';

        return {
            name: productElement.dataset.name,
            img: productElement.dataset.img,
            url: url
        };
    });

    if (!searchResultsDiv) return;

    searchResultsDiv.innerHTML = '';
    
    if (query.length < 2) {
        searchResultsDiv.style.display = 'none';
        return;
    }

    const filteredProducts = productsData.filter(product => 
        product.name.toLowerCase().includes(query.toLowerCase())
    );

    if (filteredProducts.length > 0) {
        filteredProducts.forEach(product => {
            const resultLink = document.createElement('a');
            resultLink.href = product.url; 
            resultLink.classList.add('search-result-item');

            const img = document.createElement('img');
            img.src = product.img;
            img.alt = product.name;
            
            const nameSpan = document.createElement('span');
            nameSpan.textContent = product.name;

            resultLink.appendChild(img);
            resultLink.appendChild(nameSpan);
            
            searchResultsDiv.appendChild(resultLink);
        });
        searchResultsDiv.style.display = 'block'; 
    } else {
        const noResults = document.createElement('div');
        noResults.classList.add('search-result-item');
        noResults.style.cursor = 'default';
        noResults.style.color = '#ccc';
        noResults.style.justifyContent = 'center';
        noResults.textContent = `Nenhum resultado encontrado para "${query}"`;
        searchResultsDiv.appendChild(noResults);
        searchResultsDiv.style.display = 'block';
    }
};


// ======================================
// FUNÇÃO DE COMPARTILHAMENTO (Global)
// ======================================

window.shareProduct = function() {
    const nome = document.getElementById('detalhes-nome').textContent;
    const id = document.getElementById('detalhes-id').textContent;
    // Cria uma URL baseada no ID do produto carregado no modal
    const url = window.location.origin + window.location.pathname + '?id=' + id; 

    if (navigator.share) {
        navigator.share({
            title: nome,
            text: 'Confira este produto incrível na Office Shop!',
            url: url
        }).then(() => {
            console.log('Compartilhado com sucesso!');
        }).catch((error) => {
            console.error('Erro ao compartilhar:', error);
        });
    } else {
        alert("Compartilhe este link: " + url);
    }
}


// ======================================
// INICIALIZAÇÃO E MODAIS (COMPLETO)
// ======================================
document.addEventListener('DOMContentLoaded', () => {
    // 1. Definição de elementos
    cartItemsContainer = document.getElementById('cart-items');
    cartTotalValue = document.getElementById('cart-total-value');
    cartCountElement = document.querySelector('.cart-count');
    emptyCartMessage = document.getElementById('empty-cart-message');
    checkoutButton = document.querySelector('#cart-modal .checkout-btn'); 

    const body = document.body;
    const themeToggle = document.getElementById('themeToggle');
    const openCartBtn = document.getElementById('openCart');
    cartModal = document.getElementById('cart-modal');
    loginModal = document.getElementById('login-modal');

    const openLoginBtn = document.getElementById('openLogin');
    const closeLoginBtn = document.querySelector('#login-modal .close-login-btn');
    const passwordInput = document.getElementById('password_login'); // Corrigido para o ID correto
    const passwordToggle = document.querySelector('.password-toggle');
    const micBtn = document.getElementById('microphone-btn');
    const searchInput = document.getElementById('search-input'); 
    
    // *** Elementos do NOVO Modal de Detalhes ***
    const detalhesModal = document.getElementById('detalhes-modal');
    const closeDetalhesModal = detalhesModal ? detalhesModal.querySelector('.close-btn') : null;


    // Restaurar tema salvo
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') body.classList.add('dark-mode');
    if (themeToggle) themeToggle.addEventListener('click', e => { e.preventDefault(); toggleTheme(); });

    // Inicialização Visual e Carrinho
    updateFavoriteVisuals();
    updateCartDisplay();

    // Modal do carrinho
    if (openCartBtn && cartModal) {
        const closeCartBtn = cartModal.querySelector('.close-btn');

        openCartBtn.addEventListener('click', e => {
            e.preventDefault();
            updateCartDisplay(); // Puxa dados atualizados do PHP
            cartModal.style.display = 'flex';
        });

        if (closeCartBtn) closeCartBtn.addEventListener('click', () => { cartModal.style.display = 'none'; });

        window.addEventListener('click', event => {
            if (event.target === cartModal) cartModal.style.display = 'none';
        });

        if (checkoutButton) {
            checkoutButton.addEventListener('click', () => {
                window.location.href = 'pagamento.php';
            });
        }
    } else {
        console.warn("Aviso: elementos do modal do carrinho não encontrados (#cart-modal ou #openCart).");
    }

    // Modal de login
    if (openLoginBtn && loginModal) {
        openLoginBtn.addEventListener('click', e => { e.preventDefault(); loginModal.style.display = 'flex'; });
        if (closeLoginBtn) closeLoginBtn.addEventListener('click', () => { loginModal.style.display = 'none'; });
        window.addEventListener('click', event => { if (event.target === loginModal) loginModal.style.display = 'none'; });
    }

    // Toggle de senha
    if (passwordToggle && passwordInput) {
        passwordToggle.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    }

    // Redirecionamento da busca por duplo clique
    const searchBtn = document.querySelector('.top-nav .search-btn');
    if (searchBtn) {
        searchBtn.addEventListener('dblclick', e => {
            e.preventDefault();
            const query = searchInput ? searchInput.value : '';
            window.location.href = `busca.php?q=${encodeURIComponent(query)}`;
        });
    }

    // Busca (digitação)
    if (searchInput) {
        searchInput.addEventListener('input', (event) => {
            window.performSearch(event.target.value);
        });
    }

    // Esconder o dropdown da busca
    const searchResultsDiv = document.getElementById('search-results');
    if (searchResultsDiv) {
        document.addEventListener('click', (event) => {
            if (!event.target.closest('.search-container')) {
                searchResultsDiv.style.display = 'none';
            }
        });
    }

    // Inicializa estado visual dos favoritos (marca botões)
    const initialFavorites = getFavorites();
    initialFavorites.forEach(fav => {
        const favButton = document.querySelector(`.produto[data-product-id="${fav.id}"] .favoritar`);
        if (favButton) favButton.classList.add('favoritado', 'active');
    });

    // Se estivermos na página de favoritos, renderiza lista
    if (document.getElementById('favoritos-list')) {
        displayFavorites();
    }
    
    // Configurações de Voz
    if (micBtn && 'SpeechRecognition' in window || 'webkitSpeechRecognition' in window) {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        const recognition = new SpeechRecognition();
        recognition.lang = 'pt-BR';
        recognition.interimResults = false;
        recognition.maxAlternatives = 1;

        micBtn.addEventListener('click', () => {
            micBtn.classList.add('active');
            micBtn.style.color = 'red';
            recognition.start();
        });

        recognition.onresult = (event) => {
            const speechResult = event.results[0][0].transcript;
            if(searchInput) searchInput.value = speechResult;
            if(window.performSearch) window.performSearch(speechResult);
        };

        recognition.onspeechend = () => {
            recognition.stop();
            micBtn.classList.remove('active');
            micBtn.style.color = '';
        };

        recognition.onerror = (event) => {
            console.error('Erro de reconhecimento de voz:', event.error);
            micBtn.classList.remove('active');
            micBtn.style.color = '';
            alert('Erro no microfone ou nenhuma fala detectada.');
        };
    } else if (micBtn) {
        micBtn.style.display = 'none';
    }


    // ======================================
    // >>> LÓGICA DO MODAL DE DETALHES - ATUALIZADA COM DIAGNÓSTICO <<<
    // ======================================
    
    if (detalhesModal) {
        // 1. Abrir Modal de Detalhes
        document.querySelectorAll('.open-detalhes-modal').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const productId = this.dataset.productId;
                
                console.log(`[DETALHES] Tentando buscar detalhes do produto ID: ${productId}`);

                // 2. Chamada AJAX para buscar os detalhes
                fetch('fetch_product_details.php?id=' + productId)
                    .then(response => {
                        // **DIAGNÓSTICO 1: Verifica o Status HTTP (Se o arquivo PHP existe e foi executado)**
                        if (!response.ok) {
                            console.error(`[ERRO FETCH] Status HTTP: ${response.status}. Verifique se 'fetch_product_details.php' existe.`);
                            alert('Erro no servidor ao buscar detalhes (HTTP ' + response.status + '). Veja o console.');
                            throw new Error('Erro HTTP ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        // **DIAGNÓSTICO 2: Verifica Erros Lógicos do PHP (ID não encontrado, etc.)**
                        if (data.error) {
                            console.error('[ERRO LÓGICO PHP]', data.error, data.details || '');
                            alert('Erro ao carregar detalhes: ' + data.error);
                            return;
                        }
                        
                        console.log('✅ [SUCESSO] Detalhes do produto recebidos:', data);
                        
                        // 3. Preencher o Modal com os dados
                        document.getElementById('detalhes-img').src = data.imagem_url;
                        document.getElementById('detalhes-img').alt = data.n_produto;
                        document.getElementById('detalhes-nome').textContent = data.n_produto;
                        
                        // Substitui quebras de linha (se houver) para exibição e se houver descrição
                        document.getElementById('detalhes-descricao').innerHTML = data.descricao ? data.descricao.replace(/\n/g, '<br>') : 'Descrição não disponível';
                        
                        // O PHP deve retornar data.preco como float (Ex: 19.99)
                        document.getElementById('detalhes-preco').textContent = formatPrice(data.preco);
                        
                        document.getElementById('detalhes-id').textContent = data.id_produto;
                        // Campos extras para o modal de detalhes
                        document.getElementById('detalhes-material').textContent = data.material || 'N/D';
                        document.getElementById('detalhes-dimensoes').textContent = data.dimensoes || 'N/D';
                        
                        document.getElementById('detalhes-quantidade').textContent = data.quantidade;

                        // 4. Configurar o botão Comprar Agora (para a função addToCart global)
                        const addToCartBtn = document.getElementById('detalhes-add-to-cart');
                        
                        // Define os data-atributos no botão para a função addToCart
                        addToCartBtn.setAttribute('data-product-id', data.id_produto);
                        addToCartBtn.setAttribute('data-name', data.n_produto);
                        addToCartBtn.setAttribute('data-price', data.preco); // Preço float
                        addToCartBtn.setAttribute('data-img', data.imagem_url);
                        
                        // Adiciona o listener de AddToCart
                        addToCartBtn.onclick = function() {
                            window.addToCart(addToCartBtn);
                            detalhesModal.style.display = 'none'; // Fecha após adicionar
                        };
                        
                        // Atualiza o visual do botão de favorito no modal
                        const favBtn = document.getElementById('detalhes-favoritar');
                        const isFav = getFavorites().some(f => f.id === data.id_produto);
                        if (isFav) {
                            favBtn.classList.add('favoritado', 'active');
                        } else {
                            favBtn.classList.remove('favoritado', 'active');
                        }
                        
                        // Configura o botão de Favoritar no Modal
                        favBtn.onclick = function() {
                            // Usa o botão addToCartBtn como referência do produto para o toggleFavorite
                            window.toggleFavorite(addToCartBtn); 
                        }

                        // 5. Abre o modal
                        detalhesModal.style.display = 'flex';
                    })
                    .catch(error => {
                        // **DIAGNÓSTICO 3: Erros de Rede ou JSON Inválido**
                        console.error('[ERRO FATAL DE REDE/JSON]', error);
                        alert('Erro de conexão ou a resposta do servidor não é JSON válida. Verifique o console.');
                    });
            });
        });

        // 6. Fechar o Modal
        if (closeDetalhesModal) {
            closeDetalhesModal.onclick = function() {
                detalhesModal.style.display = 'none';
            };
        }

        // Fechar ao clicar fora (Listener global para o modal de detalhes)
        window.addEventListener('click', function(event) {
            if (event.target === detalhesModal) {
                detalhesModal.style.display = 'none';
            }
        });
        
    } else {
         console.warn("Aviso: Elemento #detalhes-modal não encontrado. A lógica de exibição de detalhes não funcionará.");
    }
}); 

// Função global de compartilhamento
window.shareCurrentProduct = function() {
    const productName = document.querySelector('.titulo-produto')?.textContent || 'Produto Office Shop';
    const currentUrl = window.location.href;

    if (navigator.share) {
        navigator.share({
            title: productName,
            text: 'Confira este produto incrível na Office Shop!',
            url: currentUrl
        }).then(() => {
            console.log('Produto compartilhado com sucesso!');
        }).catch((error) => {
            console.error('Erro ao compartilhar:', error);
            fallbackShare(currentUrl);
        });
    } else {
        fallbackShare(currentUrl);
    }
}

function fallbackShare(url) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(() => {
            alert('Link copiado para a área de transferência!');
        }).catch(() => {
            prompt('Compartilhe este link:', url);
        });
    } else {
        prompt('Compartilhe este link:', url);
    }
}

// FIM DO DOMContentLoaded
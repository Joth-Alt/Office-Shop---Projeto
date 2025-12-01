// ======================================
// LÓGICA JAVASCRIPT AUTO-CONTIDA (home.js / busca.js)
// ======================================

// --- FUNÇÕES AUXILIARES DE PREÇO E CARRINHO (Mantidas, pois são usadas nas funções de ação) ---

// ... cleanAndParsePrice, formatPrice, getCart, saveCart, calculateTotal, getFavorites, saveFavorites ...
// Deixe todas as suas funções de carrinho e favoritos intactas aqui.

// --- AÇÕES DE PRODUTOS (Mantidas) ---

// ... window.toggleFavorite, window.addToCart, window.updateQuantity, window.removeItem, updateCartDisplay ...
// Deixe todas as suas funções de favoritos e carrinho intactas aqui.

// -----------------------------------------------------------
// --- LÓGICA DE BUSCA E FILTRO (ATUALIZADA PARA FILTRO LOCAL) ---
// -----------------------------------------------------------

function filterProducts(searchTerm) {
    // 1. Encontra a grade de produtos (ID que adicionamos no PHP)
    const productsGrid = document.getElementById('products-grid');
    if (!productsGrid) return;
    
    const term = searchTerm.toLowerCase().trim();
    const products = productsGrid.querySelectorAll('.produto');

    let resultsFound = 0;
    
    // 2. Itera sobre os produtos e aplica o filtro visual
    products.forEach(product => {
        const name = product.getAttribute('data-name').toLowerCase();
        const category = product.getAttribute('data-category').toLowerCase();
        
        // Verifica se o nome OU a categoria contêm o termo de busca
        const matchesSearch = name.includes(term) || category.includes(term);

        if (matchesSearch) {
            product.style.display = 'block'; // Mostra
            resultsFound++;
        } else {
            product.style.display = 'none'; // Esconde
        }
    });

    // Opcional: Atualiza o cabeçalho para refletir os resultados encontrados
    const header = document.querySelector('.busca-header');
    if (header) {
        if (term === "") {
            header.textContent = `Catálogo de Produtos Office Shop 🛍️`;
        } else if (resultsFound > 0) {
            header.textContent = `${resultsFound} resultado(s) encontrado(s) para "${term}"`;
        } else {
            header.textContent = `Nenhum resultado encontrado para "${term}"`;
        }
    }
}

function setupSearchFilter() {
    // ALTERAÇÃO: Usamos o ID do input na top-nav: '#search-input'
    const searchInput = document.getElementById('search-input'); 
    
    if (searchInput) {
        // Aplica o filtro a cada tecla digitada (Filtro local em tempo real)
        searchInput.addEventListener('input', (e) => {
            filterProducts(e.target.value); 
        });

        // Aplica o filtro ao clicar no botão de busca
        const searchButton = document.getElementById('search-local-btn');
        if (searchButton) {
            searchButton.addEventListener('click', () => {
                filterProducts(searchInput.value);
            });
        }

        // Aplica o filtro ao pressionar ENTER
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                filterProducts(searchInput.value);
            }
        });
        
        // Verifica se há um termo 'q' na URL para aplicar o filtro na carga inicial da página
        const urlParams = new URLSearchParams(window.location.search);
        const initialSearch = urlParams.get('q');

        if (initialSearch) {
            // O input já está preenchido pelo PHP, mas o filtro precisa ser executado.
            filterProducts(initialSearch); 
        }
    }
}

function updateFavoriteVisuals() {
    const favorites = getFavorites();
    const products = document.querySelectorAll('.produto');
    
    products.forEach(product => {
        const productId = product.getAttribute('data-product-id');
        const favButton = product.querySelector('.favoritar');
        
        if (favButton) {
            const isFav = favorites.some(f => f.id === productId);
            if (isFav) {
                favButton.classList.add('favoritado');
            } else {
                favButton.classList.remove('favoritado');
            }
        }
    });
}

// --- INICIALIZAÇÃO (Certifique-se de que a função de busca é chamada) ---
document.addEventListener('DOMContentLoaded', () => {
    // ... Definição de elementos globais (cartItemsContainer, etc.) ...
    
    // ... Lógica de Modais (Cart, Login) ...
    
    // ... Lógica de Tema ...
    
    // Chamadas de inicialização essenciais:
    setupSearchFilter(); // Inicializa o filtro local
    updateCartDisplay();
    updateFavoriteVisuals();
    
    // Adiciona listeners para os botões de Ação (garantindo que funcionem em todos os produtos)
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        // Seu código aqui para adicionar listeners...
    });
    document.querySelectorAll('.favoritar').forEach(button => {
        // Seu código aqui para adicionar listeners...
    });
});
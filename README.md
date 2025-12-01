
![Banner do Meu Projeto](https://media.discordapp.net/attachments/1136065868065935401/1445059284458143815/Black_and_Beige_Simple_Coming_Soon_Banner_3.png?ex=692ef7d5&is=692da655&hm=8bf37625c0d0db956a28a7272177f3fe3341d9d4a5aa4586eae69a6535588e9a&=&format=webp&quality=lossless&width=1298&height=649)


# Office Shop

Projeto de E-commerce Web

O **Office Shop** é uma aplicação de e-commerce desenvolvida com foco em
modularidade, usabilidade e recursos modernos --- incluindo sistema de
temas (claro/escuro) e suporte a múltiplos idiomas.

##Visão Geral

O projeto funciona como um protótipo funcional de loja virtual,
permitindo navegação entre produtos, busca, gerenciamento de favoritos e
um carrinho de compras integrado a uma API PHP.

### Funcionalidades Principais

-   **Internacionalização (i18n):** Interface traduzida para Português
    (PT), Inglês (EN) e Espanhol (ES).
-   **Favoritos Persistentes:** Armazenamento via LocalStorage.
-   **Carrinho Dinâmico:** Integrado à API PHP (`carrinho_api.php`) com
    AJAX.
-   **Tema Claro/Escuro:** Alternância por `home.js`.
-   **Modais:** Para carrinho e login.

## Tecnologias Utilizadas

  --------------------------------------------------------------------------
  Categoria                  Tecnologia                      Uso
  -------------------------- ------------------------------- ---------------
  Backend                    PHP                             Gerenciamento
                                                             de sessão do
                                                             carrinho

  Frontend                   HTML5, CSS3, JavaScript         Estrutura,
                                                             estilo e lógica
                                                             client-side

  Estilização                Font Awesome (CDN)              Ícones

  Dados                      LocalStorage                    Persistência

  Servidor                   Apache / XAMPP / WAMP           Execução local
  --------------------------------------------------------------------------

## Estrutura do Projeto

  Arquivo / Diretório         Descrição
  --------------------------- -------------------------------------------------
  **index.php**               Página principal
  **favoritos.php**           Gerenciamento de favoritos
  **perfil.php**              Página de perfil
  **contato.php**             Página de contato
  **minhas_compras.php**      Histórico de compras
  **home.js**                 Lógica de tema, favoritos, carrinho e traduções
  **carrinho_api.php**        API do carrinho
  **produtos/detalhes.php**   Página de detalhes
  **css/**                    Estilos
  **imagens/**                Imagens do projeto

## Como Executar Localmente

### 1. Clonar o Repositório

``` bash
git clone https://github.com/Joth-Alt/Office-Shop---Projeto.git
```

### 2. Configurar o Servidor

Mover para **htdocs/** (XAMPP) ou equivalente.

### 3. Iniciar Apache

Certifique-se de que o servidor está ativo.

### 4. Acessar no Navegador

    http://localhost/Office-Shop---Projeto/

## ❗ Solução de Problemas (Cache)

-   **Windows/Linux:** Ctrl + Shift + R ou Shift + F5\
-   **Mac:** Cmd + Shift + R

-- 1. Configuração Inicial do Banco de Dados
CREATE DATABASE projeto CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE projeto;

-- Tabela USUARIOS (APENAS CLIENTES/USUÁRIOS PADRÃO)
CREATE TABLE usuarios (
 id_usuario INT AUTO_INCREMENT PRIMARY KEY,
 n_usuario VARCHAR(100) NOT NULL,
 email VARCHAR(120) NOT NULL UNIQUE,
 CPF_usuario VARCHAR(14),
 endereco VARCHAR(255) NOT NULL,
 telefone VARCHAR(20),
 senha VARCHAR(255) NOT NULL,
 avatar_url VARCHAR(255),
 capa_url VARCHAR(255)
);

-- Tabela PRODUTO
CREATE TABLE produto (
 id_produto INT AUTO_INCREMENT PRIMARY KEY, 
 n_produto VARCHAR(100) NOT NULL,
 preco DECIMAL(10, 2) NOT NULL,
 quantidade INT NOT NULL,
 imagem_url VARCHAR(255),
 categoria VARCHAR(50) NOT NULL,
 descricao TEXT NULL 
);

-- Tabela PEDIDOS (CORRIGIDA)
CREATE TABLE pedidos (
 id_pedido INT AUTO_INCREMENT PRIMARY KEY,
 data_p DATETIME DEFAULT CURRENT_TIMESTAMP,
 id_usuario INT NOT NULL, 
 forma_pag VARCHAR(30) NOT NULL,
 endereco_entrega VARCHAR(255) NOT NULL,
 endereco_partida VARCHAR(255) DEFAULT 'Av. Monsenhor Theodomiro Lobo, 100 - Parque Res. Maria Elmira, Caçapava - SP, 12285-050',
 endereco_chegada VARCHAR(255),
 tempo_estimado INT DEFAULT 0,
 tempo_restante INT DEFAULT 0,  -- CORRIGIDO: vírgula em vez de ponto e vírgula
 status ENUM('Preparando', 'A caminho', 'Entregue') DEFAULT 'Preparando',
 inicio_entrega DATETIME DEFAULT CURRENT_TIMESTAMP,
 final_entrega DATETIME NULL,
 FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

-- Tabela ITENS_PEDIDO
CREATE TABLE itens_pedido (
 id_pedido INT NOT NULL, 
 id_produto INT NOT NULL, 
 quantidade INT NOT NULL,
 preco DECIMAL(10, 2) NOT NULL,
 PRIMARY KEY (id_pedido, id_produto),
 FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido) ON DELETE CASCADE,
 FOREIGN KEY (id_produto) REFERENCES produto(id_produto)
);
-- Create database
CREATE DATABASE IF NOT EXISTS inventario_db;
USE inventario_db;

-- Create fornecedores table
CREATE TABLE IF NOT EXISTS fornecedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    nivel_acesso ENUM('admin', 'fornecedor') NOT NULL DEFAULT 'fornecedor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create produtos table
CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    quantidade INT NOT NULL DEFAULT 0,
    quantidade_minima INT NOT NULL DEFAULT 5,
    fornecedor_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id)
);

-- Create pedidos table
CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fornecedor_id INT NOT NULL,
    status ENUM('pendente', 'enviado', 'recebido', 'concluido', 'cancelado') NOT NULL DEFAULT 'pendente',
    data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_recebimento TIMESTAMP NULL,
    observacoes TEXT,
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id)
);

-- Create pedidos_itens table
CREATE TABLE IF NOT EXISTS pedidos_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
);

-- Tabela de movimentações de estoque
CREATE TABLE movimentacoes_estoque (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fornecedor_id INT NOT NULL,
    produto_id INT NOT NULL,
    tipo ENUM('entrada', 'saida') NOT NULL,
    quantidade INT NOT NULL,
    data_movimentacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id),
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
);

-- Índices para melhor performance
CREATE INDEX idx_movimentacoes_data ON movimentacoes_estoque(data_movimentacao);
CREATE INDEX idx_pedidos_data ON pedidos(data_pedido);
CREATE INDEX idx_pedidos_status ON pedidos(status);

-- Insert sample data
INSERT INTO fornecedores (nome, email, telefone) VALUES
('Fornecedor A', 'fornecedorA@email.com', '(11) 99999-9999'),
('Fornecedor B', 'fornecedorB@email.com', '(11) 88888-8888');

INSERT INTO produtos (nome, quantidade, quantidade_minima, fornecedor_id) VALUES
('Produto 1', 10, 5, 1),
('Produto 2', 3, 5, 1),
('Produto 3', 15, 5, 2),
('Produto 4', 2, 5, 2); 
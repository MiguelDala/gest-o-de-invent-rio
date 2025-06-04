<?php
header('Content-Type: application/json');
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $conn->query("
            SELECT 
                p.*,
                CASE 
                    WHEN p.quantidade <= p.quantidade_minima THEN 'critico'
                    WHEN p.quantidade <= p.quantidade_minima * 1.5 THEN 'baixo'
                    ELSE 'normal'
                END as status
            FROM produtos p
            ORDER BY p.nome
        ");
        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $produtos]);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao buscar produtos: ' . $e->getMessage()]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO produtos (nome, quantidade, quantidade_minima, fornecedor_id)
            VALUES (:nome, :quantidade, :quantidade_minima, :fornecedor_id)
        ");
        $stmt->execute([
            ':nome' => $data['nome'],
            ':quantidade' => $data['quantidade'],
            ':quantidade_minima' => $data['quantidade_minima'],
            ':fornecedor_id' => $data['fornecedor_id']
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Produto cadastrado com sucesso']);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar produto: ' . $e->getMessage()]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        $stmt = $conn->prepare("
            UPDATE produtos 
            SET quantidade = :quantidade
            WHERE id = :id
        ");
        $stmt->execute([
            ':quantidade' => $data['quantidade'],
            ':id' => $data['id']
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Produto atualizado com sucesso']);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar produto: ' . $e->getMessage()]);
    }
}
?> 
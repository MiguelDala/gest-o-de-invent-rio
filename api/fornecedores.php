<?php
header('Content-Type: application/json');
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        $stmt = $conn->prepare("INSERT INTO fornecedores (nome, email, telefone) VALUES (:nome, :email, :telefone)");
        $stmt->execute([
            ':nome' => $data['nome'],
            ':email' => $data['email'],
            ':telefone' => $data['telefone']
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Fornecedor cadastrado com sucesso']);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar fornecedor: ' . $e->getMessage()]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $conn->query("SELECT * FROM fornecedores ORDER BY nome");
        $fornecedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $fornecedores]);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao buscar fornecedores: ' . $e->getMessage()]);
    }
}
?> 
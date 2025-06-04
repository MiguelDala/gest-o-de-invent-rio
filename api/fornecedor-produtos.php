<?php
header('Content-Type: application/json');
require_once 'config.php';

// Get token from Authorization header
$headers = getallheaders();
$token = null;

if (isset($headers['Authorization'])) {
    $token = str_replace('Bearer ', '', $headers['Authorization']);
}

if (!$token) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Token não fornecido']);
    exit;
}

try {
    // Verify token and get supplier ID
    $stmt = $conn->prepare("SELECT id FROM fornecedores WHERE token = :token");
    $stmt->execute([':token' => $token]);
    $fornecedor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$fornecedor) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token inválido']);
        exit;
    }
    
    // Get supplier's products
    $stmt = $conn->prepare("
        SELECT 
            p.*,
            CASE 
                WHEN p.quantidade <= p.quantidade_minima THEN 'critico'
                WHEN p.quantidade <= p.quantidade_minima * 1.5 THEN 'baixo'
                ELSE 'normal'
            END as status
        FROM produtos p
        WHERE p.fornecedor_id = :fornecedor_id
        ORDER BY p.nome
    ");
    $stmt->execute([':fornecedor_id' => $fornecedor['id']]);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $produtos
    ]);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar produtos: ' . $e->getMessage()
    ]);
}
?> 
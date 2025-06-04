<?php
header('Content-Type: application/json');
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        $stmt = $conn->prepare("SELECT id, nome, email, senha, nivel_acesso FROM fornecedores WHERE email = :email");
        $stmt->execute([':email' => $data['email']]);
        $fornecedor = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($fornecedor && password_verify($data['senha'], $fornecedor['senha'])) {
            // Generate a simple token (in production, use a more secure method)
            $token = bin2hex(random_bytes(32));
            
            // Store token in database (you might want to add a tokens table)
            $stmt = $conn->prepare("UPDATE fornecedores SET token = :token WHERE id = :id");
            $stmt->execute([
                ':token' => $token,
                ':id' => $fornecedor['id']
            ]);
            
            echo json_encode([
                'success' => true,
                'token' => $token,
                'fornecedor' => [
                    'id' => $fornecedor['id'],
                    'nome' => $fornecedor['nome'],
                    'email' => $fornecedor['email'],
                    'nivel_acesso' => $fornecedor['nivel_acesso']
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Email ou senha inválidos'
            ]);
        }
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao fazer login: ' . $e->getMessage()
        ]);
    }
}
?> 
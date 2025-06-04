<?php
header('Content-Type: application/json');
require_once 'config.php';

// Recebe os dados do POST
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['username']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Dados incompletos']);
    exit;
}

$username = $data['username'];
$password = $data['password'];

// Aqui você implementaria a verificação no banco de dados
// Por enquanto, vamos usar credenciais fixas para teste
if ($username === 'admin' && $password === 'admin123') {
    // Gerar token JWT (simplificado para exemplo)
    $token = bin2hex(random_bytes(32));
    
    echo json_encode([
        'success' => true,
        'token' => $token,
        'user' => [
            'username' => $username,
            'role' => 'admin'
        ]
    ]);
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Credenciais inválidas']);
}
?> 
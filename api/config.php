<?php
// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_USER', 'seu_usuario');
define('DB_PASS', 'sua_senha');
define('DB_NAME', 'gestao_inventario');

// Conexão com o banco de dados
function conectarDB() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS,
            array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        die("Erro na conexão: " . $e->getMessage());
    }
}

// Função para verificar autenticação
function verificarAutenticacao() {
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Token não fornecido']);
        exit;
    }

    $token = str_replace('Bearer ', '', $headers['Authorization']);
    // Aqui você implementaria a verificação do token
    // Por enquanto, vamos apenas verificar se existe
    if (empty($token)) {
        http_response_code(401);
        echo json_encode(['error' => 'Token inválido']);
        exit;
    }
}
?> 
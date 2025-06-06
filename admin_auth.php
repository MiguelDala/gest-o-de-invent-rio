<?php
session_start();
require_once 'config.php';

// Admin credentials
define('ADMIN_EMAIL', 'miguelxiaqutangue@epfundao.edu.pt');
define('ADMIN_PASSWORD', 'migueldalamanuel23');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        if ($email === ADMIN_EMAIL && $password === ADMIN_PASSWORD) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_email'] = $email;
            echo json_encode(['success' => true, 'message' => 'Login realizado com sucesso!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Email ou senha incorretos!']);
        }
    } elseif ($action === 'logout') {
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Logout realizado com sucesso!']);
    }
    exit;
}

// Verificar se está logado
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}
?> 
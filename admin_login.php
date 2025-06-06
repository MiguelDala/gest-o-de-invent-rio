<?php
require_once 'admin_auth.php';

if (isAdminLoggedIn()) {
    header('Location: admin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrativo</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <i class="fas fa-user-shield"></i>
                <h1>Login Administrativo</h1>
            </div>
            <form id="loginForm" class="login-form">
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Senha</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" required>
                    </div>
                </div>
                <button type="submit" class="btn-action">Entrar</button>
            </form>
            <div id="loginMessage" class="login-message"></div>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const messageDiv = document.getElementById('loginMessage');
            
            try {
                const response = await fetch('admin_auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=login&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    messageDiv.className = 'login-message success';
                    messageDiv.textContent = data.message;
                    setTimeout(() => {
                        window.location.href = 'admin.php';
                    }, 1000);
                } else {
                    messageDiv.className = 'login-message error';
                    messageDiv.textContent = data.message;
                }
            } catch (error) {
                messageDiv.className = 'login-message error';
                messageDiv.textContent = 'Erro ao tentar fazer login';
            }
        });
    </script>
</body>
</html> 
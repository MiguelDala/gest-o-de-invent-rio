<?php
require_once 'admin_auth.php';

if (!isAdminLoggedIn()) {
    header('Location: admin_login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <nav class="sidebar">
            <div class="logo">
                <i class="fas fa-user-shield"></i>
                <span>Painel Admin</span>
            </div>
            <ul class="nav-links">
                <li class="active">
                    <a href="#dashboard"><i class="fas fa-home"></i> Dashboard</a>
                </li>
                <li>
                    <a href="#usuarios"><i class="fas fa-users"></i> Usuários</a>
                </li>
                <li>
                    <a href="#configuracoes"><i class="fas fa-cog"></i> Configurações</a>
                </li>
                <li>
                    <a href="#logs"><i class="fas fa-history"></i> Logs do Sistema</a>
                </li>
                <li>
                    <a href="#" id="logoutBtn"><i class="fas fa-sign-out-alt"></i> Sair</a>
                </li>
            </ul>
        </nav>

        <main class="main-content">
            <header>
                <div class="header-content">
                    <h1>Painel Administrativo</h1>
                    <div class="user-info">
                        <i class="fas fa-bell"></i>
                        <div class="user-profile">
                            <i class="fas fa-user-shield"></i>
                            <span>Administrador</span>
                        </div>
                    </div>
                </div>
            </header>

            <div class="admin-dashboard">
                <div class="dashboard-cards">
                    <div class="card">
                        <div class="card-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="card-info">
                            <h3>Total de Usuários</h3>
                            <p>150</p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="card-info">
                            <h3>Produtos Cadastrados</h3>
                            <p>1,234</p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="card-info">
                            <h3>Fornecedores</h3>
                            <p>45</p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="card-info">
                            <h3>Vendas do Mês</h3>
                            <p>R$ 45.678</p>
                        </div>
                    </div>
                </div>

                <div class="admin-sections">
                    <section class="recent-activity">
                        <h2>Atividades Recentes</h2>
                        <div class="activity-list">
                            <div class="activity-item">
                                <i class="fas fa-user-plus"></i>
                                <div class="activity-info">
                                    <h4>Novo Usuário Registrado</h4>
                                    <p>João Silva se registrou no sistema</p>
                                    <span class="activity-time">2 minutos atrás</span>
                                </div>
                            </div>
                            <div class="activity-item">
                                <i class="fas fa-box"></i>
                                <div class="activity-info">
                                    <h4>Produto Atualizado</h4>
                                    <p>Estoque do produto XYZ foi atualizado</p>
                                    <span class="activity-time">15 minutos atrás</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="system-status">
                        <h2>Status do Sistema</h2>
                        <div class="status-list">
                            <div class="status-item">
                                <div class="status-info">
                                    <h4>Servidor</h4>
                                    <div class="status-indicator online"></div>
                                </div>
                                <p>Online</p>
                            </div>
                            <div class="status-item">
                                <div class="status-info">
                                    <h4>Banco de Dados</h4>
                                    <div class="status-indicator online"></div>
                                </div>
                                <p>Online</p>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.getElementById('logoutBtn').addEventListener('click', async (e) => {
            e.preventDefault();
            
            try {
                const response = await fetch('admin_auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=logout'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.location.href = 'admin_login.php';
                }
            } catch (error) {
                console.error('Erro ao fazer logout:', error);
            }
        });
    </script>
</body>
</html> 
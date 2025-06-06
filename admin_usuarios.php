<?php
require_once 'admin_auth.php';

if (!isAdminLoggedIn()) {
    header('Location: admin_login.php');
    exit;
}

// Processar registro de usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $tipo = $_POST['tipo'] ?? 'usuario';
    $departamento = $_POST['departamento'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $nivel_acesso = $_POST['nivel_acesso'] ?? 'basico';

    try {
        $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, tipo, departamento, telefone, nivel_acesso) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt->execute([$nome, $email, $senha_hash, $tipo, $departamento, $telefone, $nivel_acesso]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Usuário cadastrado com sucesso!']);
    } catch(PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar usuário: ' . $e->getMessage()]);
    }
    exit;
}

// Buscar usuários
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $conn->query("SELECT id, nome, email, tipo, departamento, telefone, nivel_acesso, data_criacao FROM usuarios ORDER BY nome");
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $usuarios]);
    } catch(PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Erro ao buscar usuários: ' . $e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Usuários - Admin</title>
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
                <li>
                    <a href="admin.php"><i class="fas fa-home"></i> Dashboard</a>
                </li>
                <li class="active">
                    <a href="admin_usuarios.php"><i class="fas fa-users"></i> Usuários</a>
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
                    <h1>Gestão de Usuários</h1>
                    <div class="user-info">
                        <i class="fas fa-bell"></i>
                        <div class="user-profile">
                            <i class="fas fa-user-shield"></i>
                            <span>Administrador</span>
                        </div>
                    </div>
                </div>
            </header>

            <div class="admin-content">
                <div class="admin-section">
                    <h2>Cadastrar Novo Usuário</h2>
                    <form id="userForm" class="admin-form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="nome">Nome Completo</label>
                                <input type="text" id="nome" name="nome" required>
                            </div>

                            <div class="form-group">
                                <label for="email">E-mail</label>
                                <input type="email" id="email" name="email" required>
                            </div>

                            <div class="form-group">
                                <label for="senha">Senha</label>
                                <input type="password" id="senha" name="senha" required>
                            </div>

                            <div class="form-group">
                                <label for="tipo">Tipo de Usuário</label>
                                <select id="tipo" name="tipo" required>
                                    <option value="usuario">Usuário</option>
                                    <option value="admin">Administrador</option>
                                    <option value="gerente">Gerente</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="departamento">Departamento</label>
                                <select id="departamento" name="departamento" required>
                                    <option value="vendas">Vendas</option>
                                    <option value="estoque">Estoque</option>
                                    <option value="financeiro">Financeiro</option>
                                    <option value="rh">RH</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="telefone">Telefone</label>
                                <input type="tel" id="telefone" name="telefone" required>
                            </div>

                            <div class="form-group">
                                <label for="nivel_acesso">Nível de Acesso</label>
                                <select id="nivel_acesso" name="nivel_acesso" required>
                                    <option value="basico">Básico</option>
                                    <option value="intermediario">Intermediário</option>
                                    <option value="avancado">Avançado</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn-action">Cadastrar Usuário</button>
                    </form>
                </div>

                <div class="admin-section">
                    <h2>Usuários Cadastrados</h2>
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>E-mail</th>
                                    <th>Tipo</th>
                                    <th>Departamento</th>
                                    <th>Nível de Acesso</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="userTableBody">
                                <!-- Dados serão inseridos via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Carregar usuários
        async function carregarUsuarios() {
            try {
                const response = await fetch('admin_usuarios.php');
                const data = await response.json();
                
                if (data.success) {
                    const tbody = document.getElementById('userTableBody');
                    tbody.innerHTML = data.data.map(usuario => `
                        <tr>
                            <td>${usuario.nome}</td>
                            <td>${usuario.email}</td>
                            <td>${usuario.tipo}</td>
                            <td>${usuario.departamento}</td>
                            <td>${usuario.nivel_acesso}</td>
                            <td>
                                <button class="btn-icon" onclick="editarUsuario(${usuario.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-icon" onclick="excluirUsuario(${usuario.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `).join('');
                }
            } catch (error) {
                console.error('Erro ao carregar usuários:', error);
            }
        }

        // Cadastrar usuário
        document.getElementById('userForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('admin_usuarios.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert(data.message);
                    e.target.reset();
                    carregarUsuarios();
                } else {
                    alert(data.message);
                }
            } catch (error) {
                alert('Erro ao cadastrar usuário');
            }
        });

        // Carregar usuários ao iniciar a página
        carregarUsuarios();

        // Logout
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
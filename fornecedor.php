<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $cnpj = $_POST['cnpj'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $endereco = $_POST['endereco'] ?? '';

    try {
        $stmt = $conn->prepare("INSERT INTO fornecedores (nome, cnpj, email, telefone, endereco) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nome, $cnpj, $email, $telefone, $endereco]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Fornecedor cadastrado com sucesso!']);
    } catch(PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar fornecedor: ' . $e->getMessage()]);
    }
    exit;
}

// Buscar fornecedores
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $conn->query("SELECT * FROM fornecedores ORDER BY nome");
        $fornecedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $fornecedores]);
    } catch(PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Erro ao buscar fornecedores: ' . $e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Fornecedores</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <nav class="sidebar">
            <!-- Sidebar content from index.html -->
        </nav>

        <main class="main-content">
            <header>
                <div class="header-content">
                    <h1>Cadastro de Fornecedores</h1>
                </div>
            </header>

            <div class="form-container">
                <form id="fornecedorForm" class="form">
                    <div class="form-group">
                        <label for="nome">Nome do Fornecedor</label>
                        <input type="text" id="nome" name="nome" required>
                    </div>

                    <div class="form-group">
                        <label for="cnpj">CNPJ</label>
                        <input type="text" id="cnpj" name="cnpj" required>
                    </div>

                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="telefone">Telefone</label>
                        <input type="tel" id="telefone" name="telefone" required>
                    </div>

                    <div class="form-group">
                        <label for="endereco">Endereço</label>
                        <textarea id="endereco" name="endereco" required></textarea>
                    </div>

                    <button type="submit" class="btn-action">Cadastrar Fornecedor</button>
                </form>
            </div>

            <div class="fornecedores-list">
                <h2>Fornecedores Cadastrados</h2>
                <div id="listaFornecedores"></div>
            </div>
        </main>
    </div>

    <script>
        document.getElementById('fornecedorForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('fornecedor.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert(data.message);
                    e.target.reset();
                    carregarFornecedores();
                } else {
                    alert(data.message);
                }
            } catch (error) {
                alert('Erro ao cadastrar fornecedor');
            }
        });

        async function carregarFornecedores() {
            try {
                const response = await fetch('fornecedor.php');
                const data = await response.json();
                
                if (data.success) {
                    const lista = document.getElementById('listaFornecedores');
                    lista.innerHTML = data.data.map(fornecedor => `
                        <div class="fornecedor-item">
                            <h3>${fornecedor.nome}</h3>
                            <p>CNPJ: ${fornecedor.cnpj}</p>
                            <p>E-mail: ${fornecedor.email}</p>
                            <p>Telefone: ${fornecedor.telefone}</p>
                            <p>Endereço: ${fornecedor.endereco}</p>
                        </div>
                    `).join('');
                }
            } catch (error) {
                console.error('Erro ao carregar fornecedores:', error);
            }
        }

        // Carregar fornecedores ao iniciar a página
        carregarFornecedores();
    </script>
</body>
</html> 
<?php
require_once 'config.php';

// Handle entry registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produto_id = $_POST['produto_id'];
    $quantidade = $_POST['quantidade'];
    $fornecedor_id = $_POST['fornecedor_id'];
    $nota_fiscal = $_POST['nota_fiscal'];
    $data_entrada = $_POST['data_entrada'];
    $observacoes = $_POST['observacoes'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update product quantity
        $sql = "UPDATE produtos SET quantidade = quantidade + ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $quantidade, $produto_id);
        $stmt->execute();
        
        // Record entry
        $sql = "INSERT INTO entradas (produto_id, fornecedor_id, quantidade, nota_fiscal, data_entrada, observacoes) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiisss", $produto_id, $fornecedor_id, $quantidade, $nota_fiscal, $data_entrada, $observacoes);
        $stmt->execute();
        
        // Record movement
        $sql = "INSERT INTO movimentos (produto_id, tipo, quantidade, motivo) 
                VALUES (?, 'entrada', ?, ?)";
        $stmt = $conn->prepare($sql);
        $motivo = "Entrada registrada - NF: " . $nota_fiscal;
        $stmt->bind_param("iis", $produto_id, $quantidade, $motivo);
        $stmt->execute();
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Entrada registrada com sucesso!']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Erro ao registrar entrada: ' . $e->getMessage()]);
    }
    exit;
}

// Get entries list
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT e.*, p.nome as produto_nome, f.nome as fornecedor_nome 
            FROM entradas e 
            JOIN produtos p ON e.produto_id = p.id 
            JOIN fornecedores f ON e.fornecedor_id = f.id 
            ORDER BY e.data_entrada DESC";
    $result = $conn->query($sql);
    $entradas = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $entradas[] = $row;
        }
    }
    
    echo json_encode($entradas);
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Entradas - Sistema de Inventário</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <h2>Inventário</h2>
            </div>
            <ul class="nav-links">
                <li><a href="index.html"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="fornecedores.php"><i class="fas fa-truck"></i> Fornecedores</a></li>
                <li><a href="produtos.php"><i class="fas fa-box"></i> Produtos</a></li>
                <li><a href="estoque.php"><i class="fas fa-warehouse"></i> Estoque</a></li>
                <li class="active"><a href="entradas.php"><i class="fas fa-arrow-down"></i> Entradas</a></li>
                <li><a href="saidas.php"><i class="fas fa-arrow-up"></i> Saídas</a></li>
                <li><a href="pedidos.php"><i class="fas fa-shopping-cart"></i> Pedidos</a></li>
                <li><a href="relatorios.php"><i class="fas fa-chart-bar"></i> Relatórios</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-section">
                <div class="section-header">
                    <h2>Gestão de Entradas</h2>
                    <button class="btn-primary" onclick="showEntryForm()">
                        <i class="fas fa-plus"></i> Nova Entrada
                    </button>
                </div>

                <!-- Entry Form -->
                <div id="entryForm" class="form-container" style="display: none;">
                    <form id="registerEntryForm" class="admin-form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="produto_id">Produto</label>
                                <select id="produto_id" name="produto_id" required>
                                    <option value="">Selecione um produto</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="fornecedor_id">Fornecedor</label>
                                <select id="fornecedor_id" name="fornecedor_id" required>
                                    <option value="">Selecione um fornecedor</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="quantidade">Quantidade</label>
                                <input type="number" id="quantidade" name="quantidade" required min="1">
                            </div>
                            <div class="form-group">
                                <label for="nota_fiscal">Nota Fiscal</label>
                                <input type="text" id="nota_fiscal" name="nota_fiscal" required>
                            </div>
                            <div class="form-group">
                                <label for="data_entrada">Data de Entrada</label>
                                <input type="datetime-local" id="data_entrada" name="data_entrada" required>
                            </div>
                            <div class="form-group full-width">
                                <label for="observacoes">Observações</label>
                                <textarea id="observacoes" name="observacoes" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn-cancel" onclick="hideEntryForm()">Cancelar</button>
                            <button type="submit" class="btn-primary">Registrar Entrada</button>
                        </div>
                    </form>
                </div>

                <!-- Entries List -->
                <div class="entries-grid">
                    <div class="search-bar">
                        <input type="text" id="searchEntry" placeholder="Buscar entradas...">
                        <input type="date" id="filterDate" onchange="filterEntries()">
                    </div>
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Produto</th>
                                    <th>Fornecedor</th>
                                    <th>Quantidade</th>
                                    <th>Nota Fiscal</th>
                                    <th>Observações</th>
                                </tr>
                            </thead>
                            <tbody id="entriesList">
                                <!-- Entries will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Show/Hide Entry Form
        function showEntryForm() {
            document.getElementById('entryForm').style.display = 'block';
            loadProducts();
            loadSuppliers();
            document.getElementById('data_entrada').value = new Date().toISOString().slice(0, 16);
        }

        function hideEntryForm() {
            document.getElementById('entryForm').style.display = 'none';
            document.getElementById('registerEntryForm').reset();
        }

        // Load Products
        function loadProducts() {
            fetch('produtos.php')
                .then(response => response.json())
                .then(data => {
                    const select = document.getElementById('produto_id');
                    select.innerHTML = '<option value="">Selecione um produto</option>';
                    data.forEach(produto => {
                        select.innerHTML += `<option value="${produto.id}">${produto.nome}</option>`;
                    });
                });
        }

        // Load Suppliers
        function loadSuppliers() {
            fetch('fornecedores.php')
                .then(response => response.json())
                .then(data => {
                    const select = document.getElementById('fornecedor_id');
                    select.innerHTML = '<option value="">Selecione um fornecedor</option>';
                    data.forEach(fornecedor => {
                        select.innerHTML += `<option value="${fornecedor.id}">${fornecedor.nome}</option>`;
                    });
                });
        }

        // Format Date
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('pt-BR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Handle Form Submission
        document.getElementById('registerEntryForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('entradas.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    hideEntryForm();
                    loadEntries();
                } else {
                    alert(data.message);
                }
            });
        });

        // Load Entries
        function loadEntries() {
            fetch('entradas.php')
                .then(response => response.json())
                .then(data => {
                    const entriesList = document.getElementById('entriesList');
                    entriesList.innerHTML = '';
                    
                    data.forEach(entrada => {
                        entriesList.innerHTML += `
                            <tr>
                                <td>${formatDate(entrada.data_entrada)}</td>
                                <td>${entrada.produto_nome}</td>
                                <td>${entrada.fornecedor_nome}</td>
                                <td>${entrada.quantidade}</td>
                                <td>${entrada.nota_fiscal}</td>
                                <td>${entrada.observacoes || '-'}</td>
                            </tr>
                        `;
                    });
                });
        }

        // Search and Filter
        document.getElementById('searchEntry').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const date = document.getElementById('filterDate').value;
            filterEntries(searchTerm, date);
        });

        function filterEntries(searchTerm = '', date = '') {
            const rows = document.querySelectorAll('#entriesList tr');
            
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                const rowText = Array.from(cells).map(cell => cell.textContent.toLowerCase()).join(' ');
                const rowDate = cells[0].textContent;
                
                const matchesSearch = rowText.includes(searchTerm);
                const matchesDate = !date || rowDate.includes(date);
                
                row.style.display = matchesSearch && matchesDate ? '' : 'none';
            });
        }

        // Initial Load
        loadEntries();
    </script>
</body>
</html> 
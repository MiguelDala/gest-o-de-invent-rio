<?php
require_once 'config.php';

// Handle stock movement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produto_id = $_POST['produto_id'];
    $tipo = $_POST['tipo']; // 'entrada' or 'saida'
    $quantidade = $_POST['quantidade'];
    $motivo = $_POST['motivo'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update product quantity
        $sql = "UPDATE produtos SET quantidade = quantidade " . 
               ($tipo === 'entrada' ? '+' : '-') . " ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $quantidade, $produto_id);
        $stmt->execute();
        
        // Record movement
        $sql = "INSERT INTO movimentos (produto_id, tipo, quantidade, motivo) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isis", $produto_id, $tipo, $quantidade, $motivo);
        $stmt->execute();
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Movimento registrado com sucesso!']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Erro ao registrar movimento: ' . $e->getMessage()]);
    }
    exit;
}

// Get stock status
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['action'])) {
    $sql = "SELECT p.*, f.nome as fornecedor_nome 
            FROM produtos p 
            LEFT JOIN fornecedores f ON p.fornecedor_id = f.id 
            ORDER BY p.quantidade ASC";
    $result = $conn->query($sql);
    $produtos = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $produtos[] = $row;
        }
    }
    
    echo json_encode($produtos);
    exit;
}

// Get movement history
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'movements') {
    $sql = "SELECT m.*, p.nome as produto_nome 
            FROM movimentos m 
            JOIN produtos p ON m.produto_id = p.id 
            ORDER BY m.data_movimento DESC 
            LIMIT 50";
    $result = $conn->query($sql);
    $movimentos = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $movimentos[] = $row;
        }
    }
    
    echo json_encode($movimentos);
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Estoque - Sistema de Inventário</title>
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
                <li class="active"><a href="estoque.php"><i class="fas fa-warehouse"></i> Estoque</a></li>
                <li><a href="entradas.php"><i class="fas fa-arrow-down"></i> Entradas</a></li>
                <li><a href="saidas.php"><i class="fas fa-arrow-up"></i> Saídas</a></li>
                <li><a href="pedidos.php"><i class="fas fa-shopping-cart"></i> Pedidos</a></li>
                <li><a href="relatorios.php"><i class="fas fa-chart-bar"></i> Relatórios</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-section">
                <div class="section-header">
                    <h2>Gestão de Estoque</h2>
                    <button class="btn-primary" onclick="showMovementForm()">
                        <i class="fas fa-plus"></i> Novo Movimento
                    </button>
                </div>

                <!-- Movement Form -->
                <div id="movementForm" class="form-container" style="display: none;">
                    <form id="registerMovementForm" class="admin-form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="produto_id">Produto</label>
                                <select id="produto_id" name="produto_id" required>
                                    <option value="">Selecione um produto</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="tipo">Tipo de Movimento</label>
                                <select id="tipo" name="tipo" required>
                                    <option value="">Selecione o tipo</option>
                                    <option value="entrada">Entrada</option>
                                    <option value="saida">Saída</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="quantidade">Quantidade</label>
                                <input type="number" id="quantidade" name="quantidade" required min="1">
                            </div>
                            <div class="form-group full-width">
                                <label for="motivo">Motivo</label>
                                <textarea id="motivo" name="motivo" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn-cancel" onclick="hideMovementForm()">Cancelar</button>
                            <button type="submit" class="btn-primary">Registrar Movimento</button>
                        </div>
                    </form>
                </div>

                <!-- Stock Status -->
                <div class="estoque-grid">
                    <div class="search-bar">
                        <input type="text" id="searchStock" placeholder="Buscar produtos...">
                        <select id="filterStock">
                            <option value="">Todos os produtos</option>
                            <option value="baixo">Estoque Baixo</option>
                            <option value="normal">Estoque Normal</option>
                            <option value="alto">Estoque Alto</option>
                        </select>
                    </div>
                    <div class="estoque-list" id="estoqueList">
                        <!-- Stock items will be loaded here -->
                    </div>
                </div>

                <!-- Movement History -->
                <div class="movement-history">
                    <h3>Histórico de Movimentações</h3>
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Produto</th>
                                    <th>Tipo</th>
                                    <th>Quantidade</th>
                                    <th>Motivo</th>
                                </tr>
                            </thead>
                            <tbody id="movementsList">
                                <!-- Movement history will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Show/Hide Movement Form
        function showMovementForm() {
            document.getElementById('movementForm').style.display = 'block';
            loadProducts();
        }

        function hideMovementForm() {
            document.getElementById('movementForm').style.display = 'none';
            document.getElementById('registerMovementForm').reset();
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
        document.getElementById('registerMovementForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('estoque.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    hideMovementForm();
                    loadStock();
                    loadMovements();
                } else {
                    alert(data.message);
                }
            });
        });

        // Load Stock
        function loadStock() {
            fetch('estoque.php')
                .then(response => response.json())
                .then(data => {
                    const estoqueList = document.getElementById('estoqueList');
                    estoqueList.innerHTML = '';
                    
                    data.forEach(produto => {
                        const stockStatus = getStockStatus(produto.quantidade);
                        estoqueList.innerHTML += `
                            <div class="estoque-card ${stockStatus.class}">
                                <div class="estoque-header">
                                    <h3>${produto.nome}</h3>
                                    <span class="status-badge">${stockStatus.text}</span>
                                </div>
                                <div class="estoque-info">
                                    <p><i class="fas fa-barcode"></i> ${produto.codigo_barras}</p>
                                    <p><i class="fas fa-truck"></i> ${produto.fornecedor_nome}</p>
                                    <p><i class="fas fa-boxes"></i> ${produto.quantidade} unidades</p>
                                    <p><i class="fas fa-tag"></i> ${formatPrice(produto.preco)}</p>
                                </div>
                            </div>
                        `;
                    });
                });
        }

        // Load Movements
        function loadMovements() {
            fetch('estoque.php?action=movements')
                .then(response => response.json())
                .then(data => {
                    const movementsList = document.getElementById('movementsList');
                    movementsList.innerHTML = '';
                    
                    data.forEach(movimento => {
                        movementsList.innerHTML += `
                            <tr>
                                <td>${formatDate(movimento.data_movimento)}</td>
                                <td>${movimento.produto_nome}</td>
                                <td>
                                    <span class="badge ${movimento.tipo === 'entrada' ? 'badge-success' : 'badge-danger'}">
                                        ${movimento.tipo === 'entrada' ? 'Entrada' : 'Saída'}
                                    </span>
                                </td>
                                <td>${movimento.quantidade}</td>
                                <td>${movimento.motivo}</td>
                            </tr>
                        `;
                    });
                });
        }

        // Get Stock Status
        function getStockStatus(quantidade) {
            if (quantidade <= 5) {
                return { class: 'estoque-baixo', text: 'Estoque Baixo' };
            } else if (quantidade <= 20) {
                return { class: 'estoque-normal', text: 'Estoque Normal' };
            } else {
                return { class: 'estoque-alto', text: 'Estoque Alto' };
            }
        }

        // Format Price
        function formatPrice(price) {
            return new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(price);
        }

        // Search and Filter
        document.getElementById('searchStock').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const filter = document.getElementById('filterStock').value;
            filterStock(searchTerm, filter);
        });

        document.getElementById('filterStock').addEventListener('change', function(e) {
            const searchTerm = document.getElementById('searchStock').value.toLowerCase();
            const filter = e.target.value;
            filterStock(searchTerm, filter);
        });

        function filterStock(searchTerm, filter) {
            const cards = document.querySelectorAll('.estoque-card');
            
            cards.forEach(card => {
                const nome = card.querySelector('h3').textContent.toLowerCase();
                const status = card.querySelector('.status-badge').textContent.toLowerCase();
                
                const matchesSearch = nome.includes(searchTerm);
                const matchesFilter = !filter || 
                    (filter === 'baixo' && status.includes('baixo')) ||
                    (filter === 'normal' && status.includes('normal')) ||
                    (filter === 'alto' && status.includes('alto'));
                
                card.style.display = matchesSearch && matchesFilter ? 'block' : 'none';
            });
        }

        // Initial Load
        loadStock();
        loadMovements();
    </script>
</body>
</html> 
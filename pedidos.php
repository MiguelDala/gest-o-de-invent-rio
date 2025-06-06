<?php
require_once 'config.php';

// Handle order registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produto_id = $_POST['produto_id'];
    $quantidade = $_POST['quantidade'];
    $solicitante = $_POST['solicitante'];
    $departamento = $_POST['departamento'];
    $prioridade = $_POST['prioridade'];
    $observacoes = $_POST['observacoes'];
    
    $sql = "INSERT INTO pedidos (produto_id, quantidade, solicitante, departamento, prioridade, observacoes, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'pendente')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissss", $produto_id, $quantidade, $solicitante, $departamento, $prioridade, $observacoes);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Pedido registrado com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao registrar pedido: ' . $conn->error]);
    }
    exit;
}

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    parse_str(file_get_contents("php://input"), $_PUT);
    $pedido_id = $_PUT['pedido_id'];
    $status = $_PUT['status'];
    
    $sql = "UPDATE pedidos SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $pedido_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Status do pedido atualizado com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status: ' . $conn->error]);
    }
    exit;
}

// Get orders list
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT p.*, pr.nome as produto_nome 
            FROM pedidos p 
            JOIN produtos pr ON p.produto_id = pr.id 
            ORDER BY 
                CASE p.prioridade 
                    WHEN 'alta' THEN 1 
                    WHEN 'media' THEN 2 
                    WHEN 'baixa' THEN 3 
                END,
                p.data_pedido DESC";
    $result = $conn->query($sql);
    $pedidos = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $pedidos[] = $row;
        }
    }
    
    echo json_encode($pedidos);
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Pedidos - Sistema de Inventário</title>
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
                <li><a href="entradas.php"><i class="fas fa-arrow-down"></i> Entradas</a></li>
                <li><a href="saidas.php"><i class="fas fa-arrow-up"></i> Saídas</a></li>
                <li class="active"><a href="pedidos.php"><i class="fas fa-shopping-cart"></i> Pedidos</a></li>
                <li><a href="relatorios.php"><i class="fas fa-chart-bar"></i> Relatórios</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-section">
                <div class="section-header">
                    <h2>Gestão de Pedidos</h2>
                    <button class="btn-primary" onclick="showOrderForm()">
                        <i class="fas fa-plus"></i> Novo Pedido
                    </button>
                </div>

                <!-- Order Form -->
                <div id="orderForm" class="form-container" style="display: none;">
                    <form id="registerOrderForm" class="admin-form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="produto_id">Produto</label>
                                <select id="produto_id" name="produto_id" required>
                                    <option value="">Selecione um produto</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="quantidade">Quantidade</label>
                                <input type="number" id="quantidade" name="quantidade" required min="1">
                            </div>
                            <div class="form-group">
                                <label for="solicitante">Solicitante</label>
                                <input type="text" id="solicitante" name="solicitante" required>
                            </div>
                            <div class="form-group">
                                <label for="departamento">Departamento</label>
                                <input type="text" id="departamento" name="departamento" required>
                            </div>
                            <div class="form-group">
                                <label for="prioridade">Prioridade</label>
                                <select id="prioridade" name="prioridade" required>
                                    <option value="">Selecione a prioridade</option>
                                    <option value="alta">Alta</option>
                                    <option value="media">Média</option>
                                    <option value="baixa">Baixa</option>
                                </select>
                            </div>
                            <div class="form-group full-width">
                                <label for="observacoes">Observações</label>
                                <textarea id="observacoes" name="observacoes" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn-cancel" onclick="hideOrderForm()">Cancelar</button>
                            <button type="submit" class="btn-primary">Registrar Pedido</button>
                        </div>
                    </form>
                </div>

                <!-- Orders List -->
                <div class="orders-grid">
                    <div class="search-bar">
                        <input type="text" id="searchOrder" placeholder="Buscar pedidos...">
                        <select id="filterStatus">
                            <option value="">Todos os status</option>
                            <option value="pendente">Pendente</option>
                            <option value="em_andamento">Em Andamento</option>
                            <option value="concluido">Concluído</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                        <select id="filterPriority">
                            <option value="">Todas as prioridades</option>
                            <option value="alta">Alta</option>
                            <option value="media">Média</option>
                            <option value="baixa">Baixa</option>
                        </select>
                    </div>
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Produto</th>
                                    <th>Quantidade</th>
                                    <th>Solicitante</th>
                                    <th>Departamento</th>
                                    <th>Prioridade</th>
                                    <th>Status</th>
                                    <th>Observações</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="ordersList">
                                <!-- Orders will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Show/Hide Order Form
        function showOrderForm() {
            document.getElementById('orderForm').style.display = 'block';
            loadProducts();
        }

        function hideOrderForm() {
            document.getElementById('orderForm').style.display = 'none';
            document.getElementById('registerOrderForm').reset();
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
        document.getElementById('registerOrderForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('pedidos.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    hideOrderForm();
                    loadOrders();
                } else {
                    alert(data.message);
                }
            });
        });

        // Update Order Status
        function updateOrderStatus(pedidoId, status) {
            const formData = new FormData();
            formData.append('pedido_id', pedidoId);
            formData.append('status', status);
            
            fetch('pedidos.php', {
                method: 'PUT',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    loadOrders();
                } else {
                    alert(data.message);
                }
            });
        }

        // Load Orders
        function loadOrders() {
            fetch('pedidos.php')
                .then(response => response.json())
                .then(data => {
                    const ordersList = document.getElementById('ordersList');
                    ordersList.innerHTML = '';
                    
                    data.forEach(pedido => {
                        ordersList.innerHTML += `
                            <tr class="order-row ${pedido.status}">
                                <td>${formatDate(pedido.data_pedido)}</td>
                                <td>${pedido.produto_nome}</td>
                                <td>${pedido.quantidade}</td>
                                <td>${pedido.solicitante}</td>
                                <td>${pedido.departamento}</td>
                                <td>
                                    <span class="badge badge-${pedido.prioridade}">
                                        ${pedido.prioridade.charAt(0).toUpperCase() + pedido.prioridade.slice(1)}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-${pedido.status}">
                                        ${pedido.status.charAt(0).toUpperCase() + pedido.status.slice(1)}
                                    </span>
                                </td>
                                <td>${pedido.observacoes || '-'}</td>
                                <td>
                                    <div class="order-actions">
                                        ${pedido.status === 'pendente' ? `
                                            <button class="btn-icon" onclick="updateOrderStatus(${pedido.id}, 'em_andamento')">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        ` : ''}
                                        ${pedido.status === 'em_andamento' ? `
                                            <button class="btn-icon" onclick="updateOrderStatus(${pedido.id}, 'concluido')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        ` : ''}
                                        ${pedido.status !== 'concluido' && pedido.status !== 'cancelado' ? `
                                            <button class="btn-icon" onclick="updateOrderStatus(${pedido.id}, 'cancelado')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        ` : ''}
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                });
        }

        // Search and Filter
        document.getElementById('searchOrder').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const status = document.getElementById('filterStatus').value;
            const priority = document.getElementById('filterPriority').value;
            filterOrders(searchTerm, status, priority);
        });

        document.getElementById('filterStatus').addEventListener('change', function(e) {
            const searchTerm = document.getElementById('searchOrder').value.toLowerCase();
            const status = e.target.value;
            const priority = document.getElementById('filterPriority').value;
            filterOrders(searchTerm, status, priority);
        });

        document.getElementById('filterPriority').addEventListener('change', function(e) {
            const searchTerm = document.getElementById('searchOrder').value.toLowerCase();
            const status = document.getElementById('filterStatus').value;
            const priority = e.target.value;
            filterOrders(searchTerm, status, priority);
        });

        function filterOrders(searchTerm, status, priority) {
            const rows = document.querySelectorAll('.order-row');
            
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                const rowText = Array.from(cells).map(cell => cell.textContent.toLowerCase()).join(' ');
                const rowStatus = row.classList[1];
                const rowPriority = cells[5].textContent.toLowerCase();
                
                const matchesSearch = rowText.includes(searchTerm);
                const matchesStatus = !status || rowStatus === status;
                const matchesPriority = !priority || rowPriority === priority;
                
                row.style.display = matchesSearch && matchesStatus && matchesPriority ? '' : 'none';
            });
        }

        // Initial Load
        loadOrders();
    </script>
</body>
</html> 
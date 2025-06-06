<?php
require_once 'config.php';

// Handle product registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $categoria = $_POST['categoria'];
    $preco = $_POST['preco'];
    $quantidade = $_POST['quantidade'];
    $fornecedor_id = $_POST['fornecedor_id'];
    $codigo_barras = $_POST['codigo_barras'];
    
    $sql = "INSERT INTO produtos (nome, descricao, categoria, preco, quantidade, fornecedor_id, codigo_barras) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssdiss", $nome, $descricao, $categoria, $preco, $quantidade, $fornecedor_id, $codigo_barras);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Produto cadastrado com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar produto: ' . $conn->error]);
    }
    exit;
}

// Get products list
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT p.*, f.nome as fornecedor_nome 
            FROM produtos p 
            LEFT JOIN fornecedores f ON p.fornecedor_id = f.id 
            ORDER BY p.nome";
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
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Produtos - Sistema de Inventário</title>
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
                <li class="active"><a href="produtos.php"><i class="fas fa-box"></i> Produtos</a></li>
                <li><a href="estoque.php"><i class="fas fa-warehouse"></i> Estoque</a></li>
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
                    <h2>Gestão de Produtos</h2>
                    <button class="btn-primary" onclick="showProductForm()">
                        <i class="fas fa-plus"></i> Novo Produto
                    </button>
                </div>

                <!-- Product Registration Form -->
                <div id="productForm" class="form-container" style="display: none;">
                    <form id="registerProductForm" class="admin-form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="nome">Nome do Produto</label>
                                <input type="text" id="nome" name="nome" required>
                            </div>
                            <div class="form-group">
                                <label for="codigo_barras">Código de Barras</label>
                                <input type="text" id="codigo_barras" name="codigo_barras" required>
                            </div>
                            <div class="form-group">
                                <label for="categoria">Categoria</label>
                                <select id="categoria" name="categoria" required>
                                    <option value="">Selecione uma categoria</option>
                                    <option value="Eletrônicos">Eletrônicos</option>
                                    <option value="Móveis">Móveis</option>
                                    <option value="Alimentos">Alimentos</option>
                                    <option value="Vestuário">Vestuário</option>
                                    <option value="Outros">Outros</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="fornecedor_id">Fornecedor</label>
                                <select id="fornecedor_id" name="fornecedor_id" required>
                                    <option value="">Selecione um fornecedor</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="preco">Preço</label>
                                <input type="number" id="preco" name="preco" step="0.01" required>
                            </div>
                            <div class="form-group">
                                <label for="quantidade">Quantidade</label>
                                <input type="number" id="quantidade" name="quantidade" required>
                            </div>
                            <div class="form-group full-width">
                                <label for="descricao">Descrição</label>
                                <textarea id="descricao" name="descricao" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn-cancel" onclick="hideProductForm()">Cancelar</button>
                            <button type="submit" class="btn-primary">Cadastrar Produto</button>
                        </div>
                    </form>
                </div>

                <!-- Products Grid -->
                <div class="produtos-grid">
                    <div class="search-bar">
                        <input type="text" id="searchProduct" placeholder="Buscar produtos...">
                        <select id="filterCategory">
                            <option value="">Todas as categorias</option>
                            <option value="Eletrônicos">Eletrônicos</option>
                            <option value="Móveis">Móveis</option>
                            <option value="Alimentos">Alimentos</option>
                            <option value="Vestuário">Vestuário</option>
                            <option value="Outros">Outros</option>
                        </select>
                    </div>
                    <div class="produtos-list" id="produtosList">
                        <!-- Products will be loaded here -->
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Show/Hide Product Form
        function showProductForm() {
            document.getElementById('productForm').style.display = 'block';
            loadSuppliers();
        }

        function hideProductForm() {
            document.getElementById('productForm').style.display = 'none';
            document.getElementById('registerProductForm').reset();
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

        // Format Price
        function formatPrice(price) {
            return new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(price);
        }

        // Handle Form Submission
        document.getElementById('registerProductForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('produtos.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    hideProductForm();
                    loadProducts();
                } else {
                    alert(data.message);
                }
            });
        });

        // Load Products
        function loadProducts() {
            fetch('produtos.php')
                .then(response => response.json())
                .then(data => {
                    const produtosList = document.getElementById('produtosList');
                    produtosList.innerHTML = '';
                    
                    data.forEach(produto => {
                        produtosList.innerHTML += `
                            <div class="produto-card">
                                <div class="produto-header">
                                    <h3>${produto.nome}</h3>
                                    <span class="categoria-badge">${produto.categoria}</span>
                                </div>
                                <div class="produto-info">
                                    <p><i class="fas fa-barcode"></i> ${produto.codigo_barras}</p>
                                    <p><i class="fas fa-truck"></i> ${produto.fornecedor_nome}</p>
                                    <p><i class="fas fa-tag"></i> ${formatPrice(produto.preco)}</p>
                                    <p><i class="fas fa-boxes"></i> ${produto.quantidade} unidades</p>
                                    <p><i class="fas fa-info-circle"></i> ${produto.descricao}</p>
                                </div>
                                <div class="produto-actions">
                                    <button class="btn-icon" onclick="editProduct(${produto.id})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon" onclick="deleteProduct(${produto.id})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                });
        }

        // Search and Filter
        document.getElementById('searchProduct').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const category = document.getElementById('filterCategory').value;
            filterProducts(searchTerm, category);
        });

        document.getElementById('filterCategory').addEventListener('change', function(e) {
            const searchTerm = document.getElementById('searchProduct').value.toLowerCase();
            const category = e.target.value;
            filterProducts(searchTerm, category);
        });

        function filterProducts(searchTerm, category) {
            const cards = document.querySelectorAll('.produto-card');
            
            cards.forEach(card => {
                const nome = card.querySelector('h3').textContent.toLowerCase();
                const categoria = card.querySelector('.categoria-badge').textContent;
                
                const matchesSearch = nome.includes(searchTerm);
                const matchesCategory = !category || categoria === category;
                
                card.style.display = matchesSearch && matchesCategory ? 'block' : 'none';
            });
        }

        // Initial Load
        loadProducts();
    </script>
</body>
</html> 
<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Fornecedores</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <nav class="sidebar">
            <div class="logo">
                <i class="fas fa-boxes"></i>
                <span>Gestão de Inventário</span>
            </div>
            <ul class="nav-links">
                <li>
                    <a href="index.html"><i class="fas fa-home"></i> Dashboard</a>
                </li>
                <li class="active">
                    <a href="fornecedores.php"><i class="fas fa-truck"></i> Fornecedores</a>
                </li>
                <li>
                    <a href="produtos.php"><i class="fas fa-box"></i> Produtos</a>
                </li>
                <li>
                    <a href="estoque.php"><i class="fas fa-warehouse"></i> Estoque</a>
                </li>
                <li>
                    <a href="entradas.php"><i class="fas fa-arrow-right"></i> Entradas</a>
                </li>
                <li>
                    <a href="saidas.php"><i class="fas fa-arrow-left"></i> Saídas</a>
                </li>
                <li>
                    <a href="pedidos.php"><i class="fas fa-shopping-cart"></i> Pedidos</a>
                </li>
                <li>
                    <a href="relatorios.php"><i class="fas fa-chart-bar"></i> Relatórios</a>
                </li>
            </ul>
        </nav>

        <main class="main-content">
            <header>
                <div class="header-content">
                    <h1>Gestão de Fornecedores</h1>
                    <div class="user-info">
                        <i class="fas fa-bell"></i>
                        <div class="user-profile">
                            <a href="admin_login.php" class="btn-admin">
                                <i class="fas fa-user-shield"></i>
                                Área Admin
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <div class="content-section">
                <div class="section-header">
                    <h2>Cadastro de Fornecedor</h2>
                    <button class="btn-action" onclick="showForm()">
                        <i class="fas fa-plus"></i> Novo Fornecedor
                    </button>
                </div>

                <div id="fornecedorForm" class="form-container" style="display: none;">
                    <form id="cadastroFornecedor" class="form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="nome">Nome da Empresa</label>
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

                            <div class="form-group">
                                <label for="categoria">Categoria de Produtos</label>
                                <select id="categoria" name="categoria" required>
                                    <option value="eletronicos">Eletrônicos</option>
                                    <option value="moveis">Móveis</option>
                                    <option value="alimentos">Alimentos</option>
                                    <option value="vestuario">Vestuário</option>
                                    <option value="outros">Outros</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-action">Cadastrar</button>
                            <button type="button" class="btn-cancel" onclick="hideForm()">Cancelar</button>
                        </div>
                    </form>
                </div>

                <div class="fornecedores-grid">
                    <div class="search-bar">
                        <input type="text" id="searchFornecedor" placeholder="Buscar fornecedor...">
                        <select id="filterCategoria">
                            <option value="">Todas as categorias</option>
                            <option value="eletronicos">Eletrônicos</option>
                            <option value="moveis">Móveis</option>
                            <option value="alimentos">Alimentos</option>
                            <option value="vestuario">Vestuário</option>
                            <option value="outros">Outros</option>
                        </select>
                    </div>

                    <div class="fornecedores-list" id="listaFornecedores">
                        <!-- Fornecedores serão carregados aqui via JavaScript -->
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Mostrar/Esconder formulário
        function showForm() {
            document.getElementById('fornecedorForm').style.display = 'block';
        }

        function hideForm() {
            document.getElementById('fornecedorForm').style.display = 'none';
        }

        // Máscara para CNPJ
        document.getElementById('cnpj').addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 14) {
                value = value.replace(/^(\d{2})(\d)/, '$1.$2');
                value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
                value = value.replace(/(\d{4})(\d)/, '$1-$2');
            }
            e.target.value = value;
        });

        // Máscara para telefone
        document.getElementById('telefone').addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
                value = value.replace(/(\d)(\d{4})$/, '$1-$2');
            }
            e.target.value = value;
        });

        // Cadastrar fornecedor
        document.getElementById('cadastroFornecedor').addEventListener('submit', async (e) => {
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
                    hideForm();
                    carregarFornecedores();
                } else {
                    alert(data.message);
                }
            } catch (error) {
                alert('Erro ao cadastrar fornecedor');
            }
        });

        // Carregar fornecedores
        async function carregarFornecedores() {
            try {
                const response = await fetch('fornecedor.php');
                const data = await response.json();
                
                if (data.success) {
                    const lista = document.getElementById('listaFornecedores');
                    lista.innerHTML = data.data.map(fornecedor => `
                        <div class="fornecedor-card">
                            <div class="fornecedor-header">
                                <h3>${fornecedor.nome}</h3>
                                <span class="categoria-badge">${fornecedor.categoria}</span>
                            </div>
                            <div class="fornecedor-info">
                                <p><i class="fas fa-id-card"></i> CNPJ: ${fornecedor.cnpj}</p>
                                <p><i class="fas fa-envelope"></i> ${fornecedor.email}</p>
                                <p><i class="fas fa-phone"></i> ${fornecedor.telefone}</p>
                                <p><i class="fas fa-map-marker-alt"></i> ${fornecedor.endereco}</p>
                            </div>
                            <div class="fornecedor-actions">
                                <button class="btn-icon" onclick="editarFornecedor(${fornecedor.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-icon" onclick="excluirFornecedor(${fornecedor.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <button class="btn-icon" onclick="verProdutos(${fornecedor.id})">
                                    <i class="fas fa-box"></i>
                                </button>
                            </div>
                        </div>
                    `).join('');
                }
            } catch (error) {
                console.error('Erro ao carregar fornecedores:', error);
            }
        }

        // Buscar fornecedores
        document.getElementById('searchFornecedor').addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('.fornecedor-card');
            
            cards.forEach(card => {
                const nome = card.querySelector('h3').textContent.toLowerCase();
                if (nome.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Filtrar por categoria
        document.getElementById('filterCategoria').addEventListener('change', (e) => {
            const categoria = e.target.value;
            const cards = document.querySelectorAll('.fornecedor-card');
            
            cards.forEach(card => {
                const cardCategoria = card.querySelector('.categoria-badge').textContent;
                if (!categoria || cardCategoria === categoria) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Carregar fornecedores ao iniciar a página
        carregarFornecedores();
    </script>
</body>
</html> 
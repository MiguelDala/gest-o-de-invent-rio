document.addEventListener('DOMContentLoaded', () => {
    // Navigation
    const navLinks = document.querySelectorAll('.main-nav a');
    const sections = document.querySelectorAll('.section');

    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const targetSection = link.getAttribute('data-section');
            
            // Update active states
            navLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');
            
            sections.forEach(section => {
                section.classList.remove('active');
                if (section.id === targetSection) {
                    section.classList.add('active');
                }
            });
        });
    });

    // Gerenciamento de Fornecedores
    const fornecedorForm = document.getElementById('fornecedorForm');
    const fornecedoresTableBody = document.getElementById('fornecedoresTableBody');
    
    // Carregar fornecedores salvos
    let fornecedores = JSON.parse(localStorage.getItem('fornecedores')) || [];
    
    // Função para salvar fornecedores
    function salvarFornecedores() {
        localStorage.setItem('fornecedores', JSON.stringify(fornecedores));
    }
    
    // Função para atualizar a tabela
    function atualizarTabelaFornecedores() {
        fornecedoresTableBody.innerHTML = '';
        fornecedores.forEach((fornecedor, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${fornecedor.nome}</td>
                <td>${fornecedor.email}</td>
                <td>${fornecedor.telefone}</td>
                <td>${fornecedor.categoria}</td>
                <td>
                    <button class="btn-secondary btn-sm" onclick="editarFornecedor(${index})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-secondary btn-sm" onclick="excluirFornecedor(${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            fornecedoresTableBody.appendChild(tr);
        });
    }
    
    // Função para adicionar fornecedor
    fornecedorForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const fornecedor = {
            nome: document.getElementById('nomeFornecedor').value,
            email: document.getElementById('emailFornecedor').value,
            telefone: document.getElementById('telefoneFornecedor').value,
            endereco: document.getElementById('enderecoFornecedor').value,
            categoria: document.getElementById('categoriaFornecedor').value
        };
        
        fornecedores.push(fornecedor);
        salvarFornecedores();
        atualizarTabelaFornecedores();
        fornecedorForm.reset();
        
        // Mostrar mensagem de sucesso
        mostrarMensagem('Fornecedor cadastrado com sucesso!', 'success');
    });
    
    // Função para editar fornecedor
    window.editarFornecedor = function(index) {
        const fornecedor = fornecedores[index];
        document.getElementById('nomeFornecedor').value = fornecedor.nome;
        document.getElementById('emailFornecedor').value = fornecedor.email;
        document.getElementById('telefoneFornecedor').value = fornecedor.telefone;
        document.getElementById('enderecoFornecedor').value = fornecedor.endereco;
        document.getElementById('categoriaFornecedor').value = fornecedor.categoria;
        
        fornecedores.splice(index, 1);
        salvarFornecedores();
        atualizarTabelaFornecedores();
    };
    
    // Função para excluir fornecedor
    window.excluirFornecedor = function(index) {
        if (confirm('Tem certeza que deseja excluir este fornecedor?')) {
            fornecedores.splice(index, 1);
            salvarFornecedores();
            atualizarTabelaFornecedores();
            mostrarMensagem('Fornecedor excluído com sucesso!', 'success');
        }
    };
    
    // Função para mostrar mensagens
    function mostrarMensagem(mensagem, tipo) {
        const mensagemDiv = document.createElement('div');
        mensagemDiv.className = `mensagem ${tipo}`;
        mensagemDiv.textContent = mensagem;
        
        document.body.appendChild(mensagemDiv);
        
        setTimeout(() => {
            mensagemDiv.remove();
        }, 3000);
    }
    
    // Inicializar a tabela
    atualizarTabelaFornecedores();

    // Estoque Verification
    const verificarEstoqueBtn = document.getElementById('verificarEstoque');
    if (verificarEstoqueBtn) {
        verificarEstoqueBtn.addEventListener('click', async () => {
            try {
                const response = await fetch('api/estoque.php');
                const data = await response.json();
                
                updateEstoqueTable(data);
                updateDashboardNumbers(data);
            } catch (error) {
                alert('Erro ao verificar estoque: ' + error.message);
            }
        });
    }

    // Workflow Animation
    const workflowSteps = document.querySelectorAll('.workflow-step');
    let currentStep = 0;

    function updateWorkflow() {
        workflowSteps.forEach((step, index) => {
            if (index <= currentStep) {
                step.classList.add('active');
            } else {
                step.classList.remove('active');
            }
        });
    }

    // Simulate workflow progress (for demonstration)
    setInterval(() => {
        currentStep = (currentStep + 1) % workflowSteps.length;
        updateWorkflow();
    }, 3000);

    // Helper Functions
    function updateEstoqueTable(data) {
        const tableBody = document.getElementById('estoqueTableBody');
        if (!tableBody) return;

        tableBody.innerHTML = '';
        data.forEach(item => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${item.id}</td>
                <td>${item.nome}</td>
                <td>${item.quantidade}</td>
                <td>${getStatusBadge(item.status)}</td>
                <td>
                    <button class="btn-secondary" onclick="editarProduto(${item.id})">Editar</button>
                    <button class="btn-primary" onclick="atualizarEstoque(${item.id})">Atualizar</button>
                </td>
            `;
            tableBody.appendChild(row);
        });
    }

    function getStatusBadge(status) {
        const badges = {
            'normal': '<span class="badge badge-success">Normal</span>',
            'baixo': '<span class="badge badge-warning">Baixo</span>',
            'critico': '<span class="badge badge-danger">Crítico</span>'
        };
        return badges[status] || badges.normal;
    }

    function updateDashboardNumbers(data) {
        const totalProdutos = data.length;
        const produtosEmFalta = data.filter(item => item.status === 'critico').length;
        const pedidosPendentes = data.filter(item => item.status === 'baixo').length;
        const pedidosConcluidos = data.filter(item => item.status === 'normal').length;

        document.querySelectorAll('.card .number').forEach((element, index) => {
            const numbers = [totalProdutos, pedidosPendentes, produtosEmFalta, pedidosConcluidos];
            element.textContent = numbers[index];
        });
    }
}); 
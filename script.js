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

    // Fornecedor Form
    const fornecedorForm = document.getElementById('fornecedorForm');
    if (fornecedorForm) {
        fornecedorForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = {
                nome: document.getElementById('nomeFornecedor').value,
                email: document.getElementById('emailFornecedor').value,
                telefone: document.getElementById('telefoneFornecedor').value
            };

            try {
                const response = await fetch('api/fornecedores.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                if (response.ok) {
                    alert('Fornecedor cadastrado com sucesso!');
                    fornecedorForm.reset();
                } else {
                    throw new Error('Erro ao cadastrar fornecedor');
                }
            } catch (error) {
                alert('Erro ao cadastrar fornecedor: ' + error.message);
            }
        });
    }

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
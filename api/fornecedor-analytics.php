<?php
header('Content-Type: application/json');
require_once 'config.php';

// Get token from Authorization header
$headers = getallheaders();
$token = null;

if (isset($headers['Authorization'])) {
    $token = str_replace('Bearer ', '', $headers['Authorization']);
}

if (!$token) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Token não fornecido']);
    exit;
}

try {
    // Verify token and get supplier ID
    $stmt = $conn->prepare("SELECT id FROM fornecedores WHERE token = :token");
    $stmt->execute([':token' => $token]);
    $fornecedor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$fornecedor) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token inválido']);
        exit;
    }

    $periodo = isset($_GET['periodo']) ? (int)$_GET['periodo'] : 30;
    $dataInicio = date('Y-m-d', strtotime("-{$periodo} days"));
    
    // Get movimentação de estoque
    $stmt = $conn->prepare("
        SELECT 
            DATE(data_movimentacao) as data,
            SUM(CASE WHEN tipo = 'entrada' THEN quantidade ELSE 0 END) as entradas,
            SUM(CASE WHEN tipo = 'saida' THEN quantidade ELSE 0 END) as saidas
        FROM movimentacoes_estoque
        WHERE fornecedor_id = :fornecedor_id
        AND data_movimentacao >= :data_inicio
        GROUP BY DATE(data_movimentacao)
        ORDER BY data
    ");
    $stmt->execute([
        ':fornecedor_id' => $fornecedor['id'],
        ':data_inicio' => $dataInicio
    ]);
    $movimentacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get top produtos
    $stmt = $conn->prepare("
        SELECT 
            p.nome,
            SUM(pi.quantidade) as total_vendido
        FROM pedidos_itens pi
        JOIN produtos p ON p.id = pi.produto_id
        JOIN pedidos pd ON pd.id = pi.pedido_id
        WHERE p.fornecedor_id = :fornecedor_id
        AND pd.data_pedido >= :data_inicio
        GROUP BY p.id
        ORDER BY total_vendido DESC
        LIMIT 5
    ");
    $stmt->execute([
        ':fornecedor_id' => $fornecedor['id'],
        ':data_inicio' => $dataInicio
    ]);
    $topProdutos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get resumo
    $stmt = $conn->prepare("
        SELECT 
            COUNT(DISTINCT p.id) as total_pedidos,
            SUM(pi.quantidade) as total_vendido,
            COUNT(DISTINCT CASE WHEN p.status = 'pendente' THEN p.id END) as pedidos_pendentes
        FROM pedidos p
        JOIN pedidos_itens pi ON pi.pedido_id = p.id
        JOIN produtos pr ON pr.id = pi.produto_id
        WHERE pr.fornecedor_id = :fornecedor_id
        AND p.data_pedido >= :data_inicio
    ");
    $stmt->execute([
        ':fornecedor_id' => $fornecedor['id'],
        ':data_inicio' => $dataInicio
    ]);
    $resumo = $stmt->fetch(PDO::FETCH_ASSOC);

    // Prepare data for charts
    $movimentacaoData = [
        'labels' => array_column($movimentacoes, 'data'),
        'entradas' => array_column($movimentacoes, 'entradas'),
        'saidas' => array_column($movimentacoes, 'saidas')
    ];

    $topProdutosData = [
        'labels' => array_column($topProdutos, 'nome'),
        'valores' => array_column($topProdutos, 'total_vendido')
    ];

    // Simulate previsão de demanda (in a real system, this would use a more sophisticated algorithm)
    $previsaoData = [
        'labels' => array_slice(array_column($movimentacoes, 'data'), -7),
        'real' => array_slice(array_column($movimentacoes, 'saidas'), -7),
        'previsao' => array_map(function($valor) {
            return $valor * (1 + (rand(-10, 10) / 100));
        }, array_slice(array_column($movimentacoes, 'saidas'), -7))
    ];

    echo json_encode([
        'success' => true,
        'data' => [
            'movimentacao' => $movimentacaoData,
            'topProdutos' => $topProdutosData,
            'previsao' => $previsaoData,
            'resumo' => [
                'vendasTotais' => $resumo['total_vendido'] ?? 0,
                'produtosEstoque' => $resumo['total_pedidos'] ?? 0,
                'pedidosPendentes' => $resumo['pedidos_pendentes'] ?? 0
            ]
        ]
    ]);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar dados de análise: ' . $e->getMessage()
    ]);
}
?> 
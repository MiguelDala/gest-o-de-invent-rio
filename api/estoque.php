<?php
header('Content-Type: application/json');
require_once 'config.php';

// Verifica autenticação
verificarAutenticacao();

// Conecta ao banco de dados
$conn = conectarDB();

// Função para tratar erros
function handleError($message) {
    http_response_code(500);
    echo json_encode(['error' => $message]);
    exit;
}

// Roteamento das operações
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($method) {
    case 'GET':
        // Listar produtos
        try {
            $stmt = $conn->query("SELECT * FROM produtos ORDER BY nome");
            $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $produtos]);
        } catch(PDOException $e) {
            handleError("Erro ao listar produtos: " . $e->getMessage());
        }
        break;

    case 'POST':
        // Adicionar novo produto
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['nome']) || !isset($data['categoria']) || 
            !isset($data['quantidade']) || !isset($data['preco_unitario'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Dados incompletos']);
            exit;
        }

        try {
            $stmt = $conn->prepare("INSERT INTO produtos (nome, categoria, quantidade, preco_unitario, quantidade_minima) 
                                  VALUES (:nome, :categoria, :quantidade, :preco_unitario, :quantidade_minima)");
            
            $stmt->execute([
                ':nome' => $data['nome'],
                ':categoria' => $data['categoria'],
                ':quantidade' => $data['quantidade'],
                ':preco_unitario' => $data['preco_unitario'],
                ':quantidade_minima' => $data['quantidade_minima'] ?? 0
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Produto adicionado com sucesso',
                'id' => $conn->lastInsertId()
            ]);
        } catch(PDOException $e) {
            handleError("Erro ao adicionar produto: " . $e->getMessage());
        }
        break;

    case 'PUT':
        // Atualizar produto
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID do produto não fornecido']);
            exit;
        }

        try {
            $updates = [];
            $params = [':id' => $data['id']];

            if (isset($data['nome'])) {
                $updates[] = "nome = :nome";
                $params[':nome'] = $data['nome'];
            }
            if (isset($data['categoria'])) {
                $updates[] = "categoria = :categoria";
                $params[':categoria'] = $data['categoria'];
            }
            if (isset($data['quantidade'])) {
                $updates[] = "quantidade = :quantidade";
                $params[':quantidade'] = $data['quantidade'];
            }
            if (isset($data['preco_unitario'])) {
                $updates[] = "preco_unitario = :preco_unitario";
                $params[':preco_unitario'] = $data['preco_unitario'];
            }
            if (isset($data['quantidade_minima'])) {
                $updates[] = "quantidade_minima = :quantidade_minima";
                $params[':quantidade_minima'] = $data['quantidade_minima'];
            }

            if (empty($updates)) {
                http_response_code(400);
                echo json_encode(['error' => 'Nenhum dado para atualizar']);
                exit;
            }

            $sql = "UPDATE produtos SET " . implode(", ", $updates) . " WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            echo json_encode([
                'success' => true,
                'message' => 'Produto atualizado com sucesso'
            ]);
        } catch(PDOException $e) {
            handleError("Erro ao atualizar produto: " . $e->getMessage());
        }
        break;

    case 'DELETE':
        // Remover produto
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID do produto não fornecido']);
            exit;
        }

        try {
            $stmt = $conn->prepare("DELETE FROM produtos WHERE id = :id");
            $stmt->execute([':id' => $_GET['id']]);

            echo json_encode([
                'success' => true,
                'message' => 'Produto removido com sucesso'
            ]);
        } catch(PDOException $e) {
            handleError("Erro ao remover produto: " . $e->getMessage());
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido']);
        break;
}
?> 
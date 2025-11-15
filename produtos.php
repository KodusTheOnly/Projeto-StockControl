<?php
declare(strict_types=1);

// RF03 e RF04 - API REST para gestão de produtos e lotes
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/conexao_produtos.php';

try {
    $conn = conectarEstoque();
} catch (Throwable $e) {
    responder(500, ['success' => false, 'error' => 'Falha ao conectar ao estoque: ' . $e->getMessage()]);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    switch ($method) {
        case 'GET':     // RF03.2 - Consultar produtos
            listarProdutos($conn);
            break;
        case 'POST':    // RF03.1 - Cadastrar produto/lote
            $payload = lerPayload();
            criarProduto($conn, $payload);
            break;
        case 'PUT':     // RF03.3 - Editar produto/lote
            $payload = lerPayload();
            atualizarProduto($conn, $payload);
            break;
        case 'DELETE':  // RF03.4 - Excluir produto/lote
            $payload = lerPayload();
            excluirProduto($conn, $payload);
            break;
        case 'PATCH':   // RF04 - Gerenciar QR Code
            $payload = lerPayload();
            atualizarQrCode($conn, $payload);
            break;
        default:
            header('Allow: GET, POST, PUT, DELETE, PATCH');
            responder(405, ['success' => false, 'error' => 'Método não suportado.']);
    }
} catch (Throwable $e) {
    responder(500, ['success' => false, 'error' => 'Erro inesperado: ' . $e->getMessage()]);
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}

// Lê e interpreta o corpo da requisição
function lerPayload(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return $_POST ?: [];
    }

    $json = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
        return $json;
    }

    parse_str($raw, $dados);
    return $dados;
}

// Valida e normaliza dados do produto/lote
function normalizarProduto(array $dados): array
{
    $campos = [
        'nome' => 'Nome do produto',
        'categoria' => 'Categoria',
        'fornecedor' => 'Fornecedor',
        'lote' => 'Lote',
        'validade' => 'Data de validade',
        'quantidade' => 'Quantidade',
    ];

    $limpos = [];
    foreach ($campos as $campo => $label) {
        if (!isset($dados[$campo])) {
            responder(400, ['success' => false, 'error' => "O campo {$label} é obrigatório."]);
        }
        $valor = is_string($dados[$campo]) ? trim($dados[$campo]) : $dados[$campo];
        if ($valor === '' && $campo !== 'quantidade') {
            responder(400, ['success' => false, 'error' => "Preencha o campo {$label}."]);
        }
        $limpos[$campo] = $valor;
    }

    // Valida formato da data
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $limpos['validade'])) {
        responder(400, ['success' => false, 'error' => 'Informe a validade no formato AAAA-MM-DD.']);
    }

    // Valida quantidade
    $quantidade = filter_var($limpos['quantidade'], FILTER_VALIDATE_INT);
    if ($quantidade === false || $quantidade < 0) {
        responder(400, ['success' => false, 'error' => 'Quantidade deve ser um número inteiro maior ou igual a zero.']);
    }

    $limpos['quantidade'] = $quantidade;
    return $limpos;
}

// Lista produtos (todos ou específico por ID)
function listarProdutos(mysqli $conn): void
{
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    
    if ($id) {
        // Busca produto específico com quantidade total dos lotes
        $stmt = $conn->prepare('
            SELECT p.*, 
                   (SELECT SUM(l.quantidade) FROM lotes l WHERE l.produto_id = p.id) as quantidade_total
            FROM produtos p 
            WHERE p.id = ? 
            LIMIT 1
        ');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $produto = $stmt->get_result()->fetch_assoc();

        if (!$produto) {
            responder(404, ['success' => false, 'error' => 'Produto não encontrado.']);
        }

        // Busca todos os lotes deste produto
        $stmt_lotes = $conn->prepare('
            SELECT id, lote, fornecedor, validade, quantidade, criado_em, atualizado_em 
            FROM lotes 
            WHERE produto_id = ? 
            ORDER BY validade ASC
        ');
        $stmt_lotes->bind_param('i', $id);
        $stmt_lotes->execute();
        $produto['lotes'] = $stmt_lotes->get_result()->fetch_all(MYSQLI_ASSOC);

        responder(200, ['success' => true, 'produto' => $produto]);
    }

    // Lista todos os produtos com agregações
    $termo = filter_input(INPUT_GET, 'buscar', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
    $sql = '
        SELECT p.id, p.nome, p.categoria, p.fornecedor_padrao, p.qr_code_habilitado,
               p.criado_em, p.atualizado_em,
               (SELECT SUM(l.quantidade) FROM lotes l WHERE l.produto_id = p.id) as quantidade_total,
               (SELECT MIN(l.validade) FROM lotes l WHERE l.produto_id = p.id) as proxima_validade
        FROM produtos p
    ';

    if ($termo !== '') {
        $sql .= ' WHERE p.nome LIKE ? OR p.categoria LIKE ? OR p.fornecedor_padrao LIKE ?';
        $sql .= ' ORDER BY p.criado_em DESC';
        $like = '%' . $termo . '%';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sss', $like, $like, $like);
    } else {
        $sql .= ' ORDER BY p.criado_em DESC';
        $stmt = $conn->prepare($sql);
    }

    $stmt->execute();
    $resultado = $stmt->get_result();
    $produtos = $resultado->fetch_all(MYSQLI_ASSOC);

    responder(200, ['success' => true, 'produtos' => $produtos]);
}

// Cria produto e/ou adiciona novo lote
function criarProduto(mysqli $conn, array $payload): void
{
    $dados = normalizarProduto($payload);
    
    $conn->begin_transaction();
    
    try {
        // Verifica se produto já existe pelo nome
        $stmt_check = $conn->prepare('SELECT id FROM produtos WHERE nome = ? LIMIT 1');
        $stmt_check->bind_param('s', $dados['nome']);
        $stmt_check->execute();
        $resultado = $stmt_check->get_result();
        
        if ($resultado->num_rows > 0) {
            // Produto existe, adiciona apenas novo lote
            $produto_id = $resultado->fetch_assoc()['id'];
            
            // Verifica se já existe lote com mesmo número
            $stmt_lote_check = $conn->prepare('SELECT id FROM lotes WHERE produto_id = ? AND lote = ? LIMIT 1');
            $stmt_lote_check->bind_param('is', $produto_id, $dados['lote']);
            $stmt_lote_check->execute();
            if ($stmt_lote_check->get_result()->num_rows > 0) {
                throw new Exception('Já existe um lote com este número para este produto.');
            }
            
        } else {
            // Cria novo produto
            $stmt_produto = $conn->prepare('
                INSERT INTO produtos (nome, categoria, fornecedor_padrao) 
                VALUES (?, ?, ?)
            ');
            $stmt_produto->bind_param('sss', $dados['nome'], $dados['categoria'], $dados['fornecedor']);
            
            if (!$stmt_produto->execute()) {
                throw new Exception('Não foi possível criar o produto.');
            }
            
            $produto_id = $conn->insert_id;
        }
        
        // Insere o lote
        $stmt_lote = $conn->prepare('
            INSERT INTO lotes (produto_id, lote, fornecedor, validade, quantidade) 
            VALUES (?, ?, ?, ?, ?)
        ');
        $stmt_lote->bind_param('isssi', $produto_id, $dados['lote'], $dados['fornecedor'], $dados['validade'], $dados['quantidade']);
        
        if (!$stmt_lote->execute()) {
            throw new Exception('Não foi possível adicionar o lote.');
        }
        
        $conn->commit();
        responder(201, ['success' => true, 'produto_id' => $produto_id, 'lote_id' => $conn->insert_id]);
        
    } catch (Exception $e) {
        $conn->rollback();
        responder(400, ['success' => false, 'error' => $e->getMessage()]);
    }
}

// Atualiza produto e/ou lote
function atualizarProduto(mysqli $conn, array $payload): void
{
    $id = extrairId($payload);
    if ($id === null) {
        responder(400, ['success' => false, 'error' => 'ID do produto não informado.']);
    }

    if (!produtoExiste($conn, $id)) {
        responder(404, ['success' => false, 'error' => 'Produto não encontrado.']);
    }

    $dados = normalizarProduto($payload);

    $conn->begin_transaction();
    
    try {
        // Atualiza informações gerais do produto
        $stmt_produto = $conn->prepare('
            UPDATE produtos 
            SET nome = ?, categoria = ?, fornecedor_padrao = ? 
            WHERE id = ?
        ');
        $stmt_produto->bind_param('sssi', $dados['nome'], $dados['categoria'], $dados['fornecedor'], $id);
        
        if (!$stmt_produto->execute()) {
            throw new Exception('Não foi possível atualizar o produto.');
        }
        
        // Se veio lote_id, atualiza o lote específico
        if (isset($payload['lote_id'])) {
            $lote_id = filter_var($payload['lote_id'], FILTER_VALIDATE_INT);
            if ($lote_id) {
                $stmt_lote = $conn->prepare('
                    UPDATE lotes 
                    SET lote = ?, fornecedor = ?, validade = ?, quantidade = ? 
                    WHERE id = ? AND produto_id = ?
                ');
                $stmt_lote->bind_param('sssiii', $dados['lote'], $dados['fornecedor'], $dados['validade'], $dados['quantidade'], $lote_id, $id);
                
                if (!$stmt_lote->execute()) {
                    throw new Exception('Não foi possível atualizar o lote.');
                }
            }
        }
        
        $conn->commit();
        responder(200, ['success' => true]);
        
    } catch (Exception $e) {
        $conn->rollback();
        responder(400, ['success' => false, 'error' => $e->getMessage()]);
    }
}

// Exclui produto ou lote específico
function excluirProduto(mysqli $conn, array $payload): void
{
    $id = extrairId($payload);
    if ($id === null) {
        responder(400, ['success' => false, 'error' => 'ID do produto não informado.']);
    }

    // Se veio lote_id, exclui apenas o lote
    if (isset($payload['lote_id'])) {
        $lote_id = filter_var($payload['lote_id'], FILTER_VALIDATE_INT);
        if ($lote_id) {
            $stmt = $conn->prepare('DELETE FROM lotes WHERE id = ? AND produto_id = ?');
            $stmt->bind_param('ii', $lote_id, $id);
            $stmt->execute();
            
            if ($stmt->affected_rows === 0) {
                responder(404, ['success' => false, 'error' => 'Lote não encontrado.']);
            }
            
            responder(200, ['success' => true]);
        }
    }

    // Senão, exclui o produto (CASCADE exclui os lotes automaticamente)
    $stmt = $conn->prepare('DELETE FROM produtos WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        responder(404, ['success' => false, 'error' => 'Produto não encontrado.']);
    }

    responder(200, ['success' => true]);
}

// RF04 - Habilita/desabilita QR Code do produto
function atualizarQrCode(mysqli $conn, array $payload): void
{
    $acao = $payload['acao'] ?? '';
    if (!in_array($acao, ['gerar_qr', 'remover_qr'], true)) {
        responder(400, ['success' => false, 'error' => 'Ação de QR Code inválida.']);
    }

    $id = extrairId($payload);
    if ($id === null) {
        responder(400, ['success' => false, 'error' => 'ID do produto não informado.']);
    }

    if (!produtoExiste($conn, $id)) {
        responder(404, ['success' => false, 'error' => 'Produto não encontrado.']);
    }

    $habilitar = $acao === 'gerar_qr' ? 1 : 0;
    $stmt = $conn->prepare('UPDATE produtos SET qr_code_habilitado = ? WHERE id = ?');
    $stmt->bind_param('ii', $habilitar, $id);
    if (!$stmt->execute()) {
        responder(500, ['success' => false, 'error' => 'Não foi possível atualizar o QR Code.']);
    }

    responder(200, ['success' => true, 'qr_code_habilitado' => $habilitar]);
}

// Verifica se produto existe
function produtoExiste(mysqli $conn, int $id): bool
{
    $stmt = $conn->prepare('SELECT id FROM produtos WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

// Extrai ID da query string ou payload
function extrairId(array $payload): ?int
{
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id !== null && $id !== false) {
        return $id;
    }

    if (isset($payload['id'])) {
        $id = filter_var($payload['id'], FILTER_VALIDATE_INT);
        if ($id !== false && $id > 0) {
            return $id;
        }
    }

    return null;
}

// Envia resposta JSON e finaliza execução
function responder(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}
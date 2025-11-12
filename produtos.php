<?php
declare(strict_types=1);

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
        case 'GET':
            listarProdutos($conn);
            break;
        case 'POST':
            $payload = lerPayload();
            criarProduto($conn, $payload);
            break;
        case 'PUT':
            $payload = lerPayload();
            atualizarProduto($conn, $payload);
            break;
        case 'DELETE':
            $payload = lerPayload();
            excluirProduto($conn, $payload);
            break;
        case 'PATCH':
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

/**
 * Lê e interpreta o corpo da requisição.
 */
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

/**
 * Retorna os dados do produto normalizados e validados.
 */
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

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $limpos['validade'])) {
        responder(400, ['success' => false, 'error' => 'Informe a validade no formato AAAA-MM-DD.']);
    }

    $quantidade = filter_var($limpos['quantidade'], FILTER_VALIDATE_INT);
    if ($quantidade === false || $quantidade < 0) {
        responder(400, ['success' => false, 'error' => 'Quantidade deve ser um número inteiro maior ou igual a zero.']);
    }

    $limpos['quantidade'] = $quantidade;
    return $limpos;
}

function listarProdutos(mysqli $conn): void
{
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id) {
        $stmt = $conn->prepare('SELECT * FROM produtos WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $produto = $stmt->get_result()->fetch_assoc();

        if (!$produto) {
            responder(404, ['success' => false, 'error' => 'Produto não encontrado.']);
        }

        responder(200, ['success' => true, 'produto' => $produto]);
    }

    $termo = filter_input(INPUT_GET, 'buscar', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
    $sql = 'SELECT id, nome, categoria, fornecedor, lote, validade, quantidade, qr_code_habilitado, criado_em, atualizado_em FROM produtos';

    if ($termo !== '') {
        $sql .= ' WHERE nome LIKE ? OR categoria LIKE ? OR fornecedor LIKE ? OR lote LIKE ?';
        $sql .= ' ORDER BY criado_em DESC';
        $like = '%' . $termo . '%';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssss', $like, $like, $like, $like);
    } else {
        $sql .= ' ORDER BY criado_em DESC';
        $stmt = $conn->prepare($sql);
    }

    $stmt->execute();
    $resultado = $stmt->get_result();
    $produtos = $resultado->fetch_all(MYSQLI_ASSOC);

    responder(200, ['success' => true, 'produtos' => $produtos]);
}

function criarProduto(mysqli $conn, array $payload): void
{
    $produto = normalizarProduto($payload);
    garantirProdutoUnico($conn, $produto['nome'], $produto['lote']);

    $stmt = $conn->prepare(
        'INSERT INTO produtos (nome, categoria, fornecedor, lote, validade, quantidade) VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param(
        'sssssi',
        $produto['nome'],
        $produto['categoria'],
        $produto['fornecedor'],
        $produto['lote'],
        $produto['validade'],
        $produto['quantidade']
    );

    if (!$stmt->execute()) {
        responder(500, ['success' => false, 'error' => 'Não foi possível salvar o produto.']);
    }

    responder(201, ['success' => true, 'id' => $conn->insert_id]);
}

function atualizarProduto(mysqli $conn, array $payload): void
{
    $id = extrairId($payload);
    if ($id === null) {
        responder(400, ['success' => false, 'error' => 'ID do produto não informado.']);
    }

    if (!produtoExiste($conn, $id)) {
        responder(404, ['success' => false, 'error' => 'Produto não encontrado.']);
    }

    $produto = normalizarProduto($payload);
    garantirProdutoUnico($conn, $produto['nome'], $produto['lote'], $id);

    $stmt = $conn->prepare(
        'UPDATE produtos SET nome = ?, categoria = ?, fornecedor = ?, lote = ?, validade = ?, quantidade = ? WHERE id = ?'
    );
    $stmt->bind_param(
        'sssssii',
        $produto['nome'],
        $produto['categoria'],
        $produto['fornecedor'],
        $produto['lote'],
        $produto['validade'],
        $produto['quantidade'],
        $id
    );

    if (!$stmt->execute()) {
        responder(500, ['success' => false, 'error' => 'Não foi possível atualizar o produto.']);
    }

    responder(200, ['success' => true]);
}

function excluirProduto(mysqli $conn, array $payload): void
{
    $id = extrairId($payload);
    if ($id === null) {
        responder(400, ['success' => false, 'error' => 'ID do produto não informado.']);
    }

    $stmt = $conn->prepare('DELETE FROM produtos WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        responder(404, ['success' => false, 'error' => 'Produto não encontrado.']);
    }

    responder(200, ['success' => true]);
}

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

function garantirProdutoUnico(mysqli $conn, string $nome, string $lote, ?int $ignorarId = null): void
{
    if ($ignorarId) {
        $stmt = $conn->prepare('SELECT id FROM produtos WHERE nome = ? AND lote = ? AND id <> ? LIMIT 1');
        $stmt->bind_param('ssi', $nome, $lote, $ignorarId);
    } else {
        $stmt = $conn->prepare('SELECT id FROM produtos WHERE nome = ? AND lote = ? LIMIT 1');
        $stmt->bind_param('ss', $nome, $lote);
    }

    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        responder(409, ['success' => false, 'error' => 'Já existe um produto com o mesmo nome e lote.']);
    }
}

function produtoExiste(mysqli $conn, int $id): bool
{
    $stmt = $conn->prepare('SELECT id FROM produtos WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

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

function responder(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

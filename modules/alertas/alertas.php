<?php
declare(strict_types=1);

// RF07 - API REST para gestão de alertas de validade
header('Content-Type: application/json; charset=utf-8');

// Conexão com o banco
require_once __DIR__ . '/../../config/conexao_produtos.php';

try {
    $conn = conectarEstoque();
} catch (Throwable $e) {
    responder(500, ['success' => false, 'error' => 'Falha na conexão: ' . $e->getMessage()]);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    switch ($method) {
        case 'GET':     // RF07.2 - Consultar alertas
            listarAlertas($conn);
            break;
        case 'POST':    // RF07.1 - Cadastrar alerta
            $payload = lerPayload();
            criarAlerta($conn, $payload);
            break;
        case 'PUT':     // RF07.3 - Alterar alerta
            $payload = lerPayload();
            atualizarAlerta($conn, $payload);
            break;
        case 'DELETE':  // RF07.4 - Excluir alerta
            excluirAlerta($conn);
            break;
        case 'PATCH':   // Marcar alerta como visualizado
            $payload = lerPayload();
            marcarVisualizado($conn, $payload);
            break;
        default:
            responder(405, ['success' => false, 'error' => 'Método não suportado']);
    }
} catch (Throwable $e) {
    responder(500, ['success' => false, 'error' => 'Erro: ' . $e->getMessage()]);
} finally {
    if (isset($conn)) $conn->close();
}

// Lê corpo da requisição
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

// RF07.2 - Lista alertas (todos ou específico)
function listarAlertas(mysqli $conn): void
{
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    $lote_id = filter_input(INPUT_GET, 'lote_id', FILTER_VALIDATE_INT);
    $produto_id = filter_input(INPUT_GET, 'produto_id', FILTER_VALIDATE_INT);
    $status = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_SPECIAL_CHARS);
    
    // Primeiro, atualiza status dos alertas que devem estar ativos
    $conn->query("
        UPDATE alertas_validade 
        SET status = 'ativo' 
        WHERE data_alerta <= CURDATE() 
        AND status = 'pendente'
    ");
    
    $sql = "SELECT * FROM v_alertas_completos WHERE 1=1";
    $params = [];
    $types = '';
    
    if ($id) {
        $sql .= " AND id = ?";
        $params[] = $id;
        $types .= 'i';
    }
    
    if ($lote_id) {
        $sql .= " AND lote_id = ?";
        $params[] = $lote_id;
        $types .= 'i';
    }
    
    if ($produto_id) {
        $sql .= " AND produto_id = ?";
        $params[] = $produto_id;
        $types .= 'i';
    }
    
    if ($status) {
        $sql .= " AND status = ?";
        $params[] = $status;
        $types .= 's';
    }
    
    $sql .= " ORDER BY urgencia DESC, data_alerta ASC";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $resultado = $stmt->get_result();
    $alertas = $resultado->fetch_all(MYSQLI_ASSOC);
    
    // Se for consulta específica por ID
    if ($id) {
        if (count($alertas) === 0) {
            responder(404, ['success' => false, 'error' => 'Alerta não encontrado']);
        }
        responder(200, ['success' => true, 'alerta' => $alertas[0]]);
    }
    
    // Adiciona estatísticas
    $stats = [
        'total' => count($alertas),
        'ativos' => count(array_filter($alertas, fn($a) => $a['status'] === 'ativo')),
        'pendentes' => count(array_filter($alertas, fn($a) => $a['status'] === 'pendente')),
        'proximos_7_dias' => count(array_filter($alertas, fn($a) => $a['dias_para_vencer'] <= 7 && $a['dias_para_vencer'] >= 0))
    ];
    
    responder(200, ['success' => true, 'alertas' => $alertas, 'estatisticas' => $stats]);
}

// RF07.1 - Cria novo alerta
function criarAlerta(mysqli $conn, array $payload): void
{
    $lote_id = filter_var($payload['lote_id'] ?? 0, FILTER_VALIDATE_INT);
    $tipo_alerta = $payload['tipo_alerta'] ?? '';
    $data_personalizada = $payload['data_personalizada'] ?? null;
    
    if (!$lote_id) {
        responder(400, ['success' => false, 'error' => 'ID do lote é obrigatório']);
    }
    
    // Busca informações do lote
    $stmt = $conn->prepare("
        SELECT l.*, p.id as produto_id, p.nome 
        FROM lotes l 
        JOIN produtos p ON l.produto_id = p.id 
        WHERE l.id = ?
    ");
    $stmt->bind_param('i', $lote_id);
    $stmt->execute();
    $lote = $stmt->get_result()->fetch_assoc();
    
    if (!$lote) {
        responder(404, ['success' => false, 'error' => 'Lote não encontrado']);
    }
    
    // Calcula dias de antecedência e data do alerta
    $validade = new DateTime($lote['validade']);
    $hoje = new DateTime();
    
    switch ($tipo_alerta) {
        case '1_semana':
            $dias_antecedencia = 7;
            break;
        case '1_mes':
            $dias_antecedencia = 30;
            break;
        case '3_dias':
            $dias_antecedencia = 3;
            break;
        case 'personalizado':
            if (!$data_personalizada) {
                responder(400, ['success' => false, 'error' => 'Data personalizada é obrigatória']);
            }
            $data_alerta_dt = new DateTime($data_personalizada);
            $dias_antecedencia = $validade->diff($data_alerta_dt)->days;
            break;
        default:
            responder(400, ['success' => false, 'error' => 'Tipo de alerta inválido']);
    }
    
    // Calcula data do alerta (exceto personalizado que já tem a data)
    if ($tipo_alerta !== 'personalizado') {
        $data_alerta_dt = clone $validade;
        $data_alerta_dt->sub(new DateInterval('P' . $dias_antecedencia . 'D'));
    }
    
    $data_alerta = $data_alerta_dt->format('Y-m-d');
    
    // Verifica se já existe alerta similar
    $stmt = $conn->prepare("
        SELECT id FROM alertas_validade 
        WHERE lote_id = ? AND data_alerta = ?
    ");
    $stmt->bind_param('is', $lote_id, $data_alerta);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        responder(409, ['success' => false, 'error' => 'Já existe um alerta para esta data']);
    }
    
    // Insere o alerta
    $status = $data_alerta_dt <= $hoje ? 'ativo' : 'pendente';
    
    $stmt = $conn->prepare("
        INSERT INTO alertas_validade 
        (lote_id, produto_id, tipo_alerta, dias_antecedencia, data_alerta, status) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('iisiss', 
        $lote_id, 
        $lote['produto_id'], 
        $tipo_alerta, 
        $dias_antecedencia, 
        $data_alerta,
        $status
    );
    
    if (!$stmt->execute()) {
        responder(500, ['success' => false, 'error' => 'Erro ao criar alerta']);
    }
    
    responder(201, [
        'success' => true, 
        'alerta_id' => $conn->insert_id,
        'mensagem' => 'Alerta criado com sucesso!'
    ]);
}

// RF07.3 - Atualiza alerta
function atualizarAlerta(mysqli $conn, array $payload): void
{
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        responder(400, ['success' => false, 'error' => 'ID do alerta é obrigatório']);
    }
    
    // Verifica se alerta existe
    $stmt = $conn->prepare("
        SELECT a.*, l.validade 
        FROM alertas_validade a 
        JOIN lotes l ON a.lote_id = l.id 
        WHERE a.id = ?
    ");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $alerta = $stmt->get_result()->fetch_assoc();
    
    if (!$alerta) {
        responder(404, ['success' => false, 'error' => 'Alerta não encontrado']);
    }
    
    $tipo_alerta = $payload['tipo_alerta'] ?? $alerta['tipo_alerta'];
    $data_personalizada = $payload['data_personalizada'] ?? null;
    
    // Recalcula data do alerta
    $validade = new DateTime($alerta['validade']);
    
    switch ($tipo_alerta) {
        case '1_semana':
            $dias_antecedencia = 7;
            break;
        case '1_mes':
            $dias_antecedencia = 30;
            break;
        case '3_dias':
            $dias_antecedencia = 3;
            break;
        case 'personalizado':
            if (!$data_personalizada) {
                responder(400, ['success' => false, 'error' => 'Data personalizada é obrigatória']);
            }
            $data_alerta_dt = new DateTime($data_personalizada);
            $dias_antecedencia = $validade->diff($data_alerta_dt)->days;
            break;
        default:
            $dias_antecedencia = $alerta['dias_antecedencia'];
    }
    
    if ($tipo_alerta !== 'personalizado') {
        $data_alerta_dt = clone $validade;
        $data_alerta_dt->sub(new DateInterval('P' . $dias_antecedencia . 'D'));
    }
    
    $data_alerta = $data_alerta_dt->format('Y-m-d');
    $hoje = new DateTime();
    $status = $data_alerta_dt <= $hoje ? 'ativo' : 'pendente';
    
    // Atualiza alerta
    $stmt = $conn->prepare("
        UPDATE alertas_validade 
        SET tipo_alerta = ?, dias_antecedencia = ?, data_alerta = ?, status = ?
        WHERE id = ?
    ");
    $stmt->bind_param('sissi', $tipo_alerta, $dias_antecedencia, $data_alerta, $status, $id);
    
    if (!$stmt->execute()) {
        responder(500, ['success' => false, 'error' => 'Erro ao atualizar alerta']);
    }
    
    responder(200, ['success' => true, 'mensagem' => 'Alerta atualizado com sucesso!']);
}

// RF07.4 - Exclui alerta
function excluirAlerta(mysqli $conn): void
{
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        responder(400, ['success' => false, 'error' => 'ID do alerta é obrigatório']);
    }
    
    $stmt = $conn->prepare("DELETE FROM alertas_validade WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    
    if ($stmt->affected_rows === 0) {
        responder(404, ['success' => false, 'error' => 'Alerta não encontrado']);
    }
    
    responder(200, ['success' => true, 'mensagem' => 'Alerta excluído com sucesso!']);
}

// Marca alerta como visualizado
function marcarVisualizado(mysqli $conn, array $payload): void
{
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        responder(400, ['success' => false, 'error' => 'ID do alerta é obrigatório']);
    }

    $stmt = $conn->prepare("SELECT status FROM alertas_validade WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $alerta = $stmt->get_result()->fetch_assoc();

    if (!$alerta) {
        responder(404, ['success' => false, 'error' => 'Alerta não encontrado']);
    }

    if ($alerta['status'] !== 'visualizado') {
        $stmt = $conn->prepare("UPDATE alertas_validade SET status = 'visualizado' WHERE id = ?");
        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) {
            responder(500, ['success' => false, 'error' => 'Não foi possível atualizar o alerta']);
        }
    }

    responder(200, ['success' => true]);
}

// Envia resposta JSON
function responder(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

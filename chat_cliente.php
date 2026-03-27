<?php
session_start();
require 'db.php';
require 'config_openai.php';

header('Content-Type: application/json; charset=utf-8');

date_default_timezone_set('America/Sao_Paulo');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'erro' => 'Método não permitido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

function normalizarTexto($texto)
{
    $texto = mb_strtolower((string) $texto, 'UTF-8');

    $mapa = [
        'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
        'ç' => 'c', 'ñ' => 'n'
    ];

    $texto = strtr($texto, $mapa);
    $texto = preg_replace('/[^a-z0-9\s]/', ' ', $texto);
    $texto = preg_replace('/\s+/', ' ', $texto);

    return trim((string) $texto);
}

function extrairJsonDaResposta($texto)
{
    $texto = trim((string) $texto);

    if ($texto === '') {
        return null;
    }

    $dados = json_decode($texto, true);
    if (is_array($dados)) {
        return $dados;
    }

    if (preg_match('/```(?:json)?\s*(\{.*\})\s*```/is', $texto, $match)) {
        $dados = json_decode($match[1], true);
        if (is_array($dados)) {
            return $dados;
        }
    }

    $inicio = strpos($texto, '{');
    $fim = strrpos($texto, '}');

    if ($inicio !== false && $fim !== false && $fim > $inicio) {
        $trecho = substr($texto, $inicio, $fim - $inicio + 1);
        $dados = json_decode($trecho, true);
        if (is_array($dados)) {
            return $dados;
        }
    }

    return null;
}

function encontrarItemDoCardapio($nomeBuscado, $itens)
{
    $nomeBuscadoOriginal = trim((string) $nomeBuscado);
    $nomeBuscadoNormalizado = normalizarTexto($nomeBuscadoOriginal);

    if ($nomeBuscadoNormalizado === '') {
        return null;
    }

    $melhorItem = null;
    $melhorScore = -1;

    foreach ($itens as $item) {
        $nomeItem = trim((string) ($item['nome'] ?? ''));
        $nomeItemNormalizado = normalizarTexto($nomeItem);

        if ($nomeItemNormalizado === '') {
            continue;
        }

        $score = 0;

        if ($nomeBuscadoNormalizado === $nomeItemNormalizado) {
            $score = 1000;
        } elseif (strpos($nomeItemNormalizado, $nomeBuscadoNormalizado) !== false || strpos($nomeBuscadoNormalizado, $nomeItemNormalizado) !== false) {
            $score = 900;
        } else {
            similar_text($nomeBuscadoNormalizado, $nomeItemNormalizado, $percentual);
            $score = (int) round($percentual);
        }

        if ($score > $melhorScore) {
            $melhorScore = $score;
            $melhorItem = $item;
        }
    }

    return ($melhorScore >= 55) ? $melhorItem : null;
}

$payload = json_decode(file_get_contents('php://input'), true);
$mensagem = trim((string) ($payload['mensagem'] ?? ''));
$cardapioId = (int) ($payload['cardapio_id'] ?? 0);

if ($mensagem === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'erro' => 'Digite uma mensagem para conversar.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($cardapioId <= 0) {
    if (!isset($_SESSION['usuario_id'])) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'erro' => 'Cardápio não identificado.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $db->prepare('SELECT id FROM cardapios WHERE usuario_id = ? LIMIT 1');
    $stmt->execute([$_SESSION['usuario_id']]);
    $cardapioId = (int) $stmt->fetchColumn();
}

$stmt = $db->prepare('SELECT * FROM cardapios WHERE id = ? LIMIT 1');
$stmt->execute([$cardapioId]);
$cardapio = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cardapio) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'erro' => 'Cardápio não encontrado.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt = $db->prepare('SELECT id, nome, descricao, preco FROM itens WHERE cardapio_id = ? ORDER BY nome ASC');
$stmt->execute([$cardapioId]);
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

$apiKey = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '';
$modelo = defined('OPENAI_MODEL') ? OPENAI_MODEL : 'gpt-4.1-mini';

if ($apiKey === '') {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'erro' => 'A chave da API não foi configurada no servidor.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$horarioAbertura = trim((string) ($cardapio['horario_abertura'] ?? ''));
$horarioFechamento = trim((string) ($cardapio['horario_fechamento'] ?? ''));
$agoraMinutos = (int) date('H') * 60 + (int) date('i');
$statusFuncionamento = 'não informado';

if ($horarioAbertura !== '' && $horarioFechamento !== '') {
    [$aberturaHora, $aberturaMinuto] = array_pad(array_map('intval', explode(':', $horarioAbertura)), 2, 0);
    [$fechamentoHora, $fechamentoMinuto] = array_pad(array_map('intval', explode(':', $horarioFechamento)), 2, 0);

    $aberturaTotal = ($aberturaHora * 60) + $aberturaMinuto;
    $fechamentoTotal = ($fechamentoHora * 60) + $fechamentoMinuto;

    if ($aberturaTotal === $fechamentoTotal) {
        $statusFuncionamento = 'aberto';
    } elseif ($aberturaTotal < $fechamentoTotal) {
        $statusFuncionamento = ($agoraMinutos >= $aberturaTotal && $agoraMinutos < $fechamentoTotal) ? 'aberto' : 'fechado';
    } else {
        $statusFuncionamento = ($agoraMinutos >= $aberturaTotal || $agoraMinutos < $fechamentoTotal) ? 'aberto' : 'fechado';
    }
}

$menuTexto = [];
foreach ($itens as $item) {
    $nome = trim((string) ($item['nome'] ?? ''));
    $descricao = trim((string) ($item['descricao'] ?? ''));
    $preco = number_format((float) ($item['preco'] ?? 0), 2, ',', '.');

    $linha = '- ID ' . (int) ($item['id'] ?? 0) . ' | ' . $nome . ' | R$ ' . $preco;

    if ($descricao !== '') {
        $linha .= ' | ' . $descricao;
    }

    $menuTexto[] = $linha;
}

$contextoCardapio = "Negócio: " . ($cardapio['nome_negocio'] ?? 'Restaurante') . "\n"
    . "Descrição: " . trim((string) ($cardapio['descricao'] ?? '')) . "\n"
    . "Endereço: " . trim((string) ($cardapio['endereco_estabelecimento'] ?? 'Não informado')) . "\n"
    . "Horário: " . ($horarioAbertura !== '' && $horarioFechamento !== '' ? $horarioAbertura . ' às ' . $horarioFechamento : 'Não informado') . "\n"
    . "Status atual: " . $statusFuncionamento . "\n"
    . "Itens do cardápio:\n" . (!empty($menuTexto) ? implode("\n", $menuTexto) : '- Nenhum item cadastrado.');

$systemPrompt = "Você é um atendente virtual simpático de restaurante/lanchonete. "
    . "Responda sempre em português do Brasil, com objetividade e simpatia. "
    . "Use somente as informações do cardápio fornecido. Não invente preços, ingredientes, promoções, tempos de entrega ou políticas. "
    . "Quando a pessoa pedir recomendação, sugira itens reais do cardápio. "
    . "Se a pessoa pedir para adicionar item no carrinho, identifique isso e retorne uma ação estruturada. "
    . "Se a pergunta estiver fora do contexto do cardápio, diga educadamente que você só pode ajudar com o cardápio, horários, endereço e produtos.\n\n"
    . "Você DEVE responder APENAS em JSON válido, sem markdown, sem crases e sem texto extra.\n"
    . "Formato obrigatório:\n"
    . "{\n"
    . "  \"resposta\": \"texto amigável para o cliente\",\n"
    . "  \"acao\": \"nenhuma\" ou \"adicionar_ao_carrinho\",\n"
    . "  \"itens_carrinho\": [\n"
    . "    {\"nome\": \"nome do item\", \"quantidade\": 1}\n"
    . "  ]\n"
    . "}\n"
    . "Regras para itens_carrinho:\n"
    . "- Preencha somente quando a intenção for realmente adicionar ao carrinho.\n"
    . "- Use nomes de itens existentes no cardápio.\n"
    . "- Quantidade deve ser número inteiro maior que zero.\n"
    . "- Se a pessoa pedir mais de um item, liste todos.\n"
    . "- Se houver dúvida sobre qual item adicionar, não invente; use acao='nenhuma'.\n\n"
    . $contextoCardapio;

if (!isset($_SESSION['chat_cardapio_historico']) || !is_array($_SESSION['chat_cardapio_historico'])) {
    $_SESSION['chat_cardapio_historico'] = [];
}

if (!isset($_SESSION['chat_cardapio_historico'][$cardapioId]) || !is_array($_SESSION['chat_cardapio_historico'][$cardapioId])) {
    $_SESSION['chat_cardapio_historico'][$cardapioId] = [];
}

$historico = $_SESSION['chat_cardapio_historico'][$cardapioId];
$historico[] = ['role' => 'user', 'content' => $mensagem];
$historico = array_slice($historico, -8);

$messages = array_merge(
    [['role' => 'system', 'content' => $systemPrompt]],
    $historico
);

$body = json_encode([
    'model' => $modelo,
    'messages' => $messages,
    'temperature' => 0.2,
    'max_tokens' => 450
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ],
    CURLOPT_POSTFIELDS => $body,
    CURLOPT_TIMEOUT => 45
]);

$response = curl_exec($ch);
$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

curl_close($ch);

if ($response === false || $curlError !== '') {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'erro' => 'Erro ao conectar com a API: ' . $curlError
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode($response, true);
$respostaCrua = trim((string) ($data['choices'][0]['message']['content'] ?? ''));

if ($httpCode >= 400 || $respostaCrua === '') {
    $erroApi = $data['error']['message'] ?? 'Não foi possível gerar uma resposta agora.';
    http_response_code($httpCode >= 400 ? $httpCode : 500);
    echo json_encode([
        'ok' => false,
        'erro' => $erroApi
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$respostaEstruturada = extrairJsonDaResposta($respostaCrua);

if (!is_array($respostaEstruturada)) {
    $respostaEstruturada = [
        'resposta' => $respostaCrua,
        'acao' => 'nenhuma',
        'itens_carrinho' => []
    ];
}

$respostaTexto = trim((string) ($respostaEstruturada['resposta'] ?? ''));
$acao = trim((string) ($respostaEstruturada['acao'] ?? 'nenhuma'));
$itensSolicitados = is_array($respostaEstruturada['itens_carrinho'] ?? null) ? $respostaEstruturada['itens_carrinho'] : [];
$acoesCarrinho = [];
$itensNaoEncontrados = [];

if ($acao === 'adicionar_ao_carrinho') {
    foreach ($itensSolicitados as $itemSolicitado) {
        $nomeSolicitado = trim((string) ($itemSolicitado['nome'] ?? ''));
        $quantidade = (int) ($itemSolicitado['quantidade'] ?? 0);

        if ($nomeSolicitado === '' || $quantidade <= 0) {
            continue;
        }

        $itemEncontrado = encontrarItemDoCardapio($nomeSolicitado, $itens);

        if ($itemEncontrado) {
            $acoesCarrinho[] = [
                'item_id' => (int) ($itemEncontrado['id'] ?? 0),
                'nome' => (string) ($itemEncontrado['nome'] ?? ''),
                'quantidade' => $quantidade
            ];
        } else {
            $itensNaoEncontrados[] = $nomeSolicitado;
        }
    }

    if (empty($acoesCarrinho)) {
        $acao = 'nenhuma';

        if (!empty($itensNaoEncontrados) && $respostaTexto === '') {
            $respostaTexto = 'Não encontrei esse item no cardápio para adicionar ao carrinho.';
        }
    }
}

if ($respostaTexto === '') {
    $respostaTexto = 'Desculpe, não consegui responder agora.';
}

$historico[] = ['role' => 'assistant', 'content' => $respostaTexto];
$_SESSION['chat_cardapio_historico'][$cardapioId] = array_slice($historico, -8);

echo json_encode([
    'ok' => true,
    'resposta' => $respostaTexto,
    'acao' => $acao,
    'acoes_carrinho' => $acoesCarrinho,
    'itens_nao_encontrados' => $itensNaoEncontrados
], JSON_UNESCAPED_UNICODE);

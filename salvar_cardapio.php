<?php
session_start();
require 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$usuario_id = (int) $_SESSION['usuario_id'];

function redirecionarPainel(): void
{
    header("Location: painel.php");
    exit;
}

function flash(string $tipo, string $mensagem): void
{
    $_SESSION['flash_' . $tipo] = $mensagem;
}

function salvarUpload(array $arquivo, string $prefixo = 'img'): ?string
{
    if (empty($arquivo) || !isset($arquivo['error'])) {
        return null;
    }

    if ($arquivo['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    if (empty($arquivo['tmp_name']) || !is_uploaded_file($arquivo['tmp_name'])) {
        return null;
    }

    $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $extensao = strtolower(pathinfo($arquivo['name'] ?? '', PATHINFO_EXTENSION));

    if (!in_array($extensao, $extensoesPermitidas, true)) {
        return null;
    }

    $diretorio = __DIR__ . '/uploads';

    if (!is_dir($diretorio)) {
        if (!mkdir($diretorio, 0777, true) && !is_dir($diretorio)) {
            return null;
        }
    }

    $nomeArquivo = $prefixo . '_' . uniqid('', true) . '.' . $extensao;
    $destino = $diretorio . '/' . $nomeArquivo;

    if (!move_uploaded_file($arquivo['tmp_name'], $destino)) {
        return null;
    }

    return 'uploads/' . $nomeArquivo;
}

function removerArquivoLocal(?string $caminho): void
{
    if (!$caminho) {
        return;
    }

    if (strpos($caminho, 'uploads/') !== 0) {
        return;
    }

    $arquivo = __DIR__ . '/' . $caminho;

    if (is_file($arquivo)) {
        @unlink($arquivo);
    }
}

$acao = $_POST['acao'] ?? 'salvar_cardapio';

try {
    if ($acao === 'remover_fundo') {
        $cardapio_id = (int) ($_POST['cardapio_id'] ?? 0);

        $stmt = $db->prepare("
            SELECT imagem_fundo
            FROM cardapios
            WHERE id = ? AND usuario_id = ?
            LIMIT 1
        ");
        $stmt->execute([$cardapio_id, $usuario_id]);
        $cardapio = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cardapio) {
            flash('erro', 'Cardápio não encontrado.');
            redirecionarPainel();
        }

        removerArquivoLocal($cardapio['imagem_fundo'] ?? null);

        $stmt = $db->prepare("
            UPDATE cardapios
            SET imagem_fundo = NULL
            WHERE id = ? AND usuario_id = ?
        ");
        $stmt->execute([$cardapio_id, $usuario_id]);

        flash('sucesso', 'Imagem de fundo removida com sucesso.');
        redirecionarPainel();
    }

    if ($acao === 'atualizar_foto_item') {
        $item_id = (int) ($_POST['item_id'] ?? 0);

        $stmt = $db->prepare("
            SELECT i.id, i.imagem
            FROM itens i
            INNER JOIN cardapios c ON c.id = i.cardapio_id
            WHERE i.id = ? AND c.usuario_id = ?
            LIMIT 1
        ");
        $stmt->execute([$item_id, $usuario_id]);
        $itemAtual = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$itemAtual) {
            flash('erro', 'Item não encontrado.');
            redirecionarPainel();
        }

        if (!isset($_FILES['nova_item_imagem'])) {
            flash('erro', 'Nenhum arquivo foi enviado.');
            redirecionarPainel();
        }

        $novaImagem = salvarUpload($_FILES['nova_item_imagem'], 'item_cardapio');

        if ($novaImagem === null) {
            flash('erro', 'Não foi possível salvar a imagem. Use JPG, JPEG, PNG, WEBP ou GIF.');
            redirecionarPainel();
        }

        removerArquivoLocal($itemAtual['imagem'] ?? null);

        $stmt = $db->prepare("
            UPDATE itens
            SET imagem = ?
            WHERE id = ?
        ");
        $ok = $stmt->execute([$novaImagem, $item_id]);

        if (!$ok) {
            flash('erro', 'Falha ao atualizar a foto do item.');
            redirecionarPainel();
        }

        flash('sucesso', 'Foto do item salva com sucesso.');
        redirecionarPainel();
    }

    if ($acao === 'remover_foto_item') {
        $item_id = (int) ($_POST['item_id'] ?? 0);

        $stmt = $db->prepare("
            SELECT i.id, i.imagem
            FROM itens i
            INNER JOIN cardapios c ON c.id = i.cardapio_id
            WHERE i.id = ? AND c.usuario_id = ?
            LIMIT 1
        ");
        $stmt->execute([$item_id, $usuario_id]);
        $itemAtual = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$itemAtual) {
            flash('erro', 'Item não encontrado.');
            redirecionarPainel();
        }

        removerArquivoLocal($itemAtual['imagem'] ?? null);

        $stmt = $db->prepare("
            UPDATE itens
            SET imagem = NULL
            WHERE id = ?
        ");
        $stmt->execute([$item_id]);

        flash('sucesso', 'Foto removida com sucesso.');
        redirecionarPainel();
    }

    $cardapio_id   = (int) ($_POST['cardapio_id'] ?? 0);
    $nome_negocio  = trim($_POST['nome_negocio'] ?? '');
    $descricao     = trim($_POST['descricao'] ?? '');
    $cor_principal = trim($_POST['cor_principal'] ?? '#3b8edb');

    $item_nome      = trim($_POST['item_nome'] ?? '');
    $item_categoria = trim($_POST['item_categoria'] ?? '');
    $item_descricao = trim($_POST['item_descricao'] ?? '');
    $item_preco     = trim($_POST['item_preco'] ?? '');

    if ($cardapio_id > 0) {
        $stmt = $db->prepare("
            SELECT id, imagem_fundo
            FROM cardapios
            WHERE id = ? AND usuario_id = ?
            LIMIT 1
        ");
        $stmt->execute([$cardapio_id, $usuario_id]);
        $cardapioAtual = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cardapioAtual) {
            flash('erro', 'Cardápio não encontrado.');
            redirecionarPainel();
        }

        $novaImagemFundo = salvarUpload($_FILES['imagem_fundo'] ?? [], 'fundo_cardapio');

        if ($novaImagemFundo !== null) {
            removerArquivoLocal($cardapioAtual['imagem_fundo'] ?? null);

            $stmt = $db->prepare("
                UPDATE cardapios
                SET nome_negocio = ?, descricao = ?, cor_principal = ?, imagem_fundo = ?
                WHERE id = ? AND usuario_id = ?
            ");
            $stmt->execute([
                $nome_negocio,
                $descricao,
                $cor_principal,
                $novaImagemFundo,
                $cardapio_id,
                $usuario_id
            ]);
        } else {
            $stmt = $db->prepare("
                UPDATE cardapios
                SET nome_negocio = ?, descricao = ?, cor_principal = ?
                WHERE id = ? AND usuario_id = ?
            ");
            $stmt->execute([
                $nome_negocio,
                $descricao,
                $cor_principal,
                $cardapio_id,
                $usuario_id
            ]);
        }

        if ($item_nome !== '' && $item_preco !== '') {
            $imagemItem = salvarUpload($_FILES['item_imagem'] ?? [], 'item_cardapio_novo');
            $precoNumerico = (float) str_replace(',', '.', $item_preco);

            $stmt = $db->prepare("
                INSERT INTO itens (cardapio_id, nome, descricao, preco, categoria, imagem)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $cardapio_id,
                $item_nome,
                $item_descricao,
                $precoNumerico,
                $item_categoria,
                $imagemItem
            ]);
        }

        flash('sucesso', 'Alterações salvas com sucesso.');
    }

    redirecionarPainel();
} catch (Throwable $e) {
    flash('erro', 'Erro ao salvar: ' . $e->getMessage());
    redirecionarPainel();
}
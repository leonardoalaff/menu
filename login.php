<?php
session_start();
require 'db.php';

$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

$stmt = $db->prepare("SELECT * FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario && password_verify($senha, $usuario['senha'])) {
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nome'] = $usuario['nome'];
    header("Location: painel.php");
    exit;
}

header("Location: index.php");
exit;
<?php
session_start();
include 'db.php';

$email = $_POST['email'];
$senha = $_POST['senha'];

$stmt = $db->prepare("SELECT * FROM usuarios WHERE email=?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && password_verify($senha, $user['senha'])) {

    $_SESSION['user'] = $user['id'];

    // lembrar usuário (cookie simples)
    if (isset($_POST['lembrar'])) {
        setcookie("user", $user['id'], time() + (86400 * 30)); // 30 dias
    }

    header("Location: dashboard.php");

} else {
    header("Location: index.php?erro=1");
}
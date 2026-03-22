<?php
include 'db.php';

$id = $_GET['id'];

$stmt = $db->prepare("DELETE FROM cardapios WHERE id=?");
$stmt->execute([$id]);

header("Location: dashboard.php");
<?php
require 'db.php';
session_start();

$db = Database::connect();

$userTemp = $_SESSION["userTemp"];
$_POST = json_decode(file_get_contents('php://input'), true);

$id = $_POST['id'];
$action = $_POST['action'];

if ($action == 'decrease') {
    $query = "UPDATE panier SET qte = GREATEST(qte - 1, 1) WHERE id_item = :id AND userTemp = :userTemp";
} elseif ($action == 'increase') {
    $query = "UPDATE panier SET qte = qte + 1 WHERE id_item = :id AND userTemp = :userTemp";
}

$stmt = $db->prepare($query);
$stmt->bindValue(":id", $id, PDO::PARAM_INT);
$stmt->bindValue(":userTemp", $userTemp, PDO::PARAM_STR);
$stmt->execute();

// Fetch the updated quantity
$querySelect = "SELECT qte FROM panier WHERE id_item = :id AND userTemp = :userTemp";
$stmtSelect = $db->prepare($querySelect);
$stmtSelect->bindValue(":id", $id, PDO::PARAM_INT);
$stmtSelect->bindValue(":userTemp", $userTemp, PDO::PARAM_STR);
$stmtSelect->execute();
$result = $stmtSelect->fetch(PDO::FETCH_ASSOC);

echo json_encode(['qte' => $result['qte']]);
?>
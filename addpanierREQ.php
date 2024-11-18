<?php
require 'db.php';
session_start();

$db = Database::connect();

// Determine the user identifier
if (isset($_SESSION['user_id'])) {
    $userIdentifier = $_SESSION['user_id'];
} elseif (isset($_COOKIE['userTemp'])) {
    $userIdentifier = $_COOKIE['userTemp'];
} else {
    // If there's no user identifier, create a temporary one
    $userIdentifier = uniqid('temp_', true);
    setcookie('userTemp', $userIdentifier, time() + (86400 * 30), "/"); // 30 days expiry
}

// Validate and sanitize input
$itemId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$price = filter_input(INPUT_GET, 'prix', FILTER_VALIDATE_FLOAT);

if ($itemId === false || $price === false) {
    die("Invalid input");
}

// Check if the item is already in the cart
$query = "SELECT * FROM panier WHERE id_item = :id AND userTemp = :userTemp";
$stmt = $db->prepare($query);
$stmt->bindValue(':id', $itemId, PDO::PARAM_INT);
$stmt->bindValue(':userTemp', $userIdentifier, PDO::PARAM_STR);
$stmt->execute();

$item = $stmt->fetch(PDO::FETCH_ASSOC);

if ($item) {
    // Update quantity
    $newQte = $item['qte'] + 1;
    $query = "UPDATE panier SET qte = :qte WHERE id_item = :id AND userTemp = :userTemp";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':qte', $newQte, PDO::PARAM_INT);
    $stmt->bindValue(':id', $itemId, PDO::PARAM_INT);
    $stmt->bindValue(':userTemp', $userIdentifier, PDO::PARAM_STR);
    $stmt->execute();
} else {
    // Insert item in panier
    $query = "INSERT INTO panier (id_item, qte, prix, userTemp) VALUES (:id, :qte, :prix, :userTemp)";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':id', $itemId, PDO::PARAM_INT);
    $stmt->bindValue(':qte', 1, PDO::PARAM_INT);
    $stmt->bindValue(':prix', $price, PDO::PARAM_STR);
    $stmt->bindValue(':userTemp', $userIdentifier, PDO::PARAM_STR);
    $stmt->execute();
}

Database::disconnect();

header('Location: panier.php');
exit();
?>
<?php
require 'db.php';
session_start();

$db = Database::connect();
$commandeId = $_GET['id'];  // ID de la commande passé dans l'URL

// Récupérer les détails de la commande
$query = "SELECT * FROM commandes WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindValue(':id', $commandeId, PDO::PARAM_INT);
$stmt->execute();

$commande = $stmt->fetch(PDO::FETCH_ASSOC);

Database::disconnect();

// Si la commande n'existe pas, rediriger l'utilisateur
if (!$commande) {
    header('Location: suivi_commandes.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la commande</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Détails de la commande #<?= $commande['id'] ?></h1>

        <table class="table">
            <tr>
                <th>Total</th>
                <td><?= number_format($commande['total'], 2) ?> €</td>
            </tr>
            <tr>
                <th>Date de commande</th>
                <td><?= $commande['date_commande'] ?></td>
            </tr>
            <tr>
                <th>Statut</th>
                <td>
                    <form action="update_statut.php" method="POST">
                        <input type="hidden" name="commande_id" value="<?= $commande['id'] ?>">
                        <select name="statut" class="form-select" required>
                            <option value="en attente" <?= $commande['statut'] == 'en attente' ? 'selected' : '' ?>>En attente</option>
                            <option value="expédiée" <?= $commande['statut'] == 'expédiée' ? 'selected' : '' ?>>Expédiée</option>
                            <option value="livrée" <?= $commande['statut'] == 'livrée' ? 'selected' : '' ?>>Livrée</option>
                        </select>
                        <button type="submit" class="btn btn-primary mt-2">Mettre à jour le statut</button>
                    </form>
                </td>
            </tr>
        </table>

        <a href="suivi_commandes.php" class="btn btn-secondary">Retour au suivi des commandes</a>
    </div>
</body>
</html>
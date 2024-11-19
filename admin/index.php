<?php
// session_start();


require '../db.php';
require 'verifRole.php';

$db = DataBase::connect();




$query = 
"SELECT items.*, categories.name AS category_name FROM items
JOIN categories
ON items.category = categories.id";


$stmt = $db->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>




<!DOCTYPE html>
<html>
    <head>
        <title>Burger Code</title>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <link href="	https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="	https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
        <link href='http://fonts.googleapis.com/css?family=Holtwood+One+SC' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
        <link rel="stylesheet" href="../styles.css">
    </head>

    <body>
    <div style="text-align:center; display:flex; justify-content:center; align-items:center" class="text-logo">
                <h1>Burger Doe</h1>
                <div class="icon-container">
                    <div class="dropdown">
                        <a href="#" class="bi bi-person-circle user-icon" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false"></a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                           
                                <li><a class="dropdown-item" href="gestion_commande.php">Gestion de commande</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="deconnexion.php">Log out</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        <div class="container admin">
            <div class="row">
                <h1><strong>Liste des items   </strong><a href="insert.php" class="btn btn-success btn-lg"><span class="bi-plus"></span> Ajouter</a></h1>
                <table class="table table-striped table-bordered">
                  <thead>
                    <tr>
                      <th>Nom</th>
                      <th>Description</th>
                      <th>Prix</th>
                      <th>Catégorie</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($products as $product){ ?>
                    <tr>
                      <td><?= $product['name'] ?></td>
                      <td><?= $product['description'] ?></td>
                      <td><?= number_format($product['price'], 2, ',', ' ') ?></td>
                      <td><?= $product['category_name'] ?></td>
                      <td width=340>
                        <a class="btn btn-secondary" href="view.php"><span class="bi-eye"></span> Voir</a>
                        <a class="btn btn-primary" href="update.php?id=<?= $product['id'] ?>"><span class="bi-pencil"></span> Modifier</a>
                        <a class="btn btn-danger" href="delete.php?id=<?= $product['id'] ?>"><span class="bi-x"></span> Supprimer</a>                     
                      </td>
                    </tr>
                   <?php } ?>
                  </tbody>
                </table>
            </div>
        </div>
    </body>
</html>

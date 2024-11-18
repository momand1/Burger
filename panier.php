<?php
require 'db.php';
session_start();

$db = Database::connect();

// Determine the user identifier
$userIdentifier = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : (isset($_COOKIE['userTemp']) ? $_COOKIE['userTemp'] : null);

if (!$userIdentifier) {
    // If there's no user identifier, the cart is empty
    $prodCart = [];
} else {
    $query = "SELECT p.*, i.name, i.image FROM panier p
              JOIN items i ON p.id_item = i.id 
              WHERE p.userTemp = :userIdentifier";

    $stmt = $db->prepare($query);
    $stmt->execute([":userIdentifier" => $userIdentifier]);
    $prodCart = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$subtotalHT = array_sum(array_map(function($item) {
    return $item['prix'] * $item['qte'];
}, $prodCart));

$totalTTC = isset($_GET['newPrice']) ? $_GET['newPrice'] : $subtotalHT * 1.1;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Panier</h1>
        <?php if (empty($prodCart)): ?>
            <div class="alert alert-danger" role="alert">
                Votre panier est vide !
            </div>
        <?php else: ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Produit</th>
                        <th>Prix</th>
                        <th>Quantité</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($prodCart as $prod): ?>
                        <tr data-id="<?= $prod['id_item'] ?>">
                            <td><img src="images/<?= $prod['image'] ?>" alt="<?= $prod['name'] ?>" style="width:100px"></td>
                            <td><?= $prod['name'] ?></td>
                            <td class="price"><?= number_format($prod['prix'], 2) ?> €</td>
                            <td>
                                <div class="input-group">
                                    <button class="btn btn-outline-secondary decrease-qty" type="button">-</button>
                                    <input type="text" class="form-control text-center item-qty" value="<?= $prod['qte'] ?>" readonly>
                                    <button class="btn btn-outline-secondary increase-qty" type="button">+</button>
                                </div>
                            </td>
                            <td class="item-total"><?= number_format($prod['prix'] * $prod['qte'], 2) ?> €</td>
                            <td>
                                <a href="removeItem.php?id=<?= $prod['id_item'] ?>" class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Etes vous sur de vouloir supprimer ce produit ?')">
                                    <i class="bi bi-trash"></i> Supprimer
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="row">
                <div class="col-md-6">
                    <h3>Avez vous un coupon?</h3>
                    <form action="couponREQ.php" method="POST">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" name="code" placeholder="Entrer le code de la remise">
                            <input type="hidden" name="total" value="<?= $totalTTC ?>">
                            <button class="btn btn-primary" type="submit">Valider</button>
                        </div>
                    </form>
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger" role="alert">
                            Attention : le code remise saisi est incorrect !
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_GET['newPrice'])): ?>
                        <div class="alert alert-success" role="alert">
                            Vous avez ajouté un code de réduction !
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <h3>Total panier</h3>
                    <table class="table">
                        <tr>
                            <td>Total produit HT</td>
                            <td id="subtotal"><?= number_format($subtotalHT, 2) ?> €</td>
                        </tr>
                        <tr>
                            <td>TVA (10%)</td>
                            <td id="vat"><?= number_format($subtotalHT * 0.1, 2) ?> €</td>
                        </tr>
                        <?php if (isset($_SESSION['coupon'])): ?>
                            <tr>
                                <td>Remise (<?= htmlspecialchars($_SESSION['coupon']['code']) ?>)</td>
                                <td>-<span id="coupon-discount"><?= number_format($_SESSION['coupon']['montant'], 2) ?></span> €</td>
                            </tr>
                        <?php else: ?>
                            <span id="coupon-discount" style="display:none;">0</span>
                        <?php endif; ?>
                        <tr>
                            <td><strong>Total TTC</strong></td>
                            <td id="total"><strong><?= number_format($totalTTC, 2) ?> €</strong></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Continuer vos achats</a>
                <a href="#" class="btn btn-primary">Procéder au paiement</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
    $(document).ready(function() {
        function updateQuantity(itemId, action) {
            $.ajax({
                url: 'updateQTE.php',
                method: 'POST',
                data: JSON.stringify({ id: itemId, action: action }),
                contentType: 'application/json',
                success: function(response) {
                    var data = JSON.parse(response);
                    var row = $('tr[data-id="' + itemId + '"]');
                    row.find('.item-qty').val(data.qte);
                    updateItemTotal(row);
                    updateCartTotal();
                },
                error: function(xhr, status, error) {
                    console.error("Error updating quantity:", error);
                }
            });
        }

        function updateItemTotal(row) {
            var price = parseFloat(row.find('.price').text());
            var quantity = parseInt(row.find('.item-qty').val());
            var total = price * quantity;
            row.find('.item-total').text(total.toFixed(2) + ' €');
        }

        function updateCartTotal() {
            var subtotal = 0;
            $('.item-total').each(function() {
                subtotal += parseFloat($(this).text());
            });
            var vat = subtotal * 0.1;
            var total = subtotal + vat;

            // Check if a coupon is applied
            var couponDiscount = parseFloat($('#coupon-discount').text()) || 0;
            total -= couponDiscount;

            $('#subtotal').text(subtotal.toFixed(2) + ' €');
            $('#vat').text(vat.toFixed(2) + ' €');
            $('#total').text(total.toFixed(2) + ' €');

            // Update the hidden input for the coupon form
            $('input[name="total"]').val(total.toFixed(2));
        }

        $('.increase-qty').click(function() {
            var itemId = $(this).closest('tr').data('id');
            updateQuantity(itemId, 'increase');
        });

        $('.decrease-qty').click(function() {
            var itemId = $(this).closest('tr').data('id');
            updateQuantity(itemId, 'decrease');
        });
    });
    </script>
</body>
</html>
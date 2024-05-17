<?php
include '../conf/config.php';
session_start();
 
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header('location:product.php');
    exit;
}
 
if ($conn === null) {
    die('Database connection error');
}
 
if(isset($_POST['update_cart'])){
    $update_quantity = mysqli_real_escape_string($conn, $_POST['cart_quantity']);
    $update_id = mysqli_real_escape_string($conn, $_POST['cart_id']);
    mysqli_query($conn, "UPDATE `cart` SET quantity = '$update_quantity' WHERE id = '$update_id'");
    $_SESSION['message'] = 'Cart quantity updated successfully!'; // Store messages in session
}
 
if (isset($_GET['remove'])) {
    $remove_id = mysqli_real_escape_string($conn, $_GET['remove']);
    mysqli_query($conn, "DELETE FROM `cart` WHERE id = '$remove_id'");
    header('Location: cart.php');
    exit;
}
 
if (isset($_GET['delete_all'])) {
    mysqli_query($conn, "DELETE FROM `cart` WHERE user_id = '$user_id'");
    header('Location: cart.php');
    exit;
}
?>
 
<!DOCTYPE html>
<html lang="en">
 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="../style/css/style.css">
</head>
 
<body>
    <main class="flex-center h-80">
        <div class="shopping-cart p-4">
            <h1 class="title">Shopping Cart</h1>
            <table>
                <thead>
                    <th class="title">Foto</th>
                    <th class="title">Naam</th>
                    <th class="title">Prijs</th>
                    <th class="title">Hoeveelheid</th>
                    <th class="title">Totale prijs</th>
                    <th class="title">Actie</th>
                </thead>
                <tbody>
                    <?php
                    $cart_query = mysqli_query($conn, "SELECT cart.*, products.image_data FROM `cart` JOIN `products` ON cart.product_id = products.id WHERE cart.user_id = '$user_id'") or die('query failed');
                    $grand_total = 0;
                    if(mysqli_num_rows($cart_query) > 0){
                        while($fetch_cart = mysqli_fetch_assoc($cart_query)){
                    ?>
                    <tr>
                        <td><img height="100px" src="data:image/png;base64,<?php echo $fetch_cart['image_data']; ?>" alt="<?php echo $fetch_cart['product_name']; ?>"></td>
                        <td class="text-center text"><?php echo $fetch_cart['product_name']; ?></td>
                        <td class="text text-center">$<?php echo $fetch_cart['price']; ?>/-</td>
                        <td>
                            <form action="" method="post" class="flex-column">
                                <input type="hidden" name="cart_id" value="<?php echo $fetch_cart['id']; ?>">
                                <input type="number" min="1" name="cart_quantity" value="<?php echo $fetch_cart['quantity']; ?>">
                                <input type="submit" name="update_cart" value="Update" class="button-small">
                            </form>
                        </td>
                        <td class="text text-center">$<?php echo $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']); ?>/-</td>
                        <td class="text-center"><a href="cart.php?remove=<?php echo $fetch_cart['id']; ?>" class="button-small" onclick="return confirm('Remove item from cart?');">Remove</a></td>
                    </tr>
                    <?php
                        $grand_total += $sub_total;
                        }
                    } else {
                        echo '<tr><td style="padding:20px; text-transform:capitalize;" colspan="6">No item added</td></tr>';
                    }
                    ?>
                    <tr class="table-bottom">
                        <td class="text" colspan="4">Totale prijs:</td>
                        <td class="text-center text">$<?php echo $grand_total; ?>/-</td>
                        <td><a href="cart.php?delete_all" onclick="return confirm('Delete all from cart?');" class="button-small <?php echo ($grand_total > 1)?'':'disabled'; ?>">Delete All</a></td>
                    </tr>
                </tbody>
            </table>
            <div class="cart-btn">
                <a href="checkout.php" class="button <?php echo ($grand_total > 1)?'':'disabled'; ?>">Bestellen</a>
                <a class="text b-bottom" href="product.php">Terug naar Producten</a>
            </div>
        </div>
    </main>
</body>
</html>
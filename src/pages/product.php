<?php
include '../conf/config.php';
session_start();
 
$user_id = $_SESSION['user_id'] ?? null;
 
if ($conn === null) {
    die('Database connection error');
}
 
if(isset($_POST['add_to_cart'])){
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $product_info_query = mysqli_query($conn, "SELECT name, price FROM products WHERE id = '$product_id'");
 
    if(mysqli_num_rows($product_info_query) > 0){
        $product_info = mysqli_fetch_assoc($product_info_query);
        
        $check_cart = mysqli_query($conn, "SELECT * FROM cart WHERE user_id = '$user_id' AND product_id = '$product_id'");
        
        if(mysqli_num_rows($check_cart) > 0){
            $cart_item = mysqli_fetch_assoc($check_cart);
            $new_quantity = $cart_item['quantity'] + 1;
            $update_cart = mysqli_query($conn, "UPDATE cart SET quantity = '$new_quantity' WHERE user_id = '$user_id' AND product_id = '$product_id'");
        } else {
            $insert_query = mysqli_query($conn, "INSERT INTO cart (user_id, product_id, product_name, quantity, price) VALUES ('$user_id', '$product_id', '{$product_info['name']}', 1, '{$product_info['price']}')");
        }
    } else {
        echo "Product not found.";
    }
}

$user_id = $_SESSION['user_id'] ?? null;

if ($user_id) {
    $user_query = mysqli_query($conn, "SELECT name, email FROM users WHERE id = '$user_id'");
    if ($user_query && mysqli_num_rows($user_query) > 0) {
        $user = mysqli_fetch_assoc($user_query);
        $_SESSION['email'] = $user['email'];
        $_SESSION['name'] = $user['name'];
    } else {
        $_SESSION['email'] = null;
        $_SESSION['name'] = null;
    }
}

if (isset($_SESSION['email'])) {
    if ($_SESSION['email'] == "admin@gmail.com") {
        $dashboardLink = '<li><a class="title" href="admin.php">Admin</a></li>';
    } else {
        $name = $_SESSION['name'];
        $dashboardLink = "<li><a class='title b-bottom' href='user.php'>Hallo, $name</a></li>";
    }
} else {
    $dashboardLink = '<li><a class="button" href="login.php">Login</a></li>';
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="../style/css/style.css">
</head>
<body>
    <nav class="b-bottom BG-300">
        <div class="wrapper con-nav flex-row-between">
            <h1 class="logo">WheyTooFun</h1>
            <ul class="links flex-row-between gap">
                <li><a class="title" href="../index.php">Home</a></li>
                <li><?php echo $dashboardLink; ?></li>
                <li><a href="cart.php"><img src="../img/online-shopping.png" width="30"></a></li>
            </ul>
        </div>
    </nav>
    <div class="wrapper flex-row-between gap">                     
        <?php
            $select_product = mysqli_query($conn, "SELECT * FROM `products`") or die('query failed');
            if (mysqli_num_rows($select_product) > 0) {
                while ($fetch_product = mysqli_fetch_assoc($select_product)) {
                    ?>
                    <div class="card">
                        <div class="card-img" style="background-image: url('data:image/png;base64,<?php echo $fetch_product['image_data']; ?>')">
                            <span class="highlight">nieuw</span>
                        </div>
                        <form method="post" class="card-txt" action="">
                            <p class="card-title"><?php echo $fetch_product['name']; ?></p>
                            <p class="card-price"><?php echo $fetch_product['price']; ?></p>
                            <input type="number" min="1" name="product_quantity" value="1">
                            <input type="hidden" name="product_id" value="<?php echo $fetch_product['id']; ?>">
                            <input type="submit" value="In winkelwagen" name="add_to_cart" class="button">
                        </form>
                    </div>
                    <?php
                }
            }
        ?>
    </div>
</body>
</html>
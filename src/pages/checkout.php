<?php
include '../conf/config.php';
session_start();


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];


if ($conn === null) {
    die('Database connection error');
}


$cart_total_query = "SELECT SUM(price * quantity) AS total FROM cart WHERE user_id = ?";
$total_stmt = mysqli_prepare($conn, $cart_total_query);
mysqli_stmt_bind_param($total_stmt, 'i', $user_id);
mysqli_stmt_execute($total_stmt);
$total_result = mysqli_stmt_get_result($total_stmt);
$total_row = mysqli_fetch_assoc($total_result);
$total = $total_row ? $total_row['total'] : 0;

$_SESSION['total'] = $total;  


$user_email_query = "SELECT email FROM users WHERE id = ?";
$user_email_stmt = mysqli_prepare($conn, $user_email_query);
mysqli_stmt_bind_param($user_email_stmt, 'i', $user_id);
mysqli_stmt_execute($user_email_stmt);
$user_email_result = mysqli_stmt_get_result($user_email_stmt);
$user_email_row = mysqli_fetch_assoc($user_email_result);
$user_email = $user_email_row['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send'])) {
    $land = mysqli_real_escape_string($conn, $_POST['land']);
    $naam = mysqli_real_escape_string($conn, $_POST['naam']);
    $nummer = mysqli_real_escape_string($conn, $_POST['nummer']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $hnummer = mysqli_real_escape_string($conn, $_POST['hnummer']);
    $postcode = mysqli_real_escape_string($conn, $_POST['postcode']);
    $stad = mysqli_real_escape_string($conn, $_POST['stad']);

    $full_address = "$address, $hnummer, $postcode, $stad, $land";

    $order_query = "INSERT INTO `orders` (user_id, total, address, order_date) VALUES (?, ?, ?, 'Processing' NOW())";
    $order_stmt = mysqli_prepare($conn, $order_query);
    mysqli_stmt_bind_param($order_stmt, 'ids', $user_id, $_SESSION['total'], $full_address);
    mysqli_stmt_execute($order_stmt) or die('Order query failed');
    $order_id = mysqli_insert_id($conn);

    $cart_items_query = "SELECT product_id, quantity FROM `cart` WHERE user_id = ?";
    $cart_stmt = mysqli_prepare($conn, $cart_items_query);
    mysqli_stmt_bind_param($cart_stmt, 'i', $user_id);
    mysqli_stmt_execute($cart_stmt);
    $cart_items_result = mysqli_stmt_get_result($cart_stmt);

    $order_items_html = '';
    while ($cart_item = mysqli_fetch_assoc($cart_items_result)) {
        $product_id = $cart_item['product_id'];
        $quantity = $cart_item['quantity'];

        $product_query = "SELECT name, price FROM products WHERE id = ?";
        $product_stmt = mysqli_prepare($conn, $product_query);
        mysqli_stmt_bind_param($product_stmt, 'i', $product_id);
        mysqli_stmt_execute($product_stmt);
        $product_result = mysqli_stmt_get_result($product_stmt);
        $product = mysqli_fetch_assoc($product_result);

        $order_item_query = "INSERT INTO `order_items` (order_id, product_id, quantity) VALUES (?, ?, ?)";
        $order_item_stmt = mysqli_prepare($conn, $order_item_query);
        mysqli_stmt_bind_param($order_item_stmt, 'iii', $order_id, $product_id, $quantity);
        mysqli_stmt_execute($order_item_stmt) or die('Order item query failed');

        $order_items_html .= "<p><strong>{$product['name']}</strong> ({$quantity} x €" . number_format($product['price'], 2) . ")</p>";
    }

    $clear_cart_query = "DELETE FROM `cart` WHERE user_id = ?";
    $clear_cart_stmt = mysqli_prepare($conn, $clear_cart_query);
    mysqli_stmt_bind_param($clear_cart_stmt, 'i', $user_id);
    mysqli_stmt_execute($clear_cart_stmt);

    $_SESSION['message'] = 'Order placed successfully!';

    
    $mailSubject = "Order from $naam";
    $mailBody = "
    <html>
    <head>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                color: #333;
                margin: 0;
                padding: 0;
            }
            .email-container {
                background-color: #ffffff;
                padding: 20px;
                margin: 20px auto;
                max-width: 600px;
                border: 1px solid #ddd;
                border-radius: 5px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }
            .email-header {
                background-color: #007bff;
                color: #ffffff;
                padding: 10px;
                text-align: center;
                border-radius: 5px 5px 0 0;
            }
            .email-content {
                padding: 20px;
            }
            .email-content h1 {
                font-size: 24px;
                margin-top: 0;
            }
            .email-content p {
                font-size: 16px;
                line-height: 1.5;
            }
            .email-footer {
                text-align: center;
                padding: 10px;
                font-size: 12px;
                color: #999;
            }
        </style>
    </head>
    <body>
        <div class='email-container'>
            <div class='email-header'>
                <h1>Order Confirmation</h1>
            </div>
            <div class='email-content'>
                <h1>Thank you for your order, $naam!</h1>
                <p>We have received your order and it is now being processed. Here are the details:</p>
                <p><strong>Name:</strong> $naam</p>
                <p><strong>Address:</strong> $full_address</p>
                <p><strong>Items:</strong></p>
                $order_items_html
                <p><strong>Total Amount:</strong> €" . number_format($_SESSION['total'], 2) . "</p>
                <p>We will notify you once your order has been shipped.</p>
            </div>
            <div class='email-footer'>
                &copy; 2024 WheyTooFun. All rights reserved.
            </div>
        </div>
    </body>
    </html>
    ";

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: no-reply@yourcompany.com" . "\r\n";

    mail($user_email, $mailSubject, $mailBody, $headers);

    header('Location: product.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="../style/css/style.css">
</head>

<body>
    <main class="flex-center flex-column h-80 m-2">
        <h1 class="title b-bottom">bestelling afrekenen</h1>
        <form class="m-2 p-2 border w-40" action="checkout.php" method="post">
            <div  class="p-1">
                <p class="text">Land</p>
                <input class="input-fld width-full" type="text" id="land" name="land" placeholder="Land" required>
            </div>

            <div  class="p-1">
                <p class="text">Naam</p>
                <input class="input-fld width-full" type="text" id="naam" name="naam" placeholder="Naam" required>
            </div>

            <div  class="p-1">
                <p class="text">Nummer</p>
                <input class="input-fld width-full" type="text" id="nummer" name="nummer" oninput="validatePhoneNumber(this)" pattern="[0-9]{10}"placeholder="0612345678" required>
            </div class="p-1">
                
            <div class="flex-column p-1">
                <p class="text">bezorgaddress</p>
                <input class="p-1 m-1" type="text" id="address" name="address" placeholder="Straatnaam" required>
                <input class="p-1 m-1" type="text" id="hnummer" name="hnummer" placeholder="Huisnummer" required>
                <input class="p-1 m-1" type="text" id="postcode" name="postcode" placeholder="Postcode" required>
                <input class="p-1 m-1" type="text" id="stad" name="stad" placeholder="Stad" required>
            </div>

            <div class="p-1">
                <p class="text">Totaal</p>
                <input class="input-fld" type="text" id="total" name="total" value="<?php echo $_SESSION['total']; ?>" readonly>
            </div>

            <div class="p-1 flex-center">
                <input class="button" type="submit" name="send" value="Place Order">
                <a class="text b-bottom m-2" href="product.php">Ga terug naar producten</a>
            </div>
        </form>
    </main>
    <script>
        function validatePhoneNumber(input) {
            var phoneNumber = input.value.replace(/\D/g, '');
            input.value = phoneNumber.slice(0, 10);
        }
    </script>
</body>

</html>
<?php
include '../conf/config.php';
session_start();
 
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
 
if ($conn === null) {
    die('Database connection error');
}
 
if ($user_id !== null) {
    $query = "SELECT email FROM users WHERE id = $user_id";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $user_email = $row['email'];

        if ($user_email === 'admin@gmail.com') {
            if (isset($_POST['send_newsletter']) && isset($_POST['subject']) && isset($_POST['message'])) {
                if (!isset($_SESSION['newsletter_sent'])) {
                    $subject = $_POST['subject'];
                    $message = nl2br($_POST['message']);

                    $users_query = mysqli_query($conn, "SELECT email FROM users");
                    while ($user = mysqli_fetch_assoc($users_query)) {
                        $toEmail = $user['email'];
                        $headers = "MIME-Version: 1.0" . "\r\n";
                        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                        $headers .= "From: no-reply@yourcompany.com" . "\r\n";
                        $emailContent = "
                            <html>
                            <head>
                                <style>
                                    body {
                                        font-family: Arial, sans-serif;
                                        background-color: #f4f4f4;
                                        margin: 0;
                                        padding: 0;
                                    }
                                    .email-container {
                                        max-width: 600px;
                                        margin: 20px auto;
                                        background: #fff;
                                        padding: 20px;
                                        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                                    }
                                    .header {
                                        background-color: #007bff;
                                        color: white;
                                        padding: 10px;
                                        text-align: center;
                                        font-size: 24px;
                                    }
                                    .content {
                                        padding: 20px;
                                        line-height: 1.5;
                                    }
                                    .footer {
                                        text-align: center;
                                        padding: 10px;
                                        font-size: 12px;
                                        color: #666;
                                    }
                                </style>
                            </head>
                            <body>
                                <div class='email-container'>
                                    <div class='header'>Newsletter</div>
                                    <div class='content'>
                                        <p>{$message}</p>
                                    </div>
                                    <div class='footer'>
                                        &copy; " . date("Y") . " WheyTooFun. All rights reserved.
                                    </div>
                                </div>
                            </body>
                            </html>
                        ";
                        mail($toEmail, $subject, $emailContent, $headers);
                    }

                    $_SESSION['newsletter_sent'] = true;
                    echo "Newsletter sent successfully!";
                } else {
                    echo "Newsletter already sent!";
                }
            }
    
            if ($user_email === 'admin@gmail.com') {
 
                if (isset($_GET['logout']) || isset($_POST['logout'])) {
                    session_destroy();
                    header('location:login.php');
                    exit;
                }
 
                if (isset($_POST['upload']) && isset($_FILES['image']) && isset($_POST['name']) && isset($_POST['price'])) {
                    $productName = $_POST['name'];
                    $productPrice = $_POST['price'];
 
                    if ($_FILES['image']['error'] == 0) {
                        $imageFile = $_FILES['image']['tmp_name'];
                        $imageType = $_FILES['image']['type'];
                        $imageSize = $_FILES['image']['size'];
 
                        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                        if (!in_array($imageType, $allowedTypes)) {
                            echo "File must be an image (JPEG, JPG, or PNG).";
                        } else if ($imageSize > 20000000) {
                            echo "File is too large.";
                        } else {
                            $imageData = file_get_contents($imageFile);
                            $imageBase64 = base64_encode($imageData);
 
                            $sql = "INSERT INTO products (name, price, image_data) VALUES (?, ?, ?)";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param('sds', $productName, $productPrice, $imageBase64);
                            $stmt->execute();
 
                            echo "Product uploaded successfully!";
                        }
                    } else {
                        echo "Error in file upload.";
                    }
                }
 
                if (isset($_POST['update']) && isset($_POST['product_id'])) {
                    $product_id = $_POST['product_id'];
                    $product_name = $_POST['name'];
                    $product_price = $_POST['price'];
                    $product_image = $_FILES['image'];
 
                    $product_query = mysqli_query($conn, "SELECT * FROM products WHERE id = '$product_id'");
                    $product = mysqli_fetch_assoc($product_query);
 
                    if ($product_name === '') {
                        $product_name = $product['name'];
                    }
 
                    if ($product_price === '') {
                        $product_price = $product['price'];
                    }
 
                    if ($product_image['error'] == 0) {
                        $imageFile = $product_image['tmp_name'];
                        $imageType = $product_image['type'];
                        $imageSize = $product_image['size'];
 
                        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                        if (!in_array($imageType, $allowedTypes)) {
                            echo "File must be an image (JPEG, JPG, or PNG).";
                        } else if ($imageSize > 20000000) {
                            echo "File is too large.";
                        } else {
                            $imageData = file_get_contents($imageFile);
                            $imageBase64 = base64_encode($imageData);
 
                            $sql = "UPDATE products SET name = ?, price = ?, image_data = ? WHERE id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param('sdsi', $product_name, $product_price, $imageBase64, $product_id);
                            $stmt->execute();
 
                            echo "Product updated successfully!";
                        }
                    } else {
                        $sql = "UPDATE products SET name = ?, price = ? WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param('sdi', $product_name, $product_price, $product_id);
                        $stmt->execute();
 
                        echo "Product updated successfully!";
                    }
                }

                if (isset($_POST['update_status']) && isset($_POST['order_id']) && isset($_POST['status'])) {
                    $order_id = $_POST['order_id'];
                    $status = mysqli_real_escape_string($conn, $_POST['status']);
                    $update_status_query = "UPDATE `orders` SET status = ? WHERE id = ?";
                    $update_status_stmt = mysqli_prepare($conn, $update_status_query);
                    mysqli_stmt_bind_param($update_status_stmt, 'si', $status, $order_id);
                    mysqli_stmt_execute($update_status_stmt);
                    echo "Order status updated successfully!";

                    if ($status === 'Shipped') {
                        $order_query = "SELECT users.email, users.name, orders.id, orders.total 
                                        FROM orders 
                                        JOIN users ON orders.user_id = users.id 
                                        WHERE orders.id = ?";
                        $order_stmt = mysqli_prepare($conn, $order_query);
                        mysqli_stmt_bind_param($order_stmt, 'i', $order_id);
                        mysqli_stmt_execute($order_stmt);
                        $order_result = mysqli_stmt_get_result($order_stmt);
                        $order_info = mysqli_fetch_assoc($order_result);
                        
                        $toEmail = $order_info['email'];
                        $userName = $order_info['name'];
                        $orderId = $order_info['id'];
                        $orderTotal = $order_info['total'];
                        
                        $subject = "Your Order #$orderId has been Shipped";
                        $message = "
                            <html>
                            <head>
                                <style>
                                    body {
                                        font-family: Arial, sans-serif;
                                        background-color: #f4f4f4;
                                        margin: 0;
                                        padding: 0;
                                    }
                                    .email-container {
                                        max-width: 600px;
                                        margin: 20px auto;
                                        background: #fff;
                                        padding: 20px;
                                        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                                    }
                                    .header {
                                        background-color: #007bff;
                                        color: white;
                                        padding: 10px;
                                        text-align: center;
                                        font-size: 24px;
                                    }
                                    .content {
                                        padding: 20px;
                                        line-height: 1.5;
                                    }
                                    .footer {
                                        text-align: center;
                                        padding: 10px;
                                        font-size: 12px;
                                        color: #666;
                                    }
                                </style>
                            </head>
                            <body>
                                <div class='email-container'>
                                    <div class='header'>Order Shipped</div>
                                    <div class='content'>
                                        <p>Dear $userName,</p>
                                        <p>Your order #$orderId with a total of €" . number_format($orderTotal, 2) . " has been shipped. You will receive it soon.</p>
                                        <p>Thank you for shopping with us!</p>
                                    </div>
                                    <div class='footer'>
                                        &copy; " . date("Y") . " WheyTooFun. All rights reserved.
                                    </div>
                                </div>
                            </body>
                            </html>
                        ";

                        $headers = "MIME-Version: 1.0" . "\r\n";
                        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                        $headers .= "From: no-reply@yourcompany.com" . "\r\n";

                        mail($toEmail, $subject, $message, $headers);
                    }
                }
 
                if (isset($_POST['delete']) && isset($_POST['product_id'])) {
                    $product_id = $_POST['product_id'];
                    $sql = "DELETE FROM products WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('i', $product_id);
                    $stmt->execute();
 
                    echo "Product deleted successfully!";
                }
 
                $products_query = mysqli_query($conn, "SELECT * FROM products");
                $orders_query = mysqli_query($conn, "
                    SELECT orders.id, orders.total, orders.address, orders.status, users.email 
                    FROM orders 
                    JOIN users ON orders.user_id = users.id
                ");
            }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="../style/css/style.css">
</head>
<body>
    <nav class="b-bottom BG-300">
        <div class="wrapper con-nav flex-row-between">
            <h1 class="logo">WheyTooFun</h1>
            <ul class="links flex-row-between gap">
                <li><a class="title" href="../index.php">Home</a></li>
                <li><a class="title" href="product.php">Producten</a></li>
                <li>
                    <form method="post">
                        <button class="button" type="submit" name="logout">Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </nav>
    <main class="flex-center flex-column h-80">
        <div class="newsletter m-2">
            <h1 class="title">Maak een nieuwe nieuwsbrief</h1>
            <form method="POST" class="flex-column gap">
                <input type="text" name="subject" placeholder="Titel" required>
                <textarea name="message" placeholder="Bericht" required></textarea>
                <button class="button" type="submit" name="send_newsletter">Verstuur Nieuwbrief</button>
            </form>
        </div>
        <div class="upload p-2 border m-2">
            <h1 class="title">Product Upload</h1>
            <form method="POST" enctype="multipart/form-data">
                <input type="text" class="input-fld" name="name" placeholder="Product Name" required>
                <input type="number" name="price" class="input-fld" placeholder="Product Price" required step="0.01">
                <input class="button" type="file" name="image" required>
                <button class="button" type="submit" name="upload">Upload Product</button>
            </form>
        </div>
        <div class="edit">
            <h1 class="title">Bewerk Items</h1>
            <table>
                <tr class="title">
                    <th>ID</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Image</th>
                    <th>Action</th>
                </tr>
                <?php
                    while ($product = mysqli_fetch_assoc($products_query)) {
                ?>
                <tr class="text">
                    <td><?php echo $product['id']; ?></td>
                    <td><?php echo $product['name']; ?></td>
                    <td><?php echo $product['price']; ?></td>
                    <td><img src="data:image/jpeg;base64,<?php echo $product['image_data']; ?>" style="max-width: 100px;"></td>
                    <td>
                        <form method="POST" enctype="multipart/form-data" style="display:inline;">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <input class="input-fld" type="text" name="name" placeholder="New Name">
                            <input class="input-fld" type="number" name="price" placeholder="New Price" step="0.01">
                            <input class="button" type="file" name="image">
                            <button class="button" type="submit" name="update">Update</button>
                        </form>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button class="button" type="submit" name="delete">Delete</button>
                        </form>
                    </td>
            </tr>
            <?php
        }
        ?>
            </table>
            <div class="m-2">
                <h1 class="title">Manage Orders</h1>
                    <table>
                        <tr class="title">
                            <th>Order ID</th>
                            <th>Email</th>
                            <th>Total</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        <?php
                        while ($order = mysqli_fetch_assoc($orders_query)) {
                            ?>
                            <tr class="text">
                                <td><?php echo $order['id']; ?></td>
                                <td><?php echo $order['email']; ?></td>
                                <td><?php echo $order['total']; ?></td>
                                <td><?php echo $order['address']; ?></td>
                                <td><?php echo $order['status']; ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="status">
                                            <option value="Processing" <?php if ($order['status'] == 'Processing') echo 'selected'; ?>>Processing</option>
                                            <option value="Shipped" <?php if ($order['status'] == 'Shipped') echo 'selected'; ?>>Shipped</option>
                                            <option value="Delivered" <?php if ($order['status'] == 'Delivered') echo 'selected'; ?>>Delivered</option>
                                            <option value="Cancelled" <?php if ($order['status'] == 'Cancelled') echo 'selected'; ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" name="update_status">Update Status</button>
                                    </form>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </table>
            </div>
        </div>
    </main>
</body>
</html>
 
<?php
        } else {
            header('Location: user.php');
            exit;
        }
    } else {
        header('Location: login.php');
        exit;
    }
} else {
    header('Location: login.php');
    exit;
}
?>
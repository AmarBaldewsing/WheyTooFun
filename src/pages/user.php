<?php

include '../conf/config.php';
session_start();
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header('location:login.php');
    exit;
}

if (isset($_GET['logout']) || isset($_POST['logout'])) {
    session_destroy();
    header('location:product.php');
    exit;
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

if (isset($_POST['update'])) {
    $user_query = mysqli_query($conn, "SELECT * FROM `users` WHERE id = '$user_id'");
    $user = mysqli_fetch_assoc($user_query);
 
    $name = !empty($_POST['name']) ? mysqli_real_escape_string($conn, $_POST['name']) : $user['name'];
    $email = !empty($_POST['email']) ? mysqli_real_escape_string($conn, $_POST['email']) : $user['email'];
    $password = !empty($_POST['password']) ? mysqli_real_escape_string($conn, md5($_POST['password'])) : $user['password'];
 
    $update_query = "UPDATE `users` SET name = '$name', email = '$email', password = '$password' WHERE id = '$user_id'";
 
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $imageFile = $_FILES['profile_image']['tmp_name'];
        $imageType = $_FILES['profile_image']['type'];
        $imageSize = $_FILES['profile_image']['size'];
 
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!in_array($imageType, $allowedTypes)) {
            echo "File must be an image (JPEG, JPG, or PNG).";
            exit;
        } else if ($imageSize > 20000000) {
            echo "File is too large.";
            exit;
        } else {
            $imageData = file_get_contents($imageFile);
            $imageBase64 = base64_encode($imageData);
            $update_query = "UPDATE `users` SET name = '$name', email = '$email', password = '$password', profile_image = '$imageBase64' WHERE id = '$user_id'";
        }
    }
 
    mysqli_query($conn, $update_query) or die('Update query failed');
 
    // Werk de sessievariabelen bij
    $_SESSION['name'] = $name;
    $_SESSION['email'] = $email;
    $_SESSION['password'] = $password;
 
    // Redirect naar de gebruikerspagina
    header('location:user.php');
    exit;
}

if(isset($_POST['update_newsletter'])){
    $newsletter = $_POST['nieuwsbrief'] ?? 1;
    $update_query = "UPDATE `users` SET nieuwsbrief = '$newsletter' WHERE id = '$user_id'";
    mysqli_query($conn, $update_query) or die('Update query failed');
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
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
    <main class="h-80">
        <div class="wrapper">
            <?php
                $select_user = mysqli_query($conn, "SELECT * FROM `users` WHERE id = '$user_id'") or die('query failed');
                if(mysqli_num_rows($select_user) > 0){
                    $fetch_user = mysqli_fetch_assoc($select_user);
                };
            ?>
            <div  id="profile" class="radius border flex-center width inline p-2">
                <?php if (!empty($fetch_user['profile_image'])): ?>
                    <img src="data:image/jpeg;base64,<?php echo $fetch_user['profile_image']; ?>" alt="Profile Image" style="width: 100px; height: 100px; object-fit: cover;">
                <?php else: ?>
                    <img src="../img/user.png" width="75">
                <?php endif; ?>
                <p class="title p-2" > Gebruikersnaam: <span class="text"><?php echo $fetch_user['name']; ?></span> </p>
                <p class="title p-2" > Email: <span class="text"><?php echo $fetch_user['email']; ?></span> </p>
            </div>
            <form action="" method="post" class="flex-column-center m-2 gap">
                <p class="text">Wilt u de nieuwsbrief ontvangen?</p>
                <select name="nieuwsbrief" id="nieuwsbrief">
                    <option value="1" <?php echo ($fetch_user['nieuwsbrief'] == 1) ? 'selected' : ''; ?>>Ja</option>
                    <option value="2" <?php echo ($fetch_user['nieuwsbrief'] == 2) ? 'selected' : ''; ?>>Nee</option>
                </select>
                <button class="button" type="submit" name="update_newsletter">Update</button>
            </form>
            <form action="" method="post" enctype="multipart/form-data" class="m-2 p-2 flex-column-center gap">
                <h1 class="title">Verander gegevens</h1>

                <div>
                    <p class="text">Naam:</p>
                    <input class="input-fld" type="text" name="name" placeholder="Username" value="<?php echo $fetch_user['name']; ?>">
                </div>

                <div>
                    <p class="text">Email:</p>
                    <input class="input-fld" type="email" name="email" placeholder="Email" value="<?php echo $fetch_user['email']; ?>">
                </div>

                <div>
                    <p class="text">Wachtwoord:</p>
                    <input class="input-fld" type="password" name="password" placeholder="Password">
                </div>

                <div>
                    <p class="text">Profiel Foto:</p>
                    <input class="input-fld" type="file" name="profile_image" accept="image/*">
                </div>

                <button class="button" type="submit" name="update">Update</button>
            </form>
            <div class="flex-column-center">
                <h1 class="title b-bottom p-1 m-2">Jouw bestellingen</h1>
                <div class="flex-column gap">
                    <?php
                        $select_orders = mysqli_query($conn, "SELECT * FROM `orders` WHERE user_id = '$user_id' ORDER BY order_date DESC") or die('query failed');
                        if (mysqli_num_rows($select_orders) > 0) {
                            while ($order = mysqli_fetch_assoc($select_orders)) {
                                echo "<div class='card border radius p-2'>";
                                $order_id = $order['id'];
                                $select_order_items = mysqli_query($conn,
                                "SELECT order_items.quantity, products.name
                                    FROM `order_items`
                                    JOIN `products` ON order_items.product_id = products.id
                                    WHERE order_items.order_id = '$order_id'") or die('query failed');
                                if (mysqli_num_rows($select_order_items) > 0) {
                                    echo "<h3>Order Items:</h3>";
                                    while ($item = mysqli_fetch_assoc($select_order_items)) {
                                        echo "<p>Product Name: <span>" . $item['name'] . "</span></p>";
                                    }
                                } else {
                                    echo "<p class='title'>Producten: <span class='text'>Geen producten gevonden voor deze order!</span></p>";
                                }
                
                                echo "<p class='title'>Datum van Order: <span class='text'>" . $order['order_date'] . "</span></p>";
                                echo "<p class='title'>Total: <span class='text'>" . $order['total'] . "</span></p>";
                                echo "<p class='title'>Address: <span class='text'>" . $order['address'] . "</span></p>";
                                echo "<p class='title'>Status: <span class='text'>" . $order['status'] . "</span></p>"; 
                
                                echo "</div>";
                                }
                            } else {
                                echo "<p>You have no orders yet.</p>";
                        }
                    ?>
                </div>
            </div>
        </div>
    </main>
</body>
</html>


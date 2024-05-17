<?php
    include './conf/config.php';
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
            $dashboardLink = '<li><a class="title" href="./pages/admin.php">Admin</a></li>';
        } else {
            $name = $_SESSION['name'];
            $dashboardLink = "<li><a class='title b-bottom' href='./pages/user.php'>Hallo, $name</a></li>";
        }
    } else {
        $dashboardLink = '<li><a class="button" href="./pages/login.php">Login</a></li>';
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - WheyTooFun</title>
    <link rel="stylesheet" href="./style/css/style.css">
</head>
<body>
    <nav class="b-bottom BG-300">
        <div class="wrapper con-nav flex-row-between">
            <h1 class="logo">WheyTooFun</h1>
            <ul class="links flex-row-between gap">
                <li><a class="title" href="index.php">Home</a></li>
                <li><a class="title" href="./pages/product.php">Producten</a></li>
                <li><?php echo $dashboardLink; ?></li>
            </ul>
        </div>
    </nav>
    <header class="h-60 b-bottom flex-center bg-image" style="background-image: url('./img/gym-header.jpg');">
    </header>
    <section>
        <div class="wrapper flex-row-around gap">
            <div class="text-center text-width">
                <img src="./img/fast-delivery.png" alt="Snelle Bezorging" width="100">
                <p class="title">Snelle bezorging</p>
                <p class="text">Wij zorgen ervoor dat uw paketten binnen 1 week bezorgd worden!</p>
            </div>
            <div class="text-center text-width">
                <img src="./img/badge.png" alt="Beste Kwaliteit" width="100">
                <p class="title">De beste kwaliteit</p>
                <p class="text">onze producten zijn van hoge kwaliteit!</p>
            </div>
            <div class="text-center text-width p-3">
                <img src="./img/service.png" alt="Goede klantenservice" width="100">
                <p class="title">Goede service</p>
                <p class="text">Wij staan 24/7 klaar om ervoor te zorgen dat u de beste service krijgt!</p>
            </div>
        </div>
    </section>
    <main>
        <h1 class="title p-2 text-center">De beste kwaliteit sportartikelen op 1 plek!</h1>
        <div class="wrapper flex-row-between gap">
            <div class="card">
                <div style="background-image: url('https://cdn.shopify.com/s/files/1/0185/2846/9056/files/SportSsT-Shirt_Seamless_Black-LightGrey--A3A8F-BBH1-0131.jpg?v=1687158997');" class="card-img">
                </div>
                <div class="card-txt">
                    <p class="card-title">Impact T-shirt</p>
                    <p class="card-price">$49,99</p>
                </div>
            </div>
            <div class="card">
                <div style="background-image: url('https://cleannutrition.nl/cdn/shop/files/WheyProtein.png?v=1705514686&width=800');" class="card-img">
                    <span class="highlight">nieuw</span>
                </div>
                <div class="card-txt">
                    <p class="card-title">Whey Protein</p>
                    <p class="card-price">$29,99</p>
                </div>
            </div>
            <div class="card">
                <div style="background-image: url('https://www.titan.fitness/dw/image/v2/BDBZ_PRD/on/demandware.static/-/Sites-masterCatalog_Titan/default/dw7f520176/images/hi-res/Fitness/LEVERBELT_01.jpg?sw=1001&sh=1000');" class="card-img">
                    <span class="highlight">nieuw</span>
                </div>
                <div class="card-txt">
                    <p class="card-title">Lifting Belt</p>
                    <p class="card-price">$14,99</p>
                </div>
            </div>
        </div>
    </main>
    <section>
        <div class="wrapper p-4">
            <h1 class="text-center text">Waarom moet u kiezen voor <span class="title">WheyTooFun</span></h1>
            <p class="text-center text p-2">Bij WheyTooFun bieden we alleen de hoogste kwaliteit gymproducten aan, speciaal geselecteerd voor maximale prestaties. Vertrouw op ons voor topmerken, snelle levering en deskundig advies. Shop nu en verbeter je training!</p>
        </div>
    </section>
</body>
</html>
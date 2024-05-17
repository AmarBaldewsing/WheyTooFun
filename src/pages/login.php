<?php
    include '../conf/config.php';
    session_start();

    if(isset($_POST['submit'])){
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $pass = mysqli_real_escape_string($conn, md5($_POST['password']));

        $select = mysqli_query($conn, "SELECT * FROM `users` WHERE email = '$email' AND password = '$pass'") or die('query failed');

        if(mysqli_num_rows($select) > 0){
            $row = mysqli_fetch_assoc($select);
            $_SESSION['user_id'] = $row['id'];
            header('location:../index.php');
        }else{
            $message[] = 'incorrect password or email!';
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>login</title>
    <link rel="stylesheet" href="../style/css/style.css">
</head>
<body>
    <?php
        if(isset($message)){
            foreach($message as $message){
                echo '<div class="message" onclick="this.remove();">'.$message.'</div>';
            }
        }
    ?>
    <nav class="b-bottom BG-300">
        <div class="wrapper con-nav flex-row-between">
            <h1 class="logo">WheyTooFun</h1>
            <ul class="links flex-row-between gap">
                <li><a class="title" href="../index.php">Home</a></li>
                <li><a class="title" href="producten.php">Producten</a></li>
                <li><a class="button" href="login.php">Login</a></li>
            </ul>
        </div>
    </nav>
    <main class="h-80 flex-center">
        <form action="" method="post" class="input">
            <h3 class="title p-1">login now</h3>

            <div class="fld-con">
                <input type="email" name="email" required placeholder="Email" class="input-fld">
                <input type="password" name="password" required placeholder="Wachtwoord" class="input-fld">
                <input type="submit" name="submit" class="button" value="login now">
            </div>
            
            <p class="text">Geen account? <a href="register.php">registeer nu!</a></p>
        </form>
    </main>
</body>
</html>
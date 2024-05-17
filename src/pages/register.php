<?php

include '../conf/config.php';

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) && checkdnsrr(array_pop(explode("@", $email)), "MX");
}

if(isset($_POST['submit'])){

   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $pass = mysqli_real_escape_string($conn, md5($_POST['password']));
   $cpass = mysqli_real_escape_string($conn, md5($_POST['cpassword']));

   if (!isValidEmail($email)) {
       $message[] = 'Invalid email address!';
   } else {
       $select = mysqli_query($conn, "SELECT * FROM `users` WHERE email = '$email' AND password = '$pass'") or die('query failed');

       if(mysqli_num_rows($select) > 0){
          $message[] = 'user already exist!';
       }else{
          mysqli_query($conn, "INSERT INTO `users`(name, email, password) VALUES('$name', '$email', '$pass')") or die('query failed');
          $message[] = 'registered successfully!';
          header('location:login.php');
       }
   }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>register</title>
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
                <li><a class="title" href="#">Over Ons</a></li>
                <li><a class="title" href="producten-test.php">Producten</a></li>
                <li><a class="button" href="login.php">Log In</a></li>
            </ul>
        </div>
    </nav>
    <main class="h-80 flex-center">
        <form action="" method="post" class="input">
            <h3 class="title">Registreer nu</h3>

            <input type="text" name="name" required placeholder="Gebruikersnaam" class="input-fld">
            <input type="email" name="email" required placeholder="Email" class="input-fld">
            <input type="password" name="password" required placeholder="Wachwoord" class="input-fld">
            <input type="password" name="cpassword" required placeholder="Herhaal Wachtwoord" class="input-fld">
            <input type="submit" name="submit" class="button" value="registreren">

            <p class="text">Heeft u al een account? <a href="login.php">login nu</a></p>
        </form>
    </main>
</body>
</html>
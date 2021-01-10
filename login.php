<?php
require "vendor/autoload.php";


if ($_POST) {
    $username = 'mehmet';
    $password = 'mehmet2x*';
    if (post('username') && post('password') && post('username') === $username && post('password') === $password) {
        session('authenticated', "authenticated");
        redirect('index.php');
    } else{
        redirect("login.php");

    }
} else {
    require "app/header.php"
    ?>

    <div class="container">
        <div class="col s12 m6"  style="margin-right: auto;margin-left: auto; width: 450px; margin-top: 50px">
            <form action="" method="post" class="submit">
                <input type="text" name="username" placeholder="username">
                <input type="password" name="password" placeholder="password">
                <button type="submit" class="btn blue">LOGIN</button>
            </form>
        </div>
    </div>


<?php } ?>
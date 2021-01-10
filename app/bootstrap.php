<?php

use Networking\Components\Http\Middleware;
use Networking\ProxyBalancer;
$m = new Middleware();
$b = new ProxyBalancer();

$b->setBalancer("192.168.3.38","123456");
$b->setBalancer("192.168.3.29","123456");

define("PROXY_BALANCER",$b->getAllBalancer());

$m->setMiddleware("/login.php",function (){
    if (session('authenticated')){
        redirect("index.php");
    }
});
$m->setMiddleware("/options.php",static function(){ authentication(); });
$m->setMiddleware("/index.php",static function(){ authentication(); });
$m->setMiddleware("/usb-connection.php",static function(){ authentication(); });


$m->getMiddleware($_SERVER["SCRIPT_NAME"]);


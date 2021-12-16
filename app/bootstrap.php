<?php

use Networking\Components\Http\Middleware;
use Networking\ProxyBalancer;
$m = new Middleware();
$b = new ProxyBalancer();

$b->setBalancer(\Networking\ProxyService\IPConf::getHomeINET(),"123456");
//$b->setBalancer("192.168.3.42","123456");
try {
    $dotenv = Dotenv\Dotenv::createImmutable(realpath('.'));
    $dotenv->load();
}catch (Exception $e){
    print_r($e->getMessage());
    exit();
}

define("PROXY_BALANCER",$b->getAllBalancer());
define("OUT_IP","195.174.177.104");

define("ROOT_PROXYUSR","mroot");
define("ROOT_PROXYUSRPWD","m2x*root");

$m->setMiddleware("/login.php",function (){
    if (session('authenticated')){
        redirect("index.php");
    }
});
$m->setMiddleware("/options.php",static function(){ authentication(); });
$m->setMiddleware("/index.php",static function(){ authentication(); });
$m->setMiddleware("/usb-connection.php",static function(){ authentication(); });


$m->getMiddleware($_SERVER["SCRIPT_NAME"]);


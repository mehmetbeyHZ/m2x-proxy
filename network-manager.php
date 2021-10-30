<?php
require "vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);

while (true){
    $dotenv->load();
    $mainIPV4  = $_ENV['MAIN_IPV4'];
    $mainModem = \Networking\ProxyService\IPConf::getHomeModemName();
    $currentIPV4 = \Networking\ProxyService\IPConf::getHomeINET();

    if ($mainIPV4 !== $currentIPV4){
        shell_exec("nmcli con up HOME_PROFILE ifname ". $mainModem);
    }
    sleep(5);
}


//
//
//shell_exec("nmcli con up HOME_PROFILE ifname ".\Networking\ProxyService\IPConf::getHomeModemName());
//
//shell_exec("sudo ip route add default via 192.168.3.1 dev ".\Networking\ProxyService\IPConf::getHomeModemName());

<?php

if ($_POST)
{
    $z = new \Networking\ProxyService\ModemAPI\ZTEMF667();
    $z->setLocalProxy("login1:pass1@192.168.3.29:8091");
    $z->createToken();
    $z->disconnect();
    sleep(10);
    $z->connect();
    sleep(10);
}
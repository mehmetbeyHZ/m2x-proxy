<?php

use Networking\ProxyService\IPConf;
use Networking\ProxyService\ThreeProxy;

require "../vendor/autoload.php";
$tp = new ThreeProxy();
$tp->setIPV4(IPConf::getHomeINET());
$tp = $tp->createConf(true);
print_r($tp);
print_r("RESTARTING...");
$restart = shell_exec("sudo supervisorctl restart mtproxy");
print_r($restart);

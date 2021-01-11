<?php

use Networking\ProxyService\IPConf;
use Networking\ProxyService\ModemAPI\ZTEMF667;
use Networking\ProxyService\ThreeProxy;

require "vendor/autoload.php";
$apiKey = '123456';
if (!$_REQUEST || !request('key') || request('key') !== $apiKey || !request('action') || !request('proxy')) {
    echo json(['status' => 'fail', 'message' => 'Incorrect request']);
    exit;
}

if (request('action') === 'RESET'):

    $lastReset = session('last_reset_proxy' . md5(request('proxy'))) ?: 0;
    $calcSize = time() - $lastReset;

    if ($calcSize < 60):
        echo json(['status' => 'fail', 'message' => 'Şu an sıfırlayamazsınız! sn:' . $calcSize]);
        exit;
    endif;
    session('last_reset_proxy' . md5(request('proxy')), time());

    $zte = new ZTEMF667();
    $zte->createToken();
    $zte->setLocalProxy(request('proxy'));
    $zte->disconnect();
    sleep(10);
    $zte->connect();
    sleep(10);
    echo json(['status' => 'ok', 'message' => 'reset successful']);
endif;

if (request('action') === 'SMS'):
    $zte = new ZTEMF667();
    $zte->createToken();
    $zte->setLocalProxy(request('proxy'));
    echo json($zte->getSMS());
endif;

if (request('action') === 'NETCONF'):
    $ip = new \Networking\ProxyService\IPConf();
    $networks = $ip->getAllConnections(true);
    $proxyConf = $ip->proxyConfParse();
    echo json([
        'networks' => $networks,
        'config' => $proxyConf
    ]);
endif;

if (request('action') === 'RECONF'):
    $tp = new ThreeProxy();
    $tp->setIPV4(IPConf::getHomeINET());
    $tp = $tp->createConf(true);
    $restart = shell_exec("sudo supervisorctl restart mtproxy");
    echo json(['status' => 'ok', 'message' => 'updated and restarted.']);
endif;


if (request('action') === 'IMEI'):

    $lastCheck = $_COOKIE['last_imei_get' . md5(request('proxy'))] ?? null;

    if ($lastCheck):
        echo $_COOKIE['last_imei_get' . md5(request('proxy'))];
        exit;
    endif;

    $zte = new ZTEMF667();
    $zte->createToken();
    $zte->setLocalProxy(request('proxy'));
    $imei = $zte->getImei();
    setcookie('last_imei_get' . md5(request('proxy')), $imei, time() + 60);
    echo $imei;

endif;

if (request('action') === "DATA"):
    $lastCheck = $_COOKIE['last_data_check' . md5(request('proxy'))] ?? null;
    if ($lastCheck):
        echo $_COOKIE['last_data_check' . md5(request('proxy'))];
        exit;
    endif;
    $zte = new ZTEMF667();
    $zte->createToken();
    $zte->setLocalProxy(request('proxy'));
    $data = $zte->getStatistic();
    setcookie('last_data_check' . md5(request('proxy')), $data, time() + 120);
    echo $data;
endif;
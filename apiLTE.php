<?php
ini_set('display_errors',1);
use Networking\ProxyService\IPConf;
use Networking\ProxyService\ModemAPI\MW40V1;
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
    $connStatus = null;
    $isConnected = false;

    $mw = new MW40V1();
    $mw->setLocalProxy(request('proxy'));
    $mw->login();
    $mw->reboot();

    echo json(['status' => 'ok', 'message' => 'reset successful', 'conn' => $connStatus]);
endif;

if (request('action') === 'SMS'):
    $mw = new MW40V1();
    $mw->setLocalProxy(request('proxy'));
    $mw->login();
    echo json($mw->getSMS());
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
    $restart = shell_exec("sudo service 3proxy reload");
    echo json(['status' => 'ok', 'message' => 'updated and restarted.']);
endif;


if (request('action') === 'IMEI'):

    $lastCheck = $_COOKIE['last_imei_get' . md5(request('proxy'))] ?? null;

    if ($lastCheck):
        echo $_COOKIE['last_imei_get' . md5(request('proxy'))];
        exit;
    endif;

    $mw = new MW40V1();
    $mw->setLocalProxy(request('proxy'));
    $systemInfo = $mw->getSystemInfo();
    $imei = $systemInfo["result"]["IMEI"];
    setcookie('last_imei_get' . md5(request('proxy')), $imei, time() + 60);
    echo $imei;

endif;

if (request('action') === "DATA"):

    $proxyHASH = md5(request('proxy'));
//    $saved     = redisGet("DATA_".$proxyHASH);
//    if ($saved !== null):
//        echo $saved;
//        return;
//    endif;

    $mw = new MW40V1();
    $mw->setLocalProxy(request('proxy'));
    $mw->login();
    $systemInfo = $mw->getSystemInfo();
    $usage = $mw->getUsageSettings();
    $imei = $systemInfo["result"]["IMEI"];



    $statistic = "0,0,0,0,0,0,0,0";

    $devices = json_decode(file_get_contents("database/devices.json"),true);
    $deviceInfo = [];

    $s =  array_search($imei, array_column($devices,'imei'), true);
    if (is_int($s)){
        $deviceInfo = $devices[$s];
    }

    $lastData = json([
        'imei'     => $imei,
        'realtime' => $statistic,
        'charges_total' => formatSizeUnits($usage["result"]["UsedData"]) ?? 0,
        'device' => $deviceInfo
    ]);

    redisSave("DATA_".$proxyHASH,$lastData,120);
    echo $lastData;
endif;


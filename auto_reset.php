<?php

use Carbon\Carbon;
use MClient\MultiRequest;
use MClient\Request;

require "vendor/autoload.php";
$devices = json_decode(file_get_contents("database/devices.json"),true);
$startPort = 8090;
$endPort   = 8188;
$ports     = [];
$inet      = "192.168.3.30";

for ($i = $startPort; $i <= $endPort; $i++)
{
//    print_r('RESET_PORT_'.$i);
    if (redisGet('RESET_PORT_'.$i) === null){
        $ports[] = $i;
    }
}

print_r("Total port: " . count($ports));

$randSelect = array_rand($ports,5);

$multi = new \RollingCurl\RollingCurl();
foreach ($randSelect as $proxyIndex)
{
    $myPort = $ports[$proxyIndex];
    $proxy = ROOT_PROXYUSR.":".ROOT_PROXYUSRPWD.'@'.$inet.':'.$myPort;


    $multi->post("http://192.168.3.30/api.php",http_build_query(["proxy" => $proxy, "action" => "RESET","key" => "123456"]),[],[],["port" => $myPort]);

}
$redis = redis();
$multi->setCallback(function (\RollingCurl\Request $request,\RollingCurl\RollingCurl $rollingCurl) use(&$redis){
    $rKey = 'RESET_PORT_'.$request->identifierParams['port'];
    $redis->set($rKey,Carbon::now("Europe/Istanbul")->format("Y-m-d H:i:s"));
    $redis->expire($rKey,600);

    print_r($request->getResponseText());

    $rollingCurl->clearCompleted();
    $rollingCurl->prunePendingRequestQueue();
});
$multi->setSimultaneousLimit(100);
$multi->execute();
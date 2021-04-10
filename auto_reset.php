<?php

use Networking\ProxyService\IPConf;

require "vendor/autoload.php";
$simultaneous = 0;
$maxSimule    = 5;
$responses = [];
$hasAnyReset = false;

while (true)
{
    $connectionInfo = connectionInfo();
    $rc = new \RollingCurl\RollingCurl();
    $redis = redis();
    foreach ($connectionInfo as $connection)
    {
        $canReset = $redis->exists('MODEM_RESET_AT:'.$connection['gateway']);
        if ($canReset === 0 && $connection["gateway"] !== "")
        {
            $hasAnyReset = true;
            $rc->post("http://".$connection['gateway']."/jrd/webapi?api=SetDeviceReboot",'{"jsonrpc":"2.0","method":"SetDeviceReboot","params":null,"id":"13.5"}',[],[CURLOPT_TIMEOUT => 20],['gateway' => $connection['gateway']]);
            $simultaneous++;
            redisSave('MODEM_RESET_AT:'.$connection['gateway'],\Carbon\Carbon::now('Europe/Istanbul'),3600);
            redisSave("RESET_PROXY_TIMEX:".$connection['gateway'],\Carbon\Carbon::now('Europe/Istanbul'),60);
        }
        if ($simultaneous === $maxSimule){
            break;
        }
    }

    $rc->setCallback(function (\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl)  use(&$responses){

        $responses[] = [
            'response' => $request->getResponseText(),
            'info' => $request->identifierParams
        ];
        $rollingCurl->prunePendingRequestQueue();
        $rollingCurl->clearCompleted();
    });
    $rc->setSimultaneousLimit(100);
    $rc->execute();
    print_r("\n".$simultaneous ." WAS RESET");
    $simultaneous = 0;
    $responses = [];
    $sleepSeconds = $hasAnyReset ? 60 : 5;
    print_r("\n$sleepSeconds SECONDS SLEEPING... \n");
    sleep($sleepSeconds);
    $hasAnyReset = false;
}







function connectionInfo()
{
    $ip = new IPConf();
    $connections  = $ip->getAllConnections(true);
    $proxyConf = $ip->proxyConfParse();
    $connectionInfo = [];
    foreach ($connections as $connection)
    {
        if (isset($proxyConf[$connection["cName"]]))
        {
            $modemInterface = str_replace(".255",".1",$connection["broadcast"]);
            $proxyPort = $proxyConf[$connection["cName"]]["ip"];

            $connectionInfo[] = [
                'gateway' => trim($modemInterface),
                'inet' => $connection['inet'],
                'connection_name' => $connection['cName'],
                'ip' => $proxyConf[$connection["cName"]]['ip'],
                'port' =>  $proxyConf[$connection["cName"]]['port'],
            ];
        }
    }
    return $connectionInfo;
}
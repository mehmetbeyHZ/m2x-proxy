<?php
ini_set('display_errors',1);
use MClient\Request;

require "vendor/autoload.php";
if ($_POST)
{
    $rc = new \RollingCurl\RollingCurl();
    if (request('type') === 'RESTART')
    {
        foreach (post('data') as $item){

            if ($_ENV["MODEM_TYPE"] == "ALCATEL"){
                redisSave("RESET_PROXY_TIMEX:".$item['inet'],10,60);
                $rc->post("http://".$item['inet']."/jrd/webapi?api=SetDeviceReboot",'{"jsonrpc":"2.0","method":"SetDeviceReboot","params":null,"id":"13.5"}',[],[CURLOPT_TIMEOUT => 20],['inet' => $item['inet']]);
            }
        }
    }
    if (request('type') === 'CHECK')
    {
        foreach (post('data') as $proxies)
        {
            $rc->get("http://ip-api.com/json/",[],[CURLOPT_PROXY => $proxies['proxy'],CURLOPT_TIMEOUT => 10],$proxies);
        }
    }
    $responses = [];

    $rc->setCallback(function (\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl)  use(&$responses){

        $responses[] = [
            'response' => $request->getResponseText(),
            'balancer' => $request->identifierParams
        ];
        $rollingCurl->prunePendingRequestQueue();
        $rollingCurl->clearCompleted();
    });
    $rc->setSimultaneousLimit(100);
    $rc->execute();



    echo json($responses);
}
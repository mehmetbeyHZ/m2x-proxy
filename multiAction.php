<?php
ini_set('display_errors',1);
use MClient\Request;

require "vendor/autoload.php";

if ($_POST)
{
    $rc = new \RollingCurl\RollingCurl();
    if (request('type') === 'RESTART')
    {
        foreach (post('data') as $balancer){

            $data = http_build_query([
                "action" => "RESET",
                "key" => $balancer["balancerKey"],
                "proxy" => $balancer["proxy"]
            ]);
            $rc->post("http://".$balancer['balancer']."/api.php",$data,[],[CURLOPT_TIMEOUT => 20],['balancer' => $balancer]);

        }
    }
    if (request('type') === 'CHECK')
    {
        foreach (post('data') as $proxies)
        {
            $rc->get("http://ip-api.com/json/",[],[CURLOPT_PROXY => $proxies['proxy'],CURLOPT_TIMEOUT => 35],$proxies);
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
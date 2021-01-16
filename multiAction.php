<?php
ini_set('display_errors',1);
use MClient\Request;

require "vendor/autoload.php";

if ($_POST)
{
    $multi   = new \MClient\MultiRequest();
    if (request('type') === 'RESTART')
    {
        foreach (post('data') as $balancer){

            $multi->add((new Request("http://".$balancer['balancer']."/api.php"))
                ->addPost('action',"RESET")
                ->addPost('key',$balancer['balancerKey'])
                ->addPost('proxy',$balancer['proxy'])
                ->addCurlOptions(CURLOPT_TIMEOUT,60)
                ->setIdentifierParams(['balancer' => $balancer])
            );

        }
    }
    if (request('type') === 'CHECK')
    {
        foreach (post('data') as $proxies)
        {
            $multi->add((new Request("http://ip-api.com/json/"))
                ->setProxy($proxies['proxy'])
                ->setIdentifierParams($proxies)
            );
        }
    }
    $exec = $multi->execute();
    $responses = [];
    foreach ($exec as $item){
        $responses[] = [
            'response' => $item->getResponseText(),
            'balancer' => $item->getIdentifierParams()
        ];
    }

    echo json($responses);
}
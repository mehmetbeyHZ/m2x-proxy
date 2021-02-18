<?php

require "vendor/autoload.php";

$rc = new \RollingCurl\RollingCurl();

for ($j = 0; $j < 60; $j++)
{

    for ($i =8090; $i <= 8132; $i++)
    {
        $proxy = "login1:pass1@195.174.177.104:".$i;
        $rc->get("https://jsonplaceholder.typicode.com/todos/1",[],[CURLOPT_PROXY => $proxy,CURLOPT_TIMEOUT => 35],['pr' => $proxy]);
    }
}
$success = 0;
$error = 0;
$rc->setCallback(function (\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl)  use(&$success,&$error){

    if (strpos($request->getResponseText(),"userId"))
    {
        $success++;
    }

    if (strpos($request->getResponseText(),"502")){
        $error++;
        print_r($request->getResponseText());
        print_r($request->getResponseError());
        print_r("GW:".$request->identifierParams['pr']."\n");
    }

    if ($request->getResponseError())
    {
        print_r("CURL_ERROR:" . $request->getResponseError()."\n");
    }

    if (strpos($request->getResponseError(),"502"))
    {
        $error++;
        print_r($request->identifierParams['pr']."\n");

    }

//    print_r($request->getResponseText()."\n\n");
    $rollingCurl->prunePendingRequestQueue();
    $rollingCurl->clearCompleted();
});
$rc->setSimultaneousLimit(100);
$rc->execute();

print_r("S: " . $success."\n");
print_r("E: " . $error);
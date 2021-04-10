<?php

require "vendor/autoload.php";

$rc = new \RollingCurl\RollingCurl();

for ($j = 0; $j < 1000; $j++) {

    $proxy = "mroot:m2x*root@192.168.3.32:".rand(8090,8103);
    $opt =  [
        CURLOPT_PROXY => $proxy,
        CURLOPT_TIMEOUT => 35,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT => 'Instagram 121.0.0.29.119 Android (26/8.0; 431dpi; 1080x2280; samsung; SM-J500Y; exynos9610; en_US)'
    ];
    $rc->get("https://jsonplaceholder.typicode.com/todos/1", [],$opt, ['pr' => $proxy]);
}
$success = 0;
$error = 0;
$rc->setCallback(function (\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl) use (&$success, &$error) {

    if (strpos($request->getResponseText(), "userId")) {
        $success++;
    }

    if (strpos($request->getResponseText(), "502")) {
        $error++;
        print_r($request->getResponseText());
        print_r($request->getResponseError());
        print_r("GW:" . $request->identifierParams['pr'] . "\n");
    }

    if ($request->getResponseError()) {
        print_r("CURL_ERROR:" . $request->getResponseError() . "\n");
        print_r($request->identifierParams['pr'] . "\n");
        $error++;
    }

    if (strpos($request->getResponseError(), "502")) {
        $error++;
        print_r($request->identifierParams['pr'] . "\n");
    }

    print_r($request->getResponseText() . "\n\n");
    print_r("Total: " . ($error + $success));
    $rollingCurl->prunePendingRequestQueue();
    $rollingCurl->clearCompleted();
});
$rc->setSimultaneousLimit(100);
$rc->execute();

print_r("S: " . $success . "\n");
print_r("E: " . $error);
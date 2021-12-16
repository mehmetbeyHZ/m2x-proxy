<?php
ini_set('display_errors',1);
require "vendor/autoload.php";
$apiKey = '123456';
if (!$_REQUEST || !request('key') || request('key') !== $apiKey || !request('action') || !request('proxy') || !request('ipv4')) {
    echo json(['status' => 'fail', 'message' => 'Incorrect request']);
    exit;
}



if (request('action')){
    $balancer = PROXY_BALANCER[request('ipv4')];
    echo (new \MClient\Request("http://".$_ENV["MAIN_IPV4"]."/apiLTE.php"))
        ->setCookieFile(realpath('.').'/mfcookies/client-'.md5(request('proxy')).".txt")
        ->addPost('action',request('action'))
        ->addPost('key',$balancer['key'])
        ->addPost('proxy',request("proxy"))
        ->addCurlOptions(CURLOPT_TIMEOUT,5)
        ->execute()
        ->getResponse();
}

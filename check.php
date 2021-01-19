<?php

require "vendor/autoload.php";

echo (new \MClient\Request("http://ip-api.com/json/"))
    ->setProxy(request('proxy'))
    ->addCurlOptions(CURLOPT_TIMEOUT,30)
    ->execute()
    ->getResponse();
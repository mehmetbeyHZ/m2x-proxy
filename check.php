<?php

require "vendor/autoload.php";

echo (new \MClient\Request("http://ip-api.com/json/"))
    ->setProxy(request('proxy'))
    ->execute()
    ->getResponse();
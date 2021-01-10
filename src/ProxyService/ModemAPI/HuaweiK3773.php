<?php


namespace Networking\ProxyService\ModemAPI;


use MClient\Request;

class HuaweiK3773
{
    protected $proxy;
    protected $token;
    public function setLocalProxy($proxy)
    {
        $this->proxy = $proxy;
        $this->token = $this->getToken();
    }



    public function getToken()
    {
        $request = (new Request("http://192.168.1.1/html/js/vendor.js"))
            ->setProxy($this->proxy)
            ->execute()
            ->getResponse();
        preg_match("@var STR_AJAX_VALUE     = \"(.*?)\";@si",$request,$out);
        return $out[1] ?? null;
    }

    public function connect()
    {
        return $this->mClient("api/dialup/dial")
        ->addCurlOptions(CURLOPT_POSTFIELDS,"<?xml version='1.0' encoding='UTF-8'?><request><Action>1</Action><token>{$this->token}</token></request>")
        ->execute()->getResponse();
    }

    public function information()
    {
        return $this->mClient("api/device/information")
            ->addHeader('X-Requested-With','XMLHttpRequest')
            ->addHeader('Accept','*/*')
            ->addHeader('Referer','http://192.168.1.1/html/home.htm')
            ->addHeader('Accept-Language','tr-TR,tr;q=0.9,en-US;q=0.8,en;q=0.7')
            ->addHeader('Connection','keep-alive')
            ->addHeader('Host','192.168.1.1')
            ->setUserAgent('Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36')
            ->execute()
            ->getResponse();
    }

    public function status()
    {
        return $this->mClient("api/monitoring/status")
            ->execute()
            ->getResponse();
    }

    public function disconnect()
    {
        return $this->mClient("api/dialup/dial")
            ->addCurlOptions(CURLOPT_POSTFIELDS,"<?xml version='1.0' encoding='UTF-8'?><request><Action>0</Action><token>{$this->token}</token></request>")
            ->execute()->getResponse();
    }

    public function mClient($endpoint)
    {
        return (new Request("http://192.168.1.1/".$endpoint))
            ->setProxy($this->proxy);
    }


}
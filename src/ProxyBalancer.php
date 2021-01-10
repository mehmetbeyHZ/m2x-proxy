<?php


namespace Networking;


class ProxyBalancer
{
    private $balancers = [];

    public function setBalancer($address,$apiKey)
    {
        $this->balancers[$address] = [
            'address' => $address,
            'key' => $apiKey
        ];
    }

    public function getBalancer($ipv4)
    {
        return $this->balancers[$ipv4] ?? null;
    }

    public function getAllBalancer()
    {
        return $this->balancers;
    }


    public function balancerRequest($ipv4,$action,$key,$proxy)
    {
        return (new \MClient\Request("http://$ipv4/api.php"))
            ->addPost('action',$action)
            ->addPost('key',$key)
            ->addPost('proxy',$proxy)
            ->addCurlOptions(CURLOPT_TIMEOUT,5)
            ->execute()
            ->getResponse();
    }

}
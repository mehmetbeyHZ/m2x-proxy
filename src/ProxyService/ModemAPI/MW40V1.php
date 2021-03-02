<?php


namespace Networking\ProxyService\ModemAPI;


use MClient\Request;

class MW40V1
{
    protected ?string $proxy;
    protected $key = null;
    protected $token = null;
    protected $hostName = 'http://192.168.1.1/';
    public function setLocalProxy($proxy)
    {
        $this->proxy = $proxy;
        $this->hostNameRouter();
        $this->key = $this->getVerificationKey();
    }

    public function setGateway($gateway)
    {
        $this->hostName = 'http://'.$gateway.'/';
    }

    public function hostNameRouter()
    {
        $route = $this->mClient("index.html")
            ->addCurlOptions(CURLOPT_FOLLOWLOCATION,false)
            ->execute()
            ->getResponse();
        if (strpos($route,"http://mw40.home/index.html")){
            $this->hostName = "http://mw40.home/";
        }
    }

    public function getVerificationKey()
    {
        $vkey = $this->mClient('js/sdk.js?version=2019-12-24-17-29')
            ->execute()
            ->getResponse();


        preg_match_all("@'_TclRequestVerificationKey'] = \"(.*?)\";@si",$vkey,$data);
        return $data[1][0] ?? null;

    }

    public function reboot()
    {
        return $this->mClient('jrd/webapi?api=SetDeviceReboot')
            ->addCurlOptions(CURLOPT_POSTFIELDS,'{"jsonrpc":"2.0","method":"SetDeviceReboot","params":null,"id":"13.5"}')
            ->execute()
            ->getResponse();

    }

    public function getUsageRecord()
    {
        return $this->mClient('jrd/webapi?api=GetUsageRecord')
            ->addCurlOptions(CURLOPT_POSTFIELDS,'{"jsonrpc":"2.0","method":"GetUsageRecord","params":{"current_time":"'.date('Y-m-d H:i:s').'"},"id":"7.1"}')
            ->execute()
            ->getDecodedResponse();
    }

    public function getUsageSettings()
    {
        return $this->mClient('jrd/webapi?api=GetUsageSettings')
            ->addCurlOptions(CURLOPT_POSTFIELDS,'{"jsonrpc":"2.0","method":"GetUsageSettings","params":null,"id":"7.3"}')
            ->execute()
            ->getDecodedResponse();
    }

    public function getSystemInfo()
    {
        return $this->mClient('jrd/webapi?api=GetSystemInfo')
            ->addCurlOptions(CURLOPT_POSTFIELDS,'{"jsonrpc":"2.0","method":"GetSystemInfo","params":null,"id":"13.1"}')
            ->execute()
            ->getDecodedResponse();
    }

    public function login()
    {
        $token = $this->mClient('jrd/webapi?api=Login')
            ->addCurlOptions(CURLOPT_POSTFIELDS,'{"jsonrpc":"2.0","method":"Login","params":{"UserName":"dc13ibej?7","Password":"dc13ibej?7"},"id":"1.1"}')
            ->execute()
            ->getDecodedResponse();
        $token = $token["result"]["token"] ?? null;
        $loginToken = $this->newEncryptionMW((string)$token);
        $this->token = $loginToken;
        return $this->getLoginState();
    }

    public function getLoginState()
    {
        return $this->mClient("jrd/webapi?api=GetLoginState")
            ->addCurlOptions(CURLOPT_POSTFIELDS,'{"jsonrpc":"2.0","method":"GetLoginState","params":null,"id":"1.3"}')
            ->execute()
            ->getResponse();
    }

    public function getSMS()
    {
        $smsList =  $this->mClient('jrd/webapi?api=GetSMSContactList')
            ->addCurlOptions(CURLOPT_POSTFIELDS,'{"jsonrpc":"2.0","method":"GetSMSContactList","params":{"Page":0},"id":"6.2"}')
            ->execute()
            ->getDecodedResponse();

        $sms = [];
        foreach ($smsList["result"]["SMSContactList"] as $item)
        {
            $sms[] = [
                'sender' => $item['PhoneNumber'][0],
                'date'   => $item['SMSTime'],
                'text'   => $item['SMSContent']
            ];
        }

        return $sms;
    }

    public function tokenApi()
    {
        return $this->mClient('/jrd/webapi')
            ->addCurlOptions(CURLOPT_POSTFIELDS,'{"jsonrpc":"2.0","method":"GetToken","params":{},"id":1}')
            ->execute()
            ->getResponse();
    }

    public function mClient($endpoint)
    {
        return (new Request($this->hostName.$endpoint))
            ->setProxy($this->proxy)
            ->addHeader('_TclRequestVerificationKey',$this->key)
            ->addHeader('_TclRequestVerificationToken',$this->token)
            ->addCurlOptions(CURLOPT_TIMEOUT,10)
            ->addCurlOptions(CURLOPT_REFERER,'http://mw40.home/');
    }


    public function encryptionMW($data): string
    {
        $key = "e5dl12XYVggihggafXWf0f2YSf2Xngd1";
        $str1 = [];
        $encryStr = "";
        for($i = 0, $iMax = strlen($data); $i < $iMax; $i++)
        {
            $char_i = $data[$i];
            $num_char_i = ord($char_i);
            $str1[2 * $i] = (ord($key[$i % strlen($key)]) & 0xf0) | (($num_char_i & 0xf) ^ (ord($key[$i % strlen($key)]) & 0xf));
            $str1[2 * $i + 1] = (ord($key[$i % strlen($key)]) & 0xf0) | (($num_char_i >> 4) ^ (ord($key[$i % strlen($key)]) & 0xf));
        }
        for($i = 0, $iMax = count($str1); $i < $iMax; $i++)
        {
            $encryStr .= chr($str1[$i]);
        }
        return $encryStr;
    }

    public function newEncryptionMW($str,$key = null)
    {
        $str = (string)$str;
        $key = $key ?? "e5dl12XYVggihggafXWf0f2YSf2Xngd1";
        $str1 = [];
        $encryStr = "";
        for($i = 0, $iMax = strlen($str); $i < $iMax; $i++)
        {
            $char_i = $str[$i];
            $num_char_i = ord($char_i);
            $str1[2 * $i] = (ord($key[$i % strlen($key)]) & 0xf0) | (($num_char_i & 0xf) ^ (ord($key[$i % strlen($key)]) & 0xf));
            $str1[2 * $i + 1] = (ord($key[$i % strlen($key)]) & 0xf0) | (($num_char_i >> 4) ^ (ord($key[$i % strlen($key)]) & 0xf));
        }

        for($i = 0, $iMax = count($str1); $i < $iMax; $i++)
        {
            $encryStr .= chr($str1[$i]);
        }
        return $encryStr;
    }


}
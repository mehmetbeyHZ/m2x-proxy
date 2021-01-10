<?php


namespace Networking\ProxyService\ModemAPI;


use MClient\Request;

class ZTEMF667
{
    protected $proxy;
    protected $token;

    public function setLocalProxy($proxy)
    {
        $this->proxy = $proxy;
        $this->createToken();
    }

    public static function smsContentDecode($text)
    {
        $result = "";
        for ($i = 0, $iMax = strlen($text); $i < $iMax; $i+=4)
        {

            $tmpSelect = $i+4;
            $item = substr($text,$i,$tmpSelect - $i);
            $myVal = intval($item,16);
            $result .= chr($myVal);
        }
        return $result;
    }

    public function createToken()
    {
        $this->token = $this->luckAlgorithm();
        //goformId=LOGIN&lucknum=59195&systemDate=&languageSelect=en&user=admin&psw=admin
        return $this->mClient('goform/goform_process')
            ->addParam('goformId','LOGIN')
            ->addParam('lucknum',$this->token)
            ->addParam('systemDate',"")
            ->addParam("languageSelect","en")
            ->addParam("user","admin")
            ->addParam("psw","admin")
            ->execute()
            ->getResponse();

    }

    private function connectDial($action)
    {
        return $this->mClient("goform/goform_process")
            ->addPost('goformId','NET_CONNECT')
            ->addPost('lucknum_NET_CONNECT',$this->token)
            ->addPost('dial_mode','auto_dial')
            ->addPost('action',$action)
            ->addPost('wan_conn_which_page','wan_operation')
            ->execute()
            ->getResponse();
    }

    public function getSMS()
    {
        $response =  $this->mClient("sms_xml/nv_inbox.xml")
            ->addParam('now',time())
            ->addHeader('cookie','mLangage=tr; lucknum='.$this->token.'; sim_inbox_page=1; native_inbox_page=1')
            ->execute()
            ->getResponse();
        $myData = simplexml_load_string($response);
        $smsData = [];
        foreach ($myData->sms_node as $item)
        {
            $smsData[] = [
                'sender' => (string)$item->sender,
                'date'   => $item->day . '.'. $item->month.'.'.$item->year.' '.$item->hour.':'.$item->minute,
                'text'   => self::smsContentDecode($item->sms_content)
            ];
        }
        return $smsData;
    }

    public function disconnect()
    {
        return $this->connectDial('disconnect');
    }

    public function connect()
    {
        return $this->connectDial('connect');
    }

    public function luckAlgorithm()
    {
         return floor((float)rand()/(float)getrandmax() * 1000000);
    }

    public function mClient($endpoint)
    {
        return (new Request("http://192.168.0.1/".$endpoint))
            ->setProxy($this->proxy)
            ->setCookieFile(realpath('.').'/mfcookies/'.md5($this->proxy).".txt");
    }

}
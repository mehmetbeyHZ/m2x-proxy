<?php


namespace Networking\ProxyService;


class ModemInfo
{
    protected $busNum;
    protected $deviceNum;
    protected $productID;
    protected  $vendorID;
    protected $modemName;
    public function __construct($modemName){
        $this->modemName = $modemName;
        preg_match_all("@ID (.*?):(.*?) @si",$modemName,$out);

        preg_match("@BUS (.*?) Device (.*?): ID (.*?):(.*?) @si",$modemName,$busOuter);


        if (!isset($out[1][0],$out[2][0]))
        {
            throw new \RuntimeException("Incorrect modem string");
        }
        $this->vendorID = $out[1][0];
        $this->productID = $out[2][0];
        $this->busNum = $busOuter[1];
        $this->deviceNum = $busOuter[2];
    }

    public function getBusNum()
    {
        return $this->busNum;
    }

    public function getDeviceNum()
    {
        return $this->deviceNum;
    }

    public function getVendorID()
    {
        return $this->vendorID;
    }

    public function getProductID()
    {
        return $this->productID;
    }

    public function getModemName()
    {
        return $this->modemName;
    }


}
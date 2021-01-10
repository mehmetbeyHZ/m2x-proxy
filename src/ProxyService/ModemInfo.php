<?php


namespace Networking\ProxyService;


class ModemInfo
{
    protected $productID;
    protected  $vendorID;
    protected $modemName;
    public function __construct($modemName){
        $this->modemName = $modemName;
        preg_match_all("@ID (.*?):(.*?) @si",$modemName,$out);
        if (!isset($out[1][0],$out[2][0]))
        {
            throw new \RuntimeException("Incorrect modem string");
        }
        $this->vendorID = $out[1][0];
        $this->productID = $out[2][0];
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
<?php


namespace Networking\ProxyService\Models;


class IPConfModel
{
    protected $cName;
    protected $inet;
    protected $broadcast;
    protected $data;
    public function __construct($cName,$inet,$broadcast,$data)
    {
        $this->cName = $cName;
        $this->inet = $inet;
        $this->broadcast = $broadcast;
        $this->data = $data;
    }

    public function getConnectionName()
    {
        return $this->cName;
    }

    public function getInet()
    {
        return $this->inet;
    }

    public function getBroadcast(){
        return $this->broadcast;
    }

    public function getAllData()
    {
        return $this->data;
    }

}
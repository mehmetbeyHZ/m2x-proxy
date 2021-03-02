<?php


namespace Networking\ProxyService\ModemAPI;


interface ModemInterface
{
    public function setHostName($hostName);

    public function getHostName($hostName);

    public function mClient();
}
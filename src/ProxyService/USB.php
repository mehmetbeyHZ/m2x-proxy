<?php

namespace Networking\ProxyService;

class USB
{

    protected $modems = ["ZTE WCDMA","Huawei Technologies Co., Ltd.","T & A"];

    public function getAllModems()
    {
        $items =  shell_exec("lsusb");
        return array_filter(explode("\n",$items));
    }

    /**
     * @return ModemInfo[]
     */
    public function getUSBModems()
    {
        $founded = [];
        foreach ($this->modems as $modem) {
            $items = shell_exec("lsusb | grep \"$modem\" ");
            $parseItems = explode("\n", $items);
            foreach ($parseItems as $parsed) {
                if ($parsed === null || $parsed === '') {
                    continue;
                }
                $founded[] = new ModemInfo($parsed);
            }
        }
        return $founded;
    }

    public function modemInfo($modemString)
    {
        return new ModemInfo($modemString);
    }

    public function reset($modemString)
    {
        $modem = $this->modemInfo($modemString);
        //return shell_exec("sudo usb_modeswitch -R -v {$modem->getVendorID()} -p {$modem->getProductID()}");

    }

}
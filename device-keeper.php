<?php
require "vendor/autoload.php";
$usb = new \Networking\ProxyService\USB();
while (true)
{
    $modems = $usb->getUSBModems();
    foreach ($modems as $modem)
    {
        if ($modem->getVendorID() === "1bbb" && $modem->getProductID() === "f000")
        {
//            shell_exec("sudo usb_modeswitch -v 1bbb -p f000 -c /etc/usb_modeswitch.d/alcatel.conf");
            shell_exec("sudo usb_modeswitch -b {$modem->getBusNum()} -g {$modem->getDeviceNum()} -c /etc/usb_modeswitch.d/alcatel.conf > /dev/null 2>&1 &");
            print_r("COMMAND\n");
        }
    }
    sleep(2);
}
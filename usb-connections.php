<?php
require "vendor/autoload.php";
require __DIR__ . "/app/header.php";
$ip = new \Networking\ProxyService\USB();
$usbList = $ip->getUSBModems();
?>
<br>

<div class="container">
    <label>Total <b><?=count($usbList)?></b> Connections </label>
    <table>
        <thead>
        <tr>
            <th>USB MODEM</th>
            <th>ProductID</th>
            <th>Vendor ID</th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($usbList as $usb): ?>
            <tr>
                <td><?=$usb->getModemName()?></td>
                <td><?=$usb->getProductID()?></td>
                <td><?=$usb->getVendorID()?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

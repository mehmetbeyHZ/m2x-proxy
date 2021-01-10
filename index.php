<?php


use Networking\ProxyService\IPConf;

require "vendor/autoload.php";
require __DIR__ . "/app/header.php";
//$ip = new IPConf();
//$networks = $ip->getAllConnections();
//$proxyConf = $ip->proxyConfParse();

foreach (PROXY_BALANCER as $balancer)
{
    $data = (new \Networking\ProxyBalancer())->balancerRequest($balancer['address'],"NETCONF",$balancer['key'],"NO_PROXY");
    $balancers[] = json_decode($data,true);
}
?>
<br>



<div class="container">
<!--    <label>Total <b>--><?//= count($networks) ?><!--</b> Connections </label>-->
    <table>
        <thead>
        <tr>
            <th>ConnectionName</th>
            <th>INET</th>
            <th>NETMASK</th>
            <th>Detail</th>
            <th>SETTINGS</th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($balancers as $balancer): ?>
            <?php foreach ($balancer['networks'] as $net):?>
                <tr>
                    <td><?=($net['cName'])?></td>
                    <td><?=($net['inet'])?></td>
                    <td><?=($net['broadcast'])?></td>
                    <td>
                        <?php if (isset($balancer['config'][$net['cName']])): $getConf = $balancer['config'][$net['cName']]?>
                            <span class="badge new blue" data-badge-caption="IP: <?=$getConf['ip']?>"></span>
                            <span class="badge new blue" data-badge-caption="PORT: <?=$getConf['port']?>"></span>
                            <span class="badge new blue" data-badge-caption="<?=$getConf['device']?>"></span>

                        <?php else:?>
                            NO CONFIGURATION
                        <?php endif; ?>
                    </td>
                    <td class="center">
                        <?php if(isset($balancer['config'][$net['cName']])): ?>
                            <a class="btn blue" id="restartProxy" data-ipv4="<?=$getConf['ip']?>" data-proxy="login1:pass1@<?=$getConf['ip'].":".$getConf["port"]?>">RESTART</a>
                            <a class="btn blue darken-2" id="checkProxy">CHECK</a>
                            <a class="btn yellow darken-2 black-text" id="getSms" data-ipv4="<?=$getConf['ip']?>" data-proxy="login1:pass1@<?=$getConf['ip'].":".$getConf["port"]?>">SMS</a>
                        <?php endif;?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
        </tbody>
    </table>
    <script>
        $(function (){

            $("a#getSms").on('click',function (){
               let proxy = $(this).attr('data-proxy');
                let ipv4 = $(this).attr('data-ipv4');
                $.post("apiv2.php",{key : '123456',action: 'SMS',proxy:proxy,ipv4:ipv4},function (data){

               });
            });

            $("a#restartProxy").on('click',function (){
                let proxy = $(this).attr('data-proxy');
                let ipv4 = $(this).attr('data-ipv4');
                $.post("apiv2.php",{key : '123456',action: 'RESET',proxy:proxy,ipv4:ipv4})
            });

        });
    </script>
</div>



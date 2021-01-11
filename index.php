<?php
require "vendor/autoload.php";

if (isset($_GET['_'])) {
    $r = new \RollingCurl\RollingCurl();

    foreach (PROXY_BALANCER as $balancer) {
        $data = http_build_query([
            'action' => 'NETCONF',
            'key' => $balancer['key'],
            'proxy' => "NO_PROXY"
        ]);
        $r->post("http://{$balancer['address']}/api.php", $data, [], [CURLOPT_TIMEOUT => 5], $balancer);
    }
    $configs = [];
    $r->setCallback(function (\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl) use (&$configs) {
        $configs[] = [
            'response' => json_decode($request->getResponseText(), true),
            'balancer' => $request->identifierParams
        ];
        $rollingCurl->prunePendingRequestQueue();
        $rollingCurl->clearCompleted();
    });
    $r->setSimultaneousLimit(100);
    $r->execute();

    $r = new \RollingCurl\RollingCurl();

    foreach ($configs as $server) {
        foreach ($server['response']['networks'] as $network) {
            $networkName = $network['cName'];
            if (isset($server['response']['config'][$networkName])) {
                $conf = $server['response']['config'][$networkName];
                $ip = $conf['ip'];
                $port = $conf['port'];
                $proxyUri = 'login1:pass1@' . $ip . ':' . $port;
                $userData = ['network' => $network, 'config' => $conf, 'balancer' => $server['balancer'], 'proxy' => $proxyUri];

                $postData = http_build_query([
                    'action' => 'DATA',
                    'key' => '123456',
                    'proxy' => $proxyUri
                ]);

                $r->post("http://{$server['balancer']['address']}/api.php", $postData, [], [], $userData);
            }
        }
    }
    $lastInfo = [];
    $r->setCallback(function (\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl) use (&$lastInfo) {

        $deviceInfo = json_decode($request->getResponseText(), true);
        $lastInfo[] = [
            'info' => $deviceInfo,
            'identifier' => $request->identifierParams
        ];

        $rollingCurl->prunePendingRequestQueue();
        $rollingCurl->clearCompleted();
    });
    $r->setSimultaneousLimit(100);
    $r->execute();

    echo json($lastInfo);
} else {
    require "app/header.php";
    ?>



    <div class="container">
        <?php foreach (PROXY_BALANCER as $b) : ?>
            <a class="btn black" id="re_conf" data-ipv4="<?= $b['address'] ?>"><?= $b['address'] ?> RE_CONF </a>
        <?php endforeach; ?>
        <table id="example" class="display" style="width:100%">
            <thead>
            <tr>
                <th>Ethernet</th>
                <th>IMEI</th>
                <th>Tel.</th>
                <th>INET</th>
                <th>Int.Kullanım</th>
                <th>PROXY</th>
                <th>AYAR</th>
            </tr>
            </thead>
            <tbody id="proxies">
            </tbody>
        </table>
    </div>

    <div style="width: 100%;margin-left: auto;margin-right: auto;margin-top: 25px" id="preloader">
        <center>
            <div class="preloader-wrapper big active center">
                <div class="spinner-layer spinner-blue-only">
                    <div class="circle-clipper left">
                        <div class="circle"></div>
                    </div>
                    <div class="gap-patch">
                        <div class="circle"></div>
                    </div>
                    <div class="circle-clipper right">
                        <div class="circle"></div>
                    </div>
                </div>
            </div>
        </center>
    </div>

    <script>
        $(function () {
            let connectedIMEI = [];
            $.get("index.php", {"_": true}, function (data) {
                $("div#preloader").html('');
                let myData = JSON.parse(data);
                for (let i = 0; i < myData.length; i++) {
                    let item = myData[i];

                    connectedIMEI.push(item.info.imei);
                    $("tbody#proxies").append(`<tr>
                <td>${item.identifier.network.cName}</td>
                <td>${item.info.imei}</td>
                <td>${item.info.device.phone}</td>
                <td>${item.identifier.network.inet}</td>
                <td>${item.info.charges_total}</td>
                <td>${item.identifier.proxy}</td>
                <td class="center">
                    <a id="resetProxy" class="btn blue" data-ipv4="${item.identifier.balancer.address}" data-proxy="${item.identifier.proxy}">RESTART</a>
                    <a  id="checkProxy" class="btn blue darken-2" data-ipv4="${item.identifier.balancer.address}" data-proxy="${item.identifier.proxy}">CHECK</a>
                    <a id="getSms" data-phone="${item.info.device.phone}" class="btn yellow darken-2 black-text" data-ipv4="${item.identifier.balancer.address}" data-proxy="${item.identifier.proxy}">SMS</a>
                </td>
            </tr>`);
                }
            })

            $("body").delegate("a#getSms","click",function (){
                $("tbody#sms").html('');
                $("#smsPhone").html('');
                $("#loadingModal").modal('open');
                let proxy = $(this).attr('data-proxy');
                let ipv4 = $(this).attr('data-ipv4');
                let phone = $(this).attr('data-phone');
                $.post("apiv2.php", {key: '123456', action: 'SMS', proxy: proxy, ipv4: ipv4}, function (data) {
                    $("#smsPhone").html(phone)
                    $("#loadingModal").modal('close');
                    $("#smsModal").modal('open')
                    let myData = JSON.parse(data);
                    for (let i = 0; i < myData.length; i++)
                    {
                        let item = myData[i];
                        $("tbody#sms").append(`<tr><td>${item.sender}</td><td>${item.text}</td><td>${item.date}</td></tr>`);
                    }
                });
            })

            $("body").delegate("a#resetProxy","click",function (){
                $("#loadingModal").modal('open');
                let proxy = $(this).attr('data-proxy');
                let ipv4 = $(this).attr('data-ipv4');
                $.post("apiv2.php", {key: '123456', action: 'RESET', proxy: proxy, ipv4: ipv4},function (data){
                    let myData = JSON.parse(data);
                    M.toast({html:myData.message});
                    $("#loadingModal").modal('close');
                })
            })

            $("body").delegate("a#checkProxy","click",function (){
                $("#loadingModal").modal('open');
                let proxy = $(this).attr('data-proxy');
                $.post("check.php", {proxy},function (data){
                    let myData = JSON.parse(data);
                    $("pre#proxyInfo").html(syntaxHighlight(myData));
                    $("#proxyInfoModal").modal('open');
                    $("#loadingModal").modal('close');
                })
            })

            $("a#re_conf").on('click', function () {
                let ipv4 = $(this).attr('data-ipv4');
                $.post("apiv2.php", {key: '123456', action: 'RECONF', proxy: "NO_PROXY", ipv4: ipv4},function (data){
                    let mdata = JSON.parse(data);
                    M.toast({html:mdata.message});
                })
            });

        })

        function syntaxHighlight(json) {
            if (typeof json != 'string') {
                json = JSON.stringify(json, undefined, 2);
            }
            json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
                var cls = 'number';
                if (/^"/.test(match)) {
                    if (/:$/.test(match)) {
                        cls = 'key';
                    } else {
                        cls = 'string';
                    }
                } else if (/true|false/.test(match)) {
                    cls = 'boolean';
                } else if (/null/.test(match)) {
                    cls = 'null';
                }
                return '<span class="' + cls + '">' + match + '</span>';
            });
        }
    </script>

    <div id="smsModal" class="modal">
        <div class="modal-content">
            <h4>SMS <a id="smsPhone"></a></h4>
            <table id="example" class="display" style="width:100%">
                <thead>
                <tr>
                    <th>GONDEREN</th>
                    <th>MESAJ</th>
                    <th>TARIH</th>
                </tr>
                </thead>
                <tbody id="sms">
                </tbody>
            </table>
        </div>
        <div class="modal-footer">
            <a href="#!" class="modal-close waves-effect waves-green btn-flat">KAPAT</a>
        </div>
    </div>


    <div id="proxyInfoModal" class="modal">
        <div class="modal-content">
            <pre id="proxyInfo"></pre>
        </div>
        <div class="modal-footer">
            <a href="#!" class="modal-close waves-effect waves-green btn-flat">KAPAT</a>
        </div>
    </div>

<?php } ?>
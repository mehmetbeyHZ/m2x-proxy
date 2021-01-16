<?php
require "vendor/autoload.php";
ini_set('display_errors', 1);
if (isset($_GET['_'])) {
    $r = new \RollingCurl\RollingCurl();

    foreach (PROXY_BALANCER as $balancer) {
        $data = http_build_query([
            'action' => 'NETCONF',
            'key' => $balancer['key'],
            'proxy' => "NO_PROXY"
        ]);
        $r->post("http://{$balancer['address']}/api.php", $data, [], [CURLOPT_TIMEOUT => 10], $balancer);
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
        if (!isset($server['response']['networks'])) {
            continue;
        }
        foreach ($server['response']['networks'] as $network) {
            $networkName = $network['cName'];
            if (isset($server['response']['config'][$networkName])) {
                $conf = $server['response']['config'][$networkName];
                $ip = $conf['ip'];
                $port = $conf['port'];
                $proxyUri = ROOT_PROXYUSR.':'.ROOT_PROXYUSRPWD.'@' . $ip . ':' . $port;
                $userData = ['network' => $network, 'config' => $conf, 'balancer' => $server['balancer'], 'proxy' => $proxyUri];

                $postData = http_build_query([
                    'action' => 'DATA',
                    'key' => '123456',
                    'proxy' => $proxyUri
                ]);

                $r->post("http://{$server['balancer']['address']}/api.php", $postData, [], [CURLOPT_TIMEOUT => 10], $userData);
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
    <div style="width: 100%; text-align: center;padding: 10px; background: #433efe; margin-top: -2px; margin-bottom: 5px; color: white">
        <div id="totalConn">Checking...</div>
    </div>

    <div class="container">
        <?php foreach (PROXY_BALANCER as $b) : ?>
            <a class="btn black" id="re_conf" data-ipv4="<?= $b['address'] ?>"><?= $b['address'] ?> RE_CONF </a>
        <?php endforeach; ?>
    </div>
    <div class="container">
        <div class="card wg_shadow">
            <div class="card-content">
                <div style="display: flex; justify-content: space-between;">


                    <div style="margin-top: auto; margin-bottom: auto">
                        <label>Seçilen Proxyler</label>
                        <span class="new badge blue" id="totalSelected" data-badge-caption="0"></span>
                    </div>

                    <a class='dropdown-trigger btn right blue' href='#' data-target='dropdown1'><i class="material-icons right">expand_more</i>ÇOKLU İŞLEM</a>

                </div>
            </div>
        </div>
    </div>

    <!-- Dropdown Structure -->
    <ul id='dropdown1' class='dropdown-content'>
        <li><a href="#!" id="multiAction" data-action="RESTART">RESTART</a></li>
        <li><a href="#!" id="multiAction" data-action="CHECK">CHECK</a></li>
    </ul>



    <div class="container">
        <table id="example" class="display" style="width:100%">
            <thead>
            <tr>
                <th class="center">
                    <label>
                        <input type="checkbox" id="selectProxies" />
                        <span style="padding-left: 18px!important;"></span>
                    </label>
                </th>
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
        let selectedOrderIds = [];
        $(function () {
            let connectedIMEI = [];
            $.get("index.php", {"_": true}, function (data) {
                $("div#preloader").html('');
                let myData = JSON.parse(data);
                $("div#totalConn").html(myData.length + " Connection");
                for (let i = 0; i < myData.length; i++) {
                    let item = myData[i];

                    connectedIMEI.push(item.info.imei);
                    $("tbody#proxies").append(`<tr>
                <td class="center" id="proxyI${item.info.imei}" data-balancer="${item.identifier.balancer.address}" data-bkey="${item.identifier.balancer.key}" data-proxy="${item.identifier.proxy}">
                      <label>
                        <input type="checkbox" data-imei="${item.info.imei}" class="proxySelector"/>
                        <span style="padding-left: 18px!important;"></span>
                      </label>
                </td>
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

            $("body").delegate("a#getSms", "click", function () {
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
                    for (let i = 0; i < myData.length; i++) {
                        let item = myData[i];
                        $("tbody#sms").append(`<tr><td>${item.sender}</td><td>${item.text}</td><td>${item.date}</td></tr>`);
                    }
                });
            })

            $("body").delegate("a#resetProxy", "click", function () {
                $("#loadingModal").modal('open');
                let proxy = $(this).attr('data-proxy');
                let ipv4 = $(this).attr('data-ipv4');
                $.post("apiv2.php", {key: '123456', action: 'RESET', proxy: proxy, ipv4: ipv4}, function (data) {
                    let myData = JSON.parse(data);
                    M.toast({html: myData.message});
                    $("#loadingModal").modal('close');
                })
            })

            $("body").delegate("a#checkProxy", "click", function () {
                $("#loadingModal").modal('open');
                let proxy = $(this).attr('data-proxy');
                $.post("check.php", {proxy}, function (data) {
                    let myData = JSON.parse(data);
                    $("pre#proxyInfo").html(syntaxHighlight(myData));
                    $("#proxyInfoModal").modal('open');
                    $("#loadingModal").modal('close');
                })
            })

            $("a#re_conf").on('click', function () {
                let ipv4 = $(this).attr('data-ipv4');
                $.post("apiv2.php", {key: '123456', action: 'RECONF', proxy: "NO_PROXY", ipv4: ipv4}, function (data) {
                    let mdata = JSON.parse(data);
                    M.toast({html: mdata.message});
                })
            });

            $("#selectProxies").on('click',function (e) {
                let isChecked =  $("input#selectProxies").is(':checked');
                if(isChecked)
                {
                    $("input.proxySelector").prop("checked",true);
                }else{
                    $("input.proxySelector").prop("checked",false);
                }

                while(selectedOrderIds.length > 0) {
                    selectedOrderIds.pop();
                }
                $.each($("input[class='proxySelector']:checked"),function () {
                    selectedOrderIds.push($(this).attr('data-imei'));
                });
                $("#totalSelected").attr('data-badge-caption',selectedOrderIds.length);
            });

            $("body").delegate('input.proxySelector','click',function (){
                while(selectedOrderIds.length > 0) {
                    selectedOrderIds.pop();
                }
                $.each($("input[class='proxySelector']:checked"),function () {
                    selectedOrderIds.push($(this).attr('data-imei'));
                });
                $("#totalSelected").attr('data-badge-caption',selectedOrderIds.length);
            });

        })


        function selectedOrders()
        {
            while(selectedOrderIds.length > 0) {
                selectedOrderIds.pop();
            }
            $.each($("input[class='proxySelector']:checked"),function () {
                selectedOrderIds.push($(this).attr('data-imei'));
            });
            $("#totalSelected").attr('data-badge-caption',selectedOrderIds.length);
        }

        $("a#multiAction").on('click',function (){
            selectedOrders()
            let type = $(this).attr("data-action");
            $("#loadingModal").modal('open');
            let resetItems = [];
            $("input.proxySelector").prop('checked',false);
            $("input#selectProxies").prop('checked',false);
            for (let i = 0; i < selectedOrderIds.length; i++)
            {
                let imei = selectedOrderIds[i];
                let field = $("#proxyI"+imei);
                let balancer = field.attr('data-balancer');
                let balancerKey = field.attr('data-bkey');
                let proxy = field.attr('data-proxy');

                resetItems.push({proxy,balancer,balancerKey})
            }
            $.post("multiAction.php",{data:resetItems,type},function (data){
                $("#loadingModal").modal('close');
                let resp = JSON.parse(data);
                if (type === 'CHECK')
                {
                    $("#actionInfoModal").modal('open');
                    let online  = 0;
                    let offline = 0;
                    for (let i = 0; i < resp.length; i++)
                    {
                        let item = resp[i];
                        try {
                            let itemResponse = JSON.parse(item.response);
                            online++;
                        }catch (e){
                            console.log(e);
                            offline++;
                            $("td[data-proxy=\""+item.balancer.proxy+"\"] input").prop('checked',true);
                        }
                    }
                    $("a#checkedTotalOffline").html(offline);
                    $("a#checkedTotalOnline").html(online);

                }else{
                    M.toast({html:'OK'});
                }
                selectedOrders()
            });
        });

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

    <div id="actionInfoModal" class="modal">
        <div class="modal-content">
            <h4>Online: <a id="checkedTotalOnline">0</a></h4>
            <h4>Offline: <a id="checkedTotalOffline">0</a></h4>
        </div>
        <div class="modal-footer">
            <a href="#!" class="modal-close waves-effect waves-green btn-flat">KAPAT</a>
        </div>
    </div>

<?php } ?>
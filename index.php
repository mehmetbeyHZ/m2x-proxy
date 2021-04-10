<?php

use Networking\ProxyService\IPConf;
use Networking\ProxyService\ThreeProxy;

require "vendor/autoload.php";
define("ROOTFOLDER", dirname($_SERVER['SCRIPT_NAME']));
ini_set('display_errors', 1);
$devicesDB = json_decode(file_get_contents("database/devices.json"),true);
$redis = redis();
if (isset($_GET['_'])) {

    $ip = new IPConf();
    $tp = new ThreeProxy();
    $interfaceList = [];
    $inetList      = [];
    $connections  = $ip->getAllConnections(true);
    $proxyConf = $ip->proxyConfParse();

    $systemInfo   = [];
    $usageInfo    = [];
    $fullResponse = [];

    $timeout      = 3;

    foreach ($connections as $connection)
    {
        if (isset($proxyConf[$connection["cName"]]))
        {
            $modemInterface = str_replace(".255",".1",$connection["broadcast"]);


            if ($redis->exists("RESET_PROXY_TIMEX:".trim($modemInterface)) !== 1)
            {
                $proxyPort = $proxyConf[$connection["cName"]]["ip"];
                $interfaceList[$connection["cName"]] = trim($modemInterface);
                $inetList[$connection["cName"]] = $connection["inet"];
            }
        }
    }


    $rc = new \RollingCurl\RollingCurl();
    foreach ($interfaceList as $modemName => $interface)
    {
        $uri = "http://$interface/jrd/webapi?api=GetSystemInfo";
        $modemInfo = $proxyConf[$modemName];
        $modemInfo["inet"] = $inetList[$modemName];
        $modemInfo['proxy'] = ROOT_PROXYUSR.':'.ROOT_PROXYUSRPWD.'@'.$modemInfo['ip'].":".$modemInfo['port'];
        $rc->post("http://$interface/jrd/webapi?api=GetSystemInfo",'{"jsonrpc":"2.0","method":"GetSystemInfo","params":null,"id":"13.1"}',[],[CURLOPT_TIMEOUT => $timeout],['interface' => $interface, 'modem_info' => $modemInfo]);
    }
    $rc->setCallback(function (\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl)  use(&$systemInfo){
        $data = json_decode($request->getResponseText(),true);
        $systemInfo[$request->identifierParams['interface']]['system_info'] = [
            'imei' => $data['result']['IMEI'] ?? null,
            'mac' => $data['result']['MacAddress'] ?? null
        ];
        $systemInfo[$request->identifierParams['interface']]['modem_info'] = $request->identifierParams['modem_info'];
        $rollingCurl->prunePendingRequestQueue();
        $rollingCurl->clearCompleted();
    });
    $rc->setSimultaneousLimit(100);
    $rc->execute();

    // Usage Info --------------------------------------------------------
    foreach ($systemInfo as $interface => $info)
    {
        $rc->post("http://$interface/jrd/webapi?api=GetUsageSettings",'{"jsonrpc":"2.0","method":"GetUsageSettings","params":null,"id":"7.3"}',[],[CURLOPT_TIMEOUT => $timeout],['interface' => $interface]);
    }
    $rc->setCallback(function (\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl)  use(&$systemInfo){
        $data = json_decode($request->getResponseText(),true);
        $systemInfo[$request->identifierParams['interface']]['usage_info'] = [
            'used_data' => isset($data['result']['UsedData']) ? formatSizeUnits($data['result']['UsedData']) : formatSizeUnits(0)
        ];
        $rollingCurl->prunePendingRequestQueue();
        $rollingCurl->clearCompleted();
    });
    $rc->setSimultaneousLimit(100);
    $rc->execute();

    // USAGE STATUS -------------------------------------------------
    foreach ($systemInfo as $interface => $info)
    {
        $rc->post("http://$interface/jrd/webapi?api=GetUsageSettings",'{"jsonrpc":"2.0","method":"GetSystemStatus","params":null,"id":"13.4"}',[],[CURLOPT_TIMEOUT => $timeout],['interface' => $interface]);
    }
    $rc->setCallback(function (\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl)  use(&$systemInfo,&$devicesDB){
        $data = json_decode($request->getResponseText(),true);
        $systemInfo[$request->identifierParams['interface']]['system_status'] = $data['result'] ?? [];
        $imei = $systemInfo[$request->identifierParams['interface']]['system_info']['imei'];

        $deviceInfo = [];
        $s =  array_search($imei, array_column($devicesDB,'imei'), true);
        if (is_int($s)){
            $deviceInfo = $devicesDB[$s];
        }

        $systemInfo[$request->identifierParams['interface']]['device_info'] = $deviceInfo;

        $rollingCurl->prunePendingRequestQueue();
        $rollingCurl->clearCompleted();
    });
    $rc->setSimultaneousLimit(100);
    $rc->execute();

    echo json($systemInfo);

} else {
    require "app/header.php";
    ?>

    <link rel="stylesheet" href="//cdn.datatables.net/1.10.23/css/jquery.dataTables.min.css">
    <script src="//cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js"></script>
    <script src="//cdn.datatables.net/plug-ins/1.10.22/type-detection/file-size.js"></script>
    <div style="width: 100%; text-align: center;padding: 10px; background: #433efe; margin-top: -2px; margin-bottom: 5px; color: white">
        <div id="totalConn">Checking...</div>
    </div>

    <div class="container">
        <?php foreach (PROXY_BALANCER as $b) : ?>
            <a class="btn black" id="re_conf" data-ipv4="<?= $b['address'] ?>"><?= $b['address'] ?> RE_CONF </a>
        <?php endforeach; ?>
    </div>

    <div class="container">
        <div class="card wg_shadow" style="margin-left: 5px; margin-right: 5px; max-width: 100%!important; z-index: 889;position: -webkit-sticky!important; position: sticky!important;top: 0!important;">
            <div class="card-content">
                <div style="display: flex; justify-content: space-between;">


                    <div style="margin-top: auto; margin-bottom: auto">
                        <label>Seçilen Proxyler</label>
                        <span class="new badge blue" id="totalSelected" data-badge-caption="0"></span>
                    </div>

                    <a class='dropdown-trigger btn right blue' href='#' data-target='dropdown1'><i
                                class="material-icons right">expand_more</i>ÇOKLU İŞLEM</a>

                </div>
            </div>
        </div>


    <!-- Dropdown Structure -->
        <ul id='dropdown1' class='dropdown-content'>
            <li><a href="#!" id="multiAction" data-action="RESTART">RESTART</a></li>
            <li><a href="#!" id="multiAction" data-action="CHECK">CHECK</a></li>
        </ul>


        <table id="example" class="display" style="width:100%">
            <thead>
            <tr>
                <th class="center">
                    <label>
                        <input type="checkbox" id="selectProxies"/>
                        <span style="padding-left: 18px!important;"></span>
                    </label>
                </th>
                <th>Ethernet</th>
                <th>IMEI</th>
                <th>Tel.</th>
                <th>INET</th>
                <th>Int.Kullanım</th>
                <th>Şarj</th>
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
        jQuery.fn.dataTable.ext.type.order['file-size-pre'] = function ( data ) {
            var matches = data.match( /^(\d+(?:\.\d+)?)\s*([a-z]+)/i );
            var multipliers = {
                b:  1,
                bytes: 1,
                kb: 1000,
                kib: 1024,
                mb: 1000000,
                mib: 1048576,
                gb: 1000000000,
                gib: 1073741824,
                tb: 1000000000000,
                tib: 1099511627776,
                pb: 1000000000000000,
                pib: 1125899906842624
            };

            if (matches) {
                var multiplier = multipliers[matches[2].toLowerCase()];
                return parseFloat( matches[1] ) * multiplier;
            } else {
                return -1;
            };
        };
        $(function () {


            let connectedIMEI = [];

            $.get("index.php", {"_": true}, function (data) {
                $("div#preloader").html('');
                let myData = JSON.parse(data);
                $("div#totalConn").html(Object.entries(myData).length + " Connection");
                for (const [key, value] of Object.entries(myData)) {
                    $("tbody#proxies").append(`<tr>
                <td class="center" id="proxyI${value.system_info.imei}" data-inet="${key}" data-proxy="${value.modem_info.proxy}">
                      <label>
                        <input type="checkbox" data-imei="${value.system_info.imei}" class="proxySelector"/>
                        <span style="padding-left: 18px!important;"></span>
                      </label>
                </td>
                <td>${value.modem_info.device}</td>
                <td>${value.system_info.imei}</td>
                <td>${value.device_info.phone}</td>
                <td>${value.modem_info.inet}</td>
                <td>${value.usage_info.used_data}</td>
                <td>%${value.system_status.bat_cap}</td>
                <td>${value.modem_info.proxy}</td>
                <td class="center">
                    <a  id="checkProxy" data-proxy="${value.modem_info.proxy}" data-inet="${value.modem_info.inet}" class="btn blue darken-2">CHECK</a>
                </td>
            </tr>`);
                }

                // <a id="resetProxy" class="btn blue" data-inet="${value.modem_info.inet}">RESTART</a>
                // <a id="getSms" data-phone="${value.device_info.phone}" data-inet="${value.modem_info.inet}" class="btn yellow darken-2 black-text">SMS</a>


                $("table#example").DataTable({
                    "columnDefs": [
                        { "type": "file-size", "targets": 0 }
                    ],
                    paging: false
                });
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

            $("#selectProxies").on('click', function (e) {
                let isChecked = $("input#selectProxies").is(':checked');
                if (isChecked) {
                    $("input.proxySelector").prop("checked", true);
                } else {
                    $("input.proxySelector").prop("checked", false);
                }

                while (selectedOrderIds.length > 0) {
                    selectedOrderIds.pop();
                }
                $.each($("input[class='proxySelector']:checked"), function () {
                    selectedOrderIds.push($(this).attr('data-imei'));
                });
                $("#totalSelected").attr('data-badge-caption', selectedOrderIds.length);
            });

            $("body").delegate('input.proxySelector', 'click', function () {
                while (selectedOrderIds.length > 0) {
                    selectedOrderIds.pop();
                }
                $.each($("input[class='proxySelector']:checked"), function () {
                    selectedOrderIds.push($(this).attr('data-imei'));
                });
                $("#totalSelected").attr('data-badge-caption', selectedOrderIds.length);
            });

        })


        function selectedOrders() {
            while (selectedOrderIds.length > 0) {
                selectedOrderIds.pop();
            }
            $.each($("input[class='proxySelector']:checked"), function () {
                selectedOrderIds.push($(this).attr('data-imei'));
            });
            $("#totalSelected").attr('data-badge-caption', selectedOrderIds.length);
        }

        $("a#multiAction").on('click', function () {
            selectedOrders()
            let type = $(this).attr("data-action");
            $("#loadingModal").modal('open');
            let resetItems = [];
            $("input.proxySelector").prop('checked', false);
            $("input#selectProxies").prop('checked', false);
            for (let i = 0; i < selectedOrderIds.length; i++) {
                let imei = selectedOrderIds[i];
                let field = $("#proxyI" + imei);
                let inet = field.attr('data-inet');
                let proxy = field.attr('data-proxy');

                resetItems.push({proxy, inet})
            }
            $.post("multiAction.php", {data: resetItems, type}, function (data) {
                $("#loadingModal").modal('close');
                let resp = JSON.parse(data);
                if (type === 'CHECK') {
                    $("#actionInfoModal").modal('open');
                    let online = 0;
                    let offline = 0;
                    for (let i = 0; i < resp.length; i++) {
                        let item = resp[i];
                        try {
                            let itemResponse = JSON.parse(item.response);
                            online++;
                        } catch (e) {
                            console.log(e);
                            offline++;
                            $("td[data-proxy=\"" + item.balancer.proxy + "\"] input").prop('checked', true);
                        }
                    }
                    $("a#checkedTotalOffline").html(offline);
                    $("a#checkedTotalOnline").html(online);

                } else {
                    M.toast({html: 'OK'});
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
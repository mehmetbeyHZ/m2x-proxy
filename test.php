<?php
require "vendor/autoload.php";
//$responseError = "OpenSSL SSL_connect:  SSL_ERROR_SYSCALL in connection to jsonplaceholder.typicode.com:443";
//var_dump(strpos($responseError,"SSL_ERROR_SYSCALL"));
//$mv = new \Networking\ProxyService\ModemAPI\MW40V1();
//$mv->setLocalProxy('mroot:m2x*root@192.168.3.30:8090');
//$login = $mv->login();
//print_r($mv->getUsageSettings());

//$tp = (new \TPLink\TPLinkM7200("admin","192.168.5.1"));
//$auth = $tp->authentication();
//$tp->rebootDevice($auth->getToken());


//print_r(\Networking\ProxyService\IPConf::getHomeModemName());

//function encryptx($data)
//{
//    $key = "e5dl12XYVggihggafXWf0f2YSf2Xngd1";
//    $str1 = [];
//    $encryStr = "";
//    for($i = 0; $i < strlen($data); $i++)
//    {
//        $char_i = $data[$i];
//        $num_char_i = ord($char_i);
//        $str1[2 * $i] = (ord($key[$i % strlen($key)]) & 0xf0) | (($num_char_i & 0xf) ^ (ord($key[$i % strlen($key)]) & 0xf));
//        $str1[2 * $i + 1] = (ord($key[$i % strlen($key)]) & 0xf0) | (($num_char_i >> 0xf) ^ (ord($key[$i % strlen($key)]) & 0xf));
//    }
//    for($i = 0; $i < count($str1); $i++)
//    {
//        $encryStr .= chr($str1[$i]);
//    }
//    return $encryStr;
//}


//function encrypt(str) {
//    if (str == "" || str == undefined) {
//        return "";
//    }
//    //var key = $("[name='header-meta']").attr("content");
//    var key = "e5dl12XYVggihggafXWf0f2YSf2Xngd1";
//    var str1 = [];
//    var encryStr = "";
//    for (var i = 0; i < str.length; i++) {
//        var char_i = str.charAt(i);
//        var num_char_i = char_i.charCodeAt();
//        str1[2 * i] = (key[i % key.length].charCodeAt() & 0xf0) | ((num_char_i & 0xf) ^ (key[i % key.length].charCodeAt() & 0xf));
//        str1[2 * i + 1] = (key[i % key.length].charCodeAt() & 0xf0) | ((num_char_i >> 4) ^ (key[i % key.length].charCodeAt() & 0xf));
//    }
//        for (var i = 0; i < str1.length; i++) {
//        encryStr += String.fromCharCode(str1[i]);
//    }
//        return encryStr;
//    }


//$client = new Predis\Client();
//$client->connect();
//$client->set("name","mehmets");
//$client->expire("name",30);


//$tp = new \Networking\ProxyService\ThreeProxy();
//$tp->setIPV4(\Networking\ProxyService\IPConf::getHomeINET());
//$tp = $tp->createConf(false);
//print_r($tp);
//
//print_r($tp);

//echo formatSizeUnits(571041755);




//$s = shell_exec("sudo supervisorctl start mtproxy");
////print_r($s);
//$prefix = 100;
//for ($i = 1; $i <= 10; $i++)
//{
//    $prefix++;
//    $profileName = "profile{$i}";
//    $ipv4 = "192.168.1.".$prefix;
//    shell_exec("nmcli con delete $profileName");
//    shell_exec("nmcli con add type ethernet con-name $profileName");
//}
//
//# 3proxy Conf
//


//$z = new \Networking\ProxyService\ModemAPI\ZTEMF667();
//$z->setLocalProxy("mroot:m2x*root@192.168.3.30:8091");
//$common = $z->commonStatus();
//
//preg_match("@ppp_status : '(.*?)',@si",$common,$out);
//print_r($out);
//exit;
//print_r($s);
//print_r($common);
//$z->disconnect();
//sleep(10);
//$z->connect();
//sleep(10);

// eth0 8092 2.25.244.155

// http://mw40.home/jrd/webapi?api=SetDeviceReboot

//$c = curl_init();
//$o = [
//    CURLOPT_URL => 'http://vinnwifi.home/jrd/webapi?api=GetSMSContactList',
//    CURLOPT_RETURNTRANSFER => true,
//    CURLOPT_FOLLOWLOCATION => true,
//    CURLOPT_PROXY => 'mroot:m2x*root@192.168.3.30:8090',
//    CURLOPT_HTTPHEADER => [
//        '_TclRequestVerificationKey: KSDHSDFOGQ5WERYTUIQWERTYUISDFG1HJZXCVCXBN2GDSMNDHKVKFsVBNf',
//        '_TclRequestVerificationToken: bf26mgoo9251_[\Z',
//        'Accept: text/plain, */*; q=0.01',
//        'Accept-Encoding: gzip, deflate',
//        'Accept-Language: tr-TR,tr;q=0.9,en-US;q=0.8,en;q=0.7',
//        'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
//        'Cookie: loginToken=bf26mgoo9251_%5B%5CZ',
//        'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.150 Safari/537.36'
//        ],
//    CURLOPT_COOKIE => 'loginToken=bf26mgoo9251_%5B%5CZ',
//    CURLOPT_REFERER => 'http://vinnwifi.home/default.html?version=2019-12-24-17-29',
//    CURLOPT_POSTFIELDS => '{"jsonrpc":"2.0","method":"GetSMSContactList","params":{"Page":0},"id":"6.2"}'
//
//];
//curl_setopt_array($c, $o);
//$resp = curl_exec($c);
//
//if (curl_errno($c)) {
//    $error_msg = curl_error($c);
//    print_r($error_msg);
//}
//
//curl_close($c);
//print_r($resp);

//
//preg_match_all("@var STR_AJAX_VALUE     = \"(.*?)\";@si",$resp,$out);
//print_r($out);


//}

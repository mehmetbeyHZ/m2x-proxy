<?php

use Networking\Components\Http\Session;

function redis()
{
    $redis = new Predis\Client();
    $redis->connect();
    return $redis;
}

function redisSave($key,$value,$ttl = null)
{
    $redis = redis();
    $redis->set($key,$value);
    if ($ttl !== null && is_numeric($ttl))
    {
        $redis->expire($key,$ttl);
    }
}

function redisGet($key,$default = null)
{
    $redis = redis();
    $has = $redis->exists($key);
    return $has === 1 ? $redis->get($key) : $default;
}


function session($key, $value = null)
{
    $session = new Session();
    return $session->getOrSet($key, $value);
}

function redirect($uri)
{
    header("Location: $uri");
}

function authentication(){
    if (!session('authenticated'))
    {
        http_response_code(401);
        redirect('login.php');
        exit;
    }
    return true;
}

function post($key)
{
    return $_POST[$key] ?? null;
}

function json($data)
{
    return json_encode($data);
}

function request($key)
{
    return $_REQUEST[$key] ?? null;
}

function formatSizeUnits($bytes)
{
    if ($bytes >= 1073741824)
    {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    }
    elseif ($bytes >= 1048576)
    {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    }
    elseif ($bytes >= 1024)
    {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    }
    elseif ($bytes > 1)
    {
        $bytes = $bytes . ' bytes';
    }
    elseif ($bytes == 1)
    {
        $bytes = $bytes . ' byte';
    }
    else
    {
        $bytes = '0 bytes';
    }

    return $bytes;
}

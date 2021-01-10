<?php

use Networking\Components\Http\Session;

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
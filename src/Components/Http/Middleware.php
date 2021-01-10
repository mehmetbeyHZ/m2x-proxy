<?php


namespace Networking\Components\Http;


class Middleware
{
    public $middlewares = [];

    public function setMiddleware($scriptName,$fn)
    {
        $this->middlewares[$scriptName] = $fn;
    }

    public function getMiddleware($scriptName)
    {
        if (isset($this->middlewares[$scriptName]) && is_callable($this->middlewares[$scriptName]))
        {
            return call_user_func($this->middlewares[$scriptName]);
        }
    }

}
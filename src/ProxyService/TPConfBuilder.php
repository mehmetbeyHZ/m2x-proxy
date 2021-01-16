<?php


namespace Networking\ProxyService;


class TPConfBuilder
{
    public $builder;
    public function setLine($line)
    {
        $this->builder .= $line."\n";
    }

    public function setAuth($username,$password)
    {
        $this->setLine("auth strong");
        $this->setLine("users $username:CL:$password");
    }

    public function setLogs($file = 'logs.txt')
    {
        $this->setLine("log $file");
        $this->setLine('logformat "- +_L%t.%. %N.%p %E %U %C:%c %R:%r %O %I %h %T"');
    }

    public function getConf()
    {
        return $this->builder;
    }
}
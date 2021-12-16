<?php


namespace Networking\ProxyService;


class ThreeProxy
{
    const CONF = '/etc/3proxy/3proxy.cfg';
    private $ipv4 = '192.168.3.29';
    public function setIPV4($ipv4)
    {
        $this->ipv4 = $ipv4;
    }

    public function getConf()
    {
        return file_get_contents(self::CONF);
    }

    public function setConf($data)
    {
        return file_put_contents(self::CONF, $data);
    }

    public function createRoutes()
    {
        $builder = "";
        $startPort = 8090;
        foreach ((new IPConf())->getAllConnections() as $connection) {
            if ($connection->getConnectionName() === 'wlo1') {
                continue;
            }
            if (strpos($connection->getConnectionName(),"usb") !== false){
                $builder .= "proxy -a -i{$this->ipv4} -p{$startPort} -De{$connection->getConnectionName()}\n";
                $startPort++;
            }
        }
        return $builder;
    }

    public function createConf($save = false,$ipv4 = '192.168.3.29')
    {
        $TPConfBuilder = new TPConfBuilder();
        $TPConfBuilder->setLine("monitor logs.txt");
        $TPConfBuilder->setLogs("logs2.txt");
        $TPConfBuilder->setLine("maxconn 15000");
        $TPConfBuilder->setLine("nscache 65536");
        $TPConfBuilder->setLine("timeouts 1 5 30 60 180 1800 15 60");
        $TPConfBuilder->setAuth("login1","pass1");
        $TPConfBuilder->setLine("users ".ROOT_PROXYUSR.":CL:".ROOT_PROXYUSRPWD);
        $TPConfBuilder->setLine("deny login1 * 192.168.0.1 *");
        $TPConfBuilder->setLine("allow ".ROOT_PROXYUSR." 192.168.0.1 *");
        $TPConfBuilder->setLine("allow login1");
        $TPConfBuilder->setLine("allow ".ROOT_PROXYUSR);
        $TPConfBuilder->setLine($this->createRoutes());
        $TPConfBuilder->setLine("flush");
        if ($save)
        {
            $this->setConf($TPConfBuilder->getConf());
        }
        return $TPConfBuilder->getConf();
    }

}
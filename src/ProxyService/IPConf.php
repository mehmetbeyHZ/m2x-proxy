<?php


namespace Networking\ProxyService;
use Networking\ProxyService\Models\IPConfModel;

class IPConf
{
    /**
     * @return IPConfModel[]
     */
    protected $unReadModem = ["lo","docker0","enp3s0f1","enp2s0","wlp3s0","wlo1","enp3s0","wlp4s0","wlx20e81709b6f6"];
    public function getAllConnections($asArray = false): array
    {
        $netAdapters = shell_exec("ifconfig");
        $connections = explode("\n\n",$netAdapters);
        $connectionData = [];
        $connectionDataArray = [];
        foreach (array_filter($connections) as $connection)
        {
            preg_match("@(.*?): flags=@si",$connection,$connectionName);

            preg_match("@inet (.*?) @si",$connection,$inet);

            preg_match("@broadcast (.*?) @si",$connection,$broadcast);

            preg_match("@netmask (.*?) @si",$connection,$broadcast);

            if (!in_array(trim($connectionName[1]),$this->unReadModem))
            {
                $connectionData[] = new IPConfModel( $connectionName[1] ?? 'N/A',$inet[1] ?? null,$broadcast[1] ?? null,$connection);
                $connectionDataArray[] = ['cName' => $connectionName[1] ?? 'N/A', 'inet' => $inet[1] ?? null, 'broadcast' => $broadcast[1] ?? null];
            }
        }
        return $asArray ? $connectionDataArray : $connectionData;
    }

    public function getProxyConf()
    {
        return file_get_contents("/etc/3proxy/3proxy.cfg");
    }

    public function proxyConfParse()
    {
        $proxyConf = [];
        preg_match_all("@proxy -a -i(.*?) -p(.*?) -De(.*?)\n@si", $this->getProxyConf(), $matches);
        foreach ($matches[1] as $key => $value) {
            $proxyConf[$matches[3][$key]] = [
                'ip' => $value,
                'port' => $matches[2][$key],
                'device' => $matches[3][$key]
            ];
        }
        return $proxyConf;
    }

    public static function getHomeINET()
    {
        $netAdapters = shell_exec("ifconfig");
        $connections = explode("\n\n",$netAdapters);

        foreach (array_filter($connections) as $connection)
        {
            preg_match("@inet (.*?) @si",$connection,$inet);
            if (isset($inet[1]) && strpos($inet[1],".168.3.")){
                return $inet[1];
            }
        }
        return null;
    }
}
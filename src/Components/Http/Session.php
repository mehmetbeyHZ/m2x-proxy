<?php


namespace Networking\Components\Http;


class Session
{
    /**
     * @var array
     */
    private array $session;

    /**
     * Session constructor.
     */
    public function __construct()
    {
        $this->sessionStart();
        $this->session =& $_SESSION;
    }

    /**
     *
     */
    private function sessionStart(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * @param $name
     * @param null $default
     * @return string|null|mixed
     */
    public function get($name, $default = null)
    {
        return $this->session[$name] ?? $default;
    }

    public function getOrSet($key,$value = null)
    {
        if ($value !== null){
            $this->set($key,$value);
        }
        return $this->get($key);
    }


    /**
     * @param $name
     * @return bool
     */
    public function exists($name):bool
    {
        return isset($this->session[$name]);
    }

    /**
     * @param string $name
     * @param $value
     * @return bool
     */
    public function set(string $name, $value): bool
    {
        $this->session[$name] = is_string($value) ? trim($value):$value;
        return $this->exists($name);
    }

    /**
     * @param $name
     * @return bool
     */
    public function delete($name):bool
    {
        unset($this->session[$name]);
        return  !$this->exists($name);
    }

    public function all(): array
    {
        return  $this->session;
    }



}
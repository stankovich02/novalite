<?php

namespace NovaLite\Sessions;

class Session
{
    public function __construct()
    {
        session_start();
    }
    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }
    public function all() : array
    {
        return $_SESSION;
    }
    public function has(string $key) : bool
    {
        return isset($_SESSION[$key]);
    }
    public function set(string $key, $value) : void
    {
        $_SESSION[$key] = $value;
    }
    public function remove(string|array $keys) : void
    {
        $keys = is_array($keys) ? $keys : func_get_args();
        foreach($keys as $key){
            unset($_SESSION[$key]);
        }
    }
    public function push(string $key, mixed $value) : void
    {
        $_SESSION[$key][] = $value;
    }
    public function only(string|array $keys) : array
    {
        $keys = is_array($keys) ? $keys : func_get_args();
        $data = [];
        foreach($keys as $key){
            if(isset($_SESSION[$key])){
                $data[$key] = $_SESSION[$key];
            }
        }
        return $data;
    }
    public function except(string|array $keys) : array
    {
        $keys = is_array($keys) ? $keys : func_get_args();
        $data = [];
        foreach($_SESSION as $key => $value){
            if(!in_array($key, $keys)){
                $data[$key] = $value;
            }
        }
        return $data;
    }
    public function temp(string $key, $value, int $time) : void
    {
        $_SESSION[$key] = $value;
        $_SESSION[$key . '_expire'] = time() + $time;
    }
    public function getTemp(string $key, $default = null)
    {
        if(isset($_SESSION[$key . '_expire']) && time() > $_SESSION[$key . '_expire']){
            unset($_SESSION[$key]);
            unset($_SESSION[$key . '_expire']);
            return $default;
        }
        return $_SESSION[$key] ?? $default;
    }
    public function removeTemp(string $key) : void
    {
        unset($_SESSION[$key]);
        unset($_SESSION[$key . '_expire']);
    }
    public function flash(string $key, $value) : void
    {
        $_SESSION['flash_' . $key] = $value;
    }
    public function getFlash(string $key, $default = null)
    {
        $value = $_SESSION['flash_' . $key] ?? $default;
        unset($_SESSION['flash_' . $key]);
        return $value;
    }
    public function flush() : void
    {
        session_unset();
    }
    public function notExists(string $key) : bool
    {
        return !isset($_SESSION[$key]);
    }
    public function destroy() : void
    {
        session_destroy();
    }
    public function __destruct()
    {
        foreach ($_SESSION as $key => $value) {
            if (str_starts_with($key, 'flash_')) {
                unset($_SESSION[$key]);
            }
        }
    }
}
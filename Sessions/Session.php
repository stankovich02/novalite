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
        return isset($_SESSION[$key]) || isset($_SESSION[$key . '_expire']) || isset($_SESSION['flash_' . $key]);
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
        $_SESSION['flash_keys'][] = $key; // Dodajemo ključ u listu flash ključeva
    }
    public function getFlash(string $key, $default = null)
    {
        $value = $_SESSION['flash_' . $key] ?? $default;
        unset($_SESSION['flash_' . $key]);
        return $value;
    }
    public function setErrors($errors) : void
    {
        $_SESSION['errors'] = $errors;
        $_SESSION['_errors_timestamp'] = time();
    }

    public function getErrors($default = null)
    {
        return $_SESSION['errors'] ?? $default;
    }

    public function setOldData(array $data) : void
    {
        $_SESSION['old'] = $data;
        $_SESSION['_old_timestamp'] = time();
    }

    public function getOldData($key = null, $default = null)
    {
        if ($key === null) {
            return $_SESSION['old'] ?? [];
        }
        return $_SESSION['old'][$key] ?? $default;
    }

    public function clearFormData() : void
    {
        unset($_SESSION['errors']);
        unset($_SESSION['old']);
        unset($_SESSION['_errors_timestamp']);
        unset($_SESSION['_old_timestamp']);
    }

    // Proverava da li je stranica osvežena (refresh)
    public function isPageRefresh() : bool
    {
        $currentUrl = $_SERVER['REQUEST_URI'] ?? '';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Ako je GET zahtev i imamo greške/old podatke, to je verovatno refresh
        if ($method === 'GET' && (isset($_SESSION['errors']) || isset($_SESSION['old']))) {
            return true;
        }

        return false;
    }

    // Automatski briše flash podatke na početku zahteva
    private function cleanupFlashData() : void
    {
        // Briši greške i old podatke ako je stranica osvežena
        if ($this->isPageRefresh()) {
            $this->clearFormData();
        }

        // Standardno brisanje flash podataka
        foreach ($_SESSION as $key => $value) {
            if (str_starts_with($key, 'flash_')) {
                unset($_SESSION[$key]);
            }
        }

        // Očisti listu flash ključeva
        unset($_SESSION['flash_keys']);
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
       /* foreach ($_SESSION as $key => $value) {
            if (str_starts_with($key, 'flash_')) {
                unset($_SESSION[$key]);
            }
        }*/
    }
}
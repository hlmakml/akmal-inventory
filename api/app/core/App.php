<?php

// URL routing to controller and method
class App
{
    protected $controller = 'Home';
    protected $method = 'index';
    protected $params = [];

    public function __construct()
    {
        // panggil method parseURL()
        $url = $this->parseURL();

        // tangkap index ke-0 dari array $url, jadikan sebagai controller
        // 1. cek apakah index ke-0 dari array $url ada
        if (isset($url[0])) {
            $controllerName = ucfirst(strtolower($url[0]));
            // cek apakah ada file controller dengan nama sesuai index ke-0
            if (file_exists(__DIR__ . '/../controllers/' . $controllerName . '.php')) {
                // jika ada, maka timpa index ke-0 sebagai controller
                $this->controller = $controllerName;
                // setelah itu hapus index ke-0 dari array $url
                unset($url[0]);
            }
        }

        // buat objek, yang merujuk ke class controller
        require_once __DIR__ . '/../controllers/' . $this->controller . '.php';
        $this->controller = new $this->controller;

        // tangkap index ke-1 dari array $url, jadikan sebagai method
        // 1. cek apakah index ke-1 dari array $url ada
        if (isset($url[1])) {
            // kalau ada, maka cari nama method di dalam file controllernya
            if (method_exists($this->controller, $url[1])) {
                // jika ada, maka timpa index ke-1 sebagai method
                $this->method = $url[1];
                // setelah itu hapus index ke-1 dari array $url
                unset($url[1]);
            }
        }

        // tangkap sisa index dari array $url, jadikan sebagai parameter
        // 1. cek apakah ada sisa index dari array $url
        if (!empty($url)) {
            // jika ada, maka simpan sisa index dari array $url ke dalam property $params
            $this->params = array_values($url);
        }

        // jalankan controller dan method, serta kirimkan parameter
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    public function parseURL()
    {
        if (isset($_GET['alamat'])) {
            $url = rtrim($_GET['alamat'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        }
    }
}

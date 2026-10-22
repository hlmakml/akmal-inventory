<?php

class Controller
{
    // Load view file
    public function view($view, $data = [])
    {
        require_once __DIR__ . '/../views/' . $view . '.php';
    }

    // Load model and return instance
    public function model($model)
    {
        require_once __DIR__ . '/../models/' . $model . '.php';
        return new $model;
    }
}

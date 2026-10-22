<?php

// panggil file init.php
session_start();
require_once __DIR__ . "/../app/init.php";

// buat objek, yang merujuk ke class App.php
$app = new App();

<?php

class Flasher
{
    public static function set($message, $color)
    {
        $_SESSION['flash'] = $message;
        $_SESSION['color'] = $color;
    }

    public static function get()
    {
        if (isset($_SESSION['flash'])) {

            $flash = [
                'message' => $_SESSION['flash'],
                'color'   => $_SESSION['color']
            ];

            unset($_SESSION['flash']);
            unset($_SESSION['color']);

            return $flash;
        }

        return null;
    }
}

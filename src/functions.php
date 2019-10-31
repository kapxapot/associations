<?php

if (!function_exists('dd')) {
    function dd($var)
    {
        var_dump($var);
        die();
    }
}

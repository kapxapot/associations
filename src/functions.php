<?php

if (!function_exists('dd')) {
    function dd($var)
    {
        var_dump($var);
        die();
    }
}

function toBit($value) : int
{
    return ($value === true) ? 1 : 0;
}

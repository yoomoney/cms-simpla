<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

function yooMoneyClassLoader($className)
{
    if (strncmp('YooMoneyModule', $className, 14) === 0) {
        $length = 14;
        $path = __DIR__;
    } else {
        return;
    }

    if (DIRECTORY_SEPARATOR === '/') {
        $path .= str_replace('\\', '/', substr($className, $length)) . '.php';
    } else {
        $path .= substr($className, $length) . '.php';
    }
    if (file_exists($path)) {
        require_once $path;
    }
}

spl_autoload_register('yooMoneyClassLoader');

<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit72a0a89f022b8535ec3a0ca68643c330
{
    public static $prefixLengthsPsr4 = array (
        'Y' => 
        array (
            'YooKassa\\' => 9,
        ),
        'P' => 
        array (
            'Psr\\Log\\' => 8,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'YooKassa\\' => 
        array (
            0 => __DIR__ . '/..' . '/yoomoney/yookassa-sdk-php/lib',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit72a0a89f022b8535ec3a0ca68643c330::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit72a0a89f022b8535ec3a0ca68643c330::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit72a0a89f022b8535ec3a0ca68643c330::$classMap;

        }, null, ClassLoader::class);
    }
}

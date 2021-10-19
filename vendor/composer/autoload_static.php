<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit270724bb5482e87055342b6e6f8f5826
{
    public static $files = array (
        'eccc0347283a01e62f5536bcf76b6e62' => __DIR__ . '/..' . '/wikimedia/at-ease/src/Wikimedia/Functions.php',
    );

    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'Wikimedia\\CSS\\' => 14,
            'Wikimedia\\AtEase\\' => 17,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Wikimedia\\CSS\\' => 
        array (
            0 => __DIR__ . '/..' . '/wikimedia/css-sanitizer/src',
        ),
        'Wikimedia\\AtEase\\' => 
        array (
            0 => __DIR__ . '/..' . '/wikimedia/at-ease/src/Wikimedia/AtEase',
        ),
    );

    public static $classMap = array (
        'UtfNormal\\Constants' => __DIR__ . '/..' . '/wikimedia/utfnormal/src/Constants.php',
        'UtfNormal\\Utils' => __DIR__ . '/..' . '/wikimedia/utfnormal/src/Utils.php',
        'UtfNormal\\Validator' => __DIR__ . '/..' . '/wikimedia/utfnormal/src/Validator.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit270724bb5482e87055342b6e6f8f5826::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit270724bb5482e87055342b6e6f8f5826::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit270724bb5482e87055342b6e6f8f5826::$classMap;

        }, null, ClassLoader::class);
    }
}
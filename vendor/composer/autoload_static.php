<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitfae86ef621872dc77d38baaae7018f06
{
    public static $files = array (
        'f1ec154e637ed760879a1fa0f4d9bc21' => __DIR__ . '/../..' . '/lib/db.php',
        '1d57a72899f43e0964a40c7abb65bc78' => __DIR__ . '/../..' . '/lib/reader.php',
        '23fd5a482b7afb596115364595f03498' => __DIR__ . '/../..' . '/lib/writer.php',
        '6a8700ba8d4394e1de317fcef343a1a0' => __DIR__ . '/../..' . '/lib/converter.php',
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInitfae86ef621872dc77d38baaae7018f06::$classMap;

        }, null, ClassLoader::class);
    }
}

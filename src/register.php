<?php

namespace Jaxon\Annotations;

use mindplay\annotations\AnnotationCache;
use mindplay\annotations\AnnotationManager;

use function Jaxon\jaxon;
use function php_sapi_name;
use function sys_get_temp_dir;

/**
 * Register the annotation reader into the Jaxon DI container
 *
 * @return void
 */
function _register(): void
{
    $di = jaxon()->di();

    $di->set(AnnotationReader::class, function($c) {
        $sKey = 'jaxon_annotations_cache_dir';
        $sCacheDir = $c->h($sKey) ? $c->g($sKey) : sys_get_temp_dir();
        $xAnnotationManager = new AnnotationManager();
        $xAnnotationManager->cache = new AnnotationCache($sCacheDir);

        return new AnnotationReader($xAnnotationManager);
    });

    $di->alias('metadata_reader_annotations', AnnotationReader::class);
}

function register(): void
{
    // Do nothing if running in cli.
    if(php_sapi_name() !== 'cli')
    {
        _register();
    };
}

register();

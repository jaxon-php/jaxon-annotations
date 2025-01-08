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
function register()
{
    // Do nothing if running in cli.
    if(php_sapi_name() === 'cli')
    {
        return;
    };

    $di = jaxon()->di();

    $sCacheDirKey = 'jaxon_annotations_cache_dir';
    $di->val($sCacheDirKey, sys_get_temp_dir());

    $di->set(AnnotationReader::class, function($c) use($sCacheDirKey) {
        $xAnnotationManager = new AnnotationManager();
        $xAnnotationManager->cache = new AnnotationCache($c->g($sCacheDirKey));

        return new AnnotationReader($xAnnotationManager);
    });

    $di->alias('metadata_reader_annotations', AnnotationReader::class);
}

register();

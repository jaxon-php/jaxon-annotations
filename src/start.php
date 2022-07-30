<?php

namespace Jaxon\Annotations;

use Jaxon\App\Config\ConfigEventManager;
use Jaxon\App\Config\ConfigListenerInterface;
use Jaxon\Di\Container;
use Jaxon\Plugin\AnnotationReaderInterface;
use Jaxon\Utils\Config\Config;
use mindplay\annotations\AnnotationCache;
use mindplay\annotations\AnnotationManager;

use function Jaxon\jaxon;

/**
 * Register the annotation reader into the Jaxon DI container
 *
 * @param Container $di
 * @param bool $bForce Force registration
 *
 * @return void
 */
function register(Container $di, bool $bForce = false)
{
    if(!$bForce && $di->h(AnnotationReader::class))
    {
        return;
    }

    $sCacheDirKey = 'jaxon_annotations_cache_dir';
    if(!$di->h($sCacheDirKey))
    {
        $di->val($sCacheDirKey, sys_get_temp_dir());
    }
    $di->set(AnnotationReader::class, function($c) use($sCacheDirKey) {
        $xAnnotationManager = new AnnotationManager();
        $xAnnotationManager->cache = new AnnotationCache($c->g($sCacheDirKey));
        return new AnnotationReader($xAnnotationManager);
    });
    $di->alias(AnnotationReaderInterface::class, AnnotationReader::class);
}

/**
 * Register the values into the container
 *
 * @return void
 */
function registerAnnotations()
{
    $di = jaxon()->di();
    $sEventListenerKey = AnnotationReader::class . '\\ConfigListener';
    if($di->h($sEventListenerKey))
    {
        return;
    }

    // The annotation package is installed, register the real annotation reader,
    // but only if the feature is activated in the config.
    $di->set($sEventListenerKey, function() {
        return new class implements ConfigListenerInterface
        {
            public function onChange(Config $xConfig, string $sName)
            {
                $sConfigKey = 'core.annotations.enabled';
                if(($sName === $sConfigKey || $sName === '') && $xConfig->getOption($sConfigKey))
                {
                    register(jaxon()->di());
                }
            }
        };
    });

    // Register the event listener
    $xEventManager = $di->g(ConfigEventManager::class);
    $xEventManager->addListener($sEventListenerKey);
}

// Initialize the upload handler
registerAnnotations();

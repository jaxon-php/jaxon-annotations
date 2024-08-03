<?php

/**
 * BeforeAnnotation.php
 *
 * Jaxon annotation for server side callbacks.
 *
 * @package jaxon-annotations
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-annotations
 */

namespace Jaxon\Annotations\Annotation;

/**
 * Specifies a method to be called before the one targeted by a Jaxon request.
 *
 * @usage('class' => true, 'method'=>true, 'multiple'=>true, 'inherited'=>true)
 */
class BeforeAnnotation extends HookAnnotation
{
    /**
     * @inheritDoc
     */
    protected static function getType(): string
    {
        return 'before';
    }
}

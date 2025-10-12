<?php

/**
 * AbstractAnnotation.php
 *
 * Common functions for Jaxon annotations.
 *
 * @package jaxon-annotations
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-annotations
 */

namespace Jaxon\Annotations\Annotation;

use Jaxon\App\Metadata\Metadata;
use mindplay\annotations\Annotation;
use mindplay\annotations\IAnnotationParser;

abstract class AbstractAnnotation extends Annotation implements IAnnotationParser
{
    /**
     * Save the annotation value
     *
     * @param Metadata $xMetadata
     * @param string $sMethod
     *
     * @return void
     */
    abstract public function saveValue(Metadata $xMetadata, string $sMethod = '*'): void;
}

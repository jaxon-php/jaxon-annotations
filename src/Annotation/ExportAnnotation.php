<?php

/**
 * ExportAnnotation.php
 *
 * @package jaxon-annotations
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-annotations
 */

namespace Jaxon\Annotations\Annotation;

use Jaxon\App\Metadata\Metadata;
use mindplay\annotations\AnnotationException;

use function count;
use function is_array;
use function json_decode;

/**
 * Specifies the methods to include in js export.
 *
 * @usage('class' => true)
 */
class ExportAnnotation extends AbstractAnnotation
{
    /**
     * @var array
     */
    private $aMethods = [];

    /**
     * @inheritDoc
     */
    public static function parseAnnotation($value)
    {
        $aParams = json_decode($value, true);
        return is_array($aParams) ? $aParams : [];
    }

    /**
     * @inheritDoc
     * @throws AnnotationException
     */
    public function initAnnotation(array $properties)
    {
        foreach(['base', 'only', 'except'] as $key)
        {
            if(isset($properties[$key]) && is_array($properties[$key]) &&
                count($properties[$key]) > 0)
            {
                $this->aMethods[$key] = $properties[$key];
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function saveValue(Metadata $xMetadata, string $sMethod = '*'): void
    {
        $xMetadata->export($sMethod)->setMethods($this->aMethods);
    }
}

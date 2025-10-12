<?php

/**
 * CallbackAnnotation.php
 *
 * Jaxon annotation for client side callbacks.
 *
 * @package jaxon-annotations
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-annotations
 */

namespace Jaxon\Annotations\Annotation;

use Jaxon\App\Metadata\Metadata;
use mindplay\annotations\AnnotationException;

use function count;
use function is_string;
use function preg_split;

/**
 * Specifies the javascript object to be used as callback.
 *
 * @usage('class' => true, 'method' => true, 'multiple' => true, 'inherited' => true)
 */
class CallbackAnnotation extends AbstractAnnotation
{
    /**
     * The javascript object name
     *
     * @var string
     */
    protected $sJsObject;

    /**
     * @inheritDoc
     */
    public static function parseAnnotation($value)
    {
        $aParams = preg_split('/[\s]+/', $value, 2);
        return count($aParams) === 1 ? ['name' => $aParams[0]] : ['name' => $aParams[0], 'extra' => $aParams[1]];
    }

    /**
     * @inheritDoc
     * @throws AnnotationException
     */
    public function initAnnotation(array $properties)
    {
        if(count($properties) !== 1 || !isset($properties['name']) || !is_string($properties['name']))
        {
            throw new AnnotationException('the @callback annotation requires a single string as property');
        }
        $this->sJsObject = $properties['name'];
    }

    /**
     * @inheritDoc
     */
    public function saveValue(Metadata $xMetadata, string $sMethod = '*'): void
    {
        $xMetadata->callback($sMethod)->addValue($this->sJsObject);
    }
}

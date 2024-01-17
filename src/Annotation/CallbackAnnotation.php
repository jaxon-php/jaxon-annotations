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

use mindplay\annotations\AnnotationException;

use function count;
use function is_string;
use function preg_match;
use function preg_split;

/**
 * Specifies the javascript object to be used as callback.
 *
 * @usage('class' => true, 'method'=>true, 'multiple'=>false, 'inherited'=>true)
 */
class CallbackAnnotation extends AbstractAnnotation
{
    /**
     * The name of the javascript object
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
     * Validate a javascript class name
     *
     * @param string $sJsObject    The class name
     *
     * @return bool
     */
    public function validateObjectName(string $sJsObject): bool
    {
        return (preg_match('/^([a-zA-Z][a-zA-Z0-9_]*)(\.[a-zA-Z][a-zA-Z0-9_]*)*$/', $sJsObject) > 0);
    }

    /**
     * @inheritDoc
     * @throws AnnotationException
     */
    public function initAnnotation(array $properties)
    {
        if(count($properties) !== 1 || !is_string($properties['name']))
        {
            throw new AnnotationException('the @callback annotation requires a single string as property');
        }
        if(!$this->validateObjectName($properties['name']))
        {
            throw new AnnotationException($properties['name'] . ' is not a valid "name" value for the @callback annotation');
        }
        $this->sJsObject = $properties['name'];
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'callback';
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->sJsObject;
    }
}

<?php

/**
 * AfterAnnotation.php
 *
 * Jaxon annotation for callbacks.
 *
 * @package jaxon-annotations
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-annotations
 */

namespace Jaxon\Annotations\Annotation;

use mindplay\annotations\AnnotationException;

use function array_key_exists;
use function array_keys;
use function count;
use function is_array;
use function is_string;
use function json_decode;
use function preg_match;
use function preg_split;
use function rtrim;

abstract class CallbackAnnotation extends AbstractAnnotation
{
    /**
     * @var string
     */
    protected $sMethodName = '';

    /**
     * @var array
     */
    protected $sMethodParams = [];

    /**
     *
     */
    abstract protected static function getType(): string;

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return '__' . $this->getType();
    }

    /**
     * @inheritDoc
     */
    public static function parseAnnotation($value)
    {
        $aParams = preg_split("/[\s]+/", $value, 2);
        if(count($aParams) === 1)
        {
            return ['call' => rtrim($aParams[0])];
        }
        // The second parameter must be an array of callback parameter in json format.
        return ['call' => rtrim($aParams[0]), 'with' => json_decode($aParams[1], false)];
    }

    /**
     * @param string $sMethodName
     *
     * @return bool
     */
    protected function validateMethodName(string $sMethodName): bool
    {
        return preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $sMethodName) > 0;
    }

    /**
     * @inheritDoc
     * @throws AnnotationException
     */
    public function initAnnotation(array $properties)
    {
        if(!isset($properties['call']) || !is_string($properties['call']))
        {
            throw new AnnotationException('The @' . $this->getType() .
                ' annotation requires a property "call" of type string');
        }
        if(!$this->validateMethodName($properties['call']))
        {
            throw new AnnotationException($properties['call'] .
                ' is not a valid "call" value for the @' . $this->getType() . ' annotation');
        }
        foreach(array_keys($properties) as $propName)
        {
            if($propName !== 'call' && $propName !== 'with')
            {
                throw new AnnotationException('Unknown property "' . $propName .
                    '" in the @' . $this->getType() . ' annotation');
            }
        }
        // Cannot use isset here, because it will return false in case $properties['with'] === null
        if(array_key_exists('with', $properties))
        {
            if(!is_array($properties['with']))
            {
                throw new AnnotationException('The "with" property of the @' .
                    $this->getType() . ' annotation must be of type array');
            }
            $this->sMethodParams = $properties['with'];
        }
        $this->sMethodName = $properties['call'];
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        if(is_array($this->xPrevValue))
        {
            // Add the current value to the array
            $this->xPrevValue[$this->sMethodName] = $this->sMethodParams;
            return $this->xPrevValue;
        }
        // Return the current value in an array
        return [$this->sMethodName => $this->sMethodParams];
    }
}

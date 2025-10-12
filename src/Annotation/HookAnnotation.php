<?php

/**
 * HookAnnotation.php
 *
 * Common base class for before and after annotations.
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
use function preg_split;
use function rtrim;

abstract class HookAnnotation extends AbstractAnnotation
{
    /**
     * @var string
     */
    protected $sMethod = '';

    /**
     * @var array
     */
    protected $aParams = [];

    /**
     *
     */
    abstract protected static function getType(): string;

    /**
     * @inheritDoc
     */
    public static function parseAnnotation($value)
    {
        $aParams = preg_split('/[\s]+/', $value, 2);
        if(count($aParams) === 1)
        {
            return ['call' => rtrim($aParams[0])];
        }
        // The second parameter must be an array of callback parameter in json format.
        return ['call' => rtrim($aParams[0]), 'with' => json_decode($aParams[1], false)];
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
            $this->aParams = $properties['with'];
        }
        $this->sMethod = $properties['call'];
    }
}

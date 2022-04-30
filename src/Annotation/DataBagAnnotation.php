<?php

/**
 * DataBagAnnotation.php
 *
 * Jaxon annotation for data bags.
 *
 * @package jaxon-annotations
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-annotations
 */

namespace Jaxon\Annotations\Annotation;

use mindplay\annotations\AnnotationException;

use function count;
use function is_array;
use function is_string;
use function preg_split;

/**
 * Specifies a data bag stored in the browser and included in ajax requests to a method.
 *
 * @usage('class' => true, 'method'=>true, 'multiple'=>true, 'inherited'=>true)
 */
class DataBagAnnotation extends AbstractAnnotation
{
    /**
     * The data bag name
     *
     * @var string
     */
    protected $sName = '';

    /**
     * @inheritDoc
     */
    public static function parseAnnotation($value)
    {
        $aParams = preg_split("/[\s]+/", $value, 2);
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
            throw new AnnotationException('The @databag annotation requires a property "name" of type string');
        }
        $this->sName = $properties['name'];
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'bags';
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        if(is_array($this->xPrevValue))
        {
            $this->xPrevValue[] = $this->sName; // Append the current value to the array
            return $this->xPrevValue;
        }
        return [$this->sName]; // Return the current value in an array
    }
}

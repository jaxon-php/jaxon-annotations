<?php

/**
 * DataBagAnnotation.php
 *
 * Jaxon annotation for data bags.
 *
 * @package jaxon-annotations
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-annotations
 */

namespace Jaxon\Annotations\Annotation;

use mindplay\annotations\AnnotationException;

use function count;
use function is_array;
use function is_string;

/**
 * Specifies attributes to inject into a callable object.
 *
 * @usage('class' => true, 'method'=>true, 'multiple'=>true, 'inherited'=>true)
 */
class ContainerAnnotation extends AbstractAnnotation
{
    /**
     * The attribute name
     *
     * @var string
     */
    protected $sAttr = '';

    /**
     * The attribute class
     *
     * @var string
     */
    protected $sClass = '';

    /**
     * @inheritDoc
     * @throws AnnotationException
     */
    public function initAnnotation(array $properties)
    {
        if(count($properties) != 2 ||
            !isset($properties['attr']) || !is_string($properties['attr']) ||
            !isset($properties['class']) || !is_string($properties['class']))
        {
            throw new AnnotationException('The @di annotation requires a property "attr" of type string ' .
                'and a property "class" of type string');
        }
        $this->sAttr = $properties['attr'];
        $this->sClass = $properties['class'];
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return '__di';
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        if(is_array($this->xPrevValue))
        {
            $this->xPrevValue[$this->sAttr] = $this->sClass; // Append the current value to the array
            return $this->xPrevValue;
        }
        return [$this->sAttr => $this->sClass]; // Return the current value in an array
    }
}

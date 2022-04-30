<?php

/**
 * ContainerAnnotation.php
 *
 * Jaxon annotation for DI injection.
 *
 * @package jaxon-annotations
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-annotations
 */

namespace Jaxon\Annotations\Annotation;

use mindplay\annotations\AnnotationException;
use mindplay\annotations\AnnotationFile;
use mindplay\annotations\IAnnotationFileAware;

use function count;
use function is_array;
use function is_string;
use function ltrim;
use function preg_match;
use function preg_split;
use function rtrim;

/**
 * Specifies attributes to inject into a callable object.
 *
 * @usage('class' => true, 'method'=>true, 'multiple'=>true, 'inherited'=>true)
 */
class ContainerAnnotation extends AbstractAnnotation implements IAnnotationFileAware
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
     * @var AnnotationFile
     */
    protected $xClassFile;

    /**
     * @inheritDoc
     */
    public function setAnnotationFile(AnnotationFile $file)
    {
        $this->xClassFile = $file;
    }

    /**
     * @inheritDoc
     */
    public static function parseAnnotation($value)
    {
        $aParams = preg_split("/[\s]+/", $value, 3);
        $nParamCount = count($aParams);
        if($nParamCount === 1)
        {
            return ['attr' => rtrim($aParams[0])];
        }
        if($nParamCount === 2)
        {
            return ['attr' => rtrim($aParams[0]), 'class' => $aParams[1]];
        }
        return ['attr' => rtrim($aParams[0]), 'class' => $aParams[1], 'extra' => $aParams[2]];
    }

    /**
     * @param string $sClassName
     *
     * @return bool
     */
    protected function validateClassName(string $sClassName): bool
    {
        return preg_match('/^(\\\)?([a-zA-Z][a-zA-Z0-9_]*)(\\\[a-zA-Z][a-zA-Z0-9_]*)*$/', $sClassName) > 0;
    }

    /**
     * @param string $sAttrName
     *
     * @return bool
     */
    protected function validateAttrName(string $sAttrName): bool
    {
        return preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $sAttrName) > 0;
    }

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
        if(!$this->validateAttrName($properties['attr']))
        {
            throw new AnnotationException($properties['attr'] . ' is not a valid "attr" value for the @di annotation');
        }
        if(!$this->validateClassName($properties['class']))
        {
            throw new AnnotationException($properties['class'] . ' is not a valid "class" value for the @di annotation');
        }
        $this->sAttr = $properties['attr'];
        $this->sClass = ltrim($this->xClassFile->resolveType($properties['class']), '\\');
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

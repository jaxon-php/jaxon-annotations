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

use Jaxon\Annotations\AnnotationReader;
use Jaxon\App\Metadata\Metadata;
use mindplay\annotations\AnnotationException;
use mindplay\annotations\AnnotationFile;
use mindplay\annotations\IAnnotationFileAware;

use function count;
use function is_string;
use function ltrim;
use function preg_split;

/**
 * Specifies attributes to inject into a callable object.
 *
 * @usage('class' => true, 'method' => true, 'property' => true, 'multiple' => true, 'inherited' => true)
 */
class ContainerAnnotation extends AbstractAnnotation implements IAnnotationFileAware
{
    /**
     * @var AnnotationReader
     */
    public static $xReader;

    /**
     * The annotation properties
     *
     * @var array
     */
    protected $properties = [];

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
     * @param string $sAttr
     *
     * @return void
     */
    public function setAttr(string $sAttr): void
    {
        $this->sAttr = $sAttr;
    }

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
        // We need to know which type of class member the annotation is attached to (attribute,
        // method or class), which is possible only when calling the getValue() method.
        // So we just return raw data in a custom format here.
        return ['__value__' => $value];
    }

    /**
     * @inheritDoc
     */
    public function initAnnotation(array $properties)
    {
        // We need to know which type of class member the annotation is attached to (attribute,
        // method or class), which is possible only when calling the getValue() method.
        // So we just save the properties locally here.
        $this->properties = $properties;
    }

    /**
     * @param string $sClassName
     *
     * @return string
     */
    private function getFullClassName(string $sClassName): string
    {
        return ltrim($this->xClassFile->resolveType($sClassName), '\\');
    }

    /**
     * @return void
     * @throws AnnotationException
     */
    private function parseValue()
    {
        $value = $this->properties['__value__'];
        $aParams = preg_split('/[\s]+/', $value, 3);
        $nParamCount = count($aParams);
        if($nParamCount === 1)
        {
            // For a property, the only parameter is the class. Otherwise, it is the attribute.
            if(self::$xReader->annotationIsOnProperty())
            {
                if(substr($aParams[0], 0, 1) === '$')
                {
                    throw new AnnotationException('The only property of the @di annotation must be a class name');
                }
                $this->sClass = $this->getFullClassName($aParams[0]);
                return;
            }
            if(substr($aParams[0], 0, 1) !== '$')
            {
                throw new AnnotationException('The only property of the @di annotation must be a var name');
            }
            $this->sAttr = substr($aParams[0], 1);
            return;
        }
        if($nParamCount === 2)
        {
            // For a property, having 2 parameters is not allowed.
            if(self::$xReader->annotationIsOnProperty())
            {
                throw new AnnotationException('The @di annotation accepts only one property on a class attribute');
            }

            if(substr($aParams[0], 0, 1) !== '$')
            {
                throw new AnnotationException('The only property of the @di annotation must be a var name');
            }
            if(substr($aParams[1], 0, 1) === '$')
            {
                throw new AnnotationException('The first property of the @di annotation must be a class name');
            }
            $this->sAttr = substr($aParams[0], 1);
            $this->sClass = $this->getFullClassName($aParams[1]);
            return;
        }

        throw new AnnotationException('The @di annotation only accepts one or two properties');
    }

    /**
     * @return bool
     */
    private function checkPropertiesNames(): bool
    {
        return match(count($this->properties))
        {
            0 => true,
            1 => isset($this->properties['attr']) || isset($this->properties['class']),
            2 => isset($this->properties['attr']) && isset($this->properties['class']),
            default => false,
        };
    }

    /**
     * @return void
     * @throws AnnotationException
     */
    private function parseProperties()
    {
        if(!$this->checkPropertiesNames())
        {
            throw new AnnotationException('The @di annotation accepts only "attr" or "class" as properties');
        }

        if(isset($this->properties['attr']))
        {
            if(self::$xReader->annotationIsOnProperty())
            {
                throw new AnnotationException('The @di annotation does not allow the "attr" property on class attributes');
            }
            if(!is_string($this->properties['attr']))
            {
                throw new AnnotationException('The @di annotation requires a property "attr" of type string');
            }
            $this->sAttr = $this->properties['attr'];
        }
        if(isset($this->properties['class']))
        {
            if(!is_string($this->properties['class']))
            {
                throw new AnnotationException('The @di annotation requires a property "class" of type string');
            }
            $this->sClass = $this->getFullClassName($this->properties['class']);
        }
    }

    /**
     * @inheritDoc
     */
    public function saveValue(Metadata $xMetadata, string $sMethod = '*'): void
    {
        isset($this->properties['__value__']) ? $this->parseValue() : $this->parseProperties();

        // The type in the @di annotations can be set from the values in the @var annotations
        $aPropTypes = self::$xReader->getPropTypes();
        if($this->sClass === '')
        {
            if(!isset($aPropTypes[$this->sAttr]))
            {
                throw new AnnotationException('No class defined for @di on attribute "' .
                    $this->sAttr . '".');
            }
            $this->sClass = ltrim($aPropTypes[$this->sAttr], '\\');
        }

        $xMetadata->container($sMethod)->addValue($this->sAttr, $this->sClass);
    }
}

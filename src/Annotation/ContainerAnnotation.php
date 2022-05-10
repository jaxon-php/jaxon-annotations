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
use mindplay\annotations\AnnotationManager;
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
 * @usage('class' => true, 'method'=>true, 'property'=>true, 'multiple'=>true, 'inherited'=>true)
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
     * @var string
     */
    public static $sMemberType;

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return '__di';
    }

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
        // method or class), which is possible only when calling the initAnnotation() method.
        // So we just return raw data in a custom format here.
        return ['__raw' => $value];
    }

    /**
     * @param string $value
     *
     * @return array
     * @throws AnnotationException
     */
    protected function parseValue(string $value): array
    {
        $aParams = preg_split("/[\s]+/", $value, 3);
        $nParamCount = count($aParams);
        if($nParamCount === 1)
        {
            // For a property, the only parameter is the class. Otherwise, it is the attribute.
            if(self::$sMemberType === AnnotationManager::MEMBER_PROPERTY)
            {
                $sClass = rtrim($aParams[0]);
                if(substr($sClass, 0, 1) === '$')
                {
                    throw new AnnotationException('The only property of the @di annotation must be a class name');
                }
                return ['class' => $sClass];
            }
            $sAttr = rtrim($aParams[0]);
            if(substr($sAttr, 0, 1) !== '$')
            {
                throw new AnnotationException('The only property of the @di annotation must be a var name');
            }
            return ['attr' => substr($sAttr,1)];
        }
        if($nParamCount === 2)
        {
            if(self::$sMemberType === AnnotationManager::MEMBER_PROPERTY)
            {
                throw new AnnotationException('The @di annotation accepts only one property on a class attribute');
            }
            $sAttr = rtrim($aParams[0]);
            if(substr($sAttr, 0, 1) !== '$')
            {
                throw new AnnotationException('The only property of the @di annotation must be a var name');
            }
            $sClass = rtrim($aParams[1]);
            if(substr($sClass, 0, 1) === '$')
            {
                throw new AnnotationException('The first property of the @di annotation must be a class name');
            }
            // For a property, having 2 parameters is not allowed.
            return ['attr' => substr($sAttr,1), 'class' => $sClass];
        }

        throw new AnnotationException('The @di annotation only accepts one or two properties');
    }

    /**
     * @inheritDoc
     * @throws AnnotationException
     */
    public function initAnnotation(array $properties)
    {
        if(isset($properties['__raw']))
        {
            $properties = $this->parseValue($properties['__raw']);
        }
        $nCount = count($properties);
        if($nCount > 2 ||
            ($nCount === 2 && !(isset($properties['attr']) && isset($properties['class']))) ||
            ($nCount === 1 && !(isset($properties['attr']) || isset($properties['class']))))
        {
            throw new AnnotationException('The @di annotation accepts only "attr" or "class" as properties');
        }

        if(isset($properties['attr']))
        {
            if(self::$sMemberType === AnnotationManager::MEMBER_PROPERTY)
            {
                throw new AnnotationException('The @di annotation does not allow the "attr" property on class attributes');
            }
            if(!is_string($properties['attr']))
            {
                throw new AnnotationException('The @di annotation requires a property "attr" of type string');
            }
            $this->sAttr = $properties['attr'];
        }
        if(isset($properties['class']))
        {
            if(!is_string($properties['class']))
            {
                throw new AnnotationException('The @di annotation requires a property "class" of type string');
            }
            $this->sClass = ltrim($this->xClassFile->resolveType($properties['class']), '\\');
        }
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
    public function getValue()
    {
        // The type in the @di annotations can be set from the values in the @var annotations
        $aPropTypes = $this->xReader->getPropTypes();
        if($this->sClass === '' && isset($aPropTypes[$this->sAttr]))
        {
            $this->sClass = ltrim($aPropTypes[$this->sAttr], '\\');
        }

        if(!$this->validateAttrName($this->sAttr))
        {
            throw new AnnotationException($this->sAttr . ' is not a valid "attr" value for the @di annotation');
        }
        if(!$this->validateClassName($this->sClass))
        {
            throw new AnnotationException($this->sClass . ' is not a valid "class" value for the @di annotation');
        }
        if(is_array($this->xPrevValue))
        {
            $this->xPrevValue[$this->sAttr] = $this->sClass; // Append the current value to the array
            return $this->xPrevValue;
        }
        return [$this->sAttr => $this->sClass]; // Return the current value in an array
    }
}

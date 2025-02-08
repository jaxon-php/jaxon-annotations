<?php

/**
 * AnnotationReader.php
 *
 * Jaxon annotation reader.
 *
 * @package jaxon-annotations
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-annotations
 */

namespace Jaxon\Annotations;

use Jaxon\App\Metadata\InputDataInterface;
use Jaxon\App\Metadata\Metadata;
use Jaxon\App\Metadata\MetadataInterface;
use Jaxon\App\Metadata\MetadataReaderInterface;
use Jaxon\Annotations\Annotation\AbstractAnnotation;
use Jaxon\Annotations\Annotation\AfterAnnotation;
use Jaxon\Annotations\Annotation\BeforeAnnotation;
use Jaxon\Annotations\Annotation\CallbackAnnotation;
use Jaxon\Annotations\Annotation\DataBagAnnotation;
use Jaxon\Annotations\Annotation\ExcludeAnnotation;
use Jaxon\Annotations\Annotation\UploadAnnotation;
use Jaxon\Annotations\Annotation\ContainerAnnotation;
use Jaxon\Exception\SetupException;
use mindplay\annotations\AnnotationException;
use mindplay\annotations\AnnotationManager;
use mindplay\annotations\standard\VarAnnotation;

use function array_filter;
use function array_merge;
use function count;
use function is_a;

class AnnotationReader implements MetadataReaderInterface
{
    /**
     * @var AnnotationManager
     */
    protected $xManager;

    /**
     * Properties types, read from the "var" annotations.
     *
     * @var array
     */
    protected $aPropTypes;

    /**
     * The type of the class member being currently processed.
     *
     * @var string
     */
    protected $sCurrMemberType;

    /**
     * The constructor
     *
     * @param AnnotationManager $xManager
     */
    public function __construct(AnnotationManager $xManager)
    {
        $this->xManager = $xManager;
        $this->xManager->registry['upload'] = UploadAnnotation::class;
        $this->xManager->registry['databag'] = DataBagAnnotation::class;
        $this->xManager->registry['exclude'] = ExcludeAnnotation::class;
        $this->xManager->registry['before'] = BeforeAnnotation::class;
        $this->xManager->registry['after'] = AfterAnnotation::class;
        $this->xManager->registry['di'] = ContainerAnnotation::class;
        $this->xManager->registry['callback'] = CallbackAnnotation::class;
        // Missing standard annotations.
        // We need to define this, otherwise they throw an exception, and make the whole processing fail.
        $this->xManager->registry['const'] = false;
        $this->xManager->registry['inheritDoc'] = false;
        $this->xManager->registry['template'] = false;
        $this->xManager->registry['param-closure-this'] = false;
    }

    /**
     * @return array
     */
    public function getPropTypes(): array
    {
        return $this->aPropTypes;
    }

    /**
     * @return bool
     */
    public function annotationIsOnProperty(): bool
    {
        return $this->sCurrMemberType === AnnotationManager::MEMBER_PROPERTY;
    }

    /**
     * @param array $aAnnotations
     *
     * @return array<array>
     * @throws AnnotationException
     */
    private function getMembersAttrs(array $aAnnotations): array
    {
        // Only keep the annotations declared in this package.
        $aAnnotations = array_filter($aAnnotations, function($xAnnotation) {
            return is_a($xAnnotation, AbstractAnnotation::class);
        });

        $aAttributes = [];
        foreach($aAnnotations as $xAnnotation)
        {
            $xAnnotation->setReader($this);
            $sName = $xAnnotation->getName();
            $xAnnotation->setPrevValue($aAttributes[$sName] ?? null);
            $xValue = $xAnnotation->getValue();
            if($sName === 'protected' && !$xValue)
            {
                // Ignore annotation @exclude with value false
                continue;
            }
            $aAttributes[$sName] = $xValue;
        }
        return $aAttributes;
    }

    /**
     * @param string $sClass
     *
     * @return array<array>
     * @throws AnnotationException
     */
    private function getClassAttrs(string $sClass): array
    {
        return $this->getMembersAttrs($this->xManager->getClassAnnotations($sClass));
    }

    /**
     * @param string $sClass
     * @param string $sMethod
     *
     * @return array<array>
     * @throws AnnotationException
     */
    private function getMethodAttrs(string $sClass, string $sMethod): array
    {
        return  $this->getMembersAttrs($this->xManager->getMethodAnnotations($sClass, $sMethod));
    }

    /**
     * @param string $sClass
     * @param string $sProperty
     *
     * @return array<array>
     * @throws AnnotationException
     */
    private function getPropertyAttrs(string $sClass, string $sProperty): array
    {
        /** @var array<ContainerAnnotation> */
        $aAnnotations = $this->xManager->getPropertyAnnotations($sClass, $sProperty);
        // Only keep the annotations declared in this package.
        $aAnnotations = array_filter($aAnnotations, function($xAnnotation) use($sProperty) {
            // Save the property type
            if(is_a($xAnnotation, VarAnnotation::class))
            {
                $this->aPropTypes[$sProperty] = $xAnnotation->type;
            }
            // Only container annotations are allowed on properties
            return is_a($xAnnotation, ContainerAnnotation::class);
        });

        $aAttributes = [];
        foreach($aAnnotations as $xAnnotation)
        {
            $xAnnotation->setReader($this);
            $xAnnotation->setAttr($sProperty);
            $sName = $xAnnotation->getName();
            $xAnnotation->setPrevValue($aAttributes[$sName] ?? null);
            $aAttributes[$sName] = $xAnnotation->getValue();
        }
        return $aAttributes;
    }

    /**
     * @inheritDoc
     * @throws SetupException
     */
    public function getAttributes(InputDataInterface $xInput): ?MetadataInterface
    {
        $this->aPropTypes = [];
        $sClass = $xInput->getReflectionClass()->getName();

        try
        {
            // Processing properties annotations
            $this->sCurrMemberType = AnnotationManager::MEMBER_PROPERTY;

            $aPropAttrs = [];
            // Properties annotations
            foreach($xInput->getProperties() as $sProperty)
            {
                $aPropertyAttrs = $this->getPropertyAttrs($sClass, $sProperty);
                foreach($aPropertyAttrs as $sName => $xValue)
                {
                    $aPropAttrs[$sName] = array_merge($aPropAttrs[$sName] ?? [], $xValue);
                }
            }

            // Processing class annotations
            $this->sCurrMemberType = AnnotationManager::MEMBER_CLASS;

            $aClassAttrs = $this->getClassAttrs($sClass);
            if(isset($aClassAttrs['protected']))
            {
                // The entire class is not to be exported.
                return new Metadata(true, [], []);
            }

            // Merge attributes and class annotations
            foreach($aPropAttrs as $sName => $xValue)
            {
                $aClassAttrs[$sName] = array_merge($aClassAttrs[$sName] ?? [], $xValue);
            }

            // Processing methods annotations
            $this->sCurrMemberType = AnnotationManager::MEMBER_METHOD;

            $aAttributes = count($aClassAttrs) > 0 ? ['*' => $aClassAttrs] : [];
            $aProtected = [];
            foreach($xInput->getMethods() as $sMethod)
            {
                $aMethodAttrs = $this->getMethodAttrs($sClass, $sMethod);
                if(isset($aMethodAttrs['protected']))
                {
                    $aProtected[] = $sMethod; // The method is not to be exported.
                }
                elseif(count($aMethodAttrs) > 0)
                {
                    $aAttributes[$sMethod] = $aMethodAttrs;
                }
            }
            return new Metadata(false, $aAttributes, $aProtected);
        }
        catch(AnnotationException $e)
        {
            throw new SetupException($e->getMessage());
        }
    }
}

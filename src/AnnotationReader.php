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

use Jaxon\App\Metadata\InputData;
use Jaxon\App\Metadata\Metadata;
use Jaxon\App\Metadata\MetadataReaderInterface;
use Jaxon\Annotations\Annotation\AbstractAnnotation;
use Jaxon\Annotations\Annotation\AfterAnnotation;
use Jaxon\Annotations\Annotation\BeforeAnnotation;
use Jaxon\Annotations\Annotation\CallbackAnnotation;
use Jaxon\Annotations\Annotation\DatabagAnnotation;
use Jaxon\Annotations\Annotation\ExcludeAnnotation;
use Jaxon\Annotations\Annotation\ExportAnnotation;
use Jaxon\Annotations\Annotation\UploadAnnotation;
use Jaxon\Annotations\Annotation\ContainerAnnotation;
use Jaxon\Exception\SetupException;
use mindplay\annotations\AnnotationException;
use mindplay\annotations\AnnotationManager;
use mindplay\annotations\standard\VarAnnotation;

use function array_filter;
use function count;
use function is_a;

class AnnotationReader implements MetadataReaderInterface
{
    /**
     * @var AnnotationManager
     */
    protected $xManager;

    /**
     * @var Metadata
     */
    protected $xMetadata;

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
        $this->xManager->registry['databag'] = DatabagAnnotation::class;
        $this->xManager->registry['exclude'] = ExcludeAnnotation::class;
        $this->xManager->registry['export'] = ExportAnnotation::class;
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
     * @param string $sClass
     *
     * @return void
     * @throws AnnotationException
     */
    private function getClassAttrs(string $sClass): void
    {
        // Only keep the annotations declared in this package.
        /** @var array<AbstractAnnotation> */
        $aAnnotations = array_filter(
            $this->xManager->getClassAnnotations($sClass),
            fn($xAnnotation) => is_a($xAnnotation, AbstractAnnotation::class)
        );
        // First check if the class is excluded.
        foreach($aAnnotations as $xAnnotation)
        {
            if(is_a($xAnnotation, ExcludeAnnotation::class))
            {
                $xAnnotation->saveValue($this->xMetadata);
            }
        }
        if($this->xMetadata->isExcluded())
        {
            return;
        }

        foreach($aAnnotations as $xAnnotation)
        {
            if(!is_a($xAnnotation, ExcludeAnnotation::class))
            {
                $xAnnotation->saveValue($this->xMetadata);
            }
        }
    }

    /**
     * @param string $sClass
     * @param string $sProperty
     *
     * @return void
     * @throws AnnotationException
     */
    private function getPropertyAttrs(string $sClass, string $sProperty): void
    {
        /** @var array<ContainerAnnotation> */
        // Only keep the annotations declared in this package.
        $aAnnotations = array_filter(
            $this->xManager->getPropertyAnnotations($sClass, $sProperty),
            function($xAnnotation) use($sProperty) {
                // Save the property type
                if(is_a($xAnnotation, VarAnnotation::class))
                {
                    $this->aPropTypes[$sProperty] = $xAnnotation->type;
                }
                // Only container annotations are allowed on properties
                return is_a($xAnnotation, ContainerAnnotation::class);
            }
        );
        if(count($aAnnotations) > 1)
        {
            throw new AnnotationException('Only one @di annotation is allowed on a property');
        }

        foreach($aAnnotations as $xAnnotation)
        {
            $xAnnotation->setAttr($sProperty);
            $xAnnotation->saveValue($this->xMetadata);
        }
    }

    /**
     * @param string $sClass
     * @param string $sMethod
     *
     * @return void
     * @throws AnnotationException
     */
    private function getMethodAttrs(string $sClass, string $sMethod): void
    {
        // Only keep the annotations declared in this package.
        /** @var array<AbstractAnnotation> */
        $aAnnotations = array_filter(
            $this->xManager->getMethodAnnotations($sClass, $sMethod),
            fn($xAnnotation) => is_a($xAnnotation, AbstractAnnotation::class)
        );
        foreach($aAnnotations as $xAnnotation)
        {
            $xAnnotation->saveValue($this->xMetadata, $sMethod);
        }
    }

    /**
     * @inheritDoc
     * @throws SetupException
     */
    public function getAttributes(InputData $xInput): Metadata
    {
        ContainerAnnotation::$xReader = $this;
        $this->aPropTypes = [];
        $this->xMetadata = new Metadata();
        $sClass = $xInput->getReflectionClass()->getName();

        try
        {
            // Processing class annotations
            $this->sCurrMemberType = AnnotationManager::MEMBER_CLASS;

            $this->getClassAttrs($sClass);
            if($this->xMetadata->isExcluded())
            {
                // The entire class is not to be exported.
                return $this->xMetadata;
            }

            // Processing properties annotations
            $this->sCurrMemberType = AnnotationManager::MEMBER_PROPERTY;

            // Properties annotations
            foreach($xInput->getProperties() as $sProperty)
            {
                $this->getPropertyAttrs($sClass, $sProperty);
            }

            // Processing methods annotations
            $this->sCurrMemberType = AnnotationManager::MEMBER_METHOD;

            foreach($xInput->getMethods() as $sMethod)
            {
                $this->getMethodAttrs($sClass, $sMethod);
            }

            return $this->xMetadata;
        }
        catch(AnnotationException $e)
        {
            throw new SetupException($e->getMessage());
        }
    }
}

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

use Jaxon\Annotations\Annotation\AbstractAnnotation;
use Jaxon\Annotations\Annotation\AfterAnnotation;
use Jaxon\Annotations\Annotation\BeforeAnnotation;
use Jaxon\Annotations\Annotation\DataBagAnnotation;
use Jaxon\Annotations\Annotation\ExcludeAnnotation;
use Jaxon\Annotations\Annotation\UploadAnnotation;
use Jaxon\Annotations\Annotation\ContainerAnnotation;
use Jaxon\Di\Container;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\AnnotationReaderInterface;
use mindplay\annotations\AnnotationCache;
use mindplay\annotations\AnnotationException;
use mindplay\annotations\AnnotationManager;
use mindplay\annotations\standard\VarAnnotation;

use function array_filter;
use function array_merge;
use function count;
use function is_a;
use function sys_get_temp_dir;

class AnnotationReader implements AnnotationReaderInterface
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
    protected $sMemberType;

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
        // Missing standard annotations.
        // We need to define this, otherwise they throw an exception, and make the whole processing fail.
        $this->xManager->registry['const'] = false;
        $this->xManager->registry['inheritDoc'] = false;
    }

    /**
     * Register the annotation reader into the Jaxon DI container
     *
     * @param Container $di
     * @param bool $bForce Force registration
     *
     * @return void
     */
    public static function register(Container $di, bool $bForce = false)
    {
        if(!$bForce && $di->h(AnnotationReader::class))
        {
            return;
        }
        $sCacheDirKey = 'jaxon_annotations_cache_dir';
        if(!$di->h($sCacheDirKey))
        {
            $di->val($sCacheDirKey, sys_get_temp_dir());
        }
        $di->set(AnnotationReader::class, function($c) use($sCacheDirKey) {
            $xAnnotationManager = new AnnotationManager();
            $xAnnotationManager->cache = new AnnotationCache($c->g($sCacheDirKey));
            return new AnnotationReader($xAnnotationManager);
        });
        $di->alias(AnnotationReaderInterface::class, AnnotationReader::class);
    }

    /**
     * @return array
     */
    public function getPropTypes(): array
    {
        return $this->aPropTypes;
    }

    /**
     * @return string
     */
    public function getMemberType(): string
    {
        return $this->sMemberType;
    }

    /**
     * @param array $aAnnotations
     *
     * @return AbstractAnnotation[]
     * @throws AnnotationException
     */
    private function filterAnnotations(array $aAnnotations): array
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
            if($sName !== 'protected' || ($xValue)) // Ignore annotation @exclude with value false
            {
                $aAttributes[$sName] = $xValue;
            }
        }
        return $aAttributes;
    }

    /**
     * @param string $sProperty
     * @param array $aAnnotations
     *
     * @return array
     * @throws AnnotationException
     */
    private function propAnnotations(string $sProperty, array $aAnnotations): array
    {
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

        $nCount = count($aAnnotations);
        if($nCount === 0)
        {
            return ['', null];
        }
        if($nCount > 1)
        {
            throw new AnnotationException('Only one @di annotation is allowed on a property');
        }

        $xAnnotation = $aAnnotations[0];
        $xAnnotation->setReader($this);
        $xAnnotation->setAttr($sProperty);
        return [$xAnnotation->getName(), $xAnnotation->getValue()];
    }

    /**
     * Get the class attributes from its annotations
     *
     * @param string $sClass
     * @param array $aMethods
     * @param array $aProperties
     *
     * @return array
     * @throws SetupException
     */
    public function getAttributes(string $sClass, array $aMethods = [], array $aProperties = []): array
    {
        try
        {
            // Processing properties annotations
            $this->sMemberType = AnnotationManager::MEMBER_PROPERTY;

            $this->aPropTypes = [];
            $aPropAttrs = [];
            // Properties annotations
            foreach($aProperties as $sProperty)
            {
                [$sName, $xValue] = $this->propAnnotations($sProperty, $this->xManager->getPropertyAnnotations($sClass, $sProperty));
                if($xValue !== null)
                {
                    $aPropAttrs[$sName] = array_merge($aPropAttrs[$sName] ?? [], $xValue);
                }
            }

            // Processing class annotations
            $this->sMemberType = AnnotationManager::MEMBER_CLASS;

            $aClassAttrs = $this->filterAnnotations($this->xManager->getClassAnnotations($sClass));
            if(isset($aClassAttrs['protected']))
            {
                return [true, [], []]; // The entire class is not to be exported.
            }

            // Merge attributes and class annotations
            foreach($aPropAttrs as $sName => $xValue)
            {
                $aClassAttrs[$sName] = array_merge($aClassAttrs[$sName] ?? [], $xValue);
            }

            // Processing methods annotations
            $this->sMemberType = AnnotationManager::MEMBER_METHOD;

            $aAttributes = count($aClassAttrs) > 0 ? ['*' => $aClassAttrs] : [];
            $aProtected = [];
            foreach($aMethods as $sMethod)
            {
                $aMethodAttrs = $this->filterAnnotations($this->xManager->getMethodAnnotations($sClass, $sMethod));
                if(isset($aMethodAttrs['protected']))
                {
                    $aProtected[] = $sMethod; // The method is not to be exported.
                }
                elseif(count($aMethodAttrs) > 0)
                {
                    $aAttributes[$sMethod] = $aMethodAttrs;
                }
            }
            return [false, $aAttributes, $aProtected];
        }
        catch(AnnotationException $e)
        {
            throw new SetupException($e->getMessage());
        }
    }
}

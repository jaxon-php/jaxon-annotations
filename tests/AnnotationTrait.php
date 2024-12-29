<?php

namespace Jaxon\Annotations\Tests;

use Jaxon\App\Metadata\InputData;
use Jaxon\App\Metadata\MetadataInterface;
use ReflectionClass;

use function Jaxon\jaxon;

trait AnnotationTrait
{
    /**
     * Get the metadata from a given class
     *
     * @param ReflectionClass|string $xClass
     * @param array $aMethods
     * @param array $aProperties
     *
     * @return MetadataInterface|null
     */
    public function getAttributes(ReflectionClass|string $xClass,
        array $aMethods = [], array $aProperties = []): ?MetadataInterface
    {
        $xInputData = new InputData($xClass, $aMethods, $aProperties);
        $xMetadataReader = jaxon()->di()->getMetadataReader('annotations');
        return $xMetadataReader->getAttributes($xInputData);
    }
}

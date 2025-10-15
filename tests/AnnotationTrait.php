<?php

namespace Jaxon\Annotations\Tests;

use Jaxon\App\Metadata\InputData;
use Jaxon\App\Metadata\Metadata;
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
     * @return Metadata|null
     */
    public function getAttributes(ReflectionClass|string $xClass,
        array $aMethods = [], array $aProperties = []): ?Metadata
    {
        $xInputData = new InputData($xClass, $aMethods, $aProperties);
        $xMetadataReader = jaxon()->di()->getMetadataReader('annotations');
        return $xMetadataReader->getAttributes($xInputData);
    }
}

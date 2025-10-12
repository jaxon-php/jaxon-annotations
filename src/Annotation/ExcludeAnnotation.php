<?php

/**
 * ExcludeAnnotation.php
 *
 * Jaxon annotation for exclude classes or methods from js export.
 *
 * @package jaxon-annotations
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-annotations
 */

namespace Jaxon\Annotations\Annotation;

use Jaxon\App\Metadata\Metadata;
use mindplay\annotations\AnnotationException;

use function count;
use function is_bool;

/**
 * Specifies if a class or method is excluded from js export.
 *
 * @usage('class' => true, 'method' => true)
 */
class ExcludeAnnotation extends AbstractAnnotation
{
    /**
     * @var bool
     */
    protected $bValue;

    /**
     * @inheritDoc
     */
    public static function parseAnnotation($value)
    {
        return [$value === 'true' ? true : ($value === 'false' ? false : $value)];
    }

    /**
     * @inheritDoc
     * @throws AnnotationException
     */
    public function initAnnotation(array $properties)
    {
        if(count($properties) !== 0 && (count($properties) !== 1
            || !isset($properties[0]) || !is_bool($properties[0])))
        {
            throw new AnnotationException('the @exclude annotation requires a single boolean or no property');
        }
        $this->bValue = $properties[0] ?? true;
    }

    /**
     * @inheritDoc
     */
    public function saveValue(Metadata $xMetadata, string $sMethod = '*'): void
    {
        $xMetadata->exclude($sMethod)->setValue($this->bValue);
    }
}

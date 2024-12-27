<?php

namespace Jaxon\Annotations\Tests\TestAnnotation;

use Jaxon\Annotations\AnnotationReader;
use Jaxon\Annotations\Tests\App\Ajax\TraitAnnotated;
use Jaxon\Exception\SetupException;
use PHPUnit\Framework\TestCase;

use function Jaxon\jaxon;
use function Jaxon\Annotations\registerAnnotationsReader;

class TraitAnnotationTest extends TestCase
{
    /**
     * @var string
     */
    protected $sCacheDir;

    /**
     * @var AnnotationReader
     */
    protected $xAnnotationReader;

    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        $this->sCacheDir = __DIR__ . '/../tmp';
        @mkdir($this->sCacheDir);

        jaxon()->di()->getPluginManager()->registerPlugins();
        registerAnnotationsReader();

        jaxon()->di()->val('jaxon_annotations_cache_dir', $this->sCacheDir);
        $this->xAnnotationReader = jaxon()->di()->getMetadataReader('annotations');
    }

    /**
     * @throws SetupException
     */
    public function tearDown(): void
    {
        jaxon()->reset();
        parent::tearDown();

        // Delete the temp dir and all its content
        $aFiles = scandir($this->sCacheDir);
        foreach ($aFiles as $sFile)
        {
            if($sFile !== '.' && $sFile !== '..')
            {
                @unlink($this->sCacheDir . DIRECTORY_SEPARATOR . $sFile);
            }
        }
        @rmdir($this->sCacheDir);
    }

    /**
     * @throws SetupException
     */
    public function testTraitAnnotation()
    {
        $xMetadata = $this->xAnnotationReader->getAttributes(TraitAnnotated::class, []);
        $bExcluded = $xMetadata->isExcluded();
        $aProperties = $xMetadata->getProperties();

        $this->assertFalse($bExcluded);

        $this->assertCount(1, $aProperties);
        $this->assertArrayHasKey('*', $aProperties);
        $this->assertCount(4, $aProperties['*']);
        $this->assertArrayHasKey('bags', $aProperties['*']);
        $this->assertArrayHasKey('__before', $aProperties['*']);
        $this->assertArrayHasKey('__after', $aProperties['*']);

        $this->assertCount(2, $aProperties['*']['bags']);
        $this->assertEquals('user.name', $aProperties['*']['bags'][0]);
        $this->assertEquals('page.number', $aProperties['*']['bags'][1]);

        $this->assertCount(2, $aProperties['*']['__before']);
        $this->assertArrayHasKey('funcBefore1', $aProperties['*']['__before']);
        $this->assertArrayHasKey('funcBefore2', $aProperties['*']['__before']);
        $this->assertIsArray($aProperties['*']['__before']['funcBefore1']);
        $this->assertIsArray($aProperties['*']['__before']['funcBefore2']);

        $this->assertCount(3, $aProperties['*']['__after']);
        $this->assertArrayHasKey('funcAfter1', $aProperties['*']['__after']);
        $this->assertArrayHasKey('funcAfter2', $aProperties['*']['__after']);
        $this->assertArrayHasKey('funcAfter3', $aProperties['*']['__after']);
        $this->assertIsArray($aProperties['*']['__after']['funcAfter1']);
        $this->assertIsArray($aProperties['*']['__after']['funcAfter2']);
        $this->assertIsArray($aProperties['*']['__after']['funcAfter3']);

        $this->assertCount(3, $aProperties['*']['__di']);
        $this->assertArrayHasKey('colorService', $aProperties['*']['__di']);
        $this->assertArrayHasKey('textService', $aProperties['*']['__di']);
        $this->assertArrayHasKey('fontService', $aProperties['*']['__di']);
        $this->assertEquals('Jaxon\Annotations\Tests\Service\ColorService', $aProperties['*']['__di']['colorService']);
        $this->assertEquals('Jaxon\Annotations\Tests\Service\TextService', $aProperties['*']['__di']['textService']);
        $this->assertEquals('Jaxon\Annotations\Tests\App\Ajax\FontService', $aProperties['*']['__di']['fontService']);
    }
}

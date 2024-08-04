<?php

namespace Jaxon\Annotations\Tests\TestAnnotation;

use Jaxon\Annotations\AnnotationReader;
use Jaxon\Annotations\Tests\App\Ajax\AttrAnnotated;
use Jaxon\Exception\SetupException;
use PHPUnit\Framework\TestCase;

use function Jaxon\jaxon;
use function Jaxon\Annotations\registerAnnotationsReader;

class AttrAnnotationTest extends TestCase
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
    public function testContainerAnnotation()
    {
        $aAttributes = $this->xAnnotationReader->getAttributes(AttrAnnotated::class,
            ['attrVar'], ['colorService', 'fontService', 'textService']);
        $bExcluded = $aAttributes[0];
        $aProperties = $aAttributes[1];

        $this->assertFalse($bExcluded);

        $this->assertCount(1, $aProperties);
        $this->assertArrayHasKey('attrVar', $aProperties);
        $this->assertCount(3, $aProperties['attrVar']['__di']);
        $this->assertEquals('Jaxon\Annotations\Tests\Service\ColorService', $aProperties['attrVar']['__di']['colorService']);
        $this->assertEquals('Jaxon\Annotations\Tests\App\Ajax\FontService', $aProperties['attrVar']['__di']['fontService']);
        $this->assertEquals('Jaxon\Annotations\Tests\Service\TextService', $aProperties['attrVar']['__di']['textService']);
    }

    /**
     * @throws SetupException
     */
    public function testContainerDocBlockAnnotation()
    {
        $aAttributes = $this->xAnnotationReader->getAttributes(AttrAnnotated::class,
            ['attrDbVar'], ['colorService', 'fontService', 'textService']);
        $bExcluded = $aAttributes[0];
        $aProperties = $aAttributes[1];

        $this->assertFalse($bExcluded);

        $this->assertCount(1, $aProperties);
        $this->assertArrayHasKey('attrDbVar', $aProperties);
        $this->assertCount(3, $aProperties['attrDbVar']['__di']);
        $this->assertEquals('Jaxon\Annotations\Tests\Service\ColorService', $aProperties['attrDbVar']['__di']['colorService']);
        $this->assertEquals('Jaxon\Annotations\Tests\App\Ajax\FontService', $aProperties['attrDbVar']['__di']['fontService']);
        $this->assertEquals('Jaxon\Annotations\Tests\Service\TextService', $aProperties['attrDbVar']['__di']['textService']);
    }

    /**
     * @throws SetupException
     */
    public function testContainerDiAnnotation()
    {
        $aAttributes = $this->xAnnotationReader->getAttributes(AttrAnnotated::class,
            ['attrDi'], ['colorService1', 'fontService1', 'textService1']);
        $bExcluded = $aAttributes[0];
        $aProperties = $aAttributes[1];

        $this->assertFalse($bExcluded);

        $this->assertCount(1, $aProperties);
        $this->assertArrayHasKey('*', $aProperties);
        $this->assertCount(3, $aProperties['*']['__di']);
        $this->assertEquals('Jaxon\Annotations\Tests\Service\ColorService', $aProperties['*']['__di']['colorService1']);
        $this->assertEquals('Jaxon\Annotations\Tests\App\Ajax\FontService', $aProperties['*']['__di']['fontService1']);
        $this->assertEquals('Jaxon\Annotations\Tests\Service\TextService', $aProperties['*']['__di']['textService1']);
    }

    /**
     * @throws SetupException
     */
    public function testContainerDiAndVarAnnotation()
    {
        $aAttributes = $this->xAnnotationReader->getAttributes(AttrAnnotated::class,
            ['attrDi'], ['colorService2', 'fontService2', 'textService2']);
        $bExcluded = $aAttributes[0];
        $aProperties = $aAttributes[1];

        $this->assertFalse($bExcluded);

        $this->assertCount(1, $aProperties);
        $this->assertArrayHasKey('*', $aProperties);
        $this->assertCount(3, $aProperties['*']['__di']);
        $this->assertEquals('Jaxon\Annotations\Tests\Service\ColorService', $aProperties['*']['__di']['colorService2']);
        $this->assertEquals('Jaxon\Annotations\Tests\App\Ajax\FontService', $aProperties['*']['__di']['fontService2']);
        $this->assertEquals('Jaxon\Annotations\Tests\Service\TextService', $aProperties['*']['__di']['textService2']);
    }

    /**
     * @throws SetupException
     */
    public function testContainerPropAnnotation()
    {
        $aAttributes = $this->xAnnotationReader->getAttributes(AttrAnnotated::class,
            ['attrDi'], ['colorService3', 'fontService3', 'textService3']);
        $bExcluded = $aAttributes[0];
        $aProperties = $aAttributes[1];

        $this->assertFalse($bExcluded);

        $this->assertCount(1, $aProperties);
        $this->assertArrayHasKey('*', $aProperties);
        $this->assertCount(3, $aProperties['*']['__di']);
        $this->assertEquals('Jaxon\Annotations\Tests\Service\ColorService', $aProperties['*']['__di']['colorService3']);
        $this->assertEquals('Jaxon\Annotations\Tests\App\Ajax\FontService', $aProperties['*']['__di']['fontService3']);
        $this->assertEquals('Jaxon\Annotations\Tests\Service\TextService', $aProperties['*']['__di']['textService3']);
    }

    public function testContainerAnnotationErrorTwoParams()
    {
        $this->expectException(SetupException::class);
        $this->xAnnotationReader->getAttributes(AttrAnnotated::class, [], ['errorTwoParams']);
    }

    public function testContainerAnnotationErrorDiAttr()
    {
        $this->expectException(SetupException::class);
        $this->xAnnotationReader->getAttributes(AttrAnnotated::class, [], ['errorDiAttr']);
    }

    public function testContainerAnnotationErrorDiDbAttr()
    {
        $this->expectException(SetupException::class);
        $this->xAnnotationReader->getAttributes(AttrAnnotated::class, [], ['errorDiDbAttr']);
    }

    public function testContainerAnnotationErrorTwoDi()
    {
        $this->expectException(SetupException::class);
        $this->xAnnotationReader->getAttributes(AttrAnnotated::class, [], ['errorTwoDi']);
    }

    public function testContainerAnnotationErrorDiClass()
    {
        $this->expectException(SetupException::class);
        $this->xAnnotationReader->getAttributes(AttrAnnotated::class, ['errorDiClass']);
    }

    public function testContainerAnnotationErrorNoVar()
    {
        $this->expectException(SetupException::class);
        $this->xAnnotationReader->getAttributes(AttrAnnotated::class, ['errorDiNoVar']);
    }

    public function testContainerAnnotationErrorTwoVars()
    {
        $this->expectException(SetupException::class);
        $this->xAnnotationReader->getAttributes(AttrAnnotated::class, ['errorDiTwoVars']);
    }
}

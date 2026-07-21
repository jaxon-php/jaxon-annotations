<?php

namespace Jaxon\Annotations\Tests\TestAnnotation;

use Jaxon\Annotations\Tests\AnnotationTrait;
use Jaxon\Annotations\Tests\Attr\Ajax\AttrAnnotated;
use Jaxon\Annotations\Tests\Attr\Ajax\FontService;
use Jaxon\Annotations\Tests\Service\ColorService;
use Jaxon\Annotations\Tests\Service\TextService;
use Jaxon\Exception\SetupException;
use PHPUnit\Framework\TestCase;

use function Jaxon\jaxon;
use function Jaxon\Annotations\_register;

class AttrAnnotationTest extends TestCase
{
    use AnnotationTrait;

    /**
     * @var string
     */
    protected $sCacheDir;

    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        $this->sCacheDir = __DIR__ . '/../tmp';
        @mkdir($this->sCacheDir);

        jaxon()->di()->getPluginManager()->registerPlugins();
        _register();

        jaxon()->di()->val('jaxon_annotations_cache_dir', $this->sCacheDir);
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
        $xMetadata = $this->getAttributes(AttrAnnotated::class, ['attrVar'],
            [AttrAnnotated::class => ['colorService', 'fontService', 'textService']]);
        $bExcluded = $xMetadata->isExcluded();
        $aProperties = $xMetadata->getProperties();

        $this->assertFalse($bExcluded);

        $di = $aProperties['attrVar']['__di'];
        $this->assertCount(1, $aProperties);
        $this->assertArrayHasKey('attrVar', $aProperties);
        $this->assertCount(3, $di);
        $this->assertEquals(ColorService::class, $di['colorService'][0]);
        $this->assertEquals(AttrAnnotated::class, $di['colorService'][1]);
        $this->assertEquals(FontService::class, $di['fontService'][0]);
        $this->assertEquals(AttrAnnotated::class, $di['fontService'][1]);
        $this->assertEquals(TextService::class, $di['textService'][0]);
        $this->assertEquals(AttrAnnotated::class, $di['textService'][1]);
    }

    /**
     * @throws SetupException
     */
    public function testContainerDocBlockAnnotation()
    {
        $xMetadata = $this->getAttributes(AttrAnnotated::class, ['attrDbVar'],
            [AttrAnnotated::class => ['colorService', 'fontService', 'textService']]);
        $bExcluded = $xMetadata->isExcluded();
        $aProperties = $xMetadata->getProperties();

        $this->assertFalse($bExcluded);

        $di = $aProperties['attrDbVar']['__di'];
        $this->assertCount(1, $aProperties);
        $this->assertArrayHasKey('attrDbVar', $aProperties);
        $this->assertCount(3, $di);
        $this->assertEquals(ColorService::class, $di['colorService'][0]);
        $this->assertEquals(AttrAnnotated::class, $di['colorService'][1]);
        $this->assertEquals(FontService::class, $di['fontService'][0]);
        $this->assertEquals(AttrAnnotated::class, $di['fontService'][1]);
        $this->assertEquals(TextService::class, $di['textService'][0]);
        $this->assertEquals(AttrAnnotated::class, $di['textService'][1]);
    }

    /**
     * @throws SetupException
     */
    public function testContainerDiAnnotation()
    {
        $xMetadata = $this->getAttributes(AttrAnnotated::class, ['attrDi'],
            [AttrAnnotated::class => ['colorService1', 'fontService1', 'textService1']]);
        $bExcluded = $xMetadata->isExcluded();
        $aProperties = $xMetadata->getProperties();

        $this->assertFalse($bExcluded);

        $di = $aProperties['*']['__di'];
        $this->assertCount(1, $aProperties);
        $this->assertArrayHasKey('*', $aProperties);
        $this->assertCount(3, $di);
        $this->assertEquals(ColorService::class, $di['colorService1'][0]);
        $this->assertEquals(AttrAnnotated::class, $di['colorService1'][1]);
        $this->assertEquals(FontService::class, $di['fontService1'][0]);
        $this->assertEquals(AttrAnnotated::class, $di['fontService1'][1]);
        $this->assertEquals(TextService::class, $di['textService1'][0]);
        $this->assertEquals(AttrAnnotated::class, $di['textService1'][1]);
    }

    /**
     * @throws SetupException
     */
    public function testContainerDiAndVarAnnotation()
    {
        $xMetadata = $this->getAttributes(AttrAnnotated::class, ['attrDi'],
            [AttrAnnotated::class => ['colorService2', 'fontService2', 'textService2']]);
        $bExcluded = $xMetadata->isExcluded();
        $aProperties = $xMetadata->getProperties();

        $this->assertFalse($bExcluded);

        $di = $aProperties['*']['__di'];
        $this->assertCount(1, $aProperties);
        $this->assertArrayHasKey('*', $aProperties);
        $this->assertCount(3, $di);
        $this->assertEquals(ColorService::class, $di['colorService2'][0]);
        $this->assertEquals(AttrAnnotated::class, $di['colorService2'][1]);
        $this->assertEquals(FontService::class, $di['fontService2'][0]);
        $this->assertEquals(AttrAnnotated::class, $di['fontService2'][1]);
        $this->assertEquals(TextService::class, $di['textService2'][0]);
        $this->assertEquals(AttrAnnotated::class, $di['textService2'][1]);
    }

    /**
     * @throws SetupException
     */
    public function testContainerPropAnnotation()
    {
        $xMetadata = $this->getAttributes(AttrAnnotated::class, ['attrDi'],
            [AttrAnnotated::class => ['colorService3', 'fontService3', 'textService3']]);
        $bExcluded = $xMetadata->isExcluded();
        $aProperties = $xMetadata->getProperties();

        $this->assertFalse($bExcluded);

        $di = $aProperties['*']['__di'];
        $this->assertCount(1, $aProperties);
        $this->assertArrayHasKey('*', $aProperties);
        $this->assertCount(3, $di);
        $this->assertEquals(ColorService::class, $di['colorService3'][0]);
        $this->assertEquals(AttrAnnotated::class, $di['colorService3'][1]);
        $this->assertEquals(FontService::class, $di['fontService3'][0]);
        $this->assertEquals(AttrAnnotated::class, $di['fontService3'][1]);
        $this->assertEquals(TextService::class, $di['textService3'][0]);
        $this->assertEquals(AttrAnnotated::class, $di['textService3'][1]);
    }

    public function testContainerAnnotationErrorTwoParams()
    {
        $this->expectException(SetupException::class);
        $this->getAttributes(AttrAnnotated::class, [], [AttrAnnotated::class => ['errorTwoParams']]);
    }

    public function testContainerAnnotationErrorDiAttr()
    {
        $this->expectException(SetupException::class);
        $this->getAttributes(AttrAnnotated::class, [], [AttrAnnotated::class => ['errorDiAttr']]);
    }

    public function testContainerAnnotationErrorDiDbAttr()
    {
        $this->expectException(SetupException::class);
        $this->getAttributes(AttrAnnotated::class, [], [AttrAnnotated::class => ['errorDiDbAttr']]);
    }

    public function testContainerAnnotationErrorTwoDi()
    {
        $this->expectException(SetupException::class);
        $this->getAttributes(AttrAnnotated::class, [], [AttrAnnotated::class => ['errorTwoDi']]);
    }

    public function testContainerAnnotationErrorDiClass()
    {
        $this->expectException(SetupException::class);
        $this->getAttributes(AttrAnnotated::class, ['errorDiClass']);
    }

    public function testContainerAnnotationErrorNoVar()
    {
        $this->expectException(SetupException::class);
        $this->getAttributes(AttrAnnotated::class, ['errorDiNoVar']);
    }

    public function testContainerAnnotationErrorTwoVars()
    {
        $this->expectException(SetupException::class);
        $this->getAttributes(AttrAnnotated::class, ['errorDiTwoVars']);
    }
}

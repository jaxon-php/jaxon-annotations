<?php

namespace Jaxon\Annotations\Tests\TestAnnotation;

use Jaxon\Annotations\AnnotationReader;
use Jaxon\Annotations\Tests\App\Ajax\DocBlockAnnotated;
use Jaxon\Annotations\Tests\App\Ajax\DocBlockClassAnnotated;
use Jaxon\Annotations\Tests\App\Ajax\DocBlockClassExcluded;
use Jaxon\Exception\SetupException;
use PHPUnit\Framework\TestCase;

use function jaxon;

class DocBlockAnnotationTest extends TestCase
{
    /**
     * @var AnnotationReader
     */
    protected $xAnnotationReader;

    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        $sCacheDir = __DIR__ . '/../tmp';
        @unlink($sCacheDir);
        @mkdir($sCacheDir);

        jaxon()->di()->getPluginManager()->registerPlugins();
        AnnotationReader::register(jaxon()->di());
        jaxon()->di()->val('jaxon_annotations_cache_dir', $sCacheDir);
        $this->xAnnotationReader = jaxon()->di()->g(AnnotationReader::class);
    }

    /**
     * @throws SetupException
     */
    public function tearDown(): void
    {
        jaxon()->reset();
        parent::tearDown();
    }

    /**
     * @throws SetupException
     */
    public function testUploadAndExcludeAnnotation()
    {
        // Can be called multiple times without error.
        AnnotationReader::register(jaxon()->di());
        [$bExcluded, $aProperties, $aProtected] = $this->xAnnotationReader->getAttributes(DocBlockAnnotated::class, ['saveFiles', 'doNot']);

        $this->assertFalse($bExcluded);

        $this->assertCount(1, $aProperties);
        $this->assertArrayHasKey('saveFiles', $aProperties);
        $this->assertCount(1, $aProperties['saveFiles']);
        $this->assertEquals("'user-files'", $aProperties['saveFiles']['upload']);

        $this->assertCount(1, $aProtected);
        $this->assertEquals('doNot', $aProtected[0]);
    }

    /**
     * @throws SetupException
     */
    public function testDataBagAnnotation()
    {
        [$bExcluded, $aProperties, ] = $this->xAnnotationReader->getAttributes(DocBlockAnnotated::class, ['withBags']);

        $this->assertFalse($bExcluded);

        $this->assertCount(1, $aProperties);
        $this->assertArrayHasKey('withBags', $aProperties);
        $this->assertCount(1, $aProperties['withBags']);
        $this->assertCount(2, $aProperties['withBags']['bags']);
        $this->assertEquals('user.name', $aProperties['withBags']['bags'][0]);
        $this->assertEquals('page.number', $aProperties['withBags']['bags'][1]);
    }

    /**
     * @throws SetupException
     */
    public function testCallbacksAnnotation()
    {
        [$bExcluded, $aProperties, ] = $this->xAnnotationReader->getAttributes(DocBlockAnnotated::class,
            ['cbSingle', 'cbMultiple', 'cbParams']);

        $this->assertFalse($bExcluded);

        $this->assertCount(3, $aProperties);
        $this->assertArrayHasKey('cbSingle', $aProperties);
        $this->assertArrayHasKey('cbMultiple', $aProperties);
        $this->assertArrayHasKey('cbParams', $aProperties);

        $this->assertCount(1, $aProperties['cbSingle']['__before']);
        $this->assertCount(2, $aProperties['cbMultiple']['__before']);
        $this->assertCount(2, $aProperties['cbParams']['__before']);
        $this->assertArrayHasKey('funcBefore', $aProperties['cbSingle']['__before']);
        $this->assertArrayHasKey('funcBefore1', $aProperties['cbMultiple']['__before']);
        $this->assertArrayHasKey('funcBefore2', $aProperties['cbMultiple']['__before']);
        $this->assertArrayHasKey('funcBefore1', $aProperties['cbParams']['__before']);
        $this->assertArrayHasKey('funcBefore2', $aProperties['cbParams']['__before']);
        $this->assertIsArray($aProperties['cbSingle']['__before']['funcBefore']);
        $this->assertIsArray($aProperties['cbMultiple']['__before']['funcBefore1']);
        $this->assertIsArray($aProperties['cbMultiple']['__before']['funcBefore2']);
        $this->assertIsArray($aProperties['cbParams']['__before']['funcBefore1']);
        $this->assertIsArray($aProperties['cbParams']['__before']['funcBefore2']);

        $this->assertCount(1, $aProperties['cbSingle']['__after']);
        $this->assertCount(3, $aProperties['cbMultiple']['__after']);
        $this->assertCount(1, $aProperties['cbParams']['__after']);
        $this->assertArrayHasKey('funcAfter', $aProperties['cbSingle']['__after']);
        $this->assertArrayHasKey('funcAfter1', $aProperties['cbMultiple']['__after']);
        $this->assertArrayHasKey('funcAfter2', $aProperties['cbMultiple']['__after']);
        $this->assertArrayHasKey('funcAfter3', $aProperties['cbMultiple']['__after']);
        $this->assertArrayHasKey('funcAfter1', $aProperties['cbParams']['__after']);
        $this->assertIsArray($aProperties['cbSingle']['__after']['funcAfter']);
        $this->assertIsArray($aProperties['cbMultiple']['__after']['funcAfter1']);
        $this->assertIsArray($aProperties['cbMultiple']['__after']['funcAfter2']);
        $this->assertIsArray($aProperties['cbMultiple']['__after']['funcAfter3']);
        $this->assertIsArray($aProperties['cbParams']['__after']['funcAfter1']);
    }

    /**
     * @throws SetupException
     */
    public function testContainerAnnotation()
    {
        [$bExcluded, $aProperties, ] = $this->xAnnotationReader->getAttributes(DocBlockAnnotated::class, ['di1', 'di2']);

        $this->assertFalse($bExcluded);

        $this->assertCount(2, $aProperties);
        $this->assertArrayHasKey('di1', $aProperties);
        $this->assertArrayHasKey('di2', $aProperties);
        $this->assertCount(2, $aProperties['di1']['__di']);
        $this->assertCount(2, $aProperties['di2']['__di']);
        $this->assertEquals('Jaxon\Annotations\Tests\Service\ColorService', $aProperties['di1']['__di']['colorService']);
        $this->assertEquals('Jaxon\Annotations\Tests\App\Ajax\FontService', $aProperties['di1']['__di']['fontService']);
        $this->assertEquals('Jaxon\Annotations\Tests\Service\ColorService', $aProperties['di2']['__di']['colorService']);
        $this->assertEquals('Jaxon\Annotations\Tests\Service\TextService', $aProperties['di2']['__di']['textService']);
    }

    /**
     * @throws SetupException
     */
    public function testClassAnnotation()
    {
        [$bExcluded, $aProperties,] = $this->xAnnotationReader->getAttributes(DocBlockClassAnnotated::class, []);
        // $this->assertEquals('', json_encode($aProperties));

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

    /**
     * @throws SetupException
     */
    public function testClassExcludeAnnotation()
    {
        [$bExcluded, $aProperties, $aProtected] = $this->xAnnotationReader->getAttributes(DocBlockClassExcluded::class,
            ['doNot', 'withBags', 'cbSingle']);

        $this->assertTrue($bExcluded);
        $this->assertEmpty($aProperties);
        $this->assertEmpty($aProtected);
    }

    public function testUploadAnnotationErrorFieldName()
    {
        $this->expectException(SetupException::class);
        $this->xAnnotationReader->getAttributes(DocBlockAnnotated::class, ['saveFileErrorFieldName']);
    }

    public function testUploadAnnotationErrorFieldNumber()
    {
        $this->expectException(SetupException::class);
        $this->xAnnotationReader->getAttributes(DocBlockAnnotated::class, ['saveFileErrorFieldNumber']);
    }

    public function testDataBagAnnotationErrorName()
    {
        $this->expectException(SetupException::class);
        $this->xAnnotationReader->getAttributes(DocBlockAnnotated::class, ['withBagsErrorName']);
    }

    public function testDataBagAnnotationErrorNumber()
    {
        $this->expectException(SetupException::class);
        $this->xAnnotationReader->getAttributes(DocBlockAnnotated::class, ['withBagsErrorNumber']);
    }

    public function testContainerAnnotationErrorAttr()
    {
        $this->expectException(SetupException::class);
        $this->xAnnotationReader->getAttributes(DocBlockAnnotated::class, ['diErrorAttr']);
    }

    public function testContainerAnnotationErrorClass()
    {
        $this->expectException(SetupException::class);
        $this->xAnnotationReader->getAttributes(DocBlockAnnotated::class, ['diErrorClass']);
    }

    public function testContainerAnnotationErrorOneParam()
    {
        $this->expectException(SetupException::class);
        $this->xAnnotationReader->getAttributes(DocBlockAnnotated::class, ['diErrorOneParam']);
    }

    public function testContainerAnnotationErrorThreeParams()
    {
        $this->expectException(SetupException::class);
        $this->xAnnotationReader->getAttributes(DocBlockAnnotated::class, ['diErrorThreeParams']);
    }

    public function testCbBeforeAnnotationErrorName()
    {
        $this->expectException(SetupException::class);
        $this->xAnnotationReader->getAttributes(DocBlockAnnotated::class, ['cbBeforeErrorName']);
    }

    public function testCbBeforeAnnotationErrorParam()
    {
        $this->expectException(SetupException::class);
        $this->xAnnotationReader->getAttributes(DocBlockAnnotated::class, ['cbBeforeErrorParam']);
    }

    public function testCbBeforeAnnotationErrorNumber()
    {
        $this->expectException(SetupException::class);
        $this->xAnnotationReader->getAttributes(DocBlockAnnotated::class, ['cbBeforeErrorNumber']);
    }

    public function testCbAfterAnnotationErrorName()
    {
        $this->expectException(SetupException::class);
        $this->xAnnotationReader->getAttributes(DocBlockAnnotated::class, ['cbAfterErrorName']);
    }

    public function testCbAfterAnnotationErrorParam()
    {
        $this->expectException(SetupException::class);
        $this->xAnnotationReader->getAttributes(DocBlockAnnotated::class, ['cbAfterErrorParam']);
    }

    public function testCbAfterAnnotationErrorNumber()
    {
        $this->expectException(SetupException::class);
        $this->xAnnotationReader->getAttributes(DocBlockAnnotated::class, ['cbAfterErrorNumber']);
    }
}

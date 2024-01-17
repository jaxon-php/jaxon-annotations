<?php

namespace Jaxon\Annotations\Tests\TestAnnotation;

use Jaxon\Annotations\AnnotationReader;
use Jaxon\Annotations\Tests\App\Ajax\DocBlockAnnotated;
use Jaxon\Annotations\Tests\App\Ajax\DocBlockClassAnnotated;
use Jaxon\Annotations\Tests\App\Ajax\DocBlockClassExcluded;
use Jaxon\Exception\SetupException;
use PHPUnit\Framework\TestCase;

use function Jaxon\jaxon;
use function mkdir;
use function rmdir;
use function Jaxon\Annotations\registerAnnotations;

class DocBlockAnnotationTest extends TestCase
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
        registerAnnotations();
        jaxon()->setOption('core.annotations.enabled', true);

        jaxon()->di()->val('jaxon_annotations_cache_dir', $this->sCacheDir);
        $this->xAnnotationReader = jaxon()->di()->g(AnnotationReader::class);
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
    public function testUploadAndExcludeAnnotation()
    {
        // Can be called multiple times without error.
        registerAnnotations();

        $aAttributes = $this->xAnnotationReader->getAttributes(DocBlockAnnotated::class, ['saveFiles', 'doNot']);
        $bExcluded = $aAttributes[0];
        $aProperties = $aAttributes[1];
        $aProtected = $aAttributes[2];

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
        $aAttributes = $this->xAnnotationReader->getAttributes(DocBlockAnnotated::class, ['withBags']);
        $bExcluded = $aAttributes[0];
        $aProperties = $aAttributes[1];

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
    public function testCallbackAnnotation()
    {
        $aAttributes = $this->xAnnotationReader->getAttributes(DocBlockAnnotated::class, ['withCallback']);
        $bExcluded = $aAttributes[0];
        $aProperties = $aAttributes[1];

        $this->assertFalse($bExcluded);

        $this->assertCount(1, $aProperties);
        $this->assertArrayHasKey('withCallback', $aProperties);
        $this->assertCount(1, $aProperties['withCallback']);
        $this->assertEquals('jaxon.ajax.callback.test', $aProperties['withCallback']['callback']);
    }

    /**
     * @throws SetupException
     */
    public function testHooksAnnotation()
    {
        $aAttributes = $this->xAnnotationReader->getAttributes(DocBlockAnnotated::class,
            ['cbSingle', 'cbMultiple', 'cbParams']);
        $bExcluded = $aAttributes[0];
        $aProperties = $aAttributes[1];

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
        $aAttributes = $this->xAnnotationReader->getAttributes(DocBlockAnnotated::class, ['di1', 'di2']);
        $bExcluded = $aAttributes[0];
        $aProperties = $aAttributes[1];

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
        $aAttributes = $this->xAnnotationReader->getAttributes(DocBlockClassAnnotated::class, []);
        // $this->assertEquals('', json_encode($aProperties));
        $bExcluded = $aAttributes[0];
        $aProperties = $aAttributes[1];

        $this->assertFalse($bExcluded);

        $this->assertCount(1, $aProperties);
        $this->assertArrayHasKey('*', $aProperties);
        $this->assertCount(5, $aProperties['*']);
        $this->assertArrayHasKey('bags', $aProperties['*']);
        $this->assertArrayHasKey('callback', $aProperties['*']);
        $this->assertArrayHasKey('__before', $aProperties['*']);
        $this->assertArrayHasKey('__after', $aProperties['*']);

        $this->assertCount(2, $aProperties['*']['bags']);
        $this->assertEquals('user.name', $aProperties['*']['bags'][0]);
        $this->assertEquals('page.number', $aProperties['*']['bags'][1]);

        $this->assertEquals('jaxon.ajax.callback.test', $aProperties['*']['callback']);

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
        $aAttributes = $this->xAnnotationReader->getAttributes(DocBlockClassExcluded::class,
            ['doNot', 'withBags', 'cbSingle']);
        $bExcluded = $aAttributes[0];
        $aProperties = $aAttributes[1];
        $aProtected = $aAttributes[2];

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

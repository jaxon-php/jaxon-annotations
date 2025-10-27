<?php

namespace Jaxon\Annotations\Tests\TestAnnotation;

use Jaxon\Annotations\Tests\AnnotationTrait;
use Jaxon\Annotations\Tests\App\Ajax\Annotated;
use Jaxon\Annotations\Tests\App\Ajax\CallbackError;
use Jaxon\Annotations\Tests\App\Ajax\ClassAnnotated;
use Jaxon\Annotations\Tests\App\Ajax\ClassExcluded;
use Jaxon\Annotations\Tests\App\Ajax\ContainerError;
use Jaxon\Exception\SetupException;
use PHPUnit\Framework\TestCase;

use function Jaxon\jaxon;
use function Jaxon\Annotations\_register;

class AnnotationTest extends TestCase
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
    public function testUploadAndExcludeAnnotation()
    {
        $xMetadata = $this->getAttributes(Annotated::class, ['saveFiles', 'doNot']);
        $bExcluded = $xMetadata->isExcluded();
        $aProperties = $xMetadata->getProperties();
        $aExcluded = $xMetadata->getExceptMethods();

        $this->assertFalse($bExcluded);

        $this->assertCount(1, $aProperties);
        $this->assertArrayHasKey('saveFiles', $aProperties);
        $this->assertCount(1, $aProperties['saveFiles']);
        $this->assertEquals("'user-files'", $aProperties['saveFiles']['upload']);

        $this->assertCount(1, $aExcluded);
        $this->assertEquals('doNot', $aExcluded[0]);
    }

    /**
     * @throws SetupException
     */
    public function testDatabagAnnotation()
    {
        $xMetadata = $this->getAttributes(Annotated::class, ['withBags']);
        $bExcluded = $xMetadata->isExcluded();
        $aProperties = $xMetadata->getProperties();

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
    public function testServerCallbacksAnnotation()
    {
        $xMetadata = $this->getAttributes(Annotated::class,
            ['cbSingle', 'cbMultiple', 'cbParams']);
        $bExcluded = $xMetadata->isExcluded();
        $aProperties = $xMetadata->getProperties();

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
        $xMetadata = $this->getAttributes(Annotated::class, ['di1', 'di2']);
        $bExcluded = $xMetadata->isExcluded();
        $aProperties = $xMetadata->getProperties();

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
        $xMetadata = $this->getAttributes(ClassAnnotated::class, []);
        $bExcluded = $xMetadata->isExcluded();
        $aProperties = $xMetadata->getProperties();

        $this->assertFalse($bExcluded);

        $this->assertCount(1, $aProperties);
        $this->assertArrayHasKey('*', $aProperties);
        $this->assertCount(5, $aProperties['*']);
        $this->assertArrayHasKey('bags', $aProperties['*']);
        $this->assertArrayHasKey('callback', $aProperties['*']);
        $this->assertArrayHasKey('__before', $aProperties['*']);
        $this->assertArrayHasKey('__after', $aProperties['*']);
    }

    /**
     * @throws SetupException
     */
    public function testClassBagsAnnotation()
    {
        $xMetadata = $this->getAttributes(ClassAnnotated::class, []);
        $aProperties = $xMetadata->getProperties();

        $this->assertCount(2, $aProperties['*']['bags']);
        $this->assertEquals('user.name', $aProperties['*']['bags'][0]);
        $this->assertEquals('page.number', $aProperties['*']['bags'][1]);
    }

    /**
     * @throws SetupException
     */
    public function testClassCallbackAnnotation()
    {
        $xMetadata = $this->getAttributes(ClassAnnotated::class, []);
        $aProperties = $xMetadata->getProperties();

        $this->assertIsArray($aProperties['*']['callback']);
        $this->assertEquals('jaxon.callback.global', $aProperties['*']['callback'][0]);
    }

    /**
     * @throws SetupException
     */
    public function testClassBeforeAnnotation()
    {
        $xMetadata = $this->getAttributes(ClassAnnotated::class, []);
        $aProperties = $xMetadata->getProperties();

        $this->assertCount(2, $aProperties['*']['__before']);
        $this->assertArrayHasKey('funcBefore1', $aProperties['*']['__before']);
        $this->assertArrayHasKey('funcBefore2', $aProperties['*']['__before']);
        $this->assertIsArray($aProperties['*']['__before']['funcBefore1']);
        $this->assertIsArray($aProperties['*']['__before']['funcBefore2']);
    }

    /**
     * @throws SetupException
     */
    public function testClassAfterAnnotation()
    {
        $xMetadata = $this->getAttributes(ClassAnnotated::class, []);
        $aProperties = $xMetadata->getProperties();

        $this->assertCount(3, $aProperties['*']['__after']);
        $this->assertArrayHasKey('funcAfter1', $aProperties['*']['__after']);
        $this->assertArrayHasKey('funcAfter2', $aProperties['*']['__after']);
        $this->assertArrayHasKey('funcAfter3', $aProperties['*']['__after']);
        $this->assertIsArray($aProperties['*']['__after']['funcAfter1']);
        $this->assertIsArray($aProperties['*']['__after']['funcAfter2']);
        $this->assertIsArray($aProperties['*']['__after']['funcAfter3']);
    }

    /**
     * @throws SetupException
     */
    public function testClassDiAnnotation()
    {
        $xMetadata = $this->getAttributes(ClassAnnotated::class, []);
        $aProperties = $xMetadata->getProperties();

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
        $xMetadata = $this->getAttributes(ClassExcluded::class,
            ['doNot', 'withBags', 'cbSingle']);
        $bExcluded = $xMetadata->isExcluded();
        $aProperties = $xMetadata->getProperties();
        $aExcluded = $xMetadata->getExceptMethods();

        $this->assertTrue($bExcluded);
        $this->assertEmpty($aProperties);
        $this->assertEmpty($aExcluded);
    }

    public function testExcludeAnnotationError()
    {
        $this->expectException(SetupException::class);
        $this->getAttributes(Annotated::class, ['doNotError']);
    }

    public function testDatabagAnnotationError()
    {
        $this->expectException(SetupException::class);
        $this->getAttributes(Annotated::class, ['withBagsError']);
    }

    public function testUploadAnnotationWrongName()
    {
        $this->expectException(SetupException::class);
        $this->getAttributes(Annotated::class, ['saveFilesWrongName']);
    }

    public function testUploadAnnotationMultiple()
    {
        $this->expectException(SetupException::class);
        $this->getAttributes(Annotated::class, ['saveFilesMultiple']);
    }

    public function testCallbacksBeforeAnnotationNoCall()
    {
        $this->expectException(SetupException::class);
        $this->getAttributes(Annotated::class, ['cbBeforeNoCall']);
    }

    public function testCallbacksBeforeAnnotationUnknownAttr()
    {
        $this->expectException(SetupException::class);
        $this->getAttributes(Annotated::class, ['cbBeforeUnknownAttr']);
    }

    public function testCallbacksBeforeAnnotationWrongAttrType()
    {
        $this->expectException(SetupException::class);
        $this->getAttributes(Annotated::class, ['cbBeforeWrongAttrType']);
    }

    public function testCallbacksAfterAnnotationNoCall()
    {
        $this->expectException(SetupException::class);
        $this->getAttributes(Annotated::class, ['cbAfterNoCall']);
    }

    public function testCallbacksAfterAnnotationUnknownAttr()
    {
        $this->expectException(SetupException::class);
        $this->getAttributes(Annotated::class, ['cbAfterUnknownAttr']);
    }

    public function testCallbacksAfterAnnotationWrongAttrType()
    {
        $this->expectException(SetupException::class);
        $this->getAttributes(Annotated::class, ['cbAfterWrongAttrType']);
    }

    public function testContainerAnnotationUnknownAttr()
    {
        $this->expectException(SetupException::class);
        $this->getAttributes(Annotated::class, ['diUnknownAttr']);
    }

    public function testContainerAnnotationWrongAttrType()
    {
        $this->expectException(SetupException::class);
        $this->getAttributes(Annotated::class, ['diWrongAttrType']);
    }

    public function testContainerAnnotationWrongClassType()
    {
        $this->expectException(SetupException::class);
        $this->getAttributes(Annotated::class, ['diWrongClassType']);
    }

    public function testContainerAnnotationWrongVarCount()
    {
        $this->expectException(SetupException::class);
        $this->getAttributes(Annotated::class, ['diWrongVarCount']);
    }

    public function testContainerErrorTwiceOnProp()
    {
        $this->expectException(SetupException::class);
        $this->getAttributes(ContainerError::class, [], ['prop']);
    }

    public function testCallbackErrorNoName()
    {
        $this->expectException(SetupException::class);
        $this->getAttributes(CallbackError::class, ['noName']);
    }

    public function testCallbackErrorWrongNameType()
    {
        $this->expectException(SetupException::class);
        $this->getAttributes(CallbackError::class, ['wrongNameType']);
    }

    public function testCallbackErrorWrongNameAttr()
    {
        $this->expectException(SetupException::class);
        $this->getAttributes(CallbackError::class, ['wrongNameAttr']);
    }

    public function testCallbackErrorNameWithSpace()
    {
        $this->expectException(SetupException::class);
        $this->getAttributes(CallbackError::class, ['nameWithSpace']);
    }

    public function testCallbackErrorStartWithInt()
    {
        $this->expectException(SetupException::class);
        $this->getAttributes(CallbackError::class, ['startWithInt']);
    }
}

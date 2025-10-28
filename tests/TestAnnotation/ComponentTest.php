<?php

namespace Jaxon\Annotations\Tests\TestAnnotation;

use Jaxon\Annotations\Tests\AnnotationTrait;
use Jaxon\Annotations\Tests\Attr\Ajax\Component\FuncComponent;
use Jaxon\Annotations\Tests\Attr\Ajax\Component\NodeComponent;
use Jaxon\Annotations\Tests\Attr\Ajax\Component\NodeBaseComponent;
use Jaxon\Annotations\Tests\Attr\Ajax\Component\PageComponent;
use Jaxon\Exception\SetupException;
use PHPUnit\Framework\TestCase;

use ReflectionClass;
use function Jaxon\Annotations\_register;
use function Jaxon\jaxon;

class ComponentTest extends TestCase
{
    use AnnotationTrait;

    /**
     * @var string
     */
    private $sCacheDir;

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
    public function testNodeComponentExportMethods()
    {
        $xMetadata = $this->getAttributes(NodeComponent::class,
            ['item', 'html', 'render', 'clear', 'visible'], []);
        $bExcluded = $xMetadata->isExcluded();
        $aExcluded = $xMetadata->getExceptMethods();
        $aBaseMethods = $xMetadata->getExportBaseMethods();
        $aOnlyMethods = $xMetadata->getExportOnlyMethods();

        $this->assertFalse($bExcluded);
        $this->assertCount(0, $aExcluded);
        $this->assertCount(0, $aBaseMethods);
        $this->assertCount(0, $aOnlyMethods);
    }

    /**
     * @throws SetupException
     */
    public function testPageComponentExportMethods()
    {
        $xMetadata = $this->getAttributes(PageComponent::class,
            ['item', 'html', 'render', 'clear', 'visible'], []);
        $bExcluded = $xMetadata->isExcluded();
        $aExcluded = $xMetadata->getExceptMethods();
        $aBaseMethods = $xMetadata->getExportBaseMethods();
        $aOnlyMethods = $xMetadata->getExportOnlyMethods();

        $this->assertFalse($bExcluded);
        $this->assertCount(0, $aExcluded);
        $this->assertCount(0, $aBaseMethods);
        $this->assertCount(0, $aOnlyMethods);
    }

    /**
     * @throws SetupException
     */
    public function testFuncComponentExportMethods()
    {
        $xMetadata = $this->getAttributes(FuncComponent::class,
            ['paginator'], []);
        $bExcluded = $xMetadata->isExcluded();
        $aExcluded = $xMetadata->getExceptMethods();
        $aBaseMethods = $xMetadata->getExportBaseMethods();
        $aOnlyMethods = $xMetadata->getExportOnlyMethods();

        $this->assertFalse($bExcluded);
        $this->assertCount(0, $aExcluded);
        $this->assertCount(0, $aBaseMethods);
        $this->assertCount(0, $aOnlyMethods);
    }

    /**
     * @throws SetupException
     */
    public function testNodeComponentExportBaseMethods()
    {
        // The attribute exports the 'html' and 'render' methods,
        // but only the 'render' method shall be exported.
        $xClass = new ReflectionClass(NodeBaseComponent::class);
        $aMethods = ['item', 'html', 'render', 'clear', 'visible'];
        $xMetadata = $this->getAttributes($xClass, $aMethods, []);
        $aBaseMethods = $xMetadata->getExportBaseMethods();

        // The 'html' and 'render' methods are returned.
        $this->assertCount(2, $aBaseMethods);

        $xOptions = $this->getOptions($xClass);
        $aPublicMethods = $xOptions->getPublicMethods();

        // Only the 'render' method is returned.
        $this->assertCount(1, $aPublicMethods);
        $this->assertEquals('render', $aPublicMethods[0]);
    }

    // public function testContainerAttributeErrorTwoDi()
    // {
    //     $this->expectException(SetupException::class);
    //     $this->getAttributes(PropertyAttribute::class, [], ['errorTwoDi']);
    // }

    // public function testContainerAttributeErrorDiClass()
    // {
    //     $this->expectException(SetupException::class);
    //     $this->getAttributes(PropertyAttribute::class, ['errorDiClass']);
    // }
}

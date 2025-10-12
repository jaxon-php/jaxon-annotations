<?php

namespace Jaxon\Annotations\Tests\App\Ajax;

use Jaxon\Annotations\Tests\App\FuncComponent;
// Don't delete. This declaration is actually used by an annotation.
use Jaxon\Annotations\Tests\Service\ColorService;

class DocBlockAnnotated extends FuncComponent
{
    /**
     * @exclude true
     */
    public function doNot()
    {
    }

    /**
     * @exclude Me
     */
    public function doNotError()
    {
    }

    /**
     * @databag user.name
     * @databag page.number
     */
    public function withBags()
    {
    }

    /**
     * @databag user:name
     */
    public function withBagsErrorName()
    {
    }

    /**
     * @databag page number
     */
    public function withBagsErrorNumber()
    {
    }

    /**
     * @upload user-files
     * @exclude false
     */
    public function saveFiles()
    {
    }

    /**
     * @upload user:file
     */
    public function saveFileErrorFieldName()
    {
    }

    /**
     * @upload user file
     */
    public function saveFileErrorFieldNumber()
    {
    }

    /**
     * @upload user-files
     */
    public function saveFilesWrongName()
    {
    }

    /**
     * @upload user-file1
     * @upload user-file2
     */
    public function saveFilesMultiple()
    {
    }

    /**
     * @before funcBefore
     * @after funcAfter
     */
    public function cbSingle()
    {
    }

    /**
     * @before funcBefore1
     * @before funcBefore2
     * @after funcAfter1
     * @after funcAfter2
     * @after funcAfter3
     */
    public function cbMultiple()
    {
    }

    /**
     * @before funcBefore1 ["param1"]
     * @before funcBefore2 ["param1", "param2"]
     * @after funcAfter1 ["param1", "param2"]
     */
    public function cbParams()
    {
    }

    /**
     * @before func:Before
     */
    public function cbBeforeErrorName()
    {
    }

    /**
     * @before funcBefore false
     */
    public function cbBeforeErrorParam()
    {
    }

    /**
     * @before funcBefore ["param1"] false
     */
    public function cbBeforeErrorNumber()
    {
    }

    /**
     * @before func:After
     */
    public function cbAfterErrorName()
    {
    }

    /**
     * @before funcAfter false
     */
    public function cbAfterErrorParam()
    {
    }

    /**
     * @before funcAfter ["param1"] false
     */
    public function cbAfterErrorNumber()
    {
    }

    /**
     * @di $colorService ColorService
     * @di $fontService FontService
     */
    public function di1()
    {
    }

    /**
     * @di $colorService ColorService
     * @di $textService \Jaxon\Annotations\Tests\Service\TextService
     */
    public function di2()
    {
    }

    /**
     * @di $color.Service ColorService
     */
    public function diErrorAttr()
    {
    }

    /**
     * @di $colorService Color.Service
     */
    public function diErrorClass()
    {
    }

    /**
     * @di $colorService
     */
    public function diErrorOneParam()
    {
    }

    /**
     * @di $colorService ColorService TextService
     */
    public function diErrorThreeParams()
    {
    }

    /**
     * @callback jaxon.ajax.callback.test
     */
    public function withCallback()
    {
    }
}

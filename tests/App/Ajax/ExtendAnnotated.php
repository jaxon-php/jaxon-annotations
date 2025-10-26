<?php

namespace Jaxon\Annotations\Tests\App\Ajax;

use Jaxon\Annotations\Tests\App\FuncComponent;
use Jaxon\Annotations\Tests\Service\ColorService;

class ExtendAnnotated extends FuncComponent
{
    /**
     * @exclude
     */
    public function doNot()
    {
    }

    /**
     * @exclude(true)
     */
    public function doNotBool()
    {
    }

    /**
     * @exclude('Me')
     */
    public function doNotError()
    {
    }

    /**
     * @databag('name' => 'user.name')
     * @databag('name' => 'page.number')
     */
    public function withBags()
    {
    }

    /**
     * @databag('mane' => 'user.name')
     * @databag('mane' => 'page.number')
     */
    public function withBagsError()
    {
    }

    /**
     * @upload('field' => 'user-files')
     */
    public function saveFiles()
    {
    }

    /**
     * @upload('name' => 'user-files')
     */
    public function saveFilesWrongName()
    {
    }

    /**
     * @upload('field' => 'user-file1')
     * @upload('field' => 'user-file2')
     */
    public function saveFilesMultiple()
    {
    }

    /**
     * @before('call' => 'funcBefore')
     * @after('call' => 'funcAfter')
     */
    public function cbSingle()
    {
    }

    /**
     * @before('call' => 'funcBefore1')
     * @before('call' => 'funcBefore2')
     * @after('call' => 'funcAfter1')
     * @after('call' => 'funcAfter2')
     * @after('call' => 'funcAfter3')
     */
    public function cbMultiple()
    {
    }

    /**
     * @before('call' => 'funcBefore1', 'with' => ['param1'])
     * @before('call' => 'funcBefore2', 'with' => ['param1', 'param2'])
     * @after('call' => 'funcAfter1', 'with' => ['param1', 'param2'])
     */
    public function cbParams()
    {
    }

    /**
     * @di('attr' => 'colorService', 'class' => 'ColorService')
     * @di('attr' => 'fontService', 'class' => 'FontService')
     */
    public function di1()
    {
    }

    /**
     * @di('attr' => 'colorService', 'class' => 'ColorService')
     * @di('attr' => 'textService', 'class' => '\Jaxon\Annotations\Tests\Service\TextService')
     */
    public function di2()
    {
    }

    /**
     * @before('name' => 'funcBefore', 'with' => ['param1'])
     */
    public function cbBeforeNoCall()
    {
    }

    /**
     * @before('call' => 'funcBefore', 'params' => ['param1'])
     */
    public function cbBeforeUnknownAttr()
    {
    }

    /**
     * @before('call' => 'funcBefore', 'with' => 'param1')
     */
    public function cbBeforeWrongAttrType()
    {
    }

    /**
     * @after('name' => 'funcAfter', 'with' => ['param1'])
     */
    public function cbAfterNoCall()
    {
    }

    /**
     * @after('call' => 'funcAfter', 'params' => ['param1'])
     */
    public function cbAfterUnknownAttr()
    {
    }

    /**
     * @after('call' => 'funcAfter', 'with' => true)
     */
    public function cbAfterWrongAttrType()
    {
    }

    /**
     * @di('attr' => 'attr', 'params' => '')
     */
    public function diUnknownAttr()
    {
    }

    /**
     * @di('attr' => [], 'class' => 'ClassName')
     */
    public function diWrongAttrType()
    {
    }

    /**
     * @di('attr' => 'attr', 'class' => true)
     */
    public function diWrongClassType()
    {
    }

    /**
     * @di('attr' => 'attr', 'class' => 'ClassName', 'name' => 'di')
     */
    public function diWrongVarCount()
    {
    }
}

<?php

namespace Jaxon\Annotations\Tests\Attr\Ajax;

use Jaxon\Annotations\Tests\Attr\FuncComponent;
use Jaxon\Annotations\Tests\Service\ColorService;

class AttrAnnotated extends FuncComponent
{
    /**
     * @var ColorService
     */
    protected $colorService;

    /**
     * @var FontService
     */
    protected $fontService;

    /**
     * @var \Jaxon\Annotations\Tests\Service\TextService
     */
    protected $textService;

    /**
     * @di ColorService
     */
    protected $colorService1;

    /**
     * @di FontService
     */
    protected $fontService1;

    /**
     * @di \Jaxon\Annotations\Tests\Service\TextService
     */
    protected $textService1;

    /**
     * @di
     * @var ColorService
     */
    protected $colorService2;

    /**
     * @di
     * @var FontService
     */
    protected $fontService2;

    /**
     * @di
     * @var \Jaxon\Annotations\Tests\Service\TextService
     */
    protected $textService2;

    /**
     * @di('class' => 'ColorService')
     */
    protected $colorService3;

    /**
     * @di('class' => 'FontService')
     */
    protected $fontService3;

    /**
     * @di('class' => '\Jaxon\Annotations\Tests\Service\TextService')
     */
    protected $textService3;

    /**
     * @di $fontService FontService
     */
    protected $errorTwoParams;

    /**
     * @di('attr' => 'fontService')
     */
    protected $errorDiAttr;

    /**
     * @di $fontService
     */
    protected $errorDiDbAttr;

    /**
     * @di
     * @di FontService
     */
    protected $errorTwoDi;

    /**
     * @di('attr' => 'colorService')
     * @di('attr' => 'fontService')
     * @di('attr' => 'textService')
     */
    public function attrVar()
    {
    }

    /**
     * @di $colorService
     * @di $fontService
     * @di $textService
     */
    public function attrDbVar()
    {
    }

    public function attrDi()
    {
    }

    /**
     * @di ColorService
     */
    public function errorDiClass()
    {
    }

    /**
     * @di colorService ColorService
     */
    public function errorDiNoVar()
    {
    }

    /**
     * @di $colorService $ColorService
     */
    public function errorDiTwoVars()
    {
    }
}

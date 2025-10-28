<?php

namespace Jaxon\Annotations\Tests\Attr\Ajax;

use Jaxon\Annotations\Tests\Attr\FuncComponent;
use Jaxon\Annotations\Tests\Service\TextService;

/**
 * @exclude(false)
 * @databag('name' => 'user.name')
 * @databag('name' => 'page.number')
 * @before('call' => 'funcBefore1')
 * @before('call' => 'funcBefore2')
 * @after('call' => 'funcAfter1')
 * @after('call' => 'funcAfter2')
 * @after('call' => 'funcAfter3')
 * @di('attr' => 'colorService', 'class' => '\Jaxon\Annotations\Tests\Service\ColorService')
 * @di('attr' => 'textService', 'class' => 'TextService')
 * @di('attr' => 'fontService', 'class' => 'FontService')
 * @callback('name' => 'jaxon.callback.global')
 */
class ClassAnnotated extends FuncComponent
{
    /**
     * @var \Jaxon\Annotations\Tests\Service\ColorService
     */
    protected $colorService;

    /**
     * @var TextService
     */
    protected $textService;

    /**
     * @var FontService
     */
    protected $fontService;
}

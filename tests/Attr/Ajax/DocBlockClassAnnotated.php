<?php

namespace Jaxon\Annotations\Tests\Attr\Ajax;

use Jaxon\Annotations\Tests\Attr\FuncComponent;
use Jaxon\Annotations\Tests\Service\TextService;

/**
 * @exclude false
 * @databag user.name
 * @databag page.number
 * @before funcBefore1
 * @before funcBefore2
 * @after funcAfter1
 * @after funcAfter2
 * @after funcAfter3
 * @di $colorService \Jaxon\Annotations\Tests\Service\ColorService
 * @di $textService TextService
 * @di $fontService FontService
 * @callback jaxon.ajax.callback.test
 */
class DocBlockClassAnnotated extends FuncComponent
{
}

<?php

namespace Jaxon\Annotations\Tests\App\Ajax;

use Jaxon\Annotations\Tests\App\CallableClass;
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
 */
class DocBlockClassAnnotated extends CallableClass
{
}

<?php

namespace Jaxon\Annotations\Tests\Attr\Ajax\Traits;

use Jaxon\Annotations\Tests\Service\TextService;

trait Annotated
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

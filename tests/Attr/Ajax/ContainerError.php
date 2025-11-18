<?php

namespace Jaxon\Annotations\Tests\Attr\Ajax;

use Jaxon\Annotations\Tests\Attr\FuncComponent;

class ContainerError extends FuncComponent
{
    /**
     * @di('class' => 'Class1')
     * @di('class' => 'Class2')
     */
    public $prop;
}

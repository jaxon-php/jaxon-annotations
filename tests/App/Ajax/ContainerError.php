<?php

namespace Jaxon\Annotations\Tests\App\Ajax;

use Jaxon\Annotations\Tests\App\FuncComponent;

class ContainerError extends FuncComponent
{
    /**
     * @di('class' => 'Class1')
     * @di('class' => 'Class2')
     */
    public $prop;
}

<?php

namespace Jaxon\Annotations\Tests\App\Ajax;

use Jaxon\Annotations\Tests\App\CallableClass;

class ContainerError extends CallableClass
{
    /**
     * @di('class' => 'Class1')
     * @di('class' => 'Class2')
     */
    public $prop;
}

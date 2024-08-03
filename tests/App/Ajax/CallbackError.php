<?php

namespace Jaxon\Annotations\Tests\App\Ajax;

use Jaxon\Annotations\Tests\App\CallableClass;

class CallbackError extends CallableClass
{
    /**
     * @callback
     */
    public function noName()
    {
    }

    /**
     * @callback('name' => [])
     */
    public function wrongNameType()
    {
    }

    /**
     * @callback('class' => 'jaxon.ajax.callback.test')
     */
    public function wrongNameAttr()
    {
    }

    /**
     * @callback('name' => 'jaxon.ajax callback.test')
     */
    public function nameWithSpace()
    {
    }

    /**
     * @callback('class' => '12jaxon.ajax.callback.test')
     */
    public function startWithInt()
    {
    }
}

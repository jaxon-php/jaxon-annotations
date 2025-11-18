<?php

namespace Jaxon\Annotations\Tests\Attr\Ajax;

use Jaxon\Annotations\Tests\Attr\FuncComponent;

class CallbackError extends FuncComponent
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

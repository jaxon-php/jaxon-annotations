<?php

namespace Jaxon\Annotations\Tests\App\Ajax;

use Jaxon\Annotations\Tests\App\FuncComponent;

/**
 * @exclude(true)
 */
class ClassExcluded extends FuncComponent
{
    /**
     * @exclude
     */
    public function doNot()
    {
    }

    /**
     * @databag('name' => 'user.name')
     * @databag('name' => 'page.number')
     */
    public function withBags()
    {
    }

    /**
     * @before('call' => 'funcBefore')
     * @after('call' => 'funcAfter')
     */
    public function cbSingle()
    {
    }
}

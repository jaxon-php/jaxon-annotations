<?php

namespace Jaxon\Annotations\Tests\App\Ajax;

use Jaxon\Annotations\Tests\App\CallableClass;

/**
 * @exclude(true)
 */
class ClassExcluded extends CallableClass
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

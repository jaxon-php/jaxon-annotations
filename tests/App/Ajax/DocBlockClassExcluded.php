<?php

namespace Jaxon\Annotations\Tests\App\Ajax;

use Jaxon\Annotations\Tests\App\CallableClass;

/**
 * @exclude true
 */
class DocBlockClassExcluded extends CallableClass
{
    /**
     * @exclude
     */
    public function doNot()
    {
    }

    /**
     * @databag user.name
     * @databag page.number
     */
    public function withBags()
    {
    }

    /**
     * @before funcBefore
     * @after funcAfter
     */
    public function cbSingle()
    {
    }
}

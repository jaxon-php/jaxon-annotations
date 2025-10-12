<?php

namespace Jaxon\Annotations\Tests\App\Ajax;

use Jaxon\Annotations\Tests\App\FuncComponent;

/**
 * @exclude true
 */
class DocBlockClassExcluded extends FuncComponent
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

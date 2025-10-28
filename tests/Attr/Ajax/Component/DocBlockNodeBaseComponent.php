<?php
declare(strict_types=1);

namespace Jaxon\Annotations\Tests\Attr\Ajax\Component;

use Jaxon\Annotations\Tests\Attr\NodeComponent as BaseComponent;

/**
 * @export {"base": ["html", "render"]}
 */
class DocBlockNodeBaseComponent extends BaseComponent
{
    use Traits\NodeTrait;
}

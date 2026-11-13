<?php

namespace Xaamin\Enum\Tests\Fixtures;

use Xaamin\Enum\Enum;

class Priority extends Enum
{
    protected $enum = [
        'low' => 1,
        'high' => ['value' => 10, 'meta' => ['color' => 'red']],
    ];
}

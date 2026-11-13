<?php

namespace Xaamin\Enum\Tests\Fixtures;

use Xaamin\Enum\Enum;

/**
 * @method static self pending()
 * @method static self paid()
 * @method static self overdue()
 */
class InvoiceStatus extends Enum
{
    protected $enum = [
        'pending' => 'invoice.pending',
        'paid' => 'invoice.paid',
        'overdue' => 'invoice.overdue',
    ];
}

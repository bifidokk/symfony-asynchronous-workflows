<?php
declare(strict_types=1);

namespace App\Service\Workflow\Order;

enum Transition: string
{
    case VerifyOrder = 'verify_order';

    case SendOrder = 'send_order';

    case MarkOrderAsSent = 'mark_order_as_sent';
}

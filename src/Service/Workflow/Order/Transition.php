<?php
declare(strict_types=1);

namespace App\Service\Workflow\Order;

enum Transition: string
{
    case VerifyOrder = 'verify_order';

    case ApproveOrder = 'approve_order';

    case SendOrderToEmail = 'send_order_to_email';

    case MarkOrderAsSent = 'mark_order_as_sent';
}

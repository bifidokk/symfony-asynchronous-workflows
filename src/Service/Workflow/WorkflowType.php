<?php
declare(strict_types=1);

namespace App\Service\Workflow;

enum WorkflowType: string
{
    case DefaultType = 'default';

    case OrderSend = 'order_send';
}

<?php

namespace App\Service\Workflow\Order\Stamp;

use App\Service\Workflow\WorkflowStampInterface;
use Symfony\Component\Uid\Uuid;

class OrderIdStamp implements WorkflowStampInterface
{
    public function __construct(
        private readonly Uuid $orderId,
    ) {

    }

    public function getOrderId(): Uuid
    {
        return $this->orderId;
    }
}

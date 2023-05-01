<?php
declare(strict_types=1);

namespace App\Service\Workflow\Order\Stamp;

use App\Service\Workflow\WorkflowStampInterface;
use Symfony\Component\Uid\Uuid;

class OrderIdStamp implements WorkflowStampInterface
{
    private Uuid $orderId;

    public function getOrderId(): Uuid
    {
        return $this->orderId;
    }

    public function setOrderId(Uuid $orderId): void
    {
        $this->orderId = $orderId;
    }

    public static function createWithOrderId(Uuid $orderId): OrderIdStamp
    {
        $stamp = new OrderIdStamp();
        $stamp->setOrderId($orderId);

        return $stamp;
    }
}

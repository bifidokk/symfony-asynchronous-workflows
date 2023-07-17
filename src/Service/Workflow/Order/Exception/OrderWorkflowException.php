<?php
declare(strict_types=1);

namespace App\Service\Workflow\Order\Exception;

use App\Entity\Order;
use App\Service\Workflow\Exception\StopWorkflowException;

class OrderWorkflowException extends StopWorkflowException
{
    public static function shouldHaveDescription(Order $order): \RuntimeException
    {
        return new self(
            sprintf(
                'The order %s should have description',
                $order->getId(),
            )
        );
    }
}

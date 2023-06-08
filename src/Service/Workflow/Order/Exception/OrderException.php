<?php

namespace App\Service\Workflow\Order\Exception;

use App\Entity\Order;

class OrderException extends \RuntimeException
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

<?php

namespace App\Service\Workflow\Order;

enum Transition: string
{
    case VerifyOrder = 'verify_order';

    case ConfirmOrder = 'confirm_order';

    case CompleteOrder = 'complete_order';
}

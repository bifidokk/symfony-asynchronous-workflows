<?php

namespace App\Service\Workflow\Order\Transition;

use App\Service\Workflow\Order\OrderCreate;
use Symfony\Component\Workflow\WorkflowInterface;

class CompleteOrder
{
    public function __construct(
        private WorkflowInterface $orderCreateStateMachine,
    ) {
    }

    public function handle(OrderCreate $orderCreate): void
    {
        // make some business logic
        // ....

        // order is completed
        $orderCreate->setCurrentState('completed');
        dump($orderCreate);
    }
}

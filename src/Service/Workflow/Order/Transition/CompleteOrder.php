<?php

namespace App\Service\Workflow\Order\Transition;

use App\Entity\WorkflowEntry;
use Symfony\Component\Workflow\WorkflowInterface;

class CompleteOrder
{
    public function __construct(
        private WorkflowInterface $orderCreateStateMachine,
    ) {
    }

    public function handle(WorkflowEntry $workflowEntry): void
    {
        // make some business logic
        // ....

        // order is completed
        $workflowEntry->setCurrentState('completed');
        dump($workflowEntry);
    }
}

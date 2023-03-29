<?php

namespace App\Service\Workflow\Order\Transition;

use App\Entity\WorkflowEntry;
use Symfony\Component\Workflow\WorkflowInterface;

class ConfirmOrder
{
    public function __construct(
        private WorkflowInterface $orderCompleteStateMachine,
    ) {
    }

    public function handle(WorkflowEntry $workflowEntry): void
    {
        // make some confirmation here
        // ....
        $workflowEntry->setCurrentState('confirmed');

        $this->orderCompleteStateMachine->apply($workflowEntry, 'complete_order');
    }
}

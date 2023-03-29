<?php

namespace App\Service\Workflow\Order\Transition;

use App\Entity\WorkflowEntry;
use Symfony\Component\Workflow\WorkflowInterface;

class VerifyOrder
{
    public function __construct(
        private WorkflowInterface $orderCompleteStateMachine,
    ) {
    }

    public function handle(WorkflowEntry $workflowEntry): void
    {
        // make some verification here
        // ....
        $workflowEntry->setCurrentState('verified');

        $this->orderCompleteStateMachine->apply($workflowEntry, 'confirm_order');
    }
}

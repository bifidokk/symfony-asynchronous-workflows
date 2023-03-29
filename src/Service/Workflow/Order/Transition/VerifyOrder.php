<?php

namespace App\Service\Workflow\Order\Transition;

use App\Entity\WorkflowEntry;
use Symfony\Component\Workflow\WorkflowInterface;

class VerifyOrder
{
    public function __construct(
        private WorkflowInterface $orderCreateStateMachine,
    ) {
    }

    public function handle(WorkflowEntry $workflowEntry): void
    {
        // make some verification here
        // ....
        $workflowEntry->setCurrentState('verified');

        $this->orderCreateStateMachine->apply($workflowEntry, 'confirm_order');
    }
}

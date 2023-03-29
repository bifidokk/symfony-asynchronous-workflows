<?php

namespace App\Service\Workflow\Order\Transition;

use App\Entity\WorkflowEntry;
use Symfony\Component\Workflow\WorkflowInterface;

class ConfirmOrder
{
    public function __construct(
        private WorkflowInterface $orderCreateStateMachine,
    ) {
    }

    public function handle(WorkflowEntry $workflowEntry): void
    {
        // make some confirmation here
        // ....
        $workflowEntry->setCurrentState('confirmed');

        $this->orderCreateStateMachine->apply($workflowEntry, 'complete_order');
    }
}

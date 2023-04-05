<?php

namespace App\Service\Workflow\Order\Transition;

use App\Entity\WorkflowEntry;

class CompleteOrder
{
    public function handle(WorkflowEntry $workflowEntry): void
    {
        // make some business logic
        // ....

        // order is completed
        $workflowEntry->setCurrentState('completed');
        dump('in complete');
    }
}

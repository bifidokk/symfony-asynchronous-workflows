<?php

namespace App\Service\Workflow\Order\Transition;

use App\Entity\WorkflowEntry;
use App\Service\Workflow\Event\WorkflowNextStateEvent;
use App\Service\Workflow\Order\Transition;
use Psr\EventDispatcher\EventDispatcherInterface;

class ConfirmOrder
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function handle(WorkflowEntry $workflowEntry): void
    {
        // make some confirmation here
        // ....
        $workflowEntry->setCurrentState('confirmed');
        $workflowEntry->setNextTransition(Transition::CompleteOrder->value);
        dump('in confirmed');

        $this->eventDispatcher->dispatch(new WorkflowNextStateEvent($workflowEntry));
    }
}

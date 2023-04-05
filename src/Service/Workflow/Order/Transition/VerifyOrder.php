<?php

namespace App\Service\Workflow\Order\Transition;

use App\Entity\WorkflowEntry;
use App\Service\Workflow\Event\WorkflowNextStateEvent;
use App\Service\Workflow\Order\Transition;
use Psr\EventDispatcher\EventDispatcherInterface;

class VerifyOrder
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function handle(WorkflowEntry $workflowEntry): void
    {
        $workflowEntry->setNextTransition(Transition::ConfirmOrder->value);

        // make some verification here
        // ....
        $workflowEntry->setCurrentState('verified');
        //TODO make get next transition method
        $workflowEntry->setNextTransition(Transition::ConfirmOrder->value);
        dump('in verified');

        $this->eventDispatcher->dispatch(new WorkflowNextStateEvent($workflowEntry));
    }
}

<?php
declare(strict_types=1);

namespace App\Service\Workflow;

use App\Entity\WorkflowEntry;
use App\Service\Workflow\Event\WorkflowNextStateEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class WorkflowHandler
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function handle(WorkflowEntry $workflowEntry): void
    {
        try {
            $this->eventDispatcher->dispatch(new WorkflowNextStateEvent($workflowEntry));
        } catch (\Throwable $exception) {

        }
    }

}

<?php
declare(strict_types=1);

namespace App\Service\Workflow;

use App\Entity\WorkflowEntry;
use App\Service\Workflow\Event\WorkflowNextStateEvent;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class WorkflowHandler
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LoggerInterface $logger
    ) {
    }

    public function handle(WorkflowEntry $workflowEntry): void
    {
        try {
            $this->eventDispatcher->dispatch(new WorkflowNextStateEvent($workflowEntry));
        } catch (\Throwable $exception) {
            $this->logger->error(
                sprintf(
                    'An error occurred during handling workflow "%s". Workflow state: %s',
                    $workflowEntry->getWorkflowType()->value,
                    $workflowEntry->getCurrentState(),
                ),
                [
                    $exception
                ]
            );
        }
    }

}

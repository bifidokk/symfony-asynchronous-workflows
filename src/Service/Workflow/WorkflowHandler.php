<?php
declare(strict_types=1);

namespace App\Service\Workflow;

use App\Entity\WorkflowEntry;
use App\Service\Workflow\Event\WorkflowNextStateEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class WorkflowHandler
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
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

            $workflowEntry->setStatus(WorkflowStatus::Stopped);
            $this->entityManager->persist($workflowEntry);
            $this->entityManager->flush();
        }
    }

}

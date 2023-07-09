<?php
declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\WorkflowEntry;
use App\Message\WorkflowEntryProcessNotification;
use App\Repository\WorkflowEntryRepository;
use App\Service\Workflow\WorkflowHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class WorkflowEntryProcessNotificationHandler
{
    public function __construct(
        private readonly WorkflowEntryRepository $workflowEntryRepository,
        private readonly WorkflowHandler $workflowHandler,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(WorkflowEntryProcessNotification $entryProcessNotification): void
    {
        $workflowId = $entryProcessNotification->getWorkflowId();
        $workflowEntry = $this->workflowEntryRepository->find($workflowId);

        if (!$workflowEntry instanceof WorkflowEntry) {
            return;
        }

        $this->logger->info(sprintf(
            'Process workflow entry %s asynchronously',
            $workflowEntry->getId(),
        ));

        try {
            $this->workflowHandler->retry($workflowEntry);
        } catch (\Throwable $exception) {
            // we suppose to handle errors in the App\Service\Workflow\WorkflowHandler
        }
    }
}

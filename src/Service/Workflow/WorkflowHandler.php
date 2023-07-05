<?php
declare(strict_types=1);

namespace App\Service\Workflow;

use App\Entity\WorkflowEntry;
use App\Service\Workflow\Event\WorkflowNextStateEvent;
use App\Service\Workflow\Exception\WorkflowInternalErrorException;
use App\Service\Workflow\Stamp\WorkflowInternalErrorStamp;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class WorkflowHandler
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
        private readonly NormalizerInterface $normalizer,
        private readonly DenormalizerInterface $denormalizer,
    ) {
    }

    public function handle(WorkflowEntry $workflowEntry): void
    {
        try {
            $this->eventDispatcher->dispatch(new WorkflowNextStateEvent($workflowEntry));
        } catch (WorkflowInternalErrorException | \Throwable  $exception) {
            $this->logger->error(
                sprintf(
                    'An internal error occurred during handling workflow "%s". Workflow state: %s',
                    $workflowEntry->getWorkflowType()->value,
                    $workflowEntry->getCurrentState(),
                ),
                [
                    $exception
                ]
            );

            $workflowEntry->setStatus(WorkflowStatus::Stopped);

            /** @var WorkflowEnvelope $envelope */
            $envelope = $this->denormalizer->denormalize($workflowEntry->getStamps(), WorkflowEnvelope::class);


            $envelope->addStamp(new WorkflowInternalErrorStamp(
                $exception->getMessage(),
            ));

            /** @var array<WorkflowStampInterface> $stamps */
            $stamps = $this->normalizer->normalize($envelope, 'array');
            $workflowEntry->setStamps($stamps);

            $this->entityManager->persist($workflowEntry);
            $this->entityManager->flush();
        }
    }

    public function retry(WorkflowEntry $workflowEntry): void
    {
        $workflowEntry->addRetry();
        $workflowEntry->setStatus(WorkflowStatus::Started);

        $this->entityManager->persist($workflowEntry);
        $this->entityManager->flush();

        $this->handle($workflowEntry);
    }
}

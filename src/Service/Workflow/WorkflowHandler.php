<?php
declare(strict_types=1);

namespace App\Service\Workflow;

use App\Entity\WorkflowEntry;
use App\Message\WorkflowEntryProcessNotification;
use App\Service\Workflow\Envelope\WorkflowEnvelope;
use App\Service\Workflow\Event\WorkflowNextStateEvent;
use App\Service\Workflow\Exception\WorkflowInternalErrorException;
use App\Service\Workflow\Exception\ProceedWorkflowInQueueException;
use App\Service\Workflow\Exception\StopWorkflowException;
use App\Service\Workflow\Stamp\WorkflowInternalErrorStamp;
use App\Service\Workflow\Stamp\WorkflowProcessingInQueueStamp;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
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
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function handle(WorkflowEntry $workflowEntry): void
    {
        try {
            $this->eventDispatcher->dispatch(new WorkflowNextStateEvent($workflowEntry));
        } catch (ProceedWorkflowInQueueException $exception) {
            $this->logger->error(
                sprintf(
                    'An internal error occurred during handling workflow "%s". Workflow state: %s. The workflow will be processed in a queue',
                    $workflowEntry->getWorkflowType()->value,
                    $workflowEntry->getCurrentState(),
                ),
                [
                    $exception
                ]
            );

            $this->processInQueue($workflowEntry);
        } catch (StopWorkflowException $exception) {
            $this->logger->error(
                sprintf(
                    'An permanent internal error occurred during handling workflow "%s". Workflow state: %s. The workflow stopped.',
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

            $workflowEntry->setStatus(WorkflowStatus::Failed);
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

    public function processInQueue(WorkflowEntry $workflowEntry): void
    {
        $workflowEntry->setStatus(WorkflowStatus::QueueProcessing);

        /** @var WorkflowEnvelope $envelope */
        $envelope = $this->denormalizer->denormalize($workflowEntry->getStamps(), WorkflowEnvelope::class);

        if (!$envelope->hasStamp(WorkflowProcessingInQueueStamp::class)) {
            $envelope->addStamp(new WorkflowProcessingInQueueStamp());
        }

        /** @var array<WorkflowStampInterface> $stamps */
        $stamps = $this->normalizer->normalize($envelope, 'array');
        $workflowEntry->setStamps($stamps);

        $this->entityManager->persist($workflowEntry);
        $this->entityManager->flush();

        $orderMessage = new WorkflowEntryProcessNotification($workflowEntry->getId());
        $this->bus->dispatch($orderMessage);
    }
}

<?php
declare(strict_types=1);

namespace App\Service\Workflow\EventSubscriber;

use App\Entity\WorkflowEntry;
use App\Service\Workflow\Envelope\WorkflowEnvelope;
use App\Service\Workflow\Event\WorkflowNextStateEvent;
use App\Service\Workflow\Order\State;
use App\Service\Workflow\WorkflowStatus;
use App\Service\Workflow\WorkflowTransitionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Workflow\Event\Event;

class WorkflowTransitionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ServiceLocator $transitions,
        private readonly NormalizerInterface $normalizer,
        private readonly DenormalizerInterface $denormalizer,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.transition' => 'handleTransition',
        ];
    }

    public function handleTransition(Event $event): void
    {
        /** @var WorkflowEntry $workflowEntry */
        $workflowEntry = $event->getSubject();
        $this->entityManager->getConnection()->beginTransaction();

        try {
            $envelope = $this->denormalizer->denormalize($workflowEntry->getStamps(), WorkflowEnvelope::class);

            /** @var WorkflowTransitionInterface $transition */
            $transition = $this->transitions->get($workflowEntry->getNextTransition());
            $envelope = $transition->handle($envelope);

            /** @var array $stamps */
            $stamps = $this->normalizer->normalize($envelope, 'array');

            $workflowEntry->setStamps($stamps);
            $workflowEntry->setCurrentState($transition->getState());
            $workflowEntry->setNextTransition($transition->getNextTransition());

            if ($workflowEntry->getNextTransition() === null) {
                $workflowEntry->setStatus(WorkflowStatus::Finished);
            }

            $this->entityManager->persist($workflowEntry);
            $this->entityManager->flush();

            $this->entityManager->getConnection()->commit();
        } catch (\Throwable $exception) {
            $this->entityManager->getConnection()->rollBack();

            throw $exception;
        }

        if ($workflowEntry->getNextTransition() !== null) {
            $this->eventDispatcher->dispatch(new WorkflowNextStateEvent($workflowEntry));
        }
    }
}

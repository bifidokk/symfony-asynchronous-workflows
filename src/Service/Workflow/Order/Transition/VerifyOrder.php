<?php
declare(strict_types=1);

namespace App\Service\Workflow\Order\Transition;

use App\Entity\WorkflowEntry;
use App\Service\Workflow\Event\WorkflowNextStateEvent;
use App\Service\Workflow\Order\Transition;
use App\Service\Workflow\WorkflowEnvelope;
use App\Service\Workflow\WorkflowTransitionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class VerifyOrder implements WorkflowTransitionInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly EntityManagerInterface $entityManager,
        private readonly NormalizerInterface $normalizer,
        private readonly DenormalizerInterface $denormalizer,
    ) {
    }

    public function handle(WorkflowEntry $workflowEntry): void
    {
        $envelope = $this->denormalizer->denormalize($workflowEntry->getStamps(), WorkflowEnvelope::class);

        // make some verification here
        // ....

        $workflowEntry->setCurrentState('verified');
        $workflowEntry->setNextTransition($this->getNextTransition());
        dump('in verified');

        /** @var array $stamps */
        $stamps = $this->normalizer->normalize($envelope, 'array');

        $workflowEntry->setStamps($stamps);
        $this->entityManager->persist($workflowEntry);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new WorkflowNextStateEvent($workflowEntry));
    }

    public function getNextTransition(): ?string
    {
        return Transition::ConfirmOrder->value;
    }
}

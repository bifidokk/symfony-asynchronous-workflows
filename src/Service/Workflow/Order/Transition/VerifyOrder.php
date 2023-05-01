<?php
declare(strict_types=1);

namespace App\Service\Workflow\Order\Transition;

use App\Entity\WorkflowEntry;
use App\Service\Workflow\Event\WorkflowNextStateEvent;
use App\Service\Workflow\Order\Transition;
use App\Service\Workflow\WorkflowEnvelope;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\SerializerInterface;

class VerifyOrder
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function handle(WorkflowEntry $workflowEntry): void
    {
        $envelope = $this->serializer->denormalize($workflowEntry->getStamps(), WorkflowEnvelope::class);

        // make some verification here
        // ....

        $workflowEntry->setCurrentState('verified');
        // TODO make get next transition method
        $workflowEntry->setNextTransition(Transition::ConfirmOrder->value);
        dump('in verified');

        $workflowEntry->setStamps($this->serializer->normalize($envelope));
        $this->entityManager->persist($workflowEntry);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new WorkflowNextStateEvent($workflowEntry));
    }
}

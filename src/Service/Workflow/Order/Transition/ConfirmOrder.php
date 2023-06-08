<?php
declare(strict_types=1);

namespace App\Service\Workflow\Order\Transition;

use App\Entity\WorkflowEntry;
use App\Service\Workflow\Event\WorkflowNextStateEvent;
use App\Service\Workflow\Order\Transition;
use App\Service\Workflow\WorkflowTransitionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class ConfirmOrder implements WorkflowTransitionInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function handle(WorkflowEntry $workflowEntry): void
    {
        // make some confirmation here
        // ....
        $workflowEntry->setCurrentState('confirmed');
        $workflowEntry->setNextTransition($this->getNextTransition());
        dump('in confirmed');

        $this->entityManager->persist($workflowEntry);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new WorkflowNextStateEvent($workflowEntry));
    }

    public function getNextTransition(): ?string
    {
        return Transition::CompleteOrder->value;
    }
}

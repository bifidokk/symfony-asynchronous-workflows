<?php
declare(strict_types=1);

namespace App\Service\Workflow\Order\Transition;

use App\Entity\WorkflowEntry;
use App\Service\Workflow\Event\WorkflowNextStateEvent;
use App\Service\Workflow\Order\Transition;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class ConfirmOrder
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
        $workflowEntry->setNextTransition(Transition::CompleteOrder->value);
        dump('in confirmed');

        $this->entityManager->persist($workflowEntry);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new WorkflowNextStateEvent($workflowEntry));
    }
}

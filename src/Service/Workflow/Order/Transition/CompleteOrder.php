<?php
declare(strict_types=1);

namespace App\Service\Workflow\Order\Transition;

use App\Entity\WorkflowEntry;
use App\Service\Workflow\WorkflowTransitionInterface;
use Doctrine\ORM\EntityManagerInterface;

class CompleteOrder implements WorkflowTransitionInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function handle(WorkflowEntry $workflowEntry): void
    {
        // make some business logic
        // ....

        // order is completed
        $workflowEntry->setCurrentState('completed');
        dump('in complete');

        $this->entityManager->persist($workflowEntry);
        $this->entityManager->flush();
    }

    public function getNextTransition(): ?string
    {
        return null;
    }
}

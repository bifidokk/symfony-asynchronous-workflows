<?php

namespace App\Service\Workflow\Order\Transition;

use App\Entity\WorkflowEntry;
use Doctrine\ORM\EntityManagerInterface;

class CompleteOrder
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
}

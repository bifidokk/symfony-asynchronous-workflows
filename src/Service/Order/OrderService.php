<?php
declare(strict_types=1);

namespace App\Service\Order;

use App\Entity\Order;
use App\Service\Workflow\Order\OrderCompleteWorkflowBuilder;
use App\Service\Workflow\WorkflowHandler;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly WorkflowHandler $workflowHandler,
        private readonly OrderCompleteWorkflowBuilder $orderCompleteWorkflowBuilder,
    ) {
    }

    public function createOrder(): void
    {
        $order = new Order();
        $order->setDescription('my order');

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        $this->workflowHandler->handle(
            $this->orderCompleteWorkflowBuilder->create($order)
        );
    }
}

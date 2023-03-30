<?php

namespace App\Service\Order;


use App\Entity\Order;
use App\Entity\WorkflowEntry;
use App\Service\Workflow\Order\Stamp\OrderIdStamp;
use App\Service\Workflow\WorkflowType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Workflow\WorkflowInterface;

class OrderService
{
    public function __construct(
        private readonly WorkflowInterface $orderCompleteStateMachine,
        private readonly EntityManagerInterface $entityManager
    ) {

    }

    public function createOrder(): void
    {
        $order = new Order();
        $order->setDescription('my order');

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        $orderComplete = WorkflowEntry::create(
            WorkflowType::OrderComplete,
            [
                new OrderIdStamp($order->getId()),
            ]
        );

        $this->orderCompleteStateMachine->apply($orderComplete, 'verify_order');
    }
}

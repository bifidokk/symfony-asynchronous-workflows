<?php

namespace App\Service\Order;


use App\Entity\Order;
use App\Entity\WorkflowEntry;
use App\Service\Workflow\Order\Stamp\OrderIdStamp;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Workflow\WorkflowInterface;

class OrderService
{
    public function __construct(
        private WorkflowInterface $orderCompleteStateMachine,
        private EntityManagerInterface $entityManager
    ) {

    }

    public function createOrder(): void
    {
        $order = new Order();
        $order->setDescription('my order');

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        $orderComplete = new WorkflowEntry();
        $orderComplete->setName(Order::class);
        $orderComplete->addStamp(new OrderIdStamp($order->getId()));

        $this->orderCompleteStateMachine->apply($orderComplete, 'verify_order');
    }
}

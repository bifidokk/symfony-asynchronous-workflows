<?php
declare(strict_types=1);

namespace App\Service\Order;

use App\Entity\Order;
use App\Entity\WorkflowEntry;
use App\Service\Workflow\Event\WorkflowNextStateEvent;
use App\Service\Workflow\Order\Stamp\OrderIdStamp;
use App\Service\Workflow\Order\Transition;
use App\Service\Workflow\WorkflowType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class OrderService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $eventDispatcher,
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
            Transition::VerifyOrder->value,
            [
                new OrderIdStamp($order->getId()),
            ]
        );

        $this->eventDispatcher->dispatch(new WorkflowNextStateEvent($orderComplete));
    }
}

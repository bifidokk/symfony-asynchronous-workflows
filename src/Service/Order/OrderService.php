<?php
declare(strict_types=1);

namespace App\Service\Order;

use App\Entity\Order;
use App\Service\Workflow\Order\OrderSendWorkflowBuilder;
use App\Service\Workflow\Stamp\ThrowExceptionStamp;
use App\Service\Workflow\Stamp\ThrowProcessInQueueExceptionStamp;
use App\Service\Workflow\WorkflowHandler;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly WorkflowHandler $workflowHandler,
        private readonly OrderSendWorkflowBuilder $orderSendWorkflowBuilder,
    ) {
    }

    public function createOrder(): Order
    {
        $order = new Order();
        $order->setDescription('my order');

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        $this->workflowHandler->handle(
            $this->orderSendWorkflowBuilder->create($order)
        );

        return $order;
    }

    public function createEmptyDescriptionOrder(): Order
    {
        $order = new Order();
        $order->setDescription('');

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        $this->workflowHandler->handle(
            $this->orderSendWorkflowBuilder->create($order)
        );

        return $order;
    }

    public function createOrderWithErrorFlow(): Order
    {
        $order = new Order();
        $order->setDescription('my order');

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        $this->workflowHandler->handle(
            $this->orderSendWorkflowBuilder->create(
                $order,
                [
                    new ThrowExceptionStamp(),
                ]
            ),
        );

        return $order;
    }

    public function createOrderWithErrorAndProcessingInQueueFlow(): Order
    {
        $order = new Order();
        $order->setDescription('my order');

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        $this->workflowHandler->handle(
            $this->orderSendWorkflowBuilder->create(
                $order,
                [
                    new ThrowProcessInQueueExceptionStamp(),
                ]
            ),
        );

        return $order;
    }
}

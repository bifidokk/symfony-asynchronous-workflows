<?php
declare(strict_types=1);

namespace App\Service\Order;

use App\Entity\Order;
use App\Entity\WorkflowEntry;
use App\Service\Workflow\Event\WorkflowNextStateEvent;
use App\Service\Workflow\Order\Stamp\OrderIdStamp;
use App\Service\Workflow\Order\Transition;
use App\Service\Workflow\WorkflowEnvelope;
use App\Service\Workflow\WorkflowHandler;
use App\Service\Workflow\WorkflowType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class OrderService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly WorkflowHandler $workflowHandler,
        private readonly NormalizerInterface $normalizer
    ) {
    }

    public function createOrder(): void
    {
        $order = new Order();
        $order->setDescription('my order');

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        $envelope = new WorkflowEnvelope(
            [
                OrderIdStamp::createWithOrderId($order->getId()),
            ]
        );

        /** @var array $stamps */
        $stamps = $this->normalizer->normalize($envelope, 'array');

        $orderComplete = WorkflowEntry::create(
            WorkflowType::OrderComplete,
            Transition::VerifyOrder->value,
            $stamps
        );

        $this->workflowHandler->handle($orderComplete);
    }
}

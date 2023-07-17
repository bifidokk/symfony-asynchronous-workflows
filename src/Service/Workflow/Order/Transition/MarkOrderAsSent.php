<?php
declare(strict_types=1);

namespace App\Service\Workflow\Order\Transition;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Service\Workflow\Exception\StopWorkflowException;
use App\Service\Workflow\Order\Stamp\OrderIdStamp;
use App\Service\Workflow\Order\State;
use App\Service\Workflow\Envelope\WorkflowEnvelope;
use App\Service\Workflow\WorkflowTransitionInterface;
use Doctrine\ORM\EntityManagerInterface;

class MarkOrderAsSent implements WorkflowTransitionInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly OrderRepository $orderRepository,
    ) {
    }

    public function handle(WorkflowEnvelope $envelope): WorkflowEnvelope
    {
        /** @var OrderIdStamp $orderIdStamp */
        $orderIdStamp = $envelope->getStamp(OrderIdStamp::class);
        $orderId = $orderIdStamp->getOrderId();

        /** @var Order|null $order */
        $order = $this->orderRepository->find($orderId);

        if (!$order instanceof Order) {
            throw new StopWorkflowException(sprintf('Order %s not found', $orderId));
        }

        $order->markAsSent();

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $envelope;
    }

    public function getNextTransition(): ?string
    {
        return null;
    }

    public function getState(): ?string
    {
        return State::MarkedAsSent->value;
    }
}

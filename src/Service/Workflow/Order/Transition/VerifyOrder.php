<?php
declare(strict_types=1);

namespace App\Service\Workflow\Order\Transition;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Service\Workflow\Envelope\WorkflowEnvelope;
use App\Service\Workflow\Order\Exception\OrderException;
use App\Service\Workflow\Order\Stamp\OrderIdStamp;
use App\Service\Workflow\Order\State;
use App\Service\Workflow\Order\Transition;
use App\Service\Workflow\WorkflowTransitionInterface;

class VerifyOrder implements WorkflowTransitionInterface
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
    ) {
    }

    public function handle(WorkflowEnvelope $envelope): WorkflowEnvelope
    {
        /** @var OrderIdStamp $orderIdStamp */
        $orderIdStamp = $envelope->getStamp(OrderIdStamp::class);
        $orderId = $orderIdStamp->getOrderId();

        $order = $this->orderRepository->find($orderId);

        if (!$order instanceof Order) {
            throw new \Exception(sprintf('Order %s not found', $orderId));
        }

        if ($order->getDescription() === '') {
            throw OrderException::shouldHaveDescription($order);
        }

        return $envelope;
    }

    public function getNextTransition(): ?string
    {
        return Transition::ConfirmOrder->value;
    }

    public function getState(): ?string
    {
        return State::Verified->value;
    }
}

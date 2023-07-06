<?php
declare(strict_types=1);

namespace App\Service\Workflow\Order\Transition;

use App\Entity\Order;
use App\Entity\WorkflowEntry;
use App\Repository\OrderRepository;
use App\Service\Workflow\Envelope\WorkflowEnvelopeStampHandler;
use App\Service\Workflow\Order\Exception\OrderException;
use App\Service\Workflow\Order\Stamp\OrderIdStamp;
use App\Service\Workflow\Order\State;
use App\Service\Workflow\Order\Transition;
use App\Service\Workflow\WorkflowTransitionInterface;
use Doctrine\ORM\EntityManagerInterface;
class VerifyOrder implements WorkflowTransitionInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly OrderRepository $orderRepository,
        private readonly WorkflowEnvelopeStampHandler $workflowEnvelopeStampHandler,
    ) {
    }

    public function handle(WorkflowEntry $workflowEntry): void
    {
        /** @var OrderIdStamp $orderIdStamp */
        $orderIdStamp = $this->workflowEnvelopeStampHandler->getStamp(
            $workflowEntry,
            OrderIdStamp::class
        );

        $orderId = $orderIdStamp->getOrderId();

        $order = $this->orderRepository->find($orderId);

        if (!$order instanceof Order) {
            throw new \Exception(sprintf('Order %s not found', $orderId));
        }

        if ($order->getDescription() === '') {
            throw OrderException::shouldHaveDescription($order);
        }

        $workflowEntry->setCurrentState(State::Verified->value);
        $workflowEntry->setNextTransition($this->getNextTransition());

        $this->entityManager->persist($workflowEntry);
        $this->entityManager->flush();
    }

    public function getNextTransition(): ?string
    {
        return Transition::ConfirmOrder->value;
    }
}

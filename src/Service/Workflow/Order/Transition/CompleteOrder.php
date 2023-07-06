<?php
declare(strict_types=1);

namespace App\Service\Workflow\Order\Transition;

use App\Entity\Order;
use App\Entity\WorkflowEntry;
use App\Repository\OrderRepository;
use App\Service\Workflow\Envelope\WorkflowEnvelopeStampHandler;
use App\Service\Workflow\Order\Stamp\OrderIdStamp;
use App\Service\Workflow\Order\State;
use App\Service\Workflow\Envelope\WorkflowEnvelope;
use App\Service\Workflow\WorkflowStatus;
use App\Service\Workflow\WorkflowTransitionInterface;
use Doctrine\ORM\EntityManagerInterface;

class CompleteOrder implements WorkflowTransitionInterface
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

        /** @var Order $order */
        $order = $this->orderRepository->find($orderId);
        $order->complete();

        $this->entityManager->persist($order);

        $workflowEntry->setCurrentState(State::Completed->value);
        $workflowEntry->setNextTransition($this->getNextTransition());

        if ($workflowEntry->getNextTransition() === null) {
            $workflowEntry->setStatus(WorkflowStatus::Finished);
        }

        $this->entityManager->persist($workflowEntry);
        $this->entityManager->flush();
    }

    public function getNextTransition(): ?string
    {
        return null;
    }
}

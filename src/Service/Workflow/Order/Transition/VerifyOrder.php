<?php
declare(strict_types=1);

namespace App\Service\Workflow\Order\Transition;

use App\Entity\Order;
use App\Entity\WorkflowEntry;
use App\Repository\OrderRepository;
use App\Service\Workflow\Event\WorkflowNextStateEvent;
use App\Service\Workflow\Order\Exception\OrderException;
use App\Service\Workflow\Order\Stamp\OrderIdStamp;
use App\Service\Workflow\Order\Transition;
use App\Service\Workflow\WorkflowEnvelope;
use App\Service\Workflow\WorkflowTransitionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class VerifyOrder implements WorkflowTransitionInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly EntityManagerInterface $entityManager,
        private readonly NormalizerInterface $normalizer,
        private readonly DenormalizerInterface $denormalizer,
        private readonly OrderRepository $orderRepository,
    ) {
    }

    public function handle(WorkflowEntry $workflowEntry): void
    {
        /** @var WorkflowEnvelope $envelope */
        $envelope = $this->denormalizer->denormalize($workflowEntry->getStamps(), WorkflowEnvelope::class);

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

        $workflowEntry->setCurrentState('verified');
        $workflowEntry->setNextTransition($this->getNextTransition());
        dump('in verified');

        /** @var array $stamps */
        $stamps = $this->normalizer->normalize($envelope, 'array');

        $workflowEntry->setStamps($stamps);
        $this->entityManager->persist($workflowEntry);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new WorkflowNextStateEvent($workflowEntry));
    }

    public function getNextTransition(): ?string
    {
        return Transition::ConfirmOrder->value;
    }
}

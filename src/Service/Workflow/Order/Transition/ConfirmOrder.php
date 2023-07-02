<?php
declare(strict_types=1);

namespace App\Service\Workflow\Order\Transition;

use App\Entity\Order;
use App\Entity\WorkflowEntry;
use App\Repository\OrderRepository;
use App\Service\Workflow\Order\Stamp\OrderIdStamp;
use App\Service\Workflow\Order\Stamp\ThrowExceptionStamp;
use App\Service\Workflow\Order\State;
use App\Service\Workflow\Order\Transition;
use App\Service\Workflow\WorkflowEnvelope;
use App\Service\Workflow\WorkflowTransitionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ConfirmOrder implements WorkflowTransitionInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DenormalizerInterface $denormalizer,
        private readonly OrderRepository $orderRepository,
        private readonly MailerInterface $mailer,
        private readonly string $orderConfirmationEmail
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

        $this->mailer->send(
            (new Email())
                ->from('admin@order.io')
                ->to($this->orderConfirmationEmail)
                ->subject('Order confirmed')
                ->text(sprintf('Order %s confirmed', $orderId))
        );

        $workflowEntry->setCurrentState(State::Confirmed->value);
        $workflowEntry->setNextTransition($this->getNextTransition());
        dump('in confirmed');

        $this->entityManager->persist($workflowEntry);
        $this->entityManager->flush();

        if ($envelope->hasStampWithType(ThrowExceptionStamp::class)) {
            throw new \Exception('An internal error occurred');
        }
    }

    public function getNextTransition(): ?string
    {
        return Transition::CompleteOrder->value;
    }
}

<?php
declare(strict_types=1);

namespace App\Service\Workflow\Order\Transition;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Service\Workflow\Exception\WorkflowInternalErrorException;
use App\Service\Workflow\Exception\WorkflowProcessInQueueException;
use App\Service\Workflow\Exception\WorkflowStopException;
use App\Service\Workflow\Order\Stamp\OrderIdStamp;
use App\Service\Workflow\Order\State;
use App\Service\Workflow\Order\Transition;
use App\Service\Workflow\Stamp\ThrowExceptionStamp;
use App\Service\Workflow\Stamp\ThrowProcessInQueueExceptionStamp;
use App\Service\Workflow\Stamp\WorkflowInternalErrorStamp;
use App\Service\Workflow\Envelope\WorkflowEnvelope;
use App\Service\Workflow\Stamp\WorkflowProcessingInQueueStamp;
use App\Service\Workflow\WorkflowTransitionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class SendOrder implements WorkflowTransitionInterface
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly MailerInterface $mailer,
        private readonly string $orderConfirmationEmail,
    ) {
    }

    public function handle(WorkflowEnvelope $envelope): WorkflowEnvelope
    {
        /** @var OrderIdStamp $orderIdStamp */
        $orderIdStamp = $envelope->getStamp(OrderIdStamp::class);
        $orderId = $orderIdStamp->getOrderId();

        $order = $this->orderRepository->find($orderId);

        if (!$order instanceof Order) {
            throw new WorkflowStopException(sprintf('Order %s not found', $orderId));
        }

        try {
            $this->mailer->send(
                (new Email())
                    ->from('admin@order.io')
                    ->to($this->orderConfirmationEmail)
                    ->subject('Order confirmed')
                    ->text(sprintf('Order %s confirmed', $orderId))
            );
        } catch (\Throwable $exception) {
            throw new WorkflowProcessInQueueException();
        }

        /**
         * This block simulates one time error to allow retry the workflow later
         */
        if ($envelope->hasStamp(ThrowExceptionStamp::class)
            && !$envelope->hasStamp(WorkflowInternalErrorStamp::class)
        ) {
            throw new WorkflowInternalErrorException();
        }

        if ($envelope->hasStamp(ThrowProcessInQueueExceptionStamp::class)
            && !$envelope->hasStamp(WorkflowProcessingInQueueStamp::class)
        ) {
            throw new WorkflowProcessInQueueException();
        }

        return $envelope;
    }

    public function getNextTransition(): ?string
    {
        return Transition::MarkOrderAsSent->value;
    }

    public function getState(): ?string
    {
        return State::Sent->value;
    }
}

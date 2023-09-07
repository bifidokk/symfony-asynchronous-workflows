<?php
declare(strict_types=1);

namespace App\Service\Workflow\Order\Transition;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Service\Workflow\Envelope\WorkflowEnvelope;
use App\Service\Workflow\Exception\StopWorkflowException;
use App\Service\Workflow\Exception\WorkflowInternalErrorException;
use App\Service\Workflow\Order\Stamp\OrderIdStamp;
use App\Service\Workflow\Order\State;
use App\Service\Workflow\Order\Transition;
use App\Service\Workflow\WorkflowTransitionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApproveOrder implements WorkflowTransitionInterface
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly HttpClientInterface $client,
    ){
    }

    public function handle(WorkflowEnvelope $envelope): WorkflowEnvelope
    {
        /** @var OrderIdStamp $orderIdStamp */
        $orderIdStamp = $envelope->getStamp(OrderIdStamp::class);
        $orderId = $orderIdStamp->getOrderId();

        $order = $this->orderRepository->find($orderId);

        if (!$order instanceof Order) {
            throw new StopWorkflowException(sprintf('Order %s not found', $orderId));
        }

        /**
         * Here we have to send the order to the order service and receive approval that it's possible to create it.
         * During the sending there might be a network error, the service might be unavailable or the service might refuse the order.
         */
        try {
            $response = $this->client->request(
                'GET',
                'https://api.github.com/repos/bifidokk/symfony-asynchronous-workflows'
            );
        } catch (\Throwable) {
            throw new WorkflowInternalErrorException();
        }

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            throw new WorkflowInternalErrorException();
        }

        return $envelope;
    }

    public function getNextTransition(): ?string
    {
        return Transition::SendOrderToEmail->value;
    }

    public function getState(): ?string
    {
        return State::Approved->value;
    }
}

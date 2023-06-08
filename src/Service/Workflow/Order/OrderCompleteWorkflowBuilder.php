<?php
declare(strict_types=1);

namespace App\Service\Workflow\Order;

use App\Entity\Order;
use App\Entity\WorkflowEntry;
use App\Service\Workflow\Order\Stamp\OrderIdStamp;
use App\Service\Workflow\WorkflowEnvelope;
use App\Service\Workflow\WorkflowType;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class OrderCompleteWorkflowBuilder
{
    public function __construct(
        private readonly NormalizerInterface $normalizer
    ) {
    }

    public function create(Order $order): WorkflowEntry
    {
        $envelope = new WorkflowEnvelope(
            [
                OrderIdStamp::createWithOrderId($order->getId()),
            ]
        );

        /** @var array $stamps */
        $stamps = $this->normalizer->normalize($envelope, 'array');

        return WorkflowEntry::create(
            WorkflowType::OrderComplete,
            Transition::VerifyOrder->value,
            $stamps
        );
    }
}

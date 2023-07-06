<?php
declare(strict_types=1);

namespace App\Service\Workflow\Order;

use App\Entity\Order;
use App\Entity\WorkflowEntry;
use App\Service\Workflow\Envelope\WorkflowEnvelopeStampHandler;
use App\Service\Workflow\Order\Stamp\OrderIdStamp;
use App\Service\Workflow\Envelope\WorkflowEnvelope;
use App\Service\Workflow\WorkflowType;

class OrderCompleteWorkflowBuilder
{
    public function __construct(
        private readonly WorkflowEnvelopeStampHandler $workflowEnvelopeStampHandler,
    ) {
    }

    public function create(
        Order $order,
        array $additionStamps = []
    ): WorkflowEntry {
        $envelope = new WorkflowEnvelope(
            array_merge([
                OrderIdStamp::createWithOrderId($order->getId()),
            ], $additionStamps
        ));

        $stamps = $this->workflowEnvelopeStampHandler->getNormalizedStamps($envelope);

        return WorkflowEntry::create(
            WorkflowType::OrderComplete,
            Transition::VerifyOrder->value,
            $stamps
        );
    }
}

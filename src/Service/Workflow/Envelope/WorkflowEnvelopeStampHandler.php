<?php
declare(strict_types=1);

namespace App\Service\Workflow\Envelope;

use App\Entity\WorkflowEntry;
use App\Service\Workflow\Order\Stamp\OrderIdStamp;
use App\Service\Workflow\WorkflowStampInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class WorkflowEnvelopeStampHandler
{
    public function __construct(
        private readonly NormalizerInterface $normalizer,
        private readonly DenormalizerInterface $denormalizer,
    ) {
    }

    public function getEnvelope(WorkflowEntry $workflowEntry): WorkflowEnvelope
    {
        return $this->denormalizer->denormalize($workflowEntry->getStamps(), WorkflowEnvelope::class);
    }

    public function addStamp(
        WorkflowEntry $workflowEntry,
        WorkflowStampInterface $stamp,
    ): WorkflowEntry {
        $envelope = $this->getEnvelope($workflowEntry);

        $envelope->addStamp($stamp);
        /** @var array<WorkflowStampInterface> $stamps */
        $stamps = $this->normalizer->normalize($envelope, 'array');
        $workflowEntry->setStamps($stamps);

        return $workflowEntry;
    }

    public function getStamp(
        WorkflowEntry $workflowEntry,
        string $stampClass,
    ): WorkflowStampInterface {
        $envelope = $this->getEnvelope($workflowEntry);

        return $envelope->getStamp($stampClass);
    }

    public function getNormalizedStamps(WorkflowEnvelope $envelope): array
    {
        /** @var array $stamps */
        $stamps = $this->normalizer->normalize($envelope, 'array');

        return $stamps;
    }
}

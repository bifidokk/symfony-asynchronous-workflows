<?php
declare(strict_types=1);

namespace App\Service\Workflow\Serializer;

use App\Service\Workflow\WorkflowEnvelope;
use App\Service\Workflow\WorkflowStampInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class WorkflowEnvelopeDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    {
        $stamps = [];

        foreach ($data['stamps'] as $stampClass => $normalizedStamps) {
            foreach ($normalizedStamps as $normalizedStamp) {
                $stamp = $this->denormalizer->denormalize($normalizedStamp, $stampClass);
                $stamps[] = $stamp;
            }
        }

        return new WorkflowEnvelope($stamps);
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        if (!isset($data['stamps']) || !is_array($data['stamps'])) {
            return false;
        }

        foreach ($data['stamps'] as $stampClass => $data) {
            $interfaces = class_implements($stampClass);

            if (!is_array($interfaces) || !in_array(WorkflowStampInterface::class, $interfaces)) {
                return false;
            }
        }

        return true;
    }
}

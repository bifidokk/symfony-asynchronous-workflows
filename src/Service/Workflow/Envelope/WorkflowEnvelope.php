<?php
declare(strict_types=1);

namespace App\Service\Workflow\Envelope;


use App\Service\Workflow\Exception\CorruptedEnvelopeException;
use App\Service\Workflow\WorkflowStampInterface;

class WorkflowEnvelope
{
    private array $stamps;

    /**
     * @param WorkflowStampInterface[] $stamps
     */
    public function __construct(array $stamps = [])
    {
        $this->replace($stamps);
    }

    /**
     * @param WorkflowStampInterface[] $stamps
     */
    public function replace(array $stamps): void
    {
        $this->stamps = [];

        foreach ($stamps as $stamp) {
            $this->addStamp($stamp);
        }
    }

    public function addStamp(WorkflowStampInterface $stamp): void
    {
        $this->stamps[\get_class($stamp)][] = $stamp;
    }

    public function getStamps(): array
    {
        return $this->stamps;
    }

    public function getStamp(string $stampClass): WorkflowStampInterface
    {
        $stamps = $this->getStampsWithType($stampClass);

        if (count($stamps) !== 1) {
            throw CorruptedEnvelopeException::shouldHaveExactOneStamp($stampClass, count($stamps));
        }

        return reset($stamps);
    }

    /**
     * @param string $stampClass
     *
     * @return WorkflowStampInterface[]
     */
    public function getStampsWithType(string $stampClass): array
    {
        return $this->stamps[$stampClass] ?? [];
    }

    public function hasStamp(string $stampClass): bool
    {
        return isset($this->stamps[$stampClass]);
    }
}

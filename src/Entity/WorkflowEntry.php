<?php

namespace App\Entity;

use App\Repository\WorkflowEntryRepository;
use App\Service\Workflow\WorkflowInterface;
use App\Service\Workflow\WorkflowStampInterface;
use App\Service\Workflow\WorkflowType;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: WorkflowEntryRepository::class)]
class WorkflowEntry implements WorkflowInterface
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\Column(name: "current_state", type: "string")]
    private string $currentState = 'starting';

    #[ORM\Column(name: "workflow_type", length: 32, enumType: WorkflowType::class, options: ["default" => "default"])]
    private WorkflowType $workflowType = WorkflowType::DefaultType;

    #[ORM\Column(name: "next_transition", type: "string")]
    private string $nextTransition = '';

    #[ORM\Column(type: "json")]
    private array $stamps = [];

    #[ORM\Column(type: "string")]
    private string $status = 'starting';

    #[ORM\Column(type: "smallint")]
    private int $retries = 0;

    #[ORM\Column(name: "created_at", type: "datetime_immutable")]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: "updated_at", type: "datetime_immutable")]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public static function create(
        WorkflowType $type,
        string $nextTransition,
        array $stamps = [],
    ) {
        $entry = new WorkflowEntry();
        $entry->setWorkflowType($type);
        $entry->setNextTransition($nextTransition);
        $entry->setStamps($stamps);

        return $entry;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getCurrentState(): string
    {
        return $this->currentState;
    }

    public function setCurrentState(string $currentState): void
    {
        $this->currentState = $currentState;
    }

    public function getWorkflowType(): WorkflowType
    {
        return $this->workflowType;
    }

    public function setWorkflowType(WorkflowType $workflowType): void
    {
        $this->workflowType = $workflowType;
    }

    public function getNextTransition(): string
    {
        return $this->nextTransition;
    }

    public function setNextTransition(string $nextTransition): void
    {
        $this->nextTransition = $nextTransition;
    }

    public function getStamps(): array
    {
        return $this->stamps;
    }

    public function setStamps(array $stamps): void
    {
        $this->stamps = $stamps;
    }

    public function addStamp(WorkflowStampInterface $stamp): void
    {
        $this->stamps[] = $stamp;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getRetries(): int
    {
        return $this->retries;
    }
}

<?php
declare(strict_types=1);

namespace App\Tests\Functional\Service\Order;

use App\Entity\WorkflowEntry;
use App\Repository\WorkflowEntryRepository;
use App\Service\Order\OrderService;
use App\Service\Workflow\Order\Stamp\OrderIdStamp;
use App\Service\Workflow\Order\State;
use App\Service\Workflow\WorkflowEnvelope;
use App\Service\Workflow\WorkflowStatus;
use App\Service\Workflow\WorkflowType;
use App\Tests\Functional\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class OrderServiceTest extends TestCase
{
    private OrderService $orderService;
    private WorkflowEntryRepository $workflowEntryRepository;
    private DenormalizerInterface $denormalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderService = self::getContainer()->get(OrderService::class);
        $this->workflowEntryRepository = self::getContainer()->get(WorkflowEntryRepository::class);
        $this->denormalizer = self::getContainer()->get(DenormalizerInterface::class);
    }

    /**
     * @test
     */
    public function itCreatesOrder(): void
    {
        $order = $this->orderService->createOrder();
        $this->entityManager->refresh($order);

        $this->assertTrue($order->isCompleted());

        $workflowEntry = $this->workflowEntryRepository->findOneBy(
            [],
            ['createdAt' => 'desc'],
        );

        $this->assertInstanceOf(WorkflowEntry::class, $workflowEntry);
        $this->entityManager->refresh($workflowEntry);

        $this->assertEquals(WorkflowType::OrderComplete, $workflowEntry->getWorkflowType());
        $this->assertEquals(State::Completed->value, $workflowEntry->getCurrentState());
        $this->assertEquals(WorkflowStatus::Finished, $workflowEntry->getStatus());

        /** @var WorkflowEnvelope $envelope */
        $envelope = $this->denormalizer->denormalize($workflowEntry->getStamps(), WorkflowEnvelope::class);
        $this->assertTrue($envelope->hasStampWithType(OrderIdStamp::class));

        $stamp = $envelope->getStamp(OrderIdStamp::class);
        $this->assertInstanceOf(OrderIdStamp::class, $stamp);

        $this->assertEquals($order->getId(), $stamp->getOrderId());
    }

    /**
     * @test
     */
    public function itHandlesExceptionDuringOrderCreation(): void
    {
        $order = $this->orderService->createOrderWithErrorFlow();
        $this->entityManager->refresh($order);

        $this->assertFalse($order->isCompleted());

        $workflowEntry = $this->workflowEntryRepository->findOneBy(
            [],
            ['createdAt' => 'desc'],
        );

        $this->assertInstanceOf(WorkflowEntry::class, $workflowEntry);
        $this->entityManager->refresh($workflowEntry);

        $this->assertEquals(WorkflowType::OrderComplete, $workflowEntry->getWorkflowType());
        $this->assertEquals(State::Verified->value, $workflowEntry->getCurrentState());
        $this->assertEquals(WorkflowStatus::Stopped, $workflowEntry->getStatus());
    }
}

<?php

namespace App\Service\Workflow\Order\EventSubscriber;

use App\Entity\WorkflowEntry;
use App\Service\Workflow\Order\Transition\CompleteOrder;
use App\Service\Workflow\Order\Transition\ConfirmOrder;
use App\Service\Workflow\Order\Transition\VerifyOrder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class OrderCreateWorkflowSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private VerifyOrder $verifyOrder,
        private ConfirmOrder $confirmOrder,
        private CompleteOrder $completeOrder
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.order_complete.transition.verify_order' => 'handleVerifyOrderTransition',
            'workflow.order_complete.transition.confirm_order' => 'handleConfirmOrderTransition',
            'workflow.order_complete.transition.complete_order' => 'handleCompleteOrderTransition',
        ];
    }

    public function handleVerifyOrderTransition(Event $event): void
    {
        /** @var WorkflowEntry $workflowEntry */
        $workflowEntry = $event->getSubject();

        $this->verifyOrder->handle($workflowEntry);
    }

    public function handleConfirmOrderTransition(Event $event): void
    {
        /** @var WorkflowEntry $workflowEntry */
        $workflowEntry = $event->getSubject();

        $this->confirmOrder->handle($workflowEntry);
    }

    public function handleCompleteOrderTransition(Event $event): void
    {
        /** @var WorkflowEntry $workflowEntry */
        $workflowEntry = $event->getSubject();

        $this->completeOrder->handle($workflowEntry);
    }
}

<?php

namespace App\Service\Workflow\Order\EventSubscriber;

use App\Service\Workflow\Order\OrderCreate;
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
            'workflow.order_create.transition.verify_order' => 'handleVerifyOrderTransition',
            'workflow.order_create.transition.confirm_order' => 'handleConfirmOrderTransition',
            'workflow.order_create.transition.complete_order' => 'handleCompleteOrderTransition',
        ];
    }

    public function handleVerifyOrderTransition(Event $event): void
    {
        /** @var OrderCreate $orderCreate */
        $orderCreate = $event->getSubject();

        $this->verifyOrder->handle($orderCreate);
    }

    public function handleConfirmOrderTransition(Event $event): void
    {
        /** @var OrderCreate $orderCreate */
        $orderCreate = $event->getSubject();

        $this->confirmOrder->handle($orderCreate);
    }

    public function handleCompleteOrderTransition(Event $event): void
    {
        /** @var OrderCreate $orderCreate */
        $orderCreate = $event->getSubject();

        $this->completeOrder->handle($orderCreate);
    }
}

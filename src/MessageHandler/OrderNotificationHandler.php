<?php

namespace App\MessageHandler;

use App\Message\OrderNotification;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class OrderNotificationHandler
{
    public function __invoke(OrderNotification $message)
    {
        dump('start');
        sleep(30);


        dump($message);
    }
}

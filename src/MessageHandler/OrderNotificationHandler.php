<?php
declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\OrderNotification;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class OrderNotificationHandler
{
    public function __invoke(OrderNotification $message): void
    {
        dump('start');
        sleep(30);


        dump($message);
    }
}

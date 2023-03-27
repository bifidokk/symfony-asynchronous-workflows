<?php

namespace App\Command;

use App\Message\OrderNotification;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

#[AsCommand(name: 'app:create-order')]
class OrderCommand extends Command
{
    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $orderMessage = new OrderNotification(Uuid::v4(), 'Order');
        $this->bus->dispatch($orderMessage);

        return Command::SUCCESS;
    }
}

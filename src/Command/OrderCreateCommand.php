<?php

namespace App\Command;

use App\Service\Order\OrderService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:create-order-workflow')]
class OrderCreateCommand extends Command
{

    public function __construct(private OrderService $orderService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->orderService->createOrder();

        return 0;
    }
}

<?php
declare(strict_types=1);

namespace App\Command;

use App\Entity\WorkflowEntry;
use App\Repository\WorkflowEntryRepository;
use App\Service\Workflow\WorkflowHandler;
use App\Service\Workflow\WorkflowStatus;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:retry-workflow')]
class RetryWorkflowCommand extends Command
{
    public function __construct(
        private readonly WorkflowEntryRepository $workflowEntryRepository,
        private readonly WorkflowHandler $workflowHandler,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'workflow_entry_id',
            null,
            InputOption::VALUE_REQUIRED,
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $workflowEntryId = $input->getOption('workflow_entry_id');

        if (!$workflowEntryId) {
            $output->writeln('Workflow entry id is required');

            return 0;
        }

        $workflowEntry = $this->workflowEntryRepository->findOneBy([
            'id' => $workflowEntryId,
            'status' => WorkflowStatus::Stopped->value,
        ]);

        if (!$workflowEntry instanceof WorkflowEntry) {
            $output->writeln('Workflow entry not found');

            return 0;
        }

        $this->workflowHandler->retry($workflowEntry);

        return 0;
    }
}

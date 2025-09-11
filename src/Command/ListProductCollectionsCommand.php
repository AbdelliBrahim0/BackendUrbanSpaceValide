<?php

namespace App\Command;

use App\Repository\ProductCollectionRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:collections:list',
    description: 'List all product collections in a formatted table',
)]
class ListProductCollectionsCommand extends Command
{
    private ProductCollectionRepository $collectionRepository;

    public function __construct(ProductCollectionRepository $collectionRepository)
    {
        parent::__construct();
        $this->collectionRepository = $collectionRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        // Get all collections ordered by name
        $collections = $this->collectionRepository->findAllOrderedByName();
        
        if (empty($collections)) {
            $io->warning('No collections found.');
            return Command::SUCCESS;
        }

        // Prepare table headers and rows
        $table = new Table($output);
        $table->setHeaders(['ID', 'Name', 'Description 1', 'Description 2', 'Created At', 'URL 1', 'URL 2', 'URL 3']);
        
        foreach ($collections as $collection) {
            $table->addRow([
                $collection->id,
                $collection->name ?? '',
                $collection->description1 ?? '',
                $collection->description2 ?? '',
                $collection->createdAt ? $collection->createdAt->format('Y-m-d H:i:s') : '',
                $collection->url1 ?? '',
                $collection->url2 ?? '',
                $collection->url3 ?? ''
            ]);
        }
        
        $table->render();
        
        return Command::SUCCESS;
    }
}

<?php
namespace BetteMidler\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use BetteMidler\Helper\WordPressInstallHelper;

/**
 * Make the blog installation. Create database tables and define default database values.
 * 
 * This command is based on the WordPress script wp-admin/install.php.
 */
class WordPressInstallCommand extends Command
{
    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('wordpress:install')
            ->setDescription('Make the blog installation. Create database tables and define default database values.')
        ;
    }
    
    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {        
        $output->writeln('Start WordPress installation');
        
        try {
            $helper = new WordPressInstallHelper();
            $helper->ensureDatabase();
            $helper->install();
        } catch (\Exception $e) {
            $output->writeln('Installation error');
            $output->writeln($e->getMessage());
            throw $e;
        }
        
        $output->writeln('Finished WordPress installation');
    }
}
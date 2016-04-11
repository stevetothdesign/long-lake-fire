<?php
namespace BetteMidler\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use BetteMidler\Helper\WordPressConfigHelper;

/**
 * Generate the blog wp-config.php file based in project configuration 
 * parameters.
 * 
 * This command is based on the WordPress script wp-admin/setup-config.php and
 * wp-admin/install.php.
 */
class WordPressConfigCommand extends Command
{
    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('wordpress:config')
            ->setDescription('Generate the wp-config.php file based in project configuration parameters.')
        ;
    }
    
    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start config file generate');
        
        try {
            $helper = new WordPressConfigHelper();
            $helper->ensureDatabase();
            $helper->generateConfigFile();
        } catch (\Exception $e) {
            $output->writeln('File generator aborted');
            throw $e;
        }
        
        $output->writeln('Config file generate successfully');
    }
}
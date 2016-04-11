<?php
namespace BetteMidler\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use BetteMidler\Helper\WordPressUpdateDataHelper;

/**
 * Make the blog installation. Create database tables and define default database values.
 * 
 * This command is based on the WordPress script wp-admin/install.php.
 */
class WordPressUpdateDataCommand extends Command
{
    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('wordpress:update')
            ->setDescription('Sync database and uploads files with the local website. The files is in the extra directory')
        ;
    }
    
    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {    	
        $output->writeln('Start WordPress update data');
        
        try {
            $helper = new WordPressUpdateDataHelper();
            $helper->ensureDatabase();
            $output->writeln("Copying uploads files");
            $helper->updateFiles();
            $output->writeln("Updating database");
            $helper->updateDatabase();

            if($userId = $helper->configAdminExists()) {
            	$output->writeln("Updating admin user");
            	$helper->updateUser($userId);
            } else {
            	$output->writeln("Inserting admin user");
            	$helper->insertUser();
            }
        } catch (\Exception $e) {
            $output->writeln('Update error');
            throw $e;
        }
    }
}
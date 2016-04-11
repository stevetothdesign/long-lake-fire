<?php
namespace BetteMidler\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use BetteMidler\Config\Configuration;

/**
 * A based on Symfony Framework config:dump-reference command.
 * 
 * @link https://github.com/symfony/symfony/blob/v3.0.2/src/Symfony/Bundle/FrameworkBundle/Command/ConfigDumpReferenceCommand.php
 */
class ConfigDebugCommand extends Command
{
    /**
     * 
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName("config:debug")
            ->setDescription("Display the configuration file values.");
    }

    /**
     * 
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configuration = new Configuration();
        $output->writeln(Yaml::dump($configuration->getParams()));
    }
}
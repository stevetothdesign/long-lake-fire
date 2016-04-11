<?php
namespace BetteMidler\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Config\Definition\Dumper\YamlReferenceDumper;
use BetteMidler\Config\Configuration;

/**
 * A based on Symfony Framework config:dump-reference command.
 * 
 * @link https://github.com/symfony/symfony/blob/v3.0.2/src/Symfony/Bundle/FrameworkBundle/Command/ConfigDumpReferenceCommand.php
 */
class ConfigReferenceCommand extends Command
{
    /**
     * 
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName("config:reference")
            ->setDescription("Display reference of the configuration file.");
    }

    /**
     * 
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configuration = new Configuration(false);
        $dumper = new YamlReferenceDumper();
        $output->writeln($dumper->dump($configuration));
    }
}
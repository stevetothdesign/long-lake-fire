<?php
namespace BetteMidler\Config;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Yaml;

class Loader extends FileLoader {

    /**
     * 
     * {@inheritDoc}
     * @see \Symfony\Component\Config\Loader\LoaderInterface::load()
     */
    public function load($resource, $type = null)
    {
    	if (!is_readable($resource)) {
    		throw new \Exception('Config file cannot. Make sure that the config/app.yml file was created and has the right permissions.');
    	}
    	
    	$yaml = Yaml::parse(file_get_contents($resource));
    	if (is_null($yaml)) {
    		throw new \Exception('The config/app.yml content cannot be empty.');
    	}
    	
        return $yaml;
    }

    /**
     * The project configuration file must be in YAML format.
     * 
     * {@inheritDoc}
     * @see \Symfony\Component\Config\Loader\LoaderInterface::supports()
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'yml' === pathinfo(
            $resource,
            PATHINFO_EXTENSION
        );
    }

}
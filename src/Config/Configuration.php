<?php
namespace BetteMidler\Config;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Definition\Processor;

/**
 * Manage the project configuration file.
 */
class Configuration implements ConfigurationInterface
{

    /**
     * The configuration sections.
     *
     * @var array
     */
    private $sections;

    /**
     * Load the sections configuration values.
     */
    public function __construct($load = true)
    {
    	if ($load) {
        	$this->sections = $this->load();
    	}
    }

    /**
     * (non-PHPdoc)
     * 
     * @see ConfigurationInterface::getConfigTreeBuilder()
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('bettemidler');
 
        $rootNode
            ->children()
	            ->scalarNode('title')
		            ->info('The blog title')
		            ->defaultValue('Bette Midler')
		            ->cannotBeEmpty()
	            ->end()
            	->arrayNode('database')
		            ->info('Database connection parameters.')
	            	->children()
			            ->scalarNode('dbname')
			            ->info('Database name.')
			            ->isRequired()
			            ->cannotBeEmpty()
	            	->end()
	            	->scalarNode('user')
			            ->info('Database user.')
			            ->isRequired()
			            ->cannotBeEmpty()
	            	->end()
	            	->scalarNode('password')
			            ->info('Ignore if the not has password.')
			            ->defaultValue('')
	            	->end()
		            	->scalarNode('host')
			            ->info('Ignore if is localhost.')
			            ->defaultValue('localhost')
			            ->cannotBeEmpty()
	            	->end()
	                ->scalarNode('table_prefix')
	                    ->info('The WordPress database table prefix')
	                    ->defaultValue('wp_')
	                    ->cannotBeEmpty()
	                ->end()
		        ->end()
		    ->end()
			->arrayNode('admin')
			    ->info('Admin user access data. This option must be setted. Even though the values is blank.')
			    ->isRequired()
			    ->children()
				    ->scalarNode('user')
					    ->info('The admin user login.')
			            ->defaultValue('admin')
					    ->cannotBeEmpty()
				    ->end()
				    ->scalarNode('password')
					    ->info('The password. If not set, will be generated.')
					->end()
				    ->scalarNode('email')
					    ->info('The admin user email.')
			            ->defaultValue('admin@admin.com')
					    ->cannotBeEmpty()
				    ->end()
				->end()
		    ->end()
	        ->booleanNode('public')
	            ->info('Discourage search engines from indexing this site.')
	            ->defaultValue(true)
	        ->end()
	        ->scalarNode('language')
	            ->info('The language of the blog')
	            ->defaultValue('en_US')
	            ->cannotBeEmpty()
	        ->end()
        ;
 
        return $treeBuilder;
    }
    
    /**
     * Return the parameters of a configuration section.
     * 
     * @param string $section (Optional) The configuration section. If empty, return all params. Default: empty string. 
     * @return array The section parameters. If section not exist, return an empty array.
     */
    public function getParams($section = '')
    {
        if (empty($section)) {
            return $this->sections;
        }
        
        if (!key_exists($section, $this->sections)) {
            return array();
        }
        
        return $this->sections[$section]; 
    }

    /**
     * Load the configuration file.
     *
     * return array The configuration params.
     */
    public function load()
    {
        $locator = new FileLocator(array(__DIR__ . '/../../config'));
        $loaderResolver = new LoaderResolver(array(new Loader($locator)));
        $delegatingLoader = new DelegatingLoader($loaderResolver);
        
        $configs = $delegatingLoader->load(__DIR__ . '/../../config/app.yml');
        
        $processor = new Processor();
        
        return $processor->processConfiguration(
            $this,
            $configs
        );
    }
}

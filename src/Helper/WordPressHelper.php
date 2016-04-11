<?php
namespace BetteMidler\Helper;

use BetteMidler\Config\Configuration;
use BetteMidler\Wpdb;

class WordPressHelper
{
    protected $params;
    
    protected $wpdb;
    
    public function __construct()
    {
        $configuration = new Configuration();
        $this->params = $configuration->getParams();
        $this->wpdb = new Wpdb($this->params['database']);
    }
    
    public function getParams()
    {
        return $this->params;
    }
    
    public function getWpdb()
    {
        return $this->wpdb;
    }
    
    /**
     * Get the blog path inner the project.
     * 
     * @return string The path
     */
    public function getBlogPath()
    {
        return realpath(__DIR__ . '/../../public');
    }
    
    public function defineGlobals()
    {
        define('WP_INSTALLING', true);
    }
    
    /**
     * Ensure that database is created
     */
    public function ensureDatabase()
    {
    	// database doesn't exist
    	if (!$this->wpdb->ready) {
    		// create database 
    		$dbname = $this->params['database']['dbname'];
    		if (!mysqli_query($this->wpdb->dbh, 'CREATE DATABASE ' . $dbname)) {
    			throw new \Exception('Database ' . $dbname . ' doesn\'t exist and cannot be created.');
    		}
    	}
    }
}

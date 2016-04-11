<?php
namespace BetteMidler;

class Wpdb extends \wpdb
{

    /**
     * Create a wrapper to the WordPress wpdb class.
     * 
     * Set the table prefix using configuration file parameter.
     * 
     * @param array $databaseParams The database parameter in the project 
     *      config database definition.
     */
    public function __construct($databaseParams)
    {   
        parent::__construct($databaseParams['user'], $databaseParams['password'], $databaseParams['dbname'], $databaseParams['host']);
        $this->set_prefix($databaseParams['table_prefix']);
    }
    
    /**
     * Get the database information in the DSN format.
     * 
     * @return string
     */
    public function getDsn() 
    {
    	$host = explode(':', $this->dbhost);
    	
    	if(1 === count($host)) {
    		return sprintf('mysql:dbname=%s;host=%s', $this->dbname, $host[0]);
    	}
    	
    	return sprintf('mysql:dbname=%s;unix_socket=%s', $this->dbname, $host[1]);
    }
    
    /**
     * Get the database user.
     * 
     * @return string
     */
    public function getUser() 
    {
    	return $this->dbuser;
    }
    
    /**
     * Get the database password.
     * 
     * @return string
     */
    public function getPassword() 
    {
    	return $this->dbpassword;
    }
    
    /**
     * Get all tables in the database.
     */
    public function getTables()
    {
    	return $this->get_col('SHOW TABLES');
    }
    
    /**
     * Get prefixes of all WordPress installations in the database.
     * 
     * @param string[] $tables The tables to be verified. Default: all database tables.
     * @return string[] The prefix list. 
     */
    public function getPrefixes(array $tables = array())
    {
    	if(empty($tables)) {
    		$tables = $this->getTables();
    	}
    	
    	$tableBase = $this->tables[0];
    	$prefixes = array();
    	$matches = array();
    	foreach ($tables as $table) {
    		if(preg_match('/(.*)' . $tableBase . '$/', $table, $matches)) {
    			$prefixes[] = $matches[1];
    		}
    	}
    	
    	return $prefixes;
    }

    /**
     * Drop all tables of this WordPress installation.
     */
    public function dropTables()
    {
    	$tables = $this->getTables();
    	$prefixes = $this->getPrefixes();
    	
    	$notThisInstallationPrefixes = array();
    	foreach ($prefixes as $prefix) {
    		if($this->prefix !== $prefix) {
    			$notThisInstallationPrefixes[] = $prefix;
    		}
    	}
    	
    	// regex to get only installation tables
    	$regex = '/^';
    	if (!empty($notThisInstallationPrefixes)) {
    		// ignore another installation tables
    		$regex .= '(?!(' . implode('|', $notThisInstallationPrefixes) . '))';
    	}
    	
    	// get intallation tables to prevent non WordPress tables
    	$regex .= '(' . $this->prefix . ').*/';
    	
    	foreach ($tables as $table) {
    		if(preg_match($regex, $table)) {
    			$this->query('DROP TABLE ' . $table);
    		}
    	}
    }

    /**
     * Connect with the database.
     * 
     * @return boolean True, if success. False, otherwise.
     */
    public function connect()
    {        
    	return $this->db_connect();
    }
}
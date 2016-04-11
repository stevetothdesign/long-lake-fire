<?php
namespace BetteMidler\Helper;

use Symfony\Component\Filesystem\Filesystem;
use Rah\Danpu\Dump;
use Rah\Danpu\Import;

class WordPressUpdateDataHelper extends WordPressHelper
{
	/**
	 * 
	 * @var Filesystem
	 */
	private $filesystem;
	
	/**
	 * 
	 * @var string
	 */
	private $blogUploadsPath;
	
	/**
	 * 
	 * @var string
	 */
	private $dataUploadsPath;
	
    /**
     * 
     */
    public function __construct()
    {
        /** Load WordPress Bootstrap */
        require_once $this->getBlogPath() . '/wp-load.php';
        
        parent::__construct();
        
        $this->filesystem = new Filesystem();
    	$this->blogUploadsPath = $this->getBlogPath() . '/wp-content/uploads';
    	$this->dataPath = $this->getDataPath();
    }
    
    /**
     * Get the data path inner the project.
     *
     * @return string The path
     */
    public function getDataPath()
    {
    	return realpath(__DIR__ . '/../../extra');
    }
	
    /**
     * Copy the upload media files to the blog uploads dir.
     */
    public function copyUploadsData()
    {
    	$directoryIterator = new \RecursiveDirectoryIterator($this->dataPath . '/uploads', \RecursiveDirectoryIterator::SKIP_DOTS);
    	$iterator = new \RecursiveIteratorIterator($directoryIterator, \RecursiveIteratorIterator::SELF_FIRST);
    	foreach ($iterator as $item) {
    		$target = $this->blogUploadsPath . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
    		if ($item->isDir()) {
    			$this->filesystem->mkdir($target);
    		} else {
    			$this->filesystem->copy($item, $target);
    		}
    	}
    }
    
    /**
     * Import the dump file to the database.
     */
    public function importDumpDatabase()
    {
	    $dump = new Dump();
	    $dump
	        ->file($this->dataPath . '/tmp_database.sql')
	        ->dsn($this->wpdb->getDsn())
	        ->user($this->wpdb->getUser())
	        ->pass($this->wpdb->getPassword())
	        ->tmp('/tmp')
	    ;
	
	    new Import($dump);
    }
    
    /**
     * Replace 
     */
    public function changeDumpPrefix() 
    {
    	$dump = file_get_contents($this->dataPath . '/database.sql');
    	
    	// get the table prefix on first table declaration
    	$regex = '/CREATE.+\s`?(.+)commentmeta/';

    	$matches = array();
    	preg_match($regex, $dump, $matches);
    	$dumpPrefix = $matches[1];
    	$newDump = str_replace($dumpPrefix, $this->wpdb->prefix, $dump);
    	
    	file_put_contents($this->dataPath . '/tmp_database.sql', $newDump);
    }
    
    /**
     * Upload the files 
     */
    public function updateFiles()
    {
    	$this->filesystem->remove($this->blogUploadsPath);
    	$this->copyUploadsData();
    }
    
    /**
     * Change the database data for the dump data.
     */
    public function updateDatabase()
    {
    	$this->wpdb->dropTables();
    	$this->changeDumpPrefix();
    	$this->importDumpDatabase();
    	$this->filesystem->remove($this->dataPath . '/tmp_database.sql');
    }
    
    public function configAdminExists() {
    	if( $userId = username_exists($this->params['admin']['user']) ) {
    		return $userId;
    	}

    	if( $userId = email_exists($this->params['admin']['email']) ) {
    		return $userId;
    	}
    	
    	return false;
    }
    
    public function getUserData() {
    	return [
    		'user_login' => $this->params['admin']['user'],
    		'user_email' => $this->params['admin']['email'],
    		'user_pass' => $this->params['admin']['password'],
    	];
    }
    
    public function insertUser() {
    	$userData = array_merge([
    		'role' => 'administrator',
    	], $this->getUserData());
    	
    	$userId = wp_insert_user($userData);
    	
    	if (is_a($userId, 'WP_Error')) {
    		throw new \Exception($userId->get_error_message());
    	}
    }
    
    public function updateUser($userId) {
    	$userData = array_merge([
    		'ID' => $userId,
    		'role' => 'administrator',
    	], $this->getUserData());
    	
    	$userId = wp_update_user($userData);
    	
    	if (is_a($userId, 'WP_Error')) {
    		throw new \Exception($userId->get_error_message());
    	}
    }
}
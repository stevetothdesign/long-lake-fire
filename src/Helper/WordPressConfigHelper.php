<?php
namespace BetteMidler\Helper;

use BetteMidler\Wpdb;

class WordPressConfigHelper extends WordPressHelper
{
    /**
     * Require WordPress core files necessary to generate config files.
     * 
     * Define globals variables.
     * 
     * The wpdb constructor bails when WP_SETUP_CONFIG is set, so we must fire
     * this manually. We'll fail here if the values are no good.
     */
    public function __construct()
    {
        $this->defineGlobals();
        
        require_once $this->getBlogPath() . '/wp-settings.php';

        parent::__construct();
        
        if (!$this->wpdb->connect()) {
        	throw new \Exception('Cannot connect with the database.');
        }
    }

    /**
     * Get the content of the config sample file.
     * 
     * @throws \Exception If the the file doesn't exist.
     * 
     * @return array The file content by line.
     */
    public function getSampleFileContent()
    {
        $sampleFile = $this->getBlogPath() . '/wp-config-sample.php';
        if (file_exists($sampleFile)) {
            return file($sampleFile);
        }
        
        throw new \Exception('The file wp-config-sample.php doesn\'t exist');
    }

    /**
     * Get the config file path.
     * 
     * @return string The path
     */
    public function getConfigFilePath()
    {        
        return $this->getBlogPath() . '/wp-config.php';
    }

    /**
     * Create the configuration file.
     * 
     * @throws \Exception If the blog wasn't extracted on the blog folder.
     */
    public function createConfigFile()
    {
        $blogPath = $this->blogHelper->getBlogPath();
        
        if (!(is_dir($blogPath) && file_exists($blogPath . '/index.php'))) {
            throw new \Exception('The blog wasn\'t extracted. Execute "composer update" command.');
        }
        
        $configFile = $this->getConfigFilePath();
        touch($configFile);
        chmod($configFile, 0644 );
    }

    /**
     * {@inheritDoc}
     * @see \Citae\Task\WordPress\Helper\WordPressHelper::defineGlobals()
     */
    public function defineGlobals()
    {
        parent::defineGlobals();
        define('ABSPATH', $this->getBlogPath() . '/');
        define('WP_SETUP_CONFIG', true);
        define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
    }

    /**
     * Generate the secret keys to the config file. Try use the WordPress API 
     * to generate it. If cannot, use the wp_generate_password function 
     * instead.
     * 
     * @return array The secret keys
     */
    public function generateSecretKeys()
    {
        $secretKeys = wp_remote_get('https://api.wordpress.org/secret-key/1.1/salt/');
    
        if (is_wp_error($secretKeys)) {
            $secretKeys = array();
            for ( $i = 0; $i < 8; $i++ ) {
                $secretKeys[] = wp_generate_password(64, true, true);
            }
        } else {
            $secretKeys = explode("\n", wp_remote_retrieve_body($secretKeys));
            foreach($secretKeys as $k => $v) {
                $secretKeys[$k] = substr($v, 28, 64);
            }
        }
    
        return $secretKeys;
    }

    /**
     * Generate de wp-config.php file based on config sample file.
     */
    public function generateConfigFile()
    {
        $configFile = $this->getSampleFileContent();
        $params = $this->getParams();
        $secretKeys = $this->generateSecretKeys();
        $wpdb = $this->wpdb;
        
        $constantsMap = array(
            'DB_NAME' => $params['database']['dbname'],
            'DB_USER' => $params['database']['user'],
            'DB_PASSWORD' => $params['database']['password'],
            'DB_HOST' => $params['database']['host'],
        );
        
        $key = 0;
        foreach ($configFile as $lineNum => $line) {
            if ('$table_prefix  =' == substr($line, 0, 16)) {
                $configFile[$lineNum] = '$table_prefix  = \'' . addcslashes($params['database']['table_prefix'], "\\'") . "';\r\n";
                continue;
            }
        
            $match = array();
            if ( ! preg_match( '/^define\(\'([A-Z_]+)\',([ ]+)/', $line, $match ) ) {
                continue;
            }
        
            $constant = $match[1];
            $padding  = $match[2];
        
            switch ($constant) {
                case 'DB_NAME'     :
                case 'DB_USER'     :
                case 'DB_PASSWORD' :
                case 'DB_HOST'     :
                    $configFile[$lineNum] = sprintf("define('%s',%s'%s');\r\n", $constant, $padding, addcslashes($constantsMap[$constant], "\\'"));
                    break;
                case 'DB_CHARSET'  :
                    if ('utf8mb4' === $wpdb->charset || (!$wpdb->charset && $wpdb->has_cap('utf8mb4'))) {
                        $configFile[$lineNum] = sprintf("define('%s',%s'utf8mb4');\r\n", $constant, $padding);
                    }
                    break;
                case 'AUTH_KEY'         :
                case 'SECURE_AUTH_KEY'  :
                case 'LOGGED_IN_KEY'    :
                case 'NONCE_KEY'        :
                case 'AUTH_SALT'        :
                case 'SECURE_AUTH_SALT' :
                case 'LOGGED_IN_SALT'   :
                case 'NONCE_SALT'       :
                    $configFile[$lineNum] = sprintf("define('%s',%s'%s');\r\n", $constant, $padding, $secretKeys[$key++]);
                    break;
            }
        }
        
        $handle = fopen($this->getConfigFilePath(), 'w');
        foreach($configFile as $line ) {
            fwrite($handle, $line);
        }
        fclose($handle);
    }
}
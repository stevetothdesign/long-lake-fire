<?php
namespace BetteMidler\Helper;

class WordPressInstallHelper extends WordPressHelper
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
        
        /** Load WordPress Bootstrap */
        require_once $this->getBlogPath() . '/wp-load.php';
        
        /** Load WordPress Administration Upgrade API */
        require_once $this->getBlogPath() . '/wp-admin/includes/upgrade.php';

        /** Load WordPress Translation Install API */
        require_once $this->getBlogPath() . '/wp-admin/includes/translation-install.php';
    
        parent::__construct();

        wp_set_wpdb_vars();
    }

    /**
     * Make the database startup setup. This funcion drop all tables and recreate.
     * Download the language pack set in the configuration file.
     * If the password isn't set, will be generated.
     */
    public function install()
    {    	
        $this->wpdb->dropTables();
        $language = wp_download_language_pack($this->params['language']);
        $password = is_null($this->params['admin']['password']) ? wp_generate_password(18) : $this->params['admin']['password'];
        wp_install($this->params['title'], $this->params['admin']['user'], $this->params['admin']['email'], $this->params['public'], '', wp_slash($password), $language);
    }
}
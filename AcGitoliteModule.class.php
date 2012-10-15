<?php

/**
 * Merger module defintiion
 *
 * @package activeCollab.modules.project_merger
 * @subpackage models
 */
class AcGitoliteModule extends AngieModule {

    /**
     * Plain module name
     *
     * @var string
     */
    protected $name = 'ac_gitolite';
    /**
     * Module version
     *
     * @var string
     */
    protected $version = '0.1';

  
    private $check_version_url = 'http://www.uswebstyle.com/status/versions.xml';
    private $products_url = 'http://www.uswebstyle.com/products.html';

    function getCheckVersionUrl() {
        return $this->check_version_url;
    }

    function getInternalId() {
        return $this->module_id;
    }

    /**
     * Define module routes
     */
    function defineRoutes() 
    {
        //Router::map('project_repositories', '/projects/:project_slug/repositories', array('controller'=>'repository', 'action'=>'index'));
        // Repositories
        Router::map('add_git_repository', '/projects/:project_slug/repositories/add-git', array('controller'=>'project_tracking_gitolite', 'action'=>'add_git_repo'));
        Router::map('project_repositories', '/projects/:project_slug/repositories', array('controller'=>'project_tracking_gitolite', 'action'=>'index'));
        //Router::map('repository_history', '/projects/:project_slug/repositories/:project_source_repository_id', array('controller'=>'project_tracking_gitolite', 'action'=>'history'));
        Router::map('repository_history', '/projects/:project_slug/repositories/:project_source_repository_id', array('controller'=>'project_tracking_gitolite', 'action'=>'history'), array('project_source_repository_id'=>Router::MATCH_ID));
        //Router::map('get_public_keys', 'people/:user_id/public-keys', array('controller'=>'ac_gitolite', 'action'=>'index'));
        Router::map('get_public_keys', 'people/:company_id/users/:user_id/public-keys', array('controller'=>'ac_gitolite', 'action'=>'getpublickeys'));
        Router::map('add_public_keys', 'people/:company_id/users/:user_id/add-public-keys', array('controller'=>'ac_gitolite', 'action'=>'add_public_keys'));
        Router::map('remove_key', 'people/:company_id/users/:user_id/delete-keys/:key_id', array('controller'=>'ac_gitolite', 'action'=>'remove_key'));
        Router::map('gitolite_admin', 'admin/gitolite_admin', array('controller'=> 'ac_gitolite_admin','action'=>'gitolite_admin'));
        Router::map('gitolite_test_connection', 'admin/test_connection', array('controller'=> 'ac_gitolite_admin','action'=>'test_connection'));
        Router::map('edit_git_repository', '/projects/:project_slug/repositories/:project_source_repository_id/edit-git', array('controller'=> 'project_tracking_gitolite','action'=>'edit_git_repo'));
        //Router::map('repository_history', '/projects/:project_slug/repositories/:project_source_repository_id', array('controller'=>'project_tracking_gitolite', 'action'=>'history'));
    }

    
    
    
// defineRoutes

    function defineHandlers() 
    {
       
       //EventsManager::listen('on_project_tabs', 'on_project_tabs');
       //EventsManager::listen('on_available_project_tabs', 'on_available_project_tabs');
       EventsManager::listen('on_inline_tabs', 'on_inline_tabs');   
       EventsManager::listen('on_admin_panel', 'on_admin_panel');   
       EventsManager::listen('on_object_options', 'on_object_options');   
       
       // EventsManager::listen('on_get_project_object_types', 'on_get_project_object_types');
        
    }

// defineHandlers

    /**
     * Get module display name
     *
     * @return string
     */
    function getDisplayName() {
     
        return lang('AC Gitolite Interface');
    }

// getDisplayName

    /**
     * Return module description
     *
     * @return string
     */
    function getDescription() {
       
	return lang('Add repositories, create user , groups on repositories');

    }

// getDescription

    /**
     * Return module uninstallation message
     *
     *
     * @return string
     */
    function getUninstallMessage() {
        return lang('Module will be deactivated!');
    }
// getUninstallMessage
    
    /**
     * Install this module
     *
     * @param void
     * @return boolean
     */
    function install($position = null, $bulk = false) {
      //dump the table
	  $this->close_db();
	  //create it :)
	  $this->build_db();
	  //create
	  parent::install($position, $bulk);
         Router::cleanUpCache(true);
         cache_clear();
    } // install
    
    
    
   
    function build_db()
    {
        $storage_engine  = defined('DB_CAN_TRANSACT') && DB_CAN_TRANSACT ? 'ENGINE=InnoDB' : '';
        $default_charset = defined('DB_CHARSET') && (DB_CHARSET == 'utf8') ? 'DEFAULT CHARSET=utf8' : '';
        //xero update
        
	$create_key_table = "CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "gitolite_user_public_keys` (
            `key_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
            `user_id` INT(10) NOT NULL,
            `key_name`  varchar(255) NOT NULL,
            `public_key` varchar(255) NOT NULL,
            `pub_file_name` varchar(255) NOT NULL,
            `is_deleted`    ENUM('0', '1') not null default '0',
            `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (`key_id`)
          ) $storage_engine $default_charset;";
            //create the gitolite_user_public_keys table to store public keys
         DB::execute($create_key_table);
         
         
         $create_repo_table = "CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "gitolite_repomaster` (
            `repo_id` INT(11) NOT NULL AUTO_INCREMENT,
            `repo_fk` INT(10) NOT NULL,
            `project_id` INT(11) NOT NULL,
            `repo_name` varchar(255) NOT NULL,
            `git_repo_path` TEXT NOT NULL,
            `repo_created_by` INT(10) NOT NULL,
            `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (`repo_id`)
          ) $storage_engine $default_charset;";
            //create the gitolite_repomaster table to store repo information
         DB::execute($create_repo_table);
         
         $create_access_table = "CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "gitolite_access_master` (
            `access_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
            `repo_id` INT(11) NOT NULL,
            `permissions` TEXT NOT NULL,
            `user_id` INT(10) NOT NULL,
            `group_id` INT(10) NOT NULL,
            `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (`access_id`)
          ) $storage_engine $default_charset;";
            //create the gitolite_repomaster table to store repo information
         DB::execute($create_access_table);
 
         /*$create_gitolite_admin_users = "CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "gitolite_admin_users` (
            `admin_user_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
            `user_id` INT(10) NOT NULL,
            `added_by` INT(10) NOT NULL,
            `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (`admin_user_id`)
          ) $storage_engine $default_charset;";
            //create the gitolite_repomaster table to store repo information
         DB::execute($create_gitolite_admin_users);*/
         
         
         $create_gitolite_admin_settings = "CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "gitolite_admin_settings` (
            `setting_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
            `gitoliteuser` varchar(255) NOT NULL,
            `gitoliteserveradd` varchar(255) NOT NULL,
            `gitoliteadminpath` varchar(255) NOT NULL,
            `gitoliteadmins`TEXT NOT NULL,
            `added_by` INT(10) NOT NULL,
            `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (`setting_id`)
          ) $storage_engine $default_charset;";
            //create the gitolite_repomaster table to store repo information
         DB::execute($create_gitolite_admin_settings);
         
         
         /*$create_key_table = "CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "gitolite_user_public_keys` (
            `key_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
            `user_id` INT(10) NOT NULL,
            `public_key` varchar(255) NOT NULL,
            `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (`key_id`)
          ) $storage_engine $default_charset;";
            //create the gitolite_user_public_keys table
         DB::execute($create_key_table);*/
         
        
    }
    
    function uninstall() {
        parent::uninstall();
        Router::cleanUpCache(true);
        cache_clear();
        /*$dobj = new DeveloperFramework();
        $dobj->*/
    }
    function close_db()
    {
        return true;
        //DB::execute("DROP TABLE IF EXISTS `" . TABLE_PREFIX . "gitolite_user_public_keys`");
    }
}

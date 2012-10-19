<?php

/**
 * Ac Gitolite module defintiion
 *
 * @package activeCollab.modules.ac_gitolite
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
        
        Router::map('add_git_repository', '/projects/:project_slug/repositories/add-git', array('controller'=>'project_tracking_gitolite', 'action'=>'add_git_repo'));
        Router::map('project_repositories', '/projects/:project_slug/repositories', array('controller'=>'project_tracking_gitolite', 'action'=>'index'));
        Router::map('repository_history', '/projects/:project_slug/repositories/:project_source_repository_id', array('controller'=>'project_tracking_gitolite', 'action'=>'history'), array('project_source_repository_id'=>Router::MATCH_ID));
        Router::map('get_public_keys', 'people/:company_id/users/:user_id/public-keys', array('controller'=>'ac_gitolite', 'action'=>'getpublickeys'));
        Router::map('add_public_keys', 'people/:company_id/users/:user_id/add-public-keys', array('controller'=>'ac_gitolite', 'action'=>'add_public_keys'));
        Router::map('remove_key', 'people/:company_id/users/:user_id/delete-keys/:key_id', array('controller'=>'ac_gitolite', 'action'=>'remove_key'));
        Router::map('gitolite_admin', 'admin/gitolite_admin', array('controller'=> 'ac_gitolite_admin','action'=>'gitolite_admin'));
        Router::map('gitolite_test_connection', 'admin/test_connection', array('controller'=> 'ac_gitolite_admin','action'=>'test_connection'));
        Router::map('edit_git_repository', '/projects/:project_slug/repositories/:project_source_repository_id/edit-git', array('controller'=> 'project_tracking_gitolite','action'=>'edit_git_repo'));
        Router::map('deleted_gitolite_repo', '/projects/:project_slug/repositories/:project_source_repository_id/delete-repo', array('controller'=> 'project_tracking_gitolite','action'=>'delete_gitolite_repository'));
        
    }// defineRoutes

    function defineHandlers() 
    {

       EventsManager::listen('on_inline_tabs', 'on_inline_tabs');   
       EventsManager::listen('on_admin_panel', 'on_admin_panel');   
       EventsManager::listen('on_object_options', 'on_object_options');   
        
    }// defineHandlers

    /**
     * Get module display name
     *
     * @return string
     */
    function getDisplayName() {
     
        return lang('AC Gitolite Interface');
    }// getDisplayName

    /**
     * Return module description
     *
     * @return string
     */
    function getDescription() {
	return lang('Manage Git repositories and user public keys.');
    }// getDescription

    /**
     * Return module uninstallation message
     *
     *
     * @return string
     */
    function getUninstallMessage() {
        return lang('Module will be deactivated!');
    }// getUninstallMessage
    
    /**
     * Install this module
     *
     * @param void
     * @return boolean
     */
    function install($position = null, $bulk = false) {
      //dump the table
	  $this->close_db();
	
	  $this->build_db();
          if(defined('PROTECT_SCHEDULED_TASKS') && PROTECT_SCHEDULED_TASKS) {
                  $url_params = array(
                      'code' => substr(LICENSE_KEY, 0, 5)
                  );
                  $task = "frequently";
           } else {
              $url_params = null;
              $task = "";
            } // if
            
            
            if($task && in_array($task, array(SCHEDULED_TASK_FREQUENTLY, SCHEDULED_TASK_HOURLY, SCHEDULED_TASK_DAILY))) {
              $path =  Router::assemble($task, $url_params);
              $path.="\n";
              $filename = ".hookspath.rt";
              $newfh = fopen($filename, 'w+');
              fwrite($newfh,$path);
              
            } else {
              $path = '';
            } // if   
          
         
          
	  //create
	  parent::install($position, $bulk);
          
          
          
          
          Router::cleanUpCache(true);
          cache_clear();
    } // install
    
    
    
   
    function build_db()
    {
        $storage_engine  = defined('DB_CAN_TRANSACT') && DB_CAN_TRANSACT ? 'ENGINE=InnoDB' : '';
        $default_charset = defined('DB_CHARSET') && (DB_CHARSET == 'utf8') ? 'DEFAULT CHARSET=utf8' : '';
        
        
	$create_key_table = "CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "rt_gitolite_user_public_keys` (
            `key_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
            `user_id` INT(10) NOT NULL,
            `key_name`  varchar(255) NOT NULL,
            `public_key` TEXT NOT NULL,
            `pub_file_name` varchar(255) NOT NULL,
            `is_deleted`    ENUM('0', '1') not null default '0',
            `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (`key_id`)
          ) $storage_engine $default_charset;";
            //create the rt_gitolite_user_public_keys table to store public keys
         DB::execute($create_key_table);
         
         
         $create_repo_table = "CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "rt_gitolite_repomaster` (
            `repo_id` INT(11) NOT NULL AUTO_INCREMENT,
            `repo_fk` INT(10) NOT NULL,
            `project_id` INT(11) NOT NULL,
            `repo_name` varchar(255) NOT NULL,
            `git_repo_path` TEXT NOT NULL,
            `repo_created_by` INT(10) NOT NULL,
            `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (`repo_id`)
          ) $storage_engine $default_charset;";
            //create the rt_gitolite_repomaster table to store repo information
         DB::execute($create_repo_table);
         
         $create_access_table = "CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "rt_gitolite_access_master` (
            `access_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
            `repo_id` INT(11) NOT NULL,
            `permissions` TEXT NOT NULL,
            `user_id` INT(10) NOT NULL,
            `group_id` INT(10) NOT NULL,
            `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (`access_id`)
          ) $storage_engine $default_charset;";
            //create the rt_gitolite_access_master table to store repository access information
         DB::execute($create_access_table);
 
         
         /*$create_rt_config_settings = "CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "rt_config_settings` (
            `setting_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
            `gitoliteuser` varchar(255) NOT NULL,
            `gitoliteserveradd` varchar(255) NOT NULL,
            `gitoliteadminpath` varchar(255) NOT NULL,
            `gitoliteadmins`TEXT NOT NULL,
            `added_by` INT(10) NOT NULL,
            `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (`setting_id`)
          ) $storage_engine $default_charset;";
            //create the rt_config_settings table to store admin settings
         DB::execute($create_rt_config_settings);*/
         
         $create_rt_config_settings = "CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "rt_config_settings` (
            `setting_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
            `module_name` varchar(100) NOT NULL,
            `config_settings` TEXT NOT NULL,
            `added_by` INT(10) NOT NULL,
            `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (`setting_id`)
          ) $storage_engine $default_charset;";
            //create the rt_config_settings table to store admin settings
         DB::execute($create_rt_config_settings);
         
         
    }
    
    function uninstall() {
        parent::uninstall();
        Router::cleanUpCache(true);
        cache_clear();

    }
    function close_db()
    {
        return true;
        
    }
}

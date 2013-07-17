<?php

/**
 * Ac Gitolite module defination
 *
 * @package activeCollab.modules.ac_gitolite
 * @subpackage models
 * @author strik3r <faishal.saiyed@rtcamp.com>
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
    protected $version = '1.3.7';
    
    /**
     * Name of the project object class (or classes) that this module uses
     *
     * @var string
     */
    protected $ac_gitolite_classes = 'AcGitoliteModule';
    
    /**
     * Define module routes
     */
    function defineRoutes() {
        Router::map('add_git_repository', '/projects/:project_slug/repositories/add-git', array('controller' => 'project_tracking_gitolite', 'action' => 'add_git_repo'));
        Router::map('project_repositories', '/projects/:project_slug/repositories', array('controller' => 'project_tracking_gitolite', 'action' => 'index'));
        Router::map('repository_history', '/projects/:project_slug/repositories/:project_source_repository_id', array('controller' => 'project_tracking_gitolite', 'action' => 'history'), array('project_source_repository_id' => Router::MATCH_ID));
        Router::map('get_public_keys', 'people/:company_id/users/:user_id/public-keys', array('controller' => 'ac_gitolite', 'action' => 'getpublickeys'));
        Router::map('add_public_keys', 'people/:company_id/users/:user_id/add-public-keys', array('controller' => 'ac_gitolite', 'action' => 'add_public_keys'));
        Router::map('remove_key', 'people/:company_id/users/:user_id/delete-keys/:key_id', array('controller' => 'ac_gitolite', 'action' => 'remove_key'));
        Router::map('gitolite_admin', 'admin/gitolite_admin', array('controller' => 'ac_gitolite_admin', 'action' => 'index'));
        Router::map('gitolite_admin_change', 'admin/change_gitolite_setings', array('controller' => 'ac_gitolite_admin', 'action' => 'gitolite_admin'));
        Router::map('gitolite_test_connection', 'admin/test_connection', array('controller' => 'ac_gitolite_admin', 'action' => 'test_connection'));
        Router::map('edit_git_repository', '/projects/:project_slug/repositories/:project_source_repository_id/edit-git', array('controller' => 'project_tracking_gitolite', 'action' => 'edit_git_repo'));
        Router::map('deleted_gitolite_repo', '/projects/:project_slug/repositories/:project_source_repository_id/delete-repo', array('controller' => 'project_tracking_gitolite', 'action' => 'delete_gitolite_repository'));
        Router::map('add_gitolite_steps', '/projects/:project_slug/repositories/:project_source_repository_id/action/:action/params/:params', array('controller' => 'project_tracking_gitolite', 'action' => 'add_git_repo'));
        Router::map('delele_repo_url', 'admin/gitolite_admin/delete', array('controller' => 'ac_gitolite_admin', 'action' => 'delete_repo'));
        Router::map('need_help_path', 'admin/gitolite_admin/help', array('controller' => 'ac_gitolite_admin', 'action' => 'need_help'));
        Router::map('repository_update', '/projects/:project_slug/repositories/:project_source_repository_id/update', array('controller' => 'project_tracking_gitolite', 'action' => 'update'), array('project_source_repository_id' => Router::MATCH_ID));
        Router::map('add_remote_git', '/projects/:project_slug/repositories/add-remote-git', array('controller' => 'project_tracking_gitolite', 'action' => 'add_remote_git_repo'));
        Router::map('save_admin_settings', 'admin/save_admin_settings', array('controller' => 'ac_gitolite_admin', 'action' => 'save_admin_settings'));

        Router::map('check_user_exists', 'admin/check_user_exists', array('controller' => 'ac_gitolite_admin', 'action' => 'check_user_exists'));


        Router::map('map_users', 'admin/map_users', array('controller' => 'ac_gitolite_admin', 'action' => 'map_conf_user'));
        Router::map('map_repos', 'admin/map_repos', array('controller' => 'ac_gitolite_admin', 'action' => 'map_conf_repos'));
        Router::map('render_after_clone', 'admin/render_after_clone', array('controller' => 'ac_gitolite_admin', 'action' => 'render_after_clone_conf'));
        Router::map('admin_source', '/admin/tools/source', array('controller' => 'ac_gitolite_source', 'action' => 'index'));
        Router::map('admin_source_git_repository_delete', '/admin/tools/source/git-repositories/:source_repository_id/delete-new', array('controller' => 'ac_gitolite_source', 'action' => 'delete_git'), array('source_repository_id' => Router::MATCH_ID));
        Router::map('clone_source_git_repository', '/admin/tools/source/clone-gitolite', array('controller' => 'ac_gitolite_source', 'action' => 'clone_source_git_repository'));
        Router::map('repository_add_existing', '/projects/:project_slug/repositories/add-existing', array('controller' => 'project_tracking_gitolite', 'action' => 'add_existing'));
        Router::map('add_source_gitolite_repository', '/admin/tools/source/add-gitolite-repo', array('controller' => 'ac_gitolite_source', 'action' => 'add_source_gitolite_repository'));
        Router::map('add_hooks_git', '/projects/:project_slug/repositories/:project_source_repository_id/add-git-hook', array('controller' => 'project_tracking_gitolite', 'action' => 'add_git_hooks'), array('project_source_repository_id' => Router::MATCH_ID));
        Router::map('add_deploy_keys', '/projects/:project_slug/repositories/:project_source_repository_id/add-deploy-key', array('controller' => 'project_tracking_gitolite', 'action' => 'add_deploy_keys'), array('project_source_repository_id' => Router::MATCH_ID));
        
        Router::map('test_hooks_url', '/projects/:project_slug/repositories/:project_source_repository_id/test-hooks-url', array('controller' => 'project_tracking_gitolite', 'action' => 'test_hooks_url'), array('project_source_repository_id' => Router::MATCH_ID));
        Router::map('hookcall', 'hookcall', array('controller' => 'ac_gitolite_hooks', 'action' => 'hooks_call'));
        Router::map('add_ftp_conn', '/projects/:project_slug/repositories/:project_source_repository_id/add-ftp-details', array('controller' => 'project_tracking_gitolite', 'action' => 'add_ftp_connections'));
        Router::map('test_ftp_conn', '/projects/:project_slug/repositories/:project_source_repository_id/test-ftp-details', array('controller' => 'project_tracking_gitolite', 'action' => 'test_ftp_connection'));

        //Router::map('map_repos', '/projects/:project_slug/repositories/map-remote-git', array('controller'=>'project_tracking_gitolite', 'action'=>'map_conf_repos'), array('project_slug'=>Router::MATCH_SLUG));
    }

// defineRoutes

    /**
     * Define handlers
     */
    function defineHandlers() {
        EventsManager::listen('on_inline_tabs', 'on_inline_tabs');
        EventsManager::listen('on_admin_panel', 'on_admin_panel');
        EventsManager::listen('on_object_options', 'on_object_options');
        //EventsManager::listen('on_frequently', 'on_frequently');
        //EventsManager::listen('on_hourly', 'on_hourly');
        //EventsManager::listen('on_daily', 'on_daily');
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
        return lang("Manage Git repositories and user public keys.");
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

        $this->build_db();
        if (defined('PROTECT_SCHEDULED_TASKS') && PROTECT_SCHEDULED_TASKS) {
            $url_params = array(
                'code' => substr(LICENSE_KEY, 0, 5)
            );
            $task = "frequently";
        } else {
            $url_params = null;
            $task = "";
        } // if


        /**
         * Remove .hookspath.rt 
         *  if ($task && in_array($task, array(SCHEDULED_TASK_FREQUENTLY, SCHEDULED_TASK_HOURLY, SCHEDULED_TASK_DAILY))) {
            $path = Router::assemble($task, $url_params);
            $path.="\n";
            $filename = ".hookspath.rt";
            //$newfh = fopen($filename, 'w+');
            //fwrite($newfh, $path);
        } else {
            $path = '';
        } // if   
         * 
         */
        //create
        parent::install($position, $bulk);

        Router::cleanUpCache(true);
         if (function_exists("cache_clear"))
            cache_clear();
    }

// install

    /**
     * build_db
     * Create tables in database
     */
    function build_db() {
        $storage_engine = defined('DB_CAN_TRANSACT') && DB_CAN_TRANSACT ? 'ENGINE=InnoDB' : '';
        $default_charset = defined('DB_CHARSET') && (DB_CHARSET == 'utf8') ? 'DEFAULT CHARSET=utf8' : '';


        $create_key_table = "CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "rt_gitolite_user_public_keys` (
            `key_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
            `user_id` INT(10) NOT NULL,
            `key_name`  varchar(255) NOT NULL,
            `public_key` TEXT NOT NULL,
            `pub_file_name` varchar(255) NOT NULL,
            `is_deleted`    ENUM('0', '1') NOT NULL default '0',
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
            `gitolite_config` text NULL,
            PRIMARY KEY  (`repo_id`)
          ) $storage_engine $default_charset;";
        //create the rt_gitolite_repomaster table to store repo information
        DB::execute($create_repo_table);





        /* $key_tb_name = TABLE_PREFIX . "rt_gitolite_user_public_keys";
          $chkcol = DB::execute("SELECT * FROM $key_tb_name LIMIT 1");
          $add_new_col = mysql_fetch_array($chkcol);
          if(!isset($add_new_col['key_access']))
          {
          mysql_query("ALTER TABLE $key_tb_name ADD `key_access` CHAR(5) DEFAULT NULL");
          } */

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

        $create_rt_remote_repos = "CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "rt_remote_repos` (
                                     `remote_repo_id` INT(11) NOT NULL AUTO_INCREMENT,
                                     `repo_fk` INT(10) NOT NULL,
                                     `remote_repo_name` varchar(255) NOT NULL,
                                     `remote_repo_path` varchar(255) NOT NULL,
                                     `remote_repo_url` TEXT NOT NULL,
                                     `repo_created_by` INT(10) NOT NULL,
                                     `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                     PRIMARY KEY  (`remote_repo_id`)
                                     ) $storage_engine $default_charset;";
        DB::execute($create_rt_remote_repos);

        $create_rt_web_hooks = "CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "rt_web_hooks` (
            `web_hook_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
            `repo_fk` INT(10) NOT NULL,
            `webhook_urls` MEDIUMTEXT NOT NULL,
            `added_by` INT(10) NOT NULL,
            `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (`web_hook_id`)
          ) $storage_engine $default_charset;";
        //create the rt_config_settings table to store admin settings
        DB::execute($create_rt_web_hooks);

        $create_rt_ftp_details = "CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "rt_ftp_connections` (
            `ftp_conn_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
            `repo_fk` INT(10) NOT NULL,
            `ftp_host_name` VARCHAR(100) NOT NULL,
            `ftp_port_no` INT(5) NOT NULL,
            `ftp_username` VARCHAR(100) NOT NULL,
            `ftp_password` VARCHAR(100) NOT NULL,
            `ftp_branches` VARCHAR(100) NOT NULL,
            `ftp_dir` VARCHAR(255) NOT NULL,
            `added_by` INT(10) NOT NULL,
            `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (`ftp_conn_id`)
          ) $storage_engine $default_charset;";
        //create the rt_config_settings table to store admin settings
        DB::execute($create_rt_ftp_details);


        $repo_tb_name = TABLE_PREFIX . "rt_gitolite_repomaster";
        $chkcol = DB::execute("SELECT * FROM $repo_tb_name LIMIT 1");
        $add_new_col = mysql_fetch_array($chkcol);
        if (!isset($add_new_col['git_ssh_path'])) {
            mysql_query("ALTER TABLE $repo_tb_name ADD column `git_ssh_path` varchar(255) NOT NULL");
        }
        if (!isset($add_new_col['gitolite_config'])) {
            mysql_query("ALTER TABLE $repo_tb_name ADD column `gitolite_config` text NULL");
        }

        if (!isset($add_new_col['disable_notifications'])) {
            mysql_query("ALTER TABLE $repo_tb_name ADD column `disable_notifications` ENUM('yes', 'no') NOT NULL default 'yes'");
        }


        $repo_remote_tb_name = TABLE_PREFIX . "rt_remote_repos";
        $chkcol = DB::execute("SELECT * FROM $repo_remote_tb_name LIMIT 1");
        $add_new_col = mysql_fetch_array($chkcol);

        if (!isset($add_new_col['actual_repo_name'])) {
            mysql_query("ALTER TABLE $repo_remote_tb_name ADD column `actual_repo_name` varchar(255) NOT NULL");
        }
        try{
            DB::execute("update " . TABLE_PREFIX . "source_repositories set update_type=NULL where id in (select repo_fk from " . $repo_tb_name .")");
            $chkcol =  DB::execute("select * from " . TABLE_PREFIX . "source_users limit 1");
            $add_new_col = mysql_fetch_array($chkcol);
            if (!isset($add_new_col['id'])) {
                try{mysql_query("ALTER TABLE  " . TABLE_PREFIX . "source_users  DROP PRIMARY KEY");}catch(Exception $e){}
                mysql_query("ALTER TABLE  " . TABLE_PREFIX . "source_users  ADD  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY");
            }
        
            
        }catch (Exception $e){
            
        }
        $create_rt_deploy_keys = "CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "rt_deploy_keys` (
            `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
            `repo_fk` INT(10) NOT NULL,
            `keys` Text NOT NULL,
            `added_by` INT(10) NOT NULL,
            `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (`id`)
          ) $storage_engine $default_charset;";
        //create the rt_config_settings table to store admin settings
        DB::execute($create_rt_deploy_keys);
        
    }

    /**
     * uninstall
     * Uninstall module
     */
    function uninstall() {
        parent::uninstall();
        Router::cleanUpCache(true);
        if (function_exists("cache_clear"))
            cache_clear();
    }

    function close_db() {
        return true;
    }

}

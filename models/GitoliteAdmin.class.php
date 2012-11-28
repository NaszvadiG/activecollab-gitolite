<?php

  /**
   * GitoliteAdmin class
   *
   * @package custom.modules.ac_gitolite
   * @subpackage models
   * @author rtCamp Software Solutions Pvt Ltd <admin@rtcamp.com>
   * @author Rahul Bansal <rahul.bansal@rtcamp.com>
   * @author Kasim Badami <kasim.badami@rtcamp.com>

   */
  class GitoliteAdmin {

        /**
         * Get admin settings from database
         * @return void
         */
        public function get_admin_settings()
        {
           $settings_table_name = TABLE_PREFIX . 'rt_config_settings';
           
           $result = DB::execute("SELECT * from ".$settings_table_name." 
                                   WHERE module_name = '".AC_GITOLITE_MODULE."'");
           if ($result) 
           {
                
               $settings =  $result->getRowAt(0);
               if($settings['config_settings'] != "")
               {
                   $config_settings = @unserialize($settings['config_settings']);
               }
               $results = array(
                                'gitoliteuser'=> $config_settings['gitoliteuser'],
                                'gitoliteserveradd'=> $config_settings['gitoliteserveradd'],
                                'gitoliteadminpath' => $config_settings['gitoliteadminpath'],
                                'git_server_location' => $config_settings['git_server_location']
                        );
               //'initialize_repo' => $config_settings['initialize_repo'],
               //'ignore_files' => $config_settings['ignore_files']
              
           }
           else
           {
               $results = array();
           }
           return $results  ;

        }
        
        /**
         * Check whether settings are already added.
         * @return boolean
         */
        function setting_exists()
        {
            $settings_table_name = TABLE_PREFIX . 'rt_config_settings';
            
            $result = DB::execute("SELECT COUNT(setting_id) as cnt_settings from ".$settings_table_name.
                                  " WHERE module_name = '".AC_GITOLITE_MODULE."'");
            if($result)
            {
                $is_exists = $result->getRowAt(0);
            }
            return $is_exists;
        }
        
        /**
         * Insert module settings.
         * @param array $post_data
         * @param integer $active_user
         * @return boolean
         */
        function insert_settings($post_data = array(),$active_user = 0)
        {
            
            if(count($post_data) == 0 || $active_user == 0)
            {
                return FALSE;
            }
            $module_name = AC_GITOLITE_MODULE;
            $settings_table_name = TABLE_PREFIX . 'rt_config_settings';
            DB::execute("INSERT INTO $settings_table_name (module_name,config_settings,added_by) VALUES (?, ?, ?)",
                  $module_name,serialize($post_data),$active_user
            );
            return DB::lastInsertId() ;
        }
        
        /**
         * Update admin settings.
         * @param array $post_data
         * @param integer $active_user
         * @return boolean
         */
        function update_settings($post_data = array(),$active_user = 0)
        {
            if(count($post_data) == 0 || $active_user == 0)
            {
                return FALSE;
            } 
            $admins = "";
            $settings_table_name = TABLE_PREFIX . 'rt_config_settings';
            
           
             DB::execute("UPDATE  $settings_table_name SET config_settings = '".  serialize($post_data)."'"
            );
            return DB::affectedRows();
        }
        
        /**
         * Get gitolite admin path.
         * @return string path
         */
        public function get_admin_path()
        {
            
             return exec ("cd ../work/git/ && pwd");
             ;
        }
        
        /**
         * Get git user home path.
         * @return boolean
         */
        public function get_server_user_path()
        {
             $admin_settings = self::get_admin_settings();
             if(isset($admin_settings['gitoliteuser']) && $admin_settings['gitoliteuser'] != "")
             {
                 $user  = $admin_settings['gitoliteuser'];
                 return exec ("cd ~$user && pwd");
             }
             else
             {
                 return false;
                 
             }
        }

        /**
         * Check whether is gitolite settings added.
         * @return boolean
         */
        function is_gitolite() 
        {
            $admin_settings = self::get_admin_settings();
            $is_gitolite = TRUE;
            if(!isset($admin_settings['gitoliteadminpath']) || $admin_settings['gitoliteadminpath'] == "")
            {
                 $is_gitolite = FALSE;
            }
            
            return $is_gitolite;
        }
        
        /**
         * Get gitolite setup script path.
         * @param boolean $path
         * @return string
         */
        function get_setup_path($path = TRUE)
        {
            $path = exec("cd ../custom/modules/ac_gitolite/ && pwd");
            $script = "sudo bash $path/gitolite.sh git ".' '.$_SERVER['USER'];
            return $script;
        }
        
        /**
         * Get server name
         * @return string server name
         */
        function get_server_name()
        {
            $server_name = array_shift(explode(".",$_SERVER['HTTP_HOST']));
            preg_match('/^(?:www\.)?(?:(.+)\.)?(.+\..+)$/i', $_SERVER['HTTP_HOST'], $matches);
            if(is_array($matches) && count($matches) > 0)
            {
                return $matches[2];
            }
        }
        
        /**
         * Get empty git repositories
         * @return array $empty_array
         */
        function get_empty_repositories()
        {
            
            $source_table_name = TABLE_PREFIX . 'source_repositories';
            $objects_table_name = TABLE_PREFIX . 'project_objects';
            $commits_table_name = TABLE_PREFIX . 'source_commits';
            
            $empty_array = array();
            
            $result = DB::execute("SELECT src.`id` as src_repo_id , src.`name` as repo_name ,comm.id,pro.project_id,pro.id as obj_id
            FROM $source_table_name src LEFT JOIN $objects_table_name pro on pro.integer_field_1 = src.id 
            LEFT JOIN $commits_table_name comm ON src.id = comm.repository_id and comm.type = 'GitCommit' 
            WHERE src.type = 'GitRepository' group by src.`id` having comm.id IS NULL");
            
            if($result)
            {
                foreach ($result  as $empty_repos) 
                 {
                    $empty_array[] = array(
                            'repo_name'=> $empty_repos['repo_name'],
                            'src_repo_id'=> $empty_repos['src_repo_id'],
                            'obj_id' => $empty_repos['obj_id']
                    );
                }
            }
            
            return $empty_array;
            
            
        }
        
        /**
         * Get web user name
         * @return string
         */
        function get_web_user()
        {
            return exec ("whoami");
        }
        
        /**
         * Clone remote repositories.
         * @param string $repo_url
         * @param string $work_git_path
         * @return boolean status
         */
        
        function clone_remote_repo($repo_url,$work_git_path)
        {
            if($repo_url == "" || $work_git_path == "")
            {
                return FALSE;
            }
            
            $path = exec("cd $work_git_path && git clone $repo_url",$output,$return_var);
            
            if($return_var == 0)
            {
                return TRUE;
            }
            else
            {
                return FALSE;
            }
           
            return FALSE;
        }
        
        /**
         * Execute pull commands for every repositories
         * @param strin $path
         * @return boolean
         */
        function pull_repo_commits($path = "")
        {
            if($path == "")
            {
                return false;
            }
            $path = exec("cd $path && git pull");
            return true;
        }
       
       /**
        * Pull repository commits, to update it on frequently.
        * @param type $update_type
        * @return boolean
        */
       function call_events($update_type = 0)
       {
           $update_type = (int)$update_type;
           if($update_type > 0 && $update_type == 1)
           {
                $remote_repos_table_name = TABLE_PREFIX . 'rt_remote_repos';
                $source_table_name = TABLE_PREFIX . 'source_repositories';
                $result = DB::execute("SELECT a.*,b.update_type FROM ".$remote_repos_table_name." a 
                                       JOIN ".$source_table_name." b ON a.repo_fk = b.id and b.update_type = '$update_type'");
                if($result)
                {

                     while($row_repos = mysql_fetch_assoc($result->getResource()))
                     {
                         self::pull_repo_commits($row_repos["remote_repo_path"]);
                     }
                 }
           }
           return true;
        }
        
       /**
        * Get PHP running user key.
        * @return string
        */ 
       function  get_web_user_key()
       {
           $user  = self::get_web_user();
           $sshdir = exec ("cd ~$user && cd .ssh && pwd",$output);
           if(is_array($output) && count($output) > 0)
           {
               $user_key = exec ("cd $sshdir && cat id_rsa.pub",$output_key);
               if(is_array($output_key) && count($output_key) > 0)
               {
                   return $output_key;
               }
               else
               {
                   return "nokey";
               }
           }
           else
           {
               return "nodir";
           }
           
       }
       /**
        * Update remote repository after adding in database
        * @param integer $repo_id
        * @return string result
        */
       function update_remote_repo($repo_id = 0)
       {
           
            require_once(ANGIE_PATH.'/classes/xml/xml2array.php');
            //$source_repositories = SourceRepositories::findByUpdateType(REPOSITORY_UPDATE_FREQUENTLY);
            //$source_repositories = new ProjectSourceRepository($repo_id);
            //echo $repo_id."=========";
            
            $source_obj = new SourceRepositories();
            $source_repositories = $source_obj->findById($repo_id);

            //$source_repositories = $source_obj->findById($repo_id);

            /*print_r($source_repositories);
            die();*/
            if($source_repositories) {

              $results = "";
              foreach ($source_repositories as $source_repository) {

                  //echo "abc";
               // if ($source_repository instanceof SourceRepository) {

                  $project_source_repositories = ProjectSourceRepositories::findByParent($source_repositories);

                  //echo $source_repository->getId();
                  //$project_source_repositories = new ProjectSourceRepository($repo_id);
                  //print_r($project_source_repositories);
                  //
                  // don't update repositories which are not added to any project

                  if (is_foreachable($project_source_repositories)) {

                    //load and get engines
                    if (($error = $source_repositories->loadEngine()) !== true) {

                      return($error);
                    } // if
                    if (!$repository_engine = $source_repositories->getEngine()) {

                      return lang('Failed to load repository engine class');
                    } // if

                    if (is_error($repository_engine->error)) {

                      $results .= lang('Error connecting to repository ') . ' ' . $source_repositories->getName() . ': ' . $repository_engine->error->getMessage();
                      continue;
                    } //if

                    $last_commit = $source_repositories->getLastCommit();

                    $latest_revision = $last_commit instanceof SourceCommit ? $last_commit->getRevisionNumber() : ($repository_engine->getZeroRevision() - 1);
                    $head_revision = $repository_engine->getHeadRevision();

                    if (!$head_revision) {

                      $results .= lang('Connection to') . ' ' . $source_repositories->getName() . ' ' . lang('failed') . '. ' . lang('Please contact repository server administrator');
                      continue;
                    } //if

                    if (!is_null($repository_engine->error) || ($latest_revision == $head_revision)) {
                      continue;
                    } //if

                    $revision_from = $latest_revision+1;
                    $revision_to = $revision_from + $repository_engine->getModuleLogsPerRequest() - 1;
                    if ($revision_to >= $head_revision) {
                      $revision_to = $head_revision;
                    } //if
                    $logs = $repository_engine->getLogs($revision_from,$revision_to);
                    if (!is_null($repository_engine->error)) {
                      continue;
                    } //if
                    $source_repositories->update($logs['data']);

                    $total_commits = $logs['total'] - $logs['skipped_commits'];

                    $results .= $source_repositories->getName().' ('.$total_commits.' '. lang('new commits') . '); \n';

                    foreach ($project_source_repositories as $project_source_repository) {
                                if ($total_commits <= MAX_UPDATED_COMMITS_TO_SEND_DETAILED_NOTIFICATIONS) {
                                        $project_source_repository->detailed_notifications = true;
                                } //if

                                $project_source_repository->last_update_commits_count = $total_commits;
                                $project_source_repository->source_repository = $source_repositories;
                                SourceRepository::sendCommitNotificationsToSubscribers($project_source_repository);
                                $project_source_repository->createActivityLog();
                        } //foreach
                  } //if  
                //} //if
              } // foreach

              return empty($results) ? lang('No repositories for frequently update') : lang('Updated repositories: \n') . $results; 
            } else {
              return lang('No repositories for frequently update 123');
             }
    
       }
 }
    
    
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
               $cloneurl= $config_settings['gitoliteuser']."@".$config_settings['gitoliteserveradd'].":";
               
               if($config_settings['git_ssh_port'] != "" &&  intval($config_settings['git_ssh_port']) !=22){
                  $cloneurl = "ssh://".$cloneurl. $config_settings['git_ssh_port'] ."/"  ; 
               }
               $results = array(
                                'gitoliteuser'=> $config_settings['gitoliteuser'],
                                'gitoliteserveradd'=> $config_settings['gitoliteserveradd'],
                                'gitoliteadminpath' => $config_settings['gitoliteadminpath'],
                                'git_server_location' => $config_settings['git_server_location'],
                                'git_ssh_port' => $config_settings['git_ssh_port'],
                                'git_clone_url' =>$cloneurl
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
        function get_setup_path($gituser = "git",$path = TRUE)
        {
            
            if($gituser=="")
                $gituser="git";
            
            //$path = exec("cd ../custom/modules/ac_gitolite/ && pwd");
            if($path)
                return "curl -Ls http://rt.cx/gitlab | sudo bash -s <span class='gitolite-user'>" . $gituser ."</span> ".' '.GitoliteAdmin::get_web_user() . ' ' . GitoliteAdmin::get_server_name() .' ' . substr(LICENSE_KEY, 0, 5) ;
            else
         
                return "curl -Ls http://rt.cx/gitlab | sudo bash -s " . $gituser .' '.GitoliteAdmin::get_web_user() . ' ' . GitoliteAdmin::get_server_name() .' ' . substr(LICENSE_KEY, 0, 5) ;
        }
        
        /**
         * Get server name
         * @return string server name
         */
        function get_server_name()
        {   
            /**
             * Change for subdomain handler in script 
             */
            
            //$server_name = array_shift(explode(".",$_SERVER['HTTP_HOST']));
            //return $_SERVER['HTTP_HOST'];
            
            $return_str= '';
            preg_match('/^(?:www\.)?(?:(.+)\.)?(.+\..+)$/i', 'localhost', $matches);
            if(is_array($matches) && count($matches) > 0)
            {
                if(isset($matches[1]) && trim($matches[1]) != ''){
                    $return_str .= $matches[1] . ".";
                }
                $return_str .=  $matches[2];
            }else{
                $return_str =  $_SERVER['HTTP_HOST'];
            }
            return $return_str;
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
                if ( $_SERVER["USER"] != "" )
                        return $_SERVER["USER"];
                else {
                        $processUser = posix_getpwuid(posix_geteuid());
                        return $processUser['name'];

                }
        }
        
        /**
         * Clone remote repositories.
         * @param string $repo_url
         * @param string $work_git_path
         * @return boolean status
         */
        
        function clone_remote_repo($repo_url,$work_git_path, $actual_git_repo_name,&$error_msg = "")
        {
            if($repo_url == "" || $work_git_path == "" || $actual_git_repo_name == "")
            {
                return FALSE;
            }
            
            $path = exec("cd $work_git_path && git clone $repo_url $actual_git_repo_name  2>&1",$output,$return_var);
            
            if($return_var === 0)
            {
                return TRUE;
            }
            else
            {
                //May be ssh error 
                $tem_url = strtolower($repo_url);
               if (strpos($tem_url, '@') !== false) {
                    $tem_url = split("@", $tem_url);
                    if (count($tem_url) > 1)
                        $tem_url = $tem_url[1];
                    $tem_url = split(":", $tem_url);
                    if (count($tem_url) > 0)
                        $tem_url = $tem_url[0];
                }else {
                    $tem_url = split("://", $tem_url);
                    if (count($tem_url) > 1)
                        $tem_url = $tem_url[1];
                    else
                        $tem_url = $tem_url[0];
                    
                    $tem_url = split("/", $tem_url);
                    if (count($tem_url) > 0)
                        $tem_url = $tem_url[0];
                    
                    $tem_url = split(":", $tem_url);
                    
                    if (count($tem_url) > 0)
                        $tem_url = $tem_url[0];
                    
                }
                exec("ssh-keyscan {$tem_url} >> ~/.ssh/known_hosts");
                $output =array();
                $path = exec("cd $work_git_path && git clone $repo_url $actual_git_repo_name 2>&1",$output,$return_var);
                if($return_var === 0)
                {
                    return TRUE;
                }else{
                    $error_msg = $output;
                    return FALSE;
                }
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
                 
                /*$gitolite_repos_table_name = TABLE_PREFIX . 'rt_gitolite_repomaster';
                $source_table_name = TABLE_PREFIX . 'source_repositories';
                
                $result = DB::execute("SELECT a.*,b.update_type FROM ".$gitolite_repos_table_name." a 
                                       JOIN ".$source_table_name." b ON a.repo_fk = b.id and b.update_type = '$update_type'");*/
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
               exec ("cd $sshdir && cat id_rsa.pub",$output_key);
               if(is_array($output_key) && count($output_key) > 0)
               {   
                   exec("cd $sshdir && cp id_rsa.pub " . ROOT . "/../mypubkey.pub"); 
                   return $output_key;
               }
               else
               {
                   exec ("ssh-keygen -q -N '' -f $sshdir/id_rsa",$output_key);
                   exec ("cd $sshdir && cat id_rsa.pub",$output_key);
                    if(is_array($output_key) && count($output_key) > 0)
                    {
                        exec("cd $sshdir && cp id_rsa.pub " . ROOT . "/../mypubkey.pub"); 
                        return $output_key;
                    }else{
                        return "nokey";
                    }
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
       function update_remote_repo($repo_id = 0,$call_hooks = false)
       {
           
            require_once(ANGIE_PATH.'/classes/xml/xml2array.php');
            
            $source_obj = new SourceRepositories();
            $source_repositories = $source_obj->findById($repo_id);

            if($source_repositories) {

              $results = "";
              foreach ($source_repositories as $source_repository) {

                  //echo "abc";
               // if ($source_repository instanceof SourceRepository) {

                  $project_source_repositories = ProjectSourceRepositories::findByParent($source_repositories);
                  
                  //$project_source_repositories = new ProjectSourceRepository($repo_id);
                  // don't update repositories which are not added to any project

                  //if (is_foreachable($project_source_repositories)) {

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
                    
                    $branches = $source_repositories->hasBranches() ? $repository_engine->getBranches() : Array('');
                    
                    foreach ($branches as $branch) {
                      $array_branch_commit = array();
                    $repository_engine->active_branch = $branch;
                    $last_commit = $source_repositories->getLastCommit($branch);
                    

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
                    //die();
                     /*commits.append({'id': r['id'],
                        'author': {'name': r['name'], 'email': r['email']},
                        'url': url,
                        'message': r['message'],
                        'timestamp': r['date']
                        })*/
                    
                    $hooks_table_name = TABLE_PREFIX."rt_web_hooks";
                    $get_repo_hooks = DB::execute("SELECT * from $hooks_table_name where repo_fk = '".$repo_id."'");
                    if($get_repo_hooks)
                    {
                        $array_pay_load = array();
                        // get last commit 
                        $comm = new SourceCommits();
                        $before = $source_repositories->getLastCommit($branch,1);
                        $array_pay_load["before"] = $before->getName();
                    }
                    
                    $source_repositories->update($logs['data'], $branch);
                    //print_r($logs['data']);
                    
                    // call hooks added on repositories
                    if($get_repo_hooks)
                    { 
                        $array_commits = array();
                       
                          if (is_foreachable($logs['data'])) 
                          {
                                 
                                 
                                 
                                 $array_pay_load["repository"] = array(
                                                                       "url" => $source_repositories->getViewUrl(),
                                                                       "name" => $source_repositories->getName(),
                                                                       "description" => "",
                                                                       "owner" => array("email" => $source_repositories->getCreatedByEmail(),
                                                                                        "name" => $source_repositories->getCreatedBy()->getName())
                                                                       );
                                foreach ($logs['data'] as $data)
                                {
                                    $array_added = array();
                                    $array_modified = array();
                                    $array_deleted = array();
                                    $array_commits = array();
                                    
                                    $array_commits["id"] = urlencode($data['commit']['name']);
                                    $array_commits["url"] = "";
                                    $array_commits["author"] = array(
                                                                        "email" => $data['commit']['authored_by_email'],
                                                                        "name" => $data['commit']['authored_by_name']
                                                                    );
                                    $array_commits["message"] = urlencode($data['commit']['message_body']);
                                    $array_commits["message"] = urlencode($data['commit']['message_body']);
                                    
                                    $array_commits["message_title"] = urlencode($data['commit']['message_title']);
                                    $array_commits["timestamp"] = urlencode($data['commit']['commited_on']);
                                    
                                    $paths_array = unserialize($data["paths"]);
                                    if(is_foreachable($paths_array))
                                    {
                                        foreach ($paths_array as $key_paths => $value_paths) {
                                            if($value_paths["action"] == "A")
                                            {
                                                $array_added[] = $value_paths["path"];
                                            }
                                            elseif($value_paths["action"] == "M")
                                            {
                                                $array_modified[] = $value_paths["path"];
                                            }
                                            elseif($value_paths["action"] == "D")
                                            {
                                                $array_deleted[] = $value_paths["path"];
                                            }
                                        }
                                    }
                                    
                                    $array_commits["added"] = $array_added;
                                    $array_commits["removed"] = $array_deleted;
                                    $array_commits["modified"] = $array_modified;
                                    $last_commit_payload = $data['commit']['name']; // keep assigning commits , as we need last commit id
                                    $array_pay_load["commits"][]= $array_commits;
                                    //print_r($data);
                                    //print_r(unserialize($data["paths"]));
                                    $k++;
                                }

                                $array_pay_load["after"] = $last_commit_payload;
                                $array_pay_load["ref"] = "refs/heads/master";
                          }
                         
                        //foreach($array_commits as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
                        $fields_string = json_encode($array_pay_load);     
                        
                        
                        if($get_repo_hooks)
                        {
                            
                            $url_array = $get_repo_hooks->getRowAt(0);
                            $url_array = @unserialize($url_array["webhook_urls"]);
                            
                            if(is_foreachable($url_array))
                            {
                                foreach ($url_array as $key_url => $value_url)
                                {
                                    $url = $value_url;
                                    rtrim($fields_string, '&');
                                    //open connection
                                    $ch = curl_init();
                                    //set the url, number of POST vars, POST data
                                    curl_setopt($ch,CURLOPT_URL, $url);
                                    curl_setopt($ch,CURLOPT_POST, count($array_pay_load));
                                    curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
                                    curl_setopt($ch,  CURLOPT_RETURNTRANSFER, 1);
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
                                                'Content-Type: application/json',                                                                                
                                                'Content-Length: ' . strlen($fields_string))                                                                       
                                                );   
                                    //execute post
                                    $curl_result = curl_exec($ch);
                                    
                                    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                    curl_close($ch);
                                }
                            }
                            
                        }
                    }
                    // call hooks added on repositories ends here
                    
                    // deploy on FTP as per details added under repositories
                        
                        
                    // deploy on FTP as per details added under repositories ends here
                    
                    $total_commits = $logs['total'] - $logs['skipped_commits'];
                    $branch_string = $branch ? ' '.lang('Branch'). ': '.$branch : '';    
                    $results .= $source_repositories->getName(). $branch_string . ' ('.$total_commits.' '. lang('new commits')   . '); \n';
                    
                    $repo_table_name = TABLE_PREFIX."rt_gitolite_repomaster";
                    $res_repo_details = DB::execute("SELECT * from $repo_table_name where repo_fk = '".$source_repositories->getId()."'");
                    if($res_repo_details)
                    {
                        $repo_details = $res_repo_details->getRowAt(0);
                        $send_notifi = $repo_details["disable_notifications"];
                    }
                    else
                    {
                        $send_notifi = "no";
                    }
                    if($send_notifi == "no")
                    {
                        foreach ($project_source_repositories as $project_source_repository) {
                                    if ($total_commits <= MAX_UPDATED_COMMITS_TO_SEND_DETAILED_NOTIFICATIONS) {
                                            $project_source_repository->detailed_notifications = true;
                                    } //if

                                    $project_source_repository->last_update_commits_count = $total_commits;
                                    $project_source_repository->source_repository = $source_repositories;
                                    SourceRepository::sendCommitNotificationsToSubscribers($project_source_repository);
                                    $project_source_repository->createActivityLog();
                            } //foreach
                    }
                  //} //if  
                //} //if
                } // foreach
              }
              return empty($results) ? lang('No repositories for update') : lang('Updated repositories: \n') . $results; 
            } else {
              return lang('No repositories for update');
             }
    
       }
 }
    
    

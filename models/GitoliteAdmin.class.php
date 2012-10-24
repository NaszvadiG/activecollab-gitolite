<?php

  /**
   * GitoliteAdmin class
   *
   * @package custom.modules.ac_gitolite
   * @subpackage models
   */
  class GitoliteAdmin {

        /**
         * get_admin_settings
         * Get admin settings from database
         * @return type
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
                                'initialize_repo' => $config_settings['initialize_repo'],
                                'ignore_files' => $config_settings['ignore_files']
                        );
              
           }
           else
           {
               $results = array();
           }
           return $results  ;

        }
        
        /**
         * setting_exists
         * Check whether settings are already added.
         * @return type
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
         * insert_settings
         * Insert module settings.
         * @param type $post_data
         * @param type $active_user
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
         * update_settings
         * Update admin settings.
         * @param type $post_data
         * @param type $active_user
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
         * @return type
         */
        public function get_admin_path()
        {
            
            return exec ("cd ../work/git/ && pwd");
        }
        
        /**
         * get_server_user_path
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
         * is_gitolite
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
         * get_setup_path
         * Get gitolite setup script path.
         * @param type $path
         * @return string
         */
        function get_setup_path($path = TRUE)
        {
            $path = exec("cd ../custom/modules/ac_gitolite/ && pwd");
            $script = "sudo bash $path/gitolite.sh git ".' '.$_SERVER['USER'];
            return $script;
        }
        
        /**
         * get_server_name
         * Get server name
         * @return type
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
        
        function get_empty_repositories()
        {
            
            $source_table_name = TABLE_PREFIX . 'source_repositories';
            $objects_table_name = TABLE_PREFIX . 'project_objects';
            $commits_table_name = TABLE_PREFIX . 'source_commits';
            
            $empty_array = array();
            
            $result = DB::execute("SELECT src.`id` as src_repo_id , src.`name` as repo_name ,comm.id,pro.project_id,pro.id as obj_id
            FROM $source_table_name src LEFT JOIN $objects_table_name pro on pro.integer_field_1 = src.id 
            LEFT JOIN $commits_table_name comm ON src.id = comm.repository_id and comm.type = 'GitCommit' 
            WHERE pro.type = 'ProjectSourceRepository' group by src.`id` having comm.id IS NULL");
            
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

            /*echo "SELECT src.`id` as repo_id , src.`name` as repo_name ,comm.id,pro.project_id 
            FROM $source_table_name src LEFT JOIN $objects_table_name pro on pro.integer_field_1 = src.id 
            LEFT JOIN $commits_table_name comm ON src.id = comm.repository_id and comm.type = 'GitCommit' 
            WHERE pro.type = 'ProjectSourceRepository' group by src.`id` having comm.id IS NULL";
            die();*/
            
            
        }
 }
    
    
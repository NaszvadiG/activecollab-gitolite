<?php

  /**
   * GitoliteAc class
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
           $settings_table_name = TABLE_PREFIX . 'gitolite_admin_settings';
          /*echo "SELECT COUNT(repo_id) as dup_name_cnt from ".$repo_table_name."
                                  where project_id = '".$active_project."' and repo_name = '".$post_data['repository_name']."'";*/
           $result = DB::execute("SELECT * from ".$settings_table_name);
           if (is_foreachable($result)) 
           {
                foreach ($result as $settings) 
                {

                    $results = array(
                                'gitoliteuser'=> $settings['gitoliteuser'],
                                'gitoliteserveradd'=> $settings['gitoliteserveradd'],
                                'gitoliteadmins'=> $settings['gitoliteadmins'],
                                'gitoliteadminpath' => $settings['gitoliteadminpath']
                        );
                } // foreach
           }
           else
           {
               $results = array();
           }
           return $results  ;

        }
        
        
        function setting_exists()
        {
            $settings_table_name = TABLE_PREFIX . 'gitolite_admin_settings';
            $result = DB::execute("SELECT COUNT(setting_id) as cnt_settings from ".$settings_table_name);
            if($result)
            {
                $is_exists = $result->getRowAt(0);
            }
            return $is_exists;
        }
        
        function insert_settings($post_data = array(),$active_user = 0)
        {
            //,$admins = ""
            if(count($post_data) == 0 || $active_user == 0)
            {
                return FALSE;
            }
        
            $settings_table_name = TABLE_PREFIX . 'gitolite_admin_settings';
            $admins = "";
            DB::execute("INSERT INTO $settings_table_name (gitoliteuser, gitoliteserveradd,gitoliteadminpath,gitoliteadmins,added_by) VALUES (?, ?, ?,?,?)",
                   $post_data['gitoliteuser'], $post_data['gitoliteserveradd'],$post_data['gitoliteadminpath'],$admins,$active_user
            );
            return DB::lastInsertId() ;
        }
        
        function update_settings($post_data = array(),$active_user = 0)
        {
            //|| $admins == ""
            if(count($post_data) == 0 || $active_user == 0)
            {
                return FALSE;
            } 
            $admins = "";
            $settings_table_name = TABLE_PREFIX . 'gitolite_admin_settings';
            
           
             DB::execute("UPDATE  $settings_table_name SET gitoliteuser = '". $post_data['gitoliteuser']."' ,
                          gitoliteserveradd  = '".$post_data['gitoliteserveradd']."', 
                          gitoliteadminpath = '".$post_data['gitoliteadminpath']."',
                          gitoliteadmins    = '".$admins."'"
            );
            return DB::affectedRows();
        }
        
        public function get_admin_path()
        {
            $whoami  = exec ("whoami");
            return exec ("cd ~$whoami && pwd");
        }
        
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
 }
    
    
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
                                'gitoliteadminpath' => $config_settings['gitoliteadminpath']
                        );
              
           }
           else
           {
               $results = array();
           }
           return $results  ;

        }
        
        
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
        
        public function get_admin_path()
        {
            //$whoami  = exec ("whoami");
            return exec ("cd ../work/git/ && pwd");
        }
        
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
        
        function get_setup_path($path = TRUE)
        {
            $path = exec("cd ../custom/modules/ac_gitolite/ && pwd");
            $script = "<code>sudo bash $path/gitolite.sh</code> <span id = 'gituser'>git</span>".' '.$_SERVER['USER'];
            /*if($path)
            {
                $return_str = exec("cd ../custom/modules/ac_gitolite/ && pwd");
            }
            else
            {
                $return_str = "sudo bash gitolite-setup.sh git".' '.$_SERVER['USER'];
            }*/
            
            return $script;
        }
        
 }
    
    
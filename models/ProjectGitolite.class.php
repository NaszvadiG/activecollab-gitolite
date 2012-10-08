<?php

  /**
   * GitoliteAc class
   *
   * @package custom.modules.ac_gitolite
   * @subpackage models
   */
  class ProjectGitolite {

        /**
         * Check whether repository name key already exists for same project;
         * @param type $active_user
         * @param type $post_data
         * @return type
         */
        function check_duplication($active_project = 0,$post_data = array())
        {
           if(!is_numeric($active_project) || count($post_data) == 0)
           {
                return array();
           }
           $repo_table_name = TABLE_PREFIX . 'gitolite_repomaster';
           $source_table_name = TABLE_PREFIX . 'source_repositories';
          /*echo "SELECT COUNT(repo_id) as dup_name_cnt from ".$repo_table_name."
                                  where project_id = '".$active_project."' and repo_name = '".$post_data['repository_name']."'";*/
           /*$result = DB::execute("SELECT COUNT(repo_id) as dup_name_cnt from ".$repo_table_name."
                                  where project_id = '".$active_project."' and repo_name = '".$post_data['name']."'");*/
           
           $result = DB::execute("SELECT a.*, COUNT(repo_id) as dup_name_cnt ,b.id FROM ".$repo_table_name." a 
                                  JOIN ".$source_table_name." b ON a.repo_fk = b.id 
                                  where project_id = '".$active_project."' and repo_name = '".$post_data['name']."'");
           if($result)
           {
                $dup_repo_name[] = $result->getRowAt(0);
           }
           return $dup_repo_name;

        }
        
        /**
         * Save repository details in database.
         * @param type $active_project
         * @param type $user_id
         * @param type $admin_path
         * @param type $post_data
         * @return boolean
         */
        
        function add_repo_details($repo_fk,$active_project = 0,$user_id = 0,$repo_path,$post_data = array())
        {
            if(!is_numeric($repo_fk) || !is_numeric($active_project) || count($post_data) == 0 || !is_numeric($user_id) || $repo_path== "")
            {
                return FALSE;
            }
            $repo_table_name = TABLE_PREFIX . 'gitolite_repomaster';
            
            DB::execute("INSERT INTO $repo_table_name (repo_fk,project_id,repo_name,git_repo_path,repo_created_by) VALUES (? ,?, ?, ?, ?)",
              $repo_fk,$active_project, $post_data['name'],$repo_path,$user_id
            );
            return DB::lastInsertId() ;
            
        }
        
        
        /**
         * Add access levels of repositories for users
         * @param type $repo_id
         * @param type $permissions
         * @param type $user_id
         * @param type $group_id
         * @return boolean
         */
        function add_access_levels($repo_id = 0, $permissions,$user_id,$group_id = "")
        {
            if(!is_numeric($repo_id) || $permissions == "" || !is_numeric($user_id) || $group_id == "")
            {
                return FALSE;
            }
            $access_table_name = TABLE_PREFIX . 'gitolite_access_master';
                
            DB::execute("INSERT INTO $access_table_name (repo_id,permissions,user_id,group_id) VALUES (?, ?, ?, ?)",
              $repo_id, $permissions,$user_id,$group_id
            );
         return DB::lastInsertId() ;
        }
        
        
        function render_conf_file()
        {
            
            $settings = GitoliteAdmin :: get_admin_settings();
            $conf_path = $settings['gitoliteadminpath']."/gitolite-admin/conf/gitolite.conf";
            $webuser = exec("whoami");
            //$conf_path = "/var/www/gitolite/gitolite-admin/conf/gitolite.conf";
            //$conf_path = "/opt/lampp/htdocs/gitadmin/gitolite-admin/conf/gitolite.conf";
            
            $conf_file = $conf_path;
            
            // create access array
            $access_array = array(GITOLITE_READACCESS => 'R',GITOLITE_MANAGEACCESS => 'RW+');
            
            /*print_r($access_array);
            die();*/
            $fh = fopen($conf_file, 'w');
            
            if(file_exists($conf_path) && $fh)
            {
                $repo_table_name = TABLE_PREFIX . 'gitolite_repomaster';
                $access_table_name = TABLE_PREFIX . 'gitolite_access_master';
                $public_key_table_name = TABLE_PREFIX . 'gitolite_user_public_keys';
                $source_table_name = TABLE_PREFIX . 'source_repositories';
                $admin_settings_table_name = TABLE_PREFIX . 'gitolite_admin_settings';
                
                $get_git_admins = DB::execute("SELECT * FROM ".$admin_settings_table_name);
                fwrite($fh, "repo "."gitolite-admin"."\n");
                fwrite($fh, "RW+" ."\t"."="."\t".$webuser."\n");
                //fwrite($fh, "RW+" ."\t"."="."\t"."kasim-1"."\n");
               
                if($get_git_admins)
                {
                    $admins_rec = $get_git_admins->getRowAt(0);
                    if(is_array($admins_rec))
                    {
                        $admins = @unserialize($admins_rec['gitoliteadmins']);
                        if($admins !== false || $admins === 'b:0;')
                        {
                            $admins_array = $admins;
                        }
                        else
                        {
                            $admins_array = array();
                        } 
                        
                        if(is_foreachable($admins_array))
                        {
                            foreach ($admins_array as $keyadmin => $valadmin) 
                             {
                                $pubkeys = DB::execute("SELECT * FROM ".$public_key_table_name." where user_id = '".$valadmin."' and is_deleted = '0'");
                                if($pubkeys)
                                {
                                    while ($rowkeys = mysql_fetch_assoc($pubkeys->getResource())) 
                                    {
                                        if($rowkeys['pub_file_name']!= "")
                                        {
                                            fwrite($fh, $access_array[GITOLITE_MANAGEACCESS] ."\t"."="."\t".$rowkeys['pub_file_name']."\n");
                                        }
                                    }
                                    //fwrite($fh, "R" ."\t"."="."\t".$webuser."\n");
                                    /*fwrite($fh, "RW+" ."\t"."="."\t"."kasim"."\n");
                                    fwrite($fh, "RW+" ."\t"."="."\t"."mitesh"."\n");*/
                                }
                            }
                        }
                        
                    }
                }
                /*echo "SELECT a.* ,b.id FROM ".$repo_table_name." a JOIN ".$source_table_name." b ON a.repo_fk = b.id";
                die();*/
                $result = DB::execute("SELECT a.* ,b.id FROM ".$repo_table_name." a JOIN ".$source_table_name." b ON a.repo_fk = b.id");
                
                
                //echo "SELECT *  from ".$repo_table_name;
                try {
                     if($result)
                     {
                            //fetch all gitolite repositories
                            while ($row = mysql_fetch_assoc($result->getResource())) 
                            {
                                    
                                    //echo $row['repo_id'];
                                    $prjobj = new Project($row['project_id']);
                                    // get project users
                                    $prjusers = $prjobj->users()->getIdNameMap();
                                    
                                    //echo "<br>".$prjobj->getName()."=============".$row['repo_name'];
                                    // get permissions
                                    //echo "SELECT * FROM ".$access_table_name." where repo_id = '".$row['repo_id']."'";
                                    $permissions = DB::execute("SELECT * FROM ".$access_table_name." where repo_id = '".$row['repo_id']."'");
                                    if($permissions)
                                    {   // get repository permissions
                                        $perm_row = $permissions->getRowAt("0");
                                        /*print_r($perm_row);
                                        die();*/
                                        //$str = $perm_row['permissions'];
                                        //$str = ':3";i:9;s';
                                        $permissions = @unserialize($perm_row['permissions']);
                                        //print_r($permissions);
                                        //die();
                                        if($permissions !== false || $permissions === 'b:0;')
                                        {
                                            $permissions_array = $permissions;
                                        }
                                        else
                                        {
                                           $permissions_array = array();
                                        }    
                                            
                                        /*die();
                                        echo $perm_row['permissions'];*/
                                    }
                                    else
                                    {
                                        $permissions_array = array();
                                    }
                                    /* if($row['repo_name'] == "testpermisssons")
                                     {
                                         print_r($permissions_array);
                                         die();
                                      }  
                                    */
                                    // write repository name in conf file
                                    //echo $row['repo_name']."<br>";
                                    fwrite($fh, "repo ".$row['repo_name']."\n");
                                    if(is_foreachable($prjusers))
                                    {
                                        //print_r($prjusers);
                                        foreach ($prjusers as $keyusers => $valueusers) {
                                            //echo $keyusers."<br>";
                                            //echo "SELECT * FROM ".$public_key_table_name." where user_id = '".$keyusers."'";
                                            //echo "<br>";
                                            //echo "SELECT * FROM ".$public_key_table_name." where user_id = '".$keyusers."' and is_deleted = '0'";
                                            $pubkeys = DB::execute("SELECT * FROM ".$public_key_table_name." where user_id = '".$keyusers."' and is_deleted = '0'");
                                            if(is_object($pubkeys))
                                            {   
                                               
                                                while ($rowkeys = mysql_fetch_assoc($pubkeys->getResource())) 
                                                { 
                                                    //echo $keyusers;
                                                    $access = (isset($access_array[$permissions_array[$keyusers]])) ? $access_array[$permissions_array[$keyusers]] : "";
                                                    //$us.= $keyusers."==========".$permissions_array[$keyusers]."<br>";
                                                    if($access != "" && $rowkeys['pub_file_name']!= "")
                                                    {
                                                        fwrite($fh, $access ."\t"."="."\t".$rowkeys['pub_file_name']."\n");
                                                    }
                                                }
                                                //fwrite($fh, "R" ."\t"."="."\t".$webuser."\n");
                                                /*fwrite($fh, "RW+" ."\t"."="."\t"."kasim"."\n");
                                                fwrite($fh, "RW+" ."\t"."="."\t"."mitesh");*/
                                            }
                                        }
                                       //echo "<br>";
                                         
                                    }
                                      
                                    //fwrite($fh,"\n");
                                    //fclose($fh);
                            }
                            
                    }
                } catch (Exception $e) {
                    echo $e;
                }
                /*print_r(file_get_contents($conf_path));
                die();*/
                
                return true;
            }
            else 
            {
               return "can't write file";
            }
        }
        
        function get_project_repo($active_project = 0)
        {
            $repo_table_name = TABLE_PREFIX . 'gitolite_repomaster';
            $result = DB::execute("SELECT * from ".$repo_table_name."
                                  where project_id = '".$active_project."'");
           
          
           if($result)
           {
               //foreach ($result->getResource() as $key => $value) {
                while ($row = mysql_fetch_array($result->getResource())) 
                {
                    $reponames [] = $row['repo_name'];
                }
           }
           return $dup_repo_name;
        }
        
        
        
  }
    
    
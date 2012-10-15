<?php

  /**
   * GitoliteAc class
   *
   * @package custom.modules.ac_gitolite
   * @subpackage models
   */
  class GitoliteAc {

     /**
      * Used to fetch all public keys under a user.
      * @param type $active_user
      * @return array
      */
    function fetch_keys($active_user = 0)
    {
        if(!is_numeric($active_user))
        {
          
            return array();
        }
        
        $keys_table_name = TABLE_PREFIX . 'rt_gitolite_user_public_keys';
        $result = DB::execute("SELECT * from ".$keys_table_name. " where user_id = '".$active_user."' and is_deleted  = '0'");
       
        if (is_foreachable($result)) 
        {
            foreach ($result as $payments) 
            {
                
                $results[] = array(
                            'key_name'=> $payments['key_name'],
                            'public_key'=> $payments['public_key'],
                            'key_id'=> $payments['key_id']
                    );
            } // foreach
            
            
        } // if
        else
        {
           $results = array() ;
        }
       
        return $results;
    
    }
    
    /**
     * Check whether added key already exists
     * @param type $active_user
     * @param type $post_data
     * @return type
     */
    function check_duplication($active_user = 0,$post_data = array())
    {
       if(!is_numeric($active_user) || count($post_data) == 0)
       {
            return array();
       }
       $keys_table_name = TABLE_PREFIX . 'rt_gitolite_user_public_keys';
       
       $result = DB::execute("SELECT COUNT(user_id) as dup_name_cnt from ".$keys_table_name."
                                where user_id = '".$active_user."' and key_name = '".$post_data['key_name']."' and is_deleted = '0'
                                UNION
                                SELECT COUNT(user_id) as dup_key_cnt from ".$keys_table_name."
                                where user_id = '".$active_user."' and public_key = '".$post_data['public_keys']."' and is_deleted = '0'");
       if($result)
       {
            $dup_key_name[] = $result->getRowAt(0);
            $dup_key_name[] = $result->getRowAt(1);
            
       }
       return $dup_key_name;
       
    }
    
    /**
     * Add public keys.
     * @param type $active_user
     * @param type $pub_file_name
     * @param type $post_data
     * @return boolean
     */
    function add_keys($active_user = 0,$pub_file_name = "",$post_data = array())
    {
        if(count($post_data) == 0 || !is_numeric($active_user) || $pub_file_name == "")
        {
            return FALSE;
        }
        
        $keys_table_name = TABLE_PREFIX . 'rt_gitolite_user_public_keys';
       
        
        DB::execute("INSERT INTO $keys_table_name (user_id, key_name,pub_file_name, public_key) VALUES (?, ?, ?, ?)",
              $active_user, $post_data['key_name'],$pub_file_name,$post_data['public_keys']
         );
         return DB::lastInsertId() ;
    }

    /**
     * Remove keys of user.
     * @param type $key_id
     */
    function remove_keys($key_id = 0)
    {
        $keys_table_name = TABLE_PREFIX . 'rt_gitolite_user_public_keys';
        if($key_id == 0 || $key_id == "")
        {
            return false;
        }
        else
        {
            
           $remove =  DB::execute("update  ".$keys_table_name." set is_deleted = '1' where key_id = ".DB::escape($key_id));
           if($remove)
           {
               return TRUE;
           }
           else
           {
               return FALSE;
           }
        }
    }
    
    /**
     * get_filename 
     * Get public file name
     * @param type $key_id
     * @return boolean
     */
    function get_filename($key_id = 0)
    {
        $keys_table_name = TABLE_PREFIX . 'rt_gitolite_user_public_keys';
        if($key_id == 0 || $key_id == "")
        {
            return false;
        }
        $result = DB::execute("SELECT pub_file_name FROM ".$keys_table_name. " WHERE key_id = '".$key_id."'");
        if($result)
        {
            $get_keyname = $result->getRowAt(0);
            return $get_keyname['pub_file_name'];
            
            
        }
        else
        {
            return array();
        }
        
    }
    
    /*check_keys_added
     * Check whether keys are added for particular user
     */
    function check_keys_added ($user_id = 0)
    {
        if($user_id == 0 || $user_id == "")
        {
            return false;
        }
        $keys_table_name = TABLE_PREFIX . 'rt_gitolite_user_public_keys';
        $result = DB::execute("SELECT COUNT(user_id) as key_count from ".$keys_table_name. " where user_id = '".$user_id."' and is_deleted  = '0'");
        if($result)
        {
           
            $get_key_count = $result->getRowAt(0);
            return $get_key_count['key_count'];
            
        }
        else
        {
            return false;
        }
    }
  }
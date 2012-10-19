<?php

    AngieApplication::useController('users', SYSTEM_MODULE);
  
  /**
   * AcGitoliteController controller
   * 
   * @package activeCollab.modules.ac_gitolite
   * @subpackage controllers
   */
  class AcGitoliteController extends UsersController {
  
    
   
    
    /**
     * getpublickeys
     * List public keys in inline tabs
     */
    function getpublickeys()
    {
      
     $active_user = $this->active_user;
     
     $user_public_keys = GitoliteAc::fetch_keys($active_user->getId());
     
     
     
     $is_gitolite = GitoliteAdmin::is_gitolite();
     
     
      
      $this->smarty->assign(array(
        'user_public_keys' => $user_public_keys,
        'icon' => AngieApplication::getImageUrl('layout/button-add.png', ENVIRONMENT_FRAMEWORK, AngieApplication::getPreferedInterface()),
        'delete_icon' => AngieApplication::getImageUrl('icons/12x12/delete.png', ENVIRONMENT_FRAMEWORK, AngieApplication::getPreferedInterface()),
        'add_url' => Router::assemble('add_public_keys', array('company_id' => $active_user->getCompanyId(),'user_id' => $active_user->getId())),
        'del_url' => $this->active_user->getViewUrl(),
        'is_gitolite' => $is_gitolite
        
      ));
    
    }
    
    /**
     * add_public_keys
     * Add new public key of user. Create .pub file gitolite admin dir
     */
    function add_public_keys()
    {
      
       $active_user = $this->active_user;
       
        $this->response->assign(array(
             'form_action' => Router::assemble('add_public_keys', array('company_id' => $active_user->getCompanyId(),'user_id' => $active_user->getId())),
             'user_rmail' => $active_user->getEmail()
           ));  
       
       if($this->request->isSubmitted()) // check for form submission
       {
           $post_data = $this->request->post(); 
           /* Check form with validation error */
           $errors = new ValidationErrors();    
           try{
               
                $key_name = trim($post_data['key_name']);
                $public_keys = trim($post_data['public_keys']);
                
                
                if($key_name == "") {
                   $errors->addError('Please enter key name', 'key_name');
                 } 
                 if($public_keys == "") {
                   $errors->addError('Please enter key', 'public_keys');
                 } 
                
                 /* Check for duplications Key name and Key */
                 if(!$errors->hasErrors())
                 {
                      if(!preg_match("/^[A-Za-z0-9-]+$/", $key_name))
                      {
                        $errors->addError('Please enter valid key name.', 'public_keys');
                      }
                     $dup_cnt = GitoliteAc::check_duplication($active_user->getId(),$post_data);
                     if(count($dup_cnt) == 0)
                     {
                          $errors->addError('Problem occured while saving data, please try again.', 'public_keys');
                     }
                     elseif(count($dup_cnt) > 0)
                     {
                         if($dup_cnt[0]['dup_name_cnt'] > 0)
                         {
                             $errors->addError('You have already added key with same name.');
                             
                         }
                         if($dup_cnt[1]['dup_name_cnt'] > 0)
                         {
                             $errors->addError('Entered key is already added.');
                         }
                     }
    
                 }
                 // if errors found throw error exception
                 if($errors->hasErrors()) {
                   throw $errors;
                 }
           }catch (Exception $e)
           {
                 $this->response->exception($e);
           }
           
            // insert key details in database.
            $pub_file_name  = $key_name."-".$this->request->get("user_id");

            try
            {
                DB::beginWork('Adding a new public key @ ' . __CLASS__);
                //print_r($post_data);
                $save_data = GitoliteAc::add_keys($active_user->getId(),$pub_file_name,$post_data);
                if($save_data)
                {   
                    $file = $pub_file_name.".pub";
                    
                    $admin_settings  = GitoliteAdmin :: get_admin_settings();
                    if(!isset($admin_settings['gitoliteadminpath']))
                    {
                        $this->response->exception("Gitolite admin path not set");
                        die();
                    }
                    
                    $dirpath  = $admin_settings['gitoliteadminpath']."gitolite-admin/keydir/";
                    $path = $dirpath.$file;
                    
                    $newfh = fopen($path, 'w+');
                    if(!is_writable($path))
                    {
                         $this->response->exception("Can't write to file public file");
                         die();
                    }
                    $res = fwrite($newfh,$post_data['public_keys']);
                    fclose($fh);
                    /** Git Push Files **/
                    $command = "cd ".$dirpath." && git add * && git commit -am 'added key for user $file' && git push";
                    exec($command,$output,$return_var);
                    
                   DB::commit('Key added @ ' . __CLASS__);
                   
                   $show_data['key_name'] = $post_data['key_name'];
                   $show_data['public_key'] = substr($post_data['public_keys'],0,25).".....".substr($post_data['public_keys'],-30);
                   $show_data['delete_url'] = $this->active_user->getViewUrl()."/"."delete-keys"."/".$save_data;
                   $this->response->respondWithData($show_data, array('as' => 'settings'));
        

                }
            }catch (Exception $e)
            {   
                 $this->response->exception("Can't save key this time, might be key you are adding is already added");
                
            }
           
    } 
    
 }   
    
    /**
     * remove_key
     * Remove a specific publick key
     */
    function remove_key()
    {
        $get_data = $this->request->get(); 
        $active_user = $this->active_user;
        
        if(isset($get_data['key_id']))
        {
            $key_id = $get_data['key_id'];
            
            $filename = GitoliteAc::get_filename($key_id);

            if($filename != "")
            {
                $delete_keys = GitoliteAc::remove_keys($key_id);
                if(!$delete_keys)
                {
                    throw new Exception("Can't delete key");
                }
                else
                {    
                     $settings = GitoliteAdmin :: get_admin_settings();
                     $dirpath  = $settings['gitoliteadminpath']."gitolite-admin/keydir/";
                     $path = $dirpath.$filename.".pub";
                     @unlink($path);
                     $command = "cd ".$dirpath." && git add * && git commit -am 'deleted key $filename.pub' && git push  || echo 'Not found'";
                     exec($command,$output,$return_var);
                     echo '<script type="text/javascript">window.location.href = "' . $this->active_user->getViewUrl() . '"</script>';
                }   
            }
                
        }
       
    }
    
  }
?>
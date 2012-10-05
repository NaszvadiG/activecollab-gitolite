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
     * Prepare controller
     */
   
    
  
    function index()
    {
         if(AngieApplication::isModuleLoaded("source")  && $this->getControllerName() == 'ac_gitolite')
         {
             $do_continue = false;
             if(is_dir(GIT_FILES_PATH.DIRECTORY_SEPARATOR."repositories"))
             {
                 $do_continue = true;
             }
             
         }
        /*echo "here33";
        die();*/
    } // index
     
    
    /**
     * List public keys in inline tabs
     */
    function getpublickeys()
    {
      //echo "dasd asdas d";
     $active_user = $this->active_user;
     
     $user_public_keys = GitoliteAc::fetch_keys($active_user->getId());
     
     $admin_settings  = 
     
     $is_gitolite = GitoliteAdmin::is_gitolite();
     
      //print_r($user_public_keys);
      
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
     * Add new public key of user. Create .pub file gitolite admin dir
     */
    function add_public_keys()
    {
       $active_user = $this->active_user;
       
        $this->response->assign(array(
             'form_action' => Router::assemble('add_public_keys', array('company_id' => $active_user->getCompanyId(),'user_id' => $active_user->getId()))
             
           ));
       //$this->smarty->assign();
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
                 //$this->response->exception($errors->hasErrors());
                 //die();
                 //$test =  preg_match("/^[A-Za-z0-9_]+$/", $post_data['key_name']);
                
                
                 
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
            //$pub_file_name  = $active_user->getEmail()."_".$key_name;
            $pub_file_name  = $key_name."-".$this->logged_user->getId();
            
            //$this->response->exception($key_name_save);
            //die();
            try
            {
                 DB::beginWork('Adding a new public key @ ' . __CLASS__);
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
                    //$path = AC_GITOLITE_GIT_ADMIN_PATH.'keydir/'.$file;
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
                    //file_put_contents(AC_GITOLITE_GIT_ADMIN_PATH.'keydir/'.$file, $post_data['public_keys']);
                    
                    /** Git Push Files **/
                    $command = "cd ".$dirpath." && git add * && git commit -m 'added key for user' && git push";
                    exec($command,$output,$return_var);
                    
                    DB::commit('Key added @ ' . __CLASS__);
                    $this->response->redirectToUrl($this->active_user->getViewUrl());

                }
            }catch (Exception $e)
            {   
                 $this->response->exception("Can't save key this time, please try again.");
            }
           
    } else{
            //$this->response->redirectToUrl($this->active_user->getViewUrl());
            
            
        }
    }   
    
    /**
     * Remove a specific publick key
     */
    function remove_key()
    {
        $get_data = $this->request->get(); 
        $active_user = $this->active_user;
        
        
        if(isset($get_data['key_id']))
        {
            $key_id = $get_data['key_id'];
            //try
            //{
                //if($active_user->canEdit($this->logged_user)) 
                //{
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
                             echo '<script type="text/javascript">window.location.href = "' . $this->active_user->getViewUrl() . '"</script>';
                        }   
                    }
                //}
            //}catch (Exception $e)
            //{   
            //     $this->response->exception($e);
            //}
        }
       
    }
    
  }
?>
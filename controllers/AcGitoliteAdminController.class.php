<?php

// We need admin controller
AngieApplication::useController('admin', ENVIRONMENT_FRAMEWORK_INJECT_INTO);

/**
 * Ac Gitolite Admin Controller controller
 *
 * @package activeCollab.modules.ac_gitolite
 * @subpackage controllers
 */
class AcGitoliteAdminController extends AdminController {

    /**
     * Prepare controller
     */
    function __before() {
        parent::__before();
        
    }

    /** gitolite_admin
     * Save gitolite admin settings
     */
    function gitolite_admin() {
    
        /**
       * fetch current data
       */
       
       $settings = GitoliteAdmin :: get_admin_settings();
       
       $setup_script = GitoliteAdmin :: get_setup_path();
       
       $gitoliteadminpath = GitoliteAdmin :: get_admin_path();
       
       $gitoliteadminpath = "$gitoliteadminpath/gitolite/";
       
       
       $server_name = array_shift(explode(".",$_SERVER['HTTP_HOST']));
       preg_match('/^(?:www\.)?(?:(.+)\.)?(.+\..+)$/i', $_SERVER['HTTP_HOST'], $matches);
       
       $this->response->assign(
                            array('gitoliteuser' =>      ($settings['gitoliteuser'] == "") ? "git" : $settings['gitoliteuser'],
                                  'gitoliteserveradd' => $settings['gitoliteserveradd'],
                                  'gitoliteadmins' =>    $admins,
                                  'webuser' =>           exec ("whoami"),
                                  'gitoliteadminpath' => $gitoliteadminpath,
                                  'gitolite_repo_test_connection_url' => Router::assemble('gitolite_test_connection'),
                                  'setup_script' => $setup_script,
                                  'web_user'    =>  $_SERVER['USER'],
                                  'server_name' => $matches['2']
                                  
                                )
                            );
       if($this->request->isSubmitted()) // check for form submission
       {
           $errors = new ValidationErrors();    
           $post_data = $this->request->post("gitoliteadmin"); 
           
           try
           {
               
               DB::beginWork('Save admin settings @ ' . __CLASS__);
               $setting_exists = GitoliteAdmin :: setting_exists();
               
               if($setting_exists['cnt_settings'] == 0)
               {    
                    $settings_add = GitoliteAdmin :: insert_settings($post_data,$this->logged_user->getId());
                    if(!$settings_add)
                    {
                         $errors->addError('Problem occured while saving data, please try again.');
                         throw $errors;
                    }
                }
                else
                {   
                     $settings_update = GitoliteAdmin :: update_settings($post_data,$this->logged_user->getId());
                }
                DB::commit('Admin Settings Saved @ ' . __CLASS__);

                $this->flash->success("Settings saved successfully");
                $this->response->ok();
           }
            catch (Exception $e)
            {  
                     DB::rollback('Failed to create a repository @ ' . __CLASS__);
                     $this->response->exception($e);
           }
           die();
       }
    }
    
    
    function test_connection()
    {
      
        if (!(array_var($_GET, 'dir')) || !(array_var($_GET, 'user')) || !(array_var($_GET, 'server'))) {
            die(lang('Please fill in all the connection parameters'));     
        } 
        if (!(array_var($_GET, 'dir'))) {
            die('Gitolite admin path not found');     
        } //if
        
        if(!self::exec_enabled())
        {
             die("Please enable `exec` on this sever");
        }
        
        if(!is_dir(array_var($_GET, 'dir')))
        {
           
             
             if(mkdir (array_var($_GET, 'dir')))
             {
                 
                 $comd = "cd ".array_var($_GET, 'dir')." &&  git clone ".array_var($_GET, 'user')."@".array_var($_GET, 'server').":gitolite-admin.git || pwd";
                 exec($comd,$output,$return);
                 if(count($output) > 1)
                 {
                    die("Unable to connect to server");
                 }
                 else
                 {
                    die("ok");
                 }
                
             }
            else 
            {
                 die("Unable to create folder ".array_var($_GET, 'dir')); 
            }
        }
        else
        {
           if(is_dir(array_var($_GET, 'dir')."gitolite-admin"))
           {
               $comd = "ssh -T ac@rtcamp.info | grep gitolite-admin | grep 'R W'";
               exec($comd,$output,$return_var);
               if(count($output) > 1)
               {
                   if($output[0] == "R W gitolite-admin")
                   {
                       die("ok");
                   }
               }
               else
               {
                   die("Unable to connect to server");
               }
              
               //die("ok");
               //die("gitolite-admin already exists");
               
               /*$comd = "cd ".array_var($_GET,  'dir')." && mv gitolite-admin/ gitolite-admin-".time()."/";
               exec($comd,$output);
               $comd = "cd ".array_var($_GET, 'dir')." &&  git clone ".array_var($_GET, 'user')."@".array_var($_GET, 'server').":gitolite-admin.git || pwd";
               exec($comd,$output);
               if(count($output) > 1)
               {
                   die("Unable to connect to server");
               }
               else
               {
                  die("ok");
               }
               
               die();*/
               //exec($comd,$output);
           }
           else
           {
                 /*$comd = exec ("ssh -T ".array_var($_GET, 'user')."@".array_var($_GET, 'server'),$output);
                 print_r($output);
                 die();*/
                 
                $comd = "cd ".array_var($_GET, 'dir')." &&  git clone ".array_var($_GET, 'user')."@".array_var($_GET, 'server').":gitolite-admin.git || pwd";
                exec($comd,$output);
                if(count($output) > 1)
                {
                    die("Unable to connect to server");
                }
                else
                {
                   die("ok");
                }
                /*print_r($output);
                die();*/
           }
           //var_dump(exec($comd,$output)) ;
           //die("ok");
        }
      
    }
    
    /*  exec_enabled
     *  check whether exec is enabled on server
     */
    function exec_enabled() {
         $disabled = explode(', ', ini_get('disable_functions'));
        return !in_array('exec', $disabled);
}

}
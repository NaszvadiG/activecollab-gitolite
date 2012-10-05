<?php

// We need admin controller
AngieApplication::useController('admin', ENVIRONMENT_FRAMEWORK_INJECT_INTO);

/**
 * Admin merger settings controller
 *
 * @package activeCollab.modules.merger
 * @subpackage controllers
 */
class AcGitoliteAdminController extends AdminController {

    /**
     * Prepare controller
     */
    function __before() {
        parent::__before();
        
    }

// __construct

    /** gitolite_admin
     * Save gitolite admin settings
     */
    function gitolite_admin() {
       
        //var_dump( exec("git ls-remote --heads git@192.168.0.137:repositories/gitolite-admin.git",$output));
        
        /*$res =  shell_exec("git ls-remote --heads git@192.168.0.137:repositories/gitolite-admin.git");
        echo $res;
        print_r($output);
        die();*/
        
       /**
       * fetch current data
       */
       
       //$array = array('1','2');
       $settings = GitoliteAdmin :: get_admin_settings();
       
       $admins_access = @unserialize($settings['gitoliteadmins']);
       if($admins_access !== false || $admins_access === 'b:0;')
       {
            $admins_array = $admins_access;
       }
       else
       {
            $admins_array = array();
       } 
      
       /*print_r($settings);
       die();*/
       /*echo $settings['gitoliteserveradd'];
       die();*/
       $admins = @implode(",", $admins_array);
       
       $gitoliteadminpath = GitoliteAdmin :: get_admin_path();
       $gitoliteadminpath = "$gitoliteadminpath/gitolite/";
       /*if($settings['gitoliteserveradd'] != "")
       {
           $gitoliteadminpath = "~$gitoliteadminpath/".$settings['gitoliteserveradd']."/gitolite/";
       }
       else
       {
           $gitoliteadminpath = "No path set";
       }*/
       
       $this->response->assign(
                            array('gitoliteuser' =>      $settings['gitoliteuser'],
                                  'gitoliteserveradd' => $settings['gitoliteserveradd'],
                                  'gitoliteadmins' =>    $admins,
                                  'webuser' =>           exec ("whoami"),
                                  'gitoliteadminpath' => $gitoliteadminpath,
                                  'gitolite_repo_test_connection_url' => Router::assemble('gitolite_test_connection')
                                )
                            );
       if($this->request->isSubmitted()) // check for form submission
       {
           $errors = new ValidationErrors();    
           $post_data = $this->request->post("gitoliteadmin"); 
           
           try
           {
                /*if($this->test_connection() != "ok") 
                {
                    $errors->addError('Cannot connect to gitolite admin', '');
                    throw $errors;
                }*/
               
               /*if(!isset($post_data['gitoliteadmins'])) 
               {
                    $errors->addError('Please select atleast one admin', 'gitoliteadmins');
                    throw $errors;
               }*/
               
               DB::beginWork('Save admin settings @ ' . __CLASS__);
               $setting_exists = GitoliteAdmin :: setting_exists();
               /*if(isset($post_data['gitoliteadmins']) && is_array($post_data['gitoliteadmins']))
               {
                     $admins = serialize($post_data['gitoliteadmins']);
               }
               else
               {
                     $admins = serialize(array());
               }*/
               if($setting_exists['cnt_settings'] == 0)
               {    //,$admins
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
                       
                       
                DB::commit('Repository created @ ' . __CLASS__);

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
        /*print_r($_GET);
        die();*/
        if (!(array_var($_GET, 'dir')) || !(array_var($_GET, 'user')) || !(array_var($_GET, 'server'))) {
            die(lang('Please fill in all the connection parameters'));     
        } 
        if (!(array_var($_GET, 'dir'))) {
            die('Gitolite admin path not found');     
        } //if
        
        if(!is_dir(array_var($_GET, 'dir')))
        {
             //die('ok');
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
                //die('ok');
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
               die("ok");
           }
           else
           {
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
        /*if (!(array_var($_GET, 'url'))) {
            die(lang('Please fill in all the connection parameters'));     
        } //if
        if (! $this->active_repository instanceof SourceRepository) {
            
            $repository_class_name = array_var($_GET, 'engine');
            $this->active_repository = new $repository_class_name();
        } //if
        
        $this->active_repository->setRepositoryPathUrl(array_var($_GET, 'url'));
        //$this->active_repository->setUsername(array_var($_GET, 'user'));
        //$this->active_repository->setPassword(array_var($_GET, 'password'));
        $this->active_repository->setType(array_var($_GET, 'engine'));
        
        if (!$this->active_repository->loadEngine()) {
          die(lang('Failed to load repository engine'));
        }//if

        if (($error = $this->active_repository->loadEngine()) !== true) {
          die($error);
        } // if

        $result = $this->active_repository->testRepositoryConnection();
        if ($result !== true) {
          if ($result === false) {
            die('Please check URL or login parameters.');
          } else {
            echo ($result);
            die();
          } //if
        } else {
          die('ok');
        } // if*/
    }

}
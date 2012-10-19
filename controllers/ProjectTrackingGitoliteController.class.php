<?php

  // Build on top of system module
 AngieApplication::useController('repository', SOURCE_MODULE);
/**
   * Project Tracking Gitolite Controller controller implementation
   *
   * @package custom.modules.ac_gitolite
   * @subpackage controllers
   * 
   * 
   */
  class ProjectTrackingGitoliteController extends RepositoryController {
    
    /**
     * Project Tracking Gitolite
     * Add "Add New Git Repository" option under source tab.
     * Check for allowed repository as per the permissions
     */
     function index() {
       
         parent::index();
         
         // check whether user have access to add repositories
        if(ProjectSourceRepositories::canAdd($this->logged_user, $this->active_project)) {
         $this->wireframe->actions->add('add_git', 'Add New Git Repository', Router::assemble('add_git_repository',array('project_slug' => $this->active_project->getSlug())), array(
                 'onclick' => new FlyoutFormCallback('repository_created', array('width' => 'narrow')),
                 'icon' => AngieApplication::getPreferedInterface() == AngieApplication::INTERFACE_DEFAULT ? AngieApplication::getImageUrl('icons/16X16-git.png', AC_GITOLITE_MODULE) : AngieApplication::getImageUrl('icons/16X16-git.png', AC_GITOLITE_MODULE, AngieApplication::INTERFACE_PHONE))
             );
        }
          
          $repositories = ProjectSourceRepositories::findByProjectId($this->active_project->getId(), $this->logged_user->getMinVisibility());
          
          $get_admin_settings = GitoliteAdmin::get_admin_settings();
          //print_r($get_admin_settings);
          //if(is_array($get_admin_settings) && count($get_admin_settings) > 0)
          //{
                $cloneurls = array();
                $gitolite_repos = array();
                foreach ($repositories as $repository) {

                    $repo_fk = $repository->getFieldValue("integer_field_1");
                   
                   
                    $chk_gitolite = ProjectGitolite::is_gitolite_repo($repo_fk);
                    
                    if(is_array($chk_gitolite) && sizeof($chk_gitolite) > 0 && $chk_gitolite['chk_gitolite'] > 0)
                    {
                        
                        $permissions = @unserialize($chk_gitolite['permissions']);
                        if($permissions !== false || $permissions === 'b:0;')
                        {
                           $permissions_array = $permissions;
                        }
                        else
                        {
                           $permissions_array = array();
                        }
                        
                        if((array_key_exists($this->logged_user->getId(),$permissions_array) && $permissions_array[$this->logged_user->getId()] > 1)
                           || $this->logged_user->isAdministrator() || $this->logged_user->isProjectManager() 
                           || $this->active_project->isLeader($this->logged_user) 
                           || $repository->canAdd($this->logged_user)
                           )
                        {
                            $allowed_repos[] = $repository->getId();
                        }
                        /*$clone_url = $get_admin_settings['gitoliteuser']."@".$get_admin_settings['gitoliteserveradd'].":".$repository->getName();
                        $cloneurls[$repository->getId()] = "git clone ".$clone_url.".git";*/
                        $gitolite_repos[] = $repository->getId();
                    }
                    else
                    {
                         $allowed_repos[] = $repository->getId();
                    }
                   

                }
          //}
         
          $this->response->assign(array(
                    'repositories' => $repositories,
                    'cloneurls' => $cloneurls,  
                    'gitolite_repos' => $gitolite_repos,
                    'can_add_repository' => $can_add_repository,
                    'allowed_repos' => $allowed_repos
                     ));
               
     } // index
     
     /**
      * add_git_repo 
      * Add new gitolite repository
      */
     function add_git_repo()
     {
      
         $is_gitolite  = GitoliteAdmin :: is_gitolite();
         
         if(!ProjectSourceRepositories::canAdd($this->logged_user, $this->active_project)) {
                 $this->response->forbidden();
          } // if

         $project  = $this->active_project;
         $project_id = $project->getId();
         $logged_user = $this->logged_user;
         $user_id = $logged_user->getId();
         $no_key_warning = FALSE;
         $view_url = "";
         if(AngieApplication::isModuleLoaded("source") && $this->getControllerName() == 'project_tracking_gitolite')
         { 
             
              $do_continue = true;
          }
          
          if($do_continue)
          {
              
              $project_users =  Projects::findById($project_id);
              
              // Prepare users map
              $users_details = $this->active_project->users()->describe($this->logged_user, true, true, STATE_ARCHIVED);
             
              $user_detail_permissions = array();

              if(is_foreachable($users_details))
              {    
                  
                  foreach ($users_details as $key => $value) 
                  {
                     // check key exists 
                     $user_keys = GitoliteAc::check_keys_added($value['user']['id']);
                     
                     if($user_keys > 0)
                     {
                        
                        $objuser = new User($value['user']['id']);
                        $repoobj = new ProjectSourceRepositories();
                        $user_detail_permissions[$value['user']['id']] = 
                                      array('readaccess' => $repoobj->canAccess($objuser, $project) ,
                                            'writeaccess' =>  $repoobj->canAdd($objuser, $project),
                                            'writeaccessplus'=> $repoobj->canManage($objuser, $project),
                                            'user_keys' => $user_keys);
                         $allowed_users[$value['user']['id']] = $value['user']['name'];
                     }
                  } 
              }
               // Add Administrator , Leaders and Project Manager in allowed people list
              if($this->logged_user->isAdministrator() || $this->logged_user->isProjectManager())
              {
                  $objuser = new User($user_id);
                  $user_keys = GitoliteAc::check_keys_added($user_id);
                  if($user_keys)
                  {
                    $user_detail_permissions[$user_id] = 
                                      array('readaccess' => $repoobj->canAccess($objuser, $project) ,
                                            'writeaccess' =>  $repoobj->canAdd($objuser, $project),
                                            'writeaccessplus'=> $repoobj->canManage($objuser, $project),
                                            'user_keys' => $user_keys);
                    $allowed_users[$user_id] = $logged_user->getName();
                  }
                  else
                  {
                      $no_key_warning = TRUE;
                      $view_url = $this->logged_user->getViewUrl();
                  }
              }
              $this->response->assign(
                            array(
                                  'curr_users' => $allowed_users,
                                  'user_detail_permissions' => $user_detail_permissions,
                                  'form_action' => Router::assemble('add_git_repository', array('project_slug' => $project->getSlug())),
                                  'noaccess' => GITOLITE_NOACCESS,
                                  'readaccess' => GITOLITE_READACCESS,
                                  'manageaccess' => GITOLITE_MANAGEACCESS,
                                  'is_gitolite' => $is_gitolite,
                                  'no_key_warning' => $no_key_warning,
                                  'view_url' => $view_url
                                )
                            );
        
          }
          else
          {    
              $this->response->assign(
                            array('add_error' => TRUE
                                )
                            );
          }
                     
              
         if($this->request->isSubmitted()) // check for form submission
         {    

                try {
                   
                    /* Check form with validation error */
                    $repository_data = $this->request->post('repository');
                    
                    
                    $errors = new ValidationErrors();    
                    $post_data =  $this->request->post();
                    $settings = GitoliteAdmin :: get_admin_settings();
                    
                    $sever_user_path = GitoliteAdmin::get_server_user_path();
                    if(!$sever_user_path)
                    {
                        $errors->addError('Repository path on server invalid');
                    }
                    
                    $repo_path = $sever_user_path."/repositories/".$repository_data['name'].".git";
                    
                                        
                    $repo_name = trim($repository_data['name']);
                    $access = $post_data['access'];
                    
                  
                    if($repo_name == "") {
                        $errors->addError('Please enter repository name', 'repo_name');
                    } 
                    if(!is_array($access) && count($access) == 0) {
                        $errors->addError('Select access levels for user', 'access');
                    } 
                    /* Check for duplications repository name and Key */
                    if(!$errors->hasErrors())
                    {
                      if(!preg_match("/^[A-Za-z0-9-]+$/", $repo_name))
                      {
                            $errors->addError('Please enter valid repository name.', 'repo_name');
                      }
                      $dup_cnt = ProjectGitolite::check_duplication($project_id,$repository_data);
                      if(count($dup_cnt) == 0)
                      {
                         $errors->addError('Problem occured while saving data, please try again.');
                      }
                    elseif(count($dup_cnt) > 0)
                    {
                        if($dup_cnt[0]['dup_name_cnt'] > 0)
                        {
                            $errors->addError('You have already added repository with same name.');

                        }
                       
                    }
                 }

                // if errors found throw error exception
                if($errors->hasErrors()) {
                  throw $errors;
                }
                
                /** save gitolite details in database **/
                // save reponame
                 try {
                    DB::beginWork('Creating a new repository @ ' . __CLASS__);
                    if(is_array($post_data))
                    {
                        $repository_path_url = array('repository_path_url' => $repo_path);
                    }
                    $repository_data = array_merge($repository_data,$repository_path_url);
                    
                    $this->active_repository = new GitRepository();
                    $this->active_repository->setAttributes($repository_data);
                    $this->active_repository->setCreatedBy($this->logged_user);
                    
                    
                    
                    /*$result = true;
                    if ($result !== true) {
                        if ($result === false) {
                            $message = 'Please check URL or login parameters.';
                        } else {
                            
                            $message = $result;
                        } //if
                        $errors->addError('Failed to connect to repository: :message', array('message'=>$message));
                        throw $errors;
                    } //if*/
                    
                    $this->active_repository->save();
                    $repo_fk = $this->active_repository->getId();
                    if($repo_fk)
                    {
                        $clone_url = $settings['gitoliteuser']."@".$settings['gitoliteserveradd'].":".$repo_name;
                        $body = $clone_url;
                        
                        $this->project_object_repository->setName($this->active_repository->getName());
                        $this->project_object_repository->setBody($body);
                        $this->project_object_repository->setParentId($this->active_repository->getId());
                        $this->project_object_repository->setVisibility($repository_data['visibility']);
                        $this->project_object_repository->setProjectId($this->active_project->getId());
                        $this->project_object_repository->setCreatedBy($this->logged_user);
                        $this->project_object_repository->setState(STATE_VISIBLE);
                        $this->project_object_repository->save();
                        
                        $repo_id = ProjectGitolite::add_repo_details($repo_fk,$project_id,$user_id,$repo_path,$repository_data);
                        if($repo_id)
                        {
                            
                            $add_access = ProjectGitolite::add_access_levels($repo_id,serialize($post_data['access']),$user_id,1);
                            if($add_access)
                            {
                                $res = ProjectGitolite::render_conf_file();
                               
                                $dir = $settings['gitoliteadminpath']."gitolite-admin";
                                
                                $command = "cd ".$dir." && git add * && git commit -am 'render conf file' && git push  || echo 'Not found'";
                                exec($command,$output,$return_var);
                               /*$git_server = $settings['gitoliteuser']."@".$settings['gitoliteserveradd'];
                                $command = "cd ".$settings['gitoliteadminpath']." && git clone ".$git_server.":".$repo_name;
                                exec($command,$output,$return_var);*/

                            }
                            else
                            {
                                $errors->addError('Error while saving access levels.');
                                throw $errors;
                            }
                        }
                        else
                        {    $errors->addError('Error while saving repository.');
                             throw $errors;
                        }
                    }
                    else
                    {
                        $errors->addError('Error while saving repository.');
                        throw $errors;
                    }
                    DB::commit('Repository created @ ' . __CLASS__);
                    $this->response->respondWithData($this->project_object_repository);
                 }
                catch (Exception $e)
                {  
                    DB::rollback('Failed to create a repository @ ' . __CLASS__);
                    $this->response->exception($e);
                }
               
            }catch (Exception $e)
             {
                 DB::rollback('Failed to create a repository @ ' . __CLASS__);
                 $this->response->exception($e);
             }
             
         } 
             
     }
     
     function edit_git_repo()
     {
         $repo_id = array_var($_GET, 'project_source_repository_id'); //project objects id
         
         $is_gitolite  = GitoliteAdmin :: is_gitolite();
          if(!ProjectSourceRepositories::canAdd($this->logged_user, $this->active_project)) {
                 $this->response->forbidden();
          } // if
                   
         $project  = $this->active_project;
         $project_id = $project->getId();
         $logged_user = $this->logged_user;
         $user_id = $logged_user->getId();
         $no_key_warning = FALSE;   // to give warning if logged in user has not added his public key
         if(AngieApplication::isModuleLoaded("source") && $this->getControllerName() == 'project_tracking_gitolite')
         { 
             
              $do_continue = true;
          }
          
          if($do_continue)
          {
              
              $users_details = $this->active_project->users()->describe($this->logged_user, true, true, STATE_ARCHIVED);
             
              $repo_details = ProjectGitolite::get_repo_details($repo_id);
              
              
              
              
               $repository_data = $this->request->post('repository');
               if (!is_array($repository_data)) {
                  $repository_data = array(
                          'updatetype'      		=> $this->active_repository->getUpdateType(),
                          'visibility'      		=> $this->project_object_repository->getVisibility()
                  );
                } // if
              
              if(is_array($repo_details) && count($repo_details) > 0)
              {
                  // repository id from integer_field_1 in project_objects , we are saving this id in our tables.
                  $git_repo_id = $repo_details['repo_id']; 
                  $access_array = ProjectGitolite::get_access_levels($git_repo_id);
                  //$result_access = DB::execute("SELECT * from $access_table_name where repo_id = '".$repo_details['repo_id']."'");
                  if(is_array($access_array) && count($access_array) > 0)
                  {
                      $access = $access_array['permissions'];
                      
                      $permissions = @unserialize($access);
                      
                      if($permissions !== false || $permissions === 'b:0;')
                      {
                          $permissions_array = $permissions;
                      }
                      else
                      {
                         $permissions_array = array();
                      }  
                      
                  }
                  else
                  { 
                      
                      $this->response->forbidden();
                  }
              }
              else
              {
                  
                  $this->response->forbidden();
              }
              
              //print_r($permissions_array);
              
              $user_detail_permissions = array();
              
              if(is_foreachable($users_details))
              {    
                  foreach ($users_details as $key => $value) 
                  {
                      
                     // check key exists
                     
                     $user_keys = GitoliteAc::check_keys_added($value['user']['id']);
                     $objuser = new User($key);
                     
                     if($user_keys > 0)
                     {
                       
                        $repoobj = new ProjectSourceRepositories();
                        $user_detail_permissions[$value['user']['id']] = 
                                    array('readaccess' => ($permissions_array[$value['user']['id']] == "2") ? TRUE : FALSE,
                                          'writeaccess' => ($permissions_array[$value['user']['id']] == "3") ? TRUE : FALSE,
                                          'writeaccessplus'=> ($permissions_array[$value['user']['id']] == "3") ? TRUE : FALSE,
                                          'user_keys' => $user_keys);
                        
                         $allowed_users[$value['user']['id']] = $value['user']['name'];
                     }
                     
                  } 
              }
              
              if($this->logged_user->isAdministrator() || $this->logged_user->isProjectManager())
              {
                  
                  $objuser = new User($user_id);
                  $user_keys = GitoliteAc::check_keys_added($user_id);
                  if($user_keys)
                  {
                        $user_detail_permissions[$user_id] = 
                                            array('readaccess' => $repoobj->canAccess($objuser, $project) ,
                                                  'writeaccess' =>  $repoobj->canAdd($objuser, $project),
                                                  'writeaccessplus'=> $repoobj->canManage($objuser, $project),
                                                  'user_keys' => $user_keys);
                        $allowed_users[$user_id] = $logged_user->getName();
                  }
                  else
                  {
                      $no_key_warning = TRUE;
                  }
              }
              
              $this->response->assign(
                            array(
                                  'curr_users' => $allowed_users,
                                  'repo_details' =>  $repo_details,
                                  'user_detail_permissions' => $user_detail_permissions,
                                  'form_action' => Router::assemble('edit_git_repository', array('project_slug' => $project->getSlug(),'project_source_repository_id' => $repo_id)),
                                  'noaccess' => GITOLITE_NOACCESS,
                                  'readaccess' => GITOLITE_READACCESS,
                                  'manageaccess' => GITOLITE_MANAGEACCESS,
                                  'is_gitolite' => $is_gitolite,
                                  'no_key_warning' => $no_key_warning,
                                  'repository_data' => $repository_data
                                )
                            );
        
          }
          else
          {    
             
              $this->response->assign(
                            array('add_error' => TRUE
                                )
                            );
          }
                     
              
         if($this->request->isSubmitted()) // check for form submission
          {    
                
                try {
                    
                   
                    /* Check form with validation error */
                    $repository_data = $this->request->post('repository');
                    
                    
                    $errors = new ValidationErrors();    
                    $post_data =  $this->request->post();
                    $settings = GitoliteAdmin :: get_admin_settings();
                    
                   
                    $sever_user_path = GitoliteAdmin::get_server_user_path();
                    if(!$sever_user_path)
                    {
                        $errors->addError('Repository path on server invalid');
                    }
                    
                    $repo_path = $sever_user_path."/repositories/".$repository_data['name'].".git";
                    
                     
                    $repo_name = trim($repository_data['name']);
                    $access = $post_data['access'];
                    
                  
                    if($repo_name == "") {
                        $errors->addError('Please enter repository name', 'repo_name');
                    } 
                    if(!is_array($access) && count($access) == 0) {
                        $errors->addError('Select access levels for user', 'access');
                    } 
                    /* Check for duplications repository name and Key */
                    if(!$errors->hasErrors())
                    {
                      if(!preg_match("/^[A-Za-z0-9-]+$/", $repo_name))
                      {
                            $errors->addError('Please enter valid repository name.', 'repo_name');
                      }
                      
                      /*$dup_cnt = ProjectGitolite::check_duplication($project_id,$repository_data);
                      if(count($dup_cnt) == 0)
                      {
                         $errors->addError('Problem occured while saving data, please try again.');
                      }
                    elseif(count($dup_cnt) > 0)
                    {
                        if($dup_cnt[0]['dup_name_cnt'] > 0)
                        {
                            $errors->addError('You have already added repository with same name.');

                        }
                       
                    }*/
                 }

                // if errors found throw error exception
                if($errors->hasErrors()) {
                  throw $errors;
                }
                
                /** save gitolite details in database **/
                // save reponame
                 try {
                    DB::beginWork('Update repository @ ' . __CLASS__);
                    $this->active_repository->setAttributes($repository_data);
                    $this->project_object_repository->setVisibility($repository_data['visibility']);
                    $this->project_object_repository->setName($repository_data['name']);

                    $this->active_repository->save();
                    $this->project_object_repository->save();
                    
                    $repo_fk = $this->active_repository->getId();
                    
                    if($repo_id)
                    {
                        
                        $update_access = ProjectGitolite::update_access_levels($git_repo_id,serialize($post_data['access']));
                        if($update_access)
                        {
                            $res = ProjectGitolite::render_conf_file();
                           
                            $dir = $settings['gitoliteadminpath']."gitolite-admin";
                                
                            $command = "cd ".$dir." && git add * && git commit -am 'render conf file' && git push  || echo 'Not found'";
                            exec($command,$output,$return_var);

                            /*$git_server = $settings['gitoliteuser']."@".$settings['gitoliteserveradd'];
                            $command = "cd ".$settings['gitoliteadminpath']." && git clone ".$git_server.":".$repo_name;
                            exec($command,$output,$return_var);*/
                        }
                        else
                        {
                            $errors->addError('Error while saving access levels.');
                            throw $errors;
                        }
                 
                    }
                    else
                    {
                        $errors->addError('Error while saving repository.');
                        throw $errors;
                    }
                    DB::commit('Repository created @ ' . __CLASS__);
                    //$this->response->redirectToReferer();
                     // $this->response->ok();
                    //echo urldecode($this->active_repository->getViewUrl());
                    //die();
                     //$this->response->redirectTo($this->active_repository->getViewUrl());
                    $this->response->ok();
                    //$this->response->respondWithData($this->project_object_repository);
                 }
                catch (Exception $e)
                {  
                    DB::rollback('Failed to create a repository @ ' . __CLASS__);
                    $this->response->exception($e);
                }
               
            }catch (Exception $e)
             {
                 DB::rollback('Failed to create a repository @ ' . __CLASS__);
                 $this->response->exception($e);
             }
             
         } 
     }

     /**
      * history
      * Check repository access and add repository help on history page
      */
     function history() {
         
          $project = $this->active_project;
          $repository = $this->active_repository;
         
          $repo_details = ProjectGitolite::get_repo_details($repository->getId());
         
         if(is_array($repo_details) && count($repo_details) > 0)
         {
            //if ($repository->canEdit($this->logged_user)) {
               
                $this->wireframe->actions->add('manage_access', lang('Manage Access'),
                                           Router::assemble('edit_git_repository', 
                                           array('project_slug' => $project->getSlug(),
                                           'project_source_repository_id' => $repository->getId()))
                                           , array(
                                               'id'=> 'update_access_levels',
                                               'onclick'=> new FlyoutFormCallback("access_updated",array('width' => 'narrow')),
                                               'icon' => AngieApplication::getPreferedInterface() == AngieApplication::INTERFACE_DEFAULT ? AngieApplication::getImageUrl('icons/16X16-git.png', AC_GITOLITE_MODULE) : AngieApplication::getImageUrl('icons/16X16-git.png', AC_GITOLITE_MODULE, AngieApplication::INTERFACE_PHONE),
                   ));
                //} //if
        }
                                       
           
         
          $repo_path = $repository->getRepositoryPathUrl();
         
          $repo_fk = $repository->getId();
          $chk_gitolite = ProjectGitolite::is_gitolite_repo($repo_fk);
          
          if(is_array($chk_gitolite) && sizeof($chk_gitolite) > 0 && $chk_gitolite['chk_gitolite'] > 0)
          {
                $settings = GitoliteAdmin :: get_admin_settings();
                $clone_url = $settings['gitoliteuser']."@".$settings['gitoliteserveradd'].":".$this->active_repository->getName().".git";
                $permissions = @unserialize($chk_gitolite['permissions']);
                if($permissions !== false || $permissions === 'b:0;')
                {
                   $permissions_array = $permissions;
                }
                else
                {
                   $permissions_array = array();
                }
               
                if((array_key_exists($this->logged_user->getId(),$permissions_array) && $permissions_array[$this->logged_user->getId()] > 1)
                   || $this->logged_user->isAdministrator() || $this->logged_user->isProjectManager() 
                   || $this->active_project->isLeader($this->logged_user) 
                   || $repository->canAdd($this->logged_user)
                   )
                {
                        
                        $body = "<h2>Git Global Setup</h2>";
                        $body.= "<code>";
                        $body.= "git config --global user.name '".$this->logged_user->getDisplayName()."'"."<br>";
                        $body.= "git config --global user.email '".$this->logged_user->getEmail()."'"."<br>";
                        $body.= "</code>";
                        $body.= "<h2>Create Repository:</h2>";
                        $body.= "<code>";
                        $body.= "mkdir ".$this->active_repository->getName()."<br>";
                        $body.= "cd ".$this->active_repository->getName()."<br>";
                        $body.= "git init"."<br>";
                        $body.= "touch README"."<br>";
                        $body.= "git add README"."<br>";
                        $body.= "git commit -m 'first commit'"."<br>";
                        $body.= "git remote add origin ".$clone_url."<br>";
                        $body.= "git push -u origin master"."<br>";
                        $body.= "</code>";
                        $body.= "<h2>Existing Git Repo?</h2>";
                        $body.= "<code>";
                        $body.= "cd existing_git_repo"."<br>";
                        $body.= "git remote add origin ".$clone_url."<br>";
                        $body.= "git push -u origin master";
                        $body.="</code>";
                        $body_text = $body;
                }
                else {
                  
                    $this->response->forbidden();
                }
        }
        else
        {
            $body_text = $repository->getFieldValue("repository_path_url");
            
        }
        parent::history(); 
        $this->response->assign(array(
                       'body_text' => $body_text,
                       'repo_path' => $repo_path,
                       'clone_url' => $clone_url ));
     
    }
    
    function delete_gitolite_repository()
    {
        
      if($this->request->isSubmitted()) {
          
        if($this->project_object_repository->canDelete($this->logged_user)) {
          try {
            $this->project_object_repository->delete();
            //delete gitolite repo
                ProjectGitolite::delete_git_repo($this->project_object_repository->getId());
                
            if($this->request->isApiCall()) {
              $this->response->ok();
            } else {
              $this->response->respondWithData($this->active_repository, array(
                'detailed' => true
              ));
            } // if
          } catch(Exception $e) {
            DB::rollback('Failed to delete repository');
      			$this->response->exception($e);
          } // try
        } else {
          $this->response->forbidden();
        } // if
      } else {
        $this->response->badRequest();
      } // if
    
    }
    
  }
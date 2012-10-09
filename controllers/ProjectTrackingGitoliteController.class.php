<?php

  // Build on top of system module
 AngieApplication::useController('repository', SOURCE_MODULE);
/**
   * TimeReportsPlus controller implementation
   *
   * @package custom.modules.ac_gitolite
   * @subpackage controllerslIST
   * 
   * 
   */
  class ProjectTrackingGitoliteController extends RepositoryController {
    
    /**
     * Project Tracking Gitolite
     * Add "Add New Git Repository" option under source tab.
     */
     function index() {
         
         parent::index();
         //$this->wireframe->actions->add('add_git','Add New Git Repository' , Router::assemble('add_git_repository',array('project_slug' => $this->active_project->getSlug())));
         // check whether user have access to add repositories
        if(ProjectSourceRepositories::canAdd($this->logged_user, $this->active_project)) {
         $this->wireframe->actions->add('add_git', 'Add New Git Repository', Router::assemble('add_git_repository',array('project_slug' => $this->active_project->getSlug())), array(
                 'onclick' => new FlyoutFormCallback('repository_created', array('width' => 'narrow')),
                 'icon' => AngieApplication::getImageUrl('layout/button-add.png', ENVIRONMENT_FRAMEWORK, AngieApplication::getPreferedInterface()),        	
             ));
        }

          $can_add_repository = "true";

          $repositories = ProjectSourceRepositories::findByProjectId($this->active_project->getId(), $this->logged_user->getMinVisibility());
          $this->response->assign(array(
                    'repositories' => $repositories,
                     'can_add_repository' => $can_add_repository
                     ));
               
     } // index
     
     /**
      * add_git_repo 
      * Add new gitolite repo
      */
     function add_git_repo()
     {
      
        /* $sever_user_path = GitoliteAdmin::get_server_user_path();
                    echo $sever_user_path;
                    die();*/
         /*echo $repo_path = AC_GITOLITE_GIT_REPO_PATH."/";
         die();*/
         /*$response = ProjectGitolite::render_conf_file();
         echo $response;
         die();*/
         $is_gitolite  = GitoliteAdmin :: is_gitolite();
          if(!ProjectSourceRepositories::canAdd($this->logged_user, $this->active_project)) {
                 $this->response->forbidden();
          } // if
         
         $project  = $this->active_project;
         $project_id = $project->getId();
         $logged_user = $this->logged_user;
         $user_id = $logged_user->getId();

         if(AngieApplication::isModuleLoaded("source") && $this->getControllerName() == 'project_tracking_gitolite')
         { 
             /*$do_continue = false;
             if(is_dir(AC_GITOLITE_GIT_REPO_PATH))
             {
                 $do_continue = true;
             }*/
              $do_continue = true;
          }
          
          if($do_continue)
          {

              $users_details = $this->active_project->users()->getIdNameMap();
              
              
              
              /*print_r($users_details);
              die();*/
              $user_detail_permissions = array();
              
              if(is_foreachable($users_details))
              {    
                  
                  foreach ($users_details as $key => $value) 
                  {
                      //$userobj = new User($key);
                     // check key exists 
                     $user_keys = GitoliteAc::check_keys_added($key);
                      
                              
                      
                      
                      $objuser = new User($key);
                      $repoobj = new ProjectSourceRepositories();
                      $user_detail_permissions[$key] = 
                                    array('canaccess' => $repoobj->canAccess($objuser, $project) ,
                                          'readaccess' =>  $repoobj->canAdd($objuser, $project),
                                          'writeaccess'=> $repoobj->canManage($objuser, $project),
                                          'user_keys' => $user_keys);
                  } 
              }
              
              
              $this->response->assign(
                            array(
                                  'curr_users' => $users_details,
                                  'user_detail_permissions' => $user_detail_permissions,
                                  'form_action' => Router::assemble('add_git_repository', array('project_slug' => $project->getSlug())),
                                  'noaccess' => GITOLITE_NOACCESS,
                                  'readaccess' => GITOLITE_READACCESS,
                                  'manageaccess' => GITOLITE_MANAGEACCESS,
                                  'is_gitolite' => $is_gitolite
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
                    //print_r($this->request->post());
                    //die();
                   
                    /* Check form with validation error */
                    $repository_data = $this->request->post('repository');
                    //print_r($repository_data);
                    
                    $errors = new ValidationErrors();    
                    $post_data =  $this->request->post();
                    $settings = GitoliteAdmin :: get_admin_settings();
                    
                    //$repo_path = $settings['gitoliteadminpath'].$repository_data['name'];
                    $sever_user_path = GitoliteAdmin::get_server_user_path();
                    if(!$sever_user_path)
                    {
                        $errors->addError('Repository path on server invalid');
                    }
                    
                    $repo_path = $sever_user_path."/repositories/".$repository_data['name'].".git";
                    
                    /*print_r($post_data);
                    die();*/
                    //$repo_name = trim($post_data['repository_name']);
                    
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
                    //print_r($repository_data);
                    //  die();
                    $this->active_repository = new GitRepository();
                    $this->active_repository->setAttributes($repository_data);
                    $this->active_repository->setCreatedBy($this->logged_user);
                    
                    /*print_r($this->active_repository);
                    die();*/
                    /*$result = $this->active_repository->testRepositoryConnection();
                    print_r($result);
                    die();*/
                    
                    //$result = $this->active_repository->testRepositoryConnection();
                    /*$result = $this->active_repository->testRepositoryConnection();
                    print_r($result);
                    die();*/
                    
                    $result = true;
                    if ($result !== true) {
                        if ($result === false) {
                            $message = 'Please check URL or login parameters.';
                        } else {
                            
                            $message = $result;
                        } //if
                        $errors->addError('Failed to connect to repository: :message', array('message'=>$message));
                        throw $errors;
                    } //if
                    $this->active_repository->save();
                    $repo_fk = $this->active_repository->getId();
                    if($repo_fk)
                    {
                        $this->project_object_repository->setName($this->active_repository->getName());
                        $this->project_object_repository->setBody($this->active_repository->getRepositoryPathUrl());
                        $this->project_object_repository->setParentId($this->active_repository->getId());
                        $this->project_object_repository->setVisibility($repository_data['visibility']);
                        $this->project_object_repository->setProjectId($this->active_project->getId());
                        $this->project_object_repository->setCreatedBy($this->logged_user);
                        $this->project_object_repository->setState(STATE_VISIBLE);
                        $this->project_object_repository->save();
                        
                        $repo_id = ProjectGitolite::add_repo_details($repo_fk,$project_id,$user_id,$repo_path,$repository_data);
                        if($repo_id)
                        {
                            /*print_r($post_data['access']);
                            die();*/
                            $add_access = ProjectGitolite::add_access_levels($repo_id,serialize($post_data['access']),$user_id,1);
                            if($add_access)
                            {
                                $res = ProjectGitolite::render_conf_file();
                                //print_r($res);
                                //echo $res;
                                $dir = $settings['gitoliteadminpath']."gitolite-admin";
                                
                                $command = "cd ".$dir." && git add * && git commit -am 'render conf file' && git push  || echo 'Not found'";
                                exec($command,$output,$return_var);
                                //print_r($output);
                                //die();
                                
                                /*$git_server = $settings['gitoliteuser']."@".$settings['gitoliteserveradd'];
                                $command = "cd ".$settings['gitoliteadminpath']." && git clone ".$git_server.":".$repo_name;
                                exec($command,$output,$return_var);*/
                                
                                
                                //$this->response->exception($output);
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
             //echo "adas dasd";
             //$this->response->redirectTo("project_repositories",array('project_slug' => $this->active_project->getSlug()));
         } 
             
     }
     
     function edit_git_repo()
     {
        
         $repo_id = array_var($_GET, 'project_source_repository_id'); //project objects id
         
         $is_gitolite  = GitoliteAdmin :: is_gitolite();
          if(!ProjectSourceRepositories::canAdd($this->logged_user, $this->active_project)) {
                 $this->response->forbidden();
          } // if
          //print_r($this->request->post());
                   
         $project  = $this->active_project;
         $project_id = $project->getId();
         $logged_user = $this->logged_user;
         $user_id = $logged_user->getId();

         if(AngieApplication::isModuleLoaded("source") && $this->getControllerName() == 'project_tracking_gitolite')
         { 
             /*$do_continue = false;
             if(is_dir(AC_GITOLITE_GIT_REPO_PATH))
             {
                 $do_continue = true;
             }*/
              $do_continue = true;
          }
          
          if($do_continue)
          {
              
              $repo_table_name = TABLE_PREFIX . 'gitolite_repomaster';
              $objects_table_name = TABLE_PREFIX . 'project_objects';
              $access_table_name = TABLE_PREFIX . 'gitolite_access_master';      
              
              $users_details = $this->active_project->users()->getIdNameMap();
              $result = DB::execute("SELECT a.repo_id,a.repo_name,a.git_repo_path,b.name FROM $repo_table_name a, $objects_table_name b 
                                where a.`repo_fk` = b.integer_field_1 and b.type = 'ProjectSourceRepository'
                                and b.id = '".$repo_id."'");
              
              if($result)
              {
                  $repo_details = $result->getRowAt("0");
                  // repository id from integer_field_1 in project_objects , we are saving this id in our tables.
                  $git_repo_id = $repo_details['repo_id']; 
                  $result_access = DB::execute("SELECT * from $access_table_name where repo_id = '".$repo_details['repo_id']."'");
                  if($result_access)
                  {
                      $access_array =  $result_access->getRowAt("0");
                      $access = $access_array['permissions'];
                      
                      $permissions = @unserialize($access);
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
              //die();
              $user_detail_permissions = array();
              
              if(is_foreachable($users_details))
              {    
                  foreach ($users_details as $key => $value) 
                  {
                      //$userobj = new User($key);
                     // check key exists
                     
                     $user_keys = GitoliteAc::check_keys_added($key);
                     $objuser = new User($key);
                     
                     if($user_keys > 0)
                     {
                        $repoobj = new ProjectSourceRepositories();
                        $user_detail_permissions[$key] = 
                                    array('canaccess' => ($permissions_array[$key] == "2") ? TRUE : FALSE,
                                          'readaccess' => ($permissions_array[$key] == "2") ? TRUE : FALSE,
                                          'writeaccess'=> ($permissions_array[$key] == "3") ? TRUE : FALSE,
                                          'user_keys' => $user_keys);
                        $allowed_users[$key] = $users_details[$key];
                     }
                     
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
                                  'is_gitolite' => $is_gitolite
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
                    /*print_r($this->request->post());
                    die();*/
                   
                    /* Check form with validation error */
                    $repository_data = $this->request->post('repository');
                    //print_r($repository_data);
                    
                    $errors = new ValidationErrors();    
                    $post_data =  $this->request->post();
                    $settings = GitoliteAdmin :: get_admin_settings();
                    
                    //$repo_path = $settings['gitoliteadminpath'].$repository_data['name'];
                    $sever_user_path = GitoliteAdmin::get_server_user_path();
                    if(!$sever_user_path)
                    {
                        $errors->addError('Repository path on server invalid');
                    }
                    
                    $repo_path = $sever_user_path."/repositories/".$repository_data['name'].".git";
                    
                    /*print_r($post_data);
                    die();*/
                    //$repo_name = trim($post_data['repository_name']);
                    
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
                    
                    $repo_fk = $this->active_repository->getId();
                    
                    //$repo_id = ProjectGitolite::add_repo_details($repo_fk,$project_id,$user_id,$repo_path,$repository_data);
                    if($repo_id)
                    {
                        
                        $update_access = ProjectGitolite::update_access_levels($git_repo_id,serialize($post_data['access']));
                        if($update_access)
                        {
                            $res = ProjectGitolite::render_conf_file();
                            //echo $update_access;
                           // die();
                            //print_r($res);
                            //echo $res;
                            $dir = $settings['gitoliteadminpath']."gitolite-admin";
                                
                            $command = "cd ".$dir." && git add * && git commit -am 'render conf file' && git push  || echo 'Not found'";
                            exec($command,$output,$return_var);
                            //print_r($output);
                            //die();

                            /*$git_server = $settings['gitoliteuser']."@".$settings['gitoliteserveradd'];
                            $command = "cd ".$settings['gitoliteadminpath']." && git clone ".$git_server.":".$repo_name;
                            exec($command,$output,$return_var);*/
                             
                            //$this->flash->success("Repository udated successfully");
                            //$this->response->exception($output);
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
                    $this->flash->success('Project ":name" has been created', array('name' => $repo_name));
                    //$this->response->redirectToUrl($project->getViewUrl());
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
             //echo "adas dasd";
             //$this->response->redirectTo("project_repositories",array('project_slug' => $this->active_project->getSlug()));
         } 
     }
     /**
     * List repositories
     */
  }

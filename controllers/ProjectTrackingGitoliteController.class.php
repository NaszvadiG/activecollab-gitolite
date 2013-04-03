<?php
 require_once SOURCE_MODULE_PATH.'/engines/git.class.php';
  // Build on top of system module
 AngieApplication::useController('repository', SOURCE_MODULE);

/**
* Project Tracking Gitolite Controller controller implementation
*
* @package custom.modules.ac_gitolite
* @subpackage controllers
* @author rtCamp Software Solutions Pvt Ltd <admin@rtcamp.com>
* @author Rahul Bansal <rahul.bansal@rtcamp.com> 
* @author Kasim Badami <kasim.badami@rtcamp.com>
* @author  Mitesh Shah <mitesh.shah@rtcamp.com>
* 
*/
  class ProjectTrackingGitoliteController extends RepositoryController {
    
      
      function __before() {
          
        parent::__before();
        
      }
    /**
     * Project Tracking Gitolite
     * Add "Add New Git Repository" option under source tab.
     * Check for allowed repository as per the permissions
     */
      
     function index() {
       
         parent::index();
         
         // check whether user have access to add repositories
        if(ProjectSourceRepositories::canAdd($this->logged_user, $this->active_project)) {
         $this->wireframe->actions->add('add_git', lang('Create Git Repository'), Router::assemble('add_git_repository',array('project_slug' => $this->active_project->getSlug())), array(
                 'onclick' => new FlyoutFormCallback('repository_created', array('width' => 'narrow')),
                 'icon' => AngieApplication::getPreferedInterface() == AngieApplication::INTERFACE_DEFAULT ? AngieApplication::getImageUrl('icons/16X16-git.png', AC_GITOLITE_MODULE) : AngieApplication::getImageUrl('icons/16X16-git.png', AC_GITOLITE_MODULE, AngieApplication::INTERFACE_PHONE))
             );
         
          $this->wireframe->actions->add('add_remote_git', lang('Clone Remote Repository'), Router::assemble('add_remote_git',array('project_slug' => $this->active_project->getSlug())), array(
                 'onclick' => new FlyoutFormCallback('repository_created', array('width' => '900')),
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
                $remote_repos = array();
                
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
                    elseif(is_array($chk_gitolite) && sizeof($chk_gitolite) > 0 && $chk_gitolite['chk_gitolite'] == 0)
                    { 
                       
                        $chk_remote = ProjectGitolite::chk_remote_repo($repo_fk);
                        
                        if(is_array($chk_remote) && sizeof($chk_remote) > 0 && $chk_remote['chk_remote'] > 0)
                        {
                            $remote_repos[] = $repository->getId();
                            $allowed_repos[] = $repository->getId();
                        }
                        else
                        {   
                            
                            $allowed_repos[] = $repository->getId();
                        }
                    }
                    else
                    {   
                        $allowed_repos[] = $repository->getId();
                    }
                }
          //}
                //echo "asdas dasd";
                //print_r($allowed_repos);
                //die();
          $this->response->assign(array(
                    'repositories' => $repositories,
                    'cloneurls' => $cloneurls,  
                    'gitolite_repos' => $gitolite_repos,
                    'remote_repos' => $remote_repos,
                    'can_add_repository' => $can_add_repository,
                    'allowed_repos' => $allowed_repos
                     ));
               
     } // index

     /**
      *  Add new gitolite repository
      * @throws ValidationErrors
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
              $users_details = $this->active_project->users()->describe($this->logged_user, true, true,STATE_VISIBLE);
             
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
                    
                    /*print_r($repository_data);
                    die();*/
                    
                    $errors = new ValidationErrors();    
                    $post_data =  $this->request->post();
                    
                    /*print_r($post_data['access']);
                    die();*/
                    $settings = GitoliteAdmin :: get_admin_settings();
                    $is_remote = (!isset($settings["git_server_location"]) || $settings["git_server_location"] != "remote") ? FALSE : TRUE;
                    if($is_remote == FALSE)
                    {
                        $sever_user_path = GitoliteAdmin::get_server_user_path();
                        if(!$sever_user_path)
                        {
                            $errors->addError('Repository path on server invalid');
                        }
                    }
                              
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
                            $errors->addError('Repository name already used');

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
                    
                    /**
                     * if gitolite is setup on remote, change repo path
                     */
                    if(!$is_remote)
                    {
                        $repo_path = $sever_user_path."/repositories/".$repository_data['name'].".git";
                    }
                    elseif($is_remote)
                    {
                        $repo_path = GIT_FILES_PATH."/".$repo_name;
                    }
                    if(is_array($post_data))
                    {
                        $repository_path_url = array('repository_path_url' => $repo_path);
                        $notif_setting = (isset($repository_data["disable_notifications"])) ? "yes" : "no";
                        $repo_notification_setting = array('repo_notification_setting' => $notif_setting);
                    }
                    $repository_data = array_merge($repository_data,$repository_path_url);
                    $repository_data = array_merge($repository_data,$repo_notification_setting);
                   
                    $this->active_repository = new GitRepository();
                    $this->active_repository->setAttributes($repository_data);
                    $this->active_repository->setCreatedBy($this->logged_user);
                    
                    
                    $this->active_repository->save();
                    $repo_fk = $this->active_repository->getId();
                    if($repo_fk)
                    {
                        $clone_url = $settings['git_clone_url'].$repo_name;
                        $body = $clone_url;
                        
                        $this->project_object_repository->setName($this->active_repository->getName());
                        $this->project_object_repository->setBody($body);
                        $this->project_object_repository->setParentId($this->active_repository->getId());
                        $this->project_object_repository->setVisibility($repository_data['visibility']);
                        $this->project_object_repository->setProjectId($this->active_project->getId());
                        $this->project_object_repository->setCreatedBy($this->logged_user);
                        $this->project_object_repository->setState(STATE_VISIBLE);
                        
                        $this->project_object_repository->save();
                        
                        $repo_id = ProjectGitolite::add_repo_details($repo_fk,$project_id,$user_id,$repo_path,$repository_data,$clone_url);
                        if($repo_id)
                        {
                            $add_access = ProjectGitolite::add_access_levels($repo_id,serialize($post_data['access']),$user_id,1);
                            if($add_access)
                            {
                                $res = ProjectGitolite::render_conf_file();
                               
                                $dir = $settings['gitoliteadminpath']."gitolite-admin";
                                
                                $command = "cd ".$dir." && git add * && git commit -am 'render conf file' && git push  || echo 'Not found'";
                                exec($command,$output,$return_var);
                                
                                if($is_remote)
                                {
                                   $git_server = $settings['git_clone_url'];
                                   //$command = "cd ".$settings['gitoliteadminpath']." && git clone ".$git_server.":".$repo_name;
                                   chdir(GIT_FILES_PATH);
                                   $command = "git clone ".$git_server.$repo_name;
                                   exec($command,$output,$return_var);
                                    /*@set_time_limit(0);
                                    $pull_all_branches = ProjectGitolite::pull_branches($repo_path);
                                    if(!$pull_all_branches)
                                    {
                                        @ProjectGitolite::remove_directory($work_git_path);
                                        $errors->addError('Error while saving branches.');
                                        throw $errors;
                                    }*/
                                }
                               
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
    	            $this->response->respondWithData($this->active_repository);
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
      *  Add new gitolite repository
      * @throws ValidationErrors
      */
     function add_remote_git_repo()
     {
        $project  = $this->active_project;
        $project_id = $project->getId();
        $logged_user = $this->logged_user;
        $user_id = $logged_user->getId();
        $web_user = GitoliteAdmin::get_web_user();
        $webuser_pub_key = GitoliteAdmin::get_web_user_key();
        /*echo $webuser_pub_key;
        print_r($webuser_pub_key);
        //die();*/
        $this->response->assign(
                    array(
                        'form_action' => Router::assemble('add_remote_git', array('project_slug' => $project->getSlug())),
                        'web_user' => $web_user,
                        'webuser_pub_key' => $webuser_pub_key
                        )
                   );
        if($this->request->isSubmitted()) // check for form submission
        {    
                try {
                   $repository_data = $this->request->post('repository');
                   $repo_name = trim($repository_data["name"]);
                   $repo_url = trim($this->request->post("remoteurl"));
                   $errors = new ValidationErrors();    
                   $post_data =  $this->request->post();
                   
                   if($repo_name == "") {
                        $errors->addError('Please enter repository name', 'repo_name');
                    }
                    
                    if($repo_url == "") {
                        $errors->addError('Please enter repository URL', 'repo_name');
                    }

                   $dup_cnt = ProjectGitolite::check_remote_duplication($project_id,$repository_data,$repo_url);
                   /*print_r($dup_cnt);
                   die();*/
                  
                   if(!$errors->hasErrors())
                   {
                      if(!preg_match("/^[A-Za-z0-9-]+$/", $repo_name))
                      {
                            $errors->addError('Please enter valid repository name.', 'repo_name');
                      } 
                      /*if(preg_match('|^[a-z]?:@[a-z0-9]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url))
                      {
                        return ;
                      }*/
                      
                      if(strstr($repo_url,  "http://") || strstr($repo_url,  "https://"))
                      {
                            $errors->addError('HTTP url not allowed to add remote repository', 'repo_url');
                      }
                      
                      /*if(!strstr($repo_url,  "git://github.com/"))
                      {
                            $errors->addError('Please enter valid Git URL', 'repo_url');
                      }*/
                      if(count($dup_cnt) == 0)
                      {
                         $errors->addError('Problem occured while saving data, please try again.');
                      }
                      elseif(is_array($dup_cnt) && count($dup_cnt) > 0)
                      {
                        
                        if($dup_cnt[0]['dup_name_cnt'] > 0)
                         {
                             $errors->addError('Repository with same name is already added');
                             
                         }
                         if($dup_cnt[1]['dup_name_cnt'] > 0)
                         {
                             $errors->addError('Remote URL already cloned under this project.');
                         }
                       
                      }
                   }
                   if($errors->hasErrors()) {
                     throw $errors;
                    }
                    
                    try {
                        DB::beginWork('Creating a new remote repository @ ' . __CLASS__);
                        
                        $actual_git_repo_name = ProjectGitolite::get_actual_repo_name($repo_url);
                        if(!$actual_git_repo_name)
                        {
                            $errors->addError('Invalid Git Repository.');
                            throw $errors;
                        }
                        
                        // clone remote repo
                        
                        
                        /*echo $actual_git_repo_name;
                        die();*/
                        
                        // path with folder name which is created as same as repo name to avoid same git repo collision
                        
                        $work_git_path = GIT_FILES_PATH."/";
                        
                        // path with folder name which is created after repo is cloned 
                        
                        //
//                        /echo $actual_git_repo_name;
                        $git_ext = strpos($actual_git_repo_name,".git");
                        
                        
                        if($git_ext)
                        {
                            $actual_git_repo_name = substr($actual_git_repo_name, 0,-4);
                        }
                        
                        $folder_append = "";
                        $chk_actual_name_exists_cnt = ProjectGitolite::check_actual_name_count($actual_git_repo_name);
                        if(is_array($chk_actual_name_exists_cnt) && isset($chk_actual_name_exists_cnt["actual_name_cnt"]))
                        {
                           
                            $cnt = ($chk_actual_name_exists_cnt["actual_name_cnt"] > 0) ? $chk_actual_name_exists_cnt["actual_name_cnt"]+1 : "";
                            $folder_append = ($cnt != "") ? "-$cnt" : "";
                        }
                        else
                        {
                            $folder_append = "-1";
                        }

                        // if git repsitory name is same , we need to change the folder name while cloning the repository
                        $folder_name =  $actual_git_repo_name.$folder_append;
                        $actual_repo_path = GIT_FILES_PATH."/".$folder_name."/";
                        //echo $actual_git_repo_name;
                        
                        
                        
                        $return_status =  GitoliteAdmin::clone_remote_repo($repo_url,$work_git_path,$folder_name);
                        if(!$return_status)
                        {
                            $errors->addError('Problem occured while cloning repository.');
                            throw $errors;
                        }
                        
                        $repository_path_url = array('repository_path_url' => $actual_repo_path);
                       
                        
                        //echo $work_git_path;
                        
                        $repository_data = array_merge($repository_data,$repository_path_url);
                        
                        /*print_r($repository_data);
                        die();*/
                        $this->active_repository = new GitRepository();
                        $this->active_repository->setAttributes($repository_data);
                        $this->active_repository->setCreatedBy($this->logged_user);

                        $this->active_repository->save();
                        $repo_fk = $this->active_repository->getId();
                        
                        if($repo_fk)
                        {
                            $clone_url = $repo_url;
                            $body = $clone_url;

                            $this->project_object_repository->setName($this->active_repository->getName());
                            $this->project_object_repository->setBody($body);
                            $this->project_object_repository->setParentId($this->active_repository->getId());
                            $this->project_object_repository->setVisibility($repository_data['visibility']);
                            $this->project_object_repository->setProjectId($this->active_project->getId());
                            $this->project_object_repository->setCreatedBy($this->logged_user);
                            $this->project_object_repository->setState(STATE_VISIBLE);
                            $this->project_object_repository->save();
                            
                            $repo_id = ProjectGitolite::add_remote_repo_details($repo_fk,$user_id,$actual_repo_path,$repo_name,$repo_url,$actual_git_repo_name);
                            if($repo_id)
                            {
                                ini_set('max_execution_time', 500);
                                $pull_all_branches = ProjectGitolite::pull_branches($actual_repo_path);
                                if(!$pull_all_branches)
                                {
                                    @ProjectGitolite::remove_directory($work_git_path);
                                    $errors->addError('Error while saving branches.');
                                    throw $errors;
                                }
                                //$out = $this->update_remote_repo($repo_fk);
                                /*$branches = $get_branches = exec("cd $actual_repo_path && git branch -a",$output);
                                if(is_foreachable($output))
                                {
                                    $array_unique_banch = array();
                                    foreach ($output as $key => $value) 
                                    {
                                        $branch_name = substr(strrchr($value, "/"), 1);

                                        if(!in_array($branch_name, $array_unique_banch))
                                        {
                                            exec("cd $actual_repo_path && git checkout -b $branch_name origin/$branch_name && git pull");
                                        }
                                        $array_unique_banch[] = $branch_name;
                                    }
                                }*/
                                DB::commit('Repository created @ ' . __CLASS__);
                                $out = GitoliteAdmin::update_remote_repo($repo_fk);
                                
                                $this->response->respondWithData($this->project_object_repository);
                               
                            }
                            else
                            {     
                                 @ProjectGitolite::remove_directory($actual_repo_path);
                                 $errors->addError('Error while saving repository');
                                 throw $errors;
                            }
                        }
                        else
                        {
                           @ProjectGitolite::remove_directory($actual_repo_path);
                            $errors->addError('Error while saving repository.');
                            throw $errors;
                        }
                        
                 }
                catch (Exception $e)
                {  
                    DB::rollback('Failed to create a repository @ ' . __CLASS__);
                    $this->response->exception($e);
                }
                
                   //$this->response->respondWithData($this->project_object_repository);
                   //repo path
                   //$repo_path = $sever_user_path."/repositories/".$repository_data['name'].".git";
                }
                catch (Exception $e)
                {
                    DB::rollback('Failed to create a repository @ ' . __CLASS__);
                    $this->response->exception($e);
                }
        }
     }
     
     
     
     
     /**
      * Edit gitolite repository access levels
      * @throws ValidationErrors
      */
     function edit_git_repo(){
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
               
              /*print_r($repo_details);
              die();    */
              
               
               $repository_data = $this->request->post('repository');
               
               if (!is_array($repository_data)) {
                  $repository_data = array(
                          'updatetype'      => $this->active_repository->getFieldValue("update_type"),
                          'visibility'      => $this->project_object_repository->getVisibility()
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
                     
                      $view_url = $this->logged_user->getViewUrl();
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
                                  'repository_data' => $repository_data,
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
                        $notif_setting = (isset($repository_data["disable_notifications"])) ? "yes" : "no";    
                        $repo_table_name = TABLE_PREFIX."rt_gitolite_repomaster";
                        DB::execute("update $repo_table_name set disable_notifications = '".$notif_setting."' where repo_fk = '".$repo_fk."'");
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
                    $this->response->ok();
                    
                 }catch (Exception $e)
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
      * Check repository access and add repository help on history page
      * @return void
      */
     function history() {
          
         //ProjectGitolite::delete_commits($this->active_repository->getId());
         /**$this->wireframe->actions->add('branches', lang('Branches'), '#', array(
        	'subitems' => self::ac_gitolite_get_branches(),
                'icon' => AngieApplication::getPreferedInterface() == AngieApplication::INTERFACE_DEFAULT ? AngieApplication::getImageUrl('icons/16X16-git.png', AC_GITOLITE_MODULE) : AngieApplication::getImageUrl('icons/16X16-git.png', AC_GITOLITE_MODULE, AngieApplication::INTERFACE_PHONE),
                'id'=> 'branches_list'
        ));
        
        $this->wireframe->actions->add('tags', lang('Tags'), '#', array(
        	'subitems' => self::ac_gitolite_get_tags(),
                'icon' => AngieApplication::getPreferedInterface() == AngieApplication::INTERFACE_DEFAULT ? AngieApplication::getImageUrl('icons/16X16-git.png', AC_GITOLITE_MODULE) : AngieApplication::getImageUrl('icons/16X16-git.png', AC_GITOLITE_MODULE, AngieApplication::INTERFACE_PHONE),
                'id'=> 'tags_list'
        ));
         */
         
          $repo_id = array_var($_GET, 'project_source_repository_id'); //project objects id
          $project = $this->active_project;
          $repository = $this->active_repository;
         
           $repo_details = ProjectGitolite::get_repo_details($repo_id);
           
           if(is_array($repo_details) && count($repo_details) > 0)
           {
               
              if ($this->logged_user->isAdministrator() || $this->logged_user->isProjectManager() || $project->isLeader($this->logged_user)) {

                  $this->wireframe->actions->add('manage_access', lang('Manage Access'),
                                             Router::assemble('edit_git_repository', 
                                             array('project_slug' => $project->getSlug(),
                                             'project_source_repository_id' => $repo_id))
                                             , array(
                                                 'id'=> 'update_access_levels',
                                                 'onclick'=> new FlyoutFormCallback("access_updated",array('width' => 'narrow')),
                                                 'icon' => AngieApplication::getPreferedInterface() == AngieApplication::INTERFACE_DEFAULT ? AngieApplication::getImageUrl('icons/16X16-git.png', AC_GITOLITE_MODULE) : AngieApplication::getImageUrl('icons/16X16-git.png', AC_GITOLITE_MODULE, AngieApplication::INTERFACE_PHONE),
                     ));
                } //if
                          
                
            }
      /*  Commented FTP section
         $this->wireframe->actions->add('add_ftp', 'FTP Connectiions', Router::assemble('add_ftp_conn',array('project_slug' => $this->active_project->getSlug(),
                                             'project_source_repository_id' => $repo_id)), array(
                 'onclick' => new FlyoutFormCallback('ftps_updated', array('width' => '1100')),
                 'icon' => AngieApplication::getPreferedInterface() == AngieApplication::INTERFACE_DEFAULT ? AngieApplication::getImageUrl('icons/16X16-git.png', AC_GITOLITE_MODULE) : AngieApplication::getImageUrl('icons/16X16-git.png', AC_GITOLITE_MODULE, AngieApplication::INTERFACE_PHONE))
                );
        */ 
         $this->wireframe->actions->add('add_hooks', 'Hooks', Router::assemble('add_hooks_git',array('project_slug' => $this->active_project->getSlug(),
                                             'project_source_repository_id' => $repo_id)), array(
                 'onclick' => new FlyoutFormCallback('urls_updated', array('width' => '900')),
                 'icon' => AngieApplication::getPreferedInterface() == AngieApplication::INTERFACE_DEFAULT ? AngieApplication::getImageUrl('icons/16X16-git.png', AC_GITOLITE_MODULE) : AngieApplication::getImageUrl('icons/16X16-git.png', AC_GITOLITE_MODULE, AngieApplication::INTERFACE_PHONE))
                );
         
          $repo_path = $repository->getRepositoryPathUrl();
         
          $repo_fk = $repository->getId();
          $chk_gitolite = ProjectGitolite::is_gitolite_repo($repo_fk);
          
          if(is_array($chk_gitolite) && sizeof($chk_gitolite) > 0 && $chk_gitolite['chk_gitolite'] > 0)
          {
                $settings = GitoliteAdmin :: get_admin_settings();
                $clone_url = $settings['git_clone_url'].$this->active_repository->getName().".git";
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
    
    /**
     * Removes directory created to clone repository, if cloning repository or adding repository gets falied
     * @param string $dir
     * @return boolean
     */
    /*function remove_directory($dir)
    {
        if (is_dir($dir)) {
         $objects = scandir($dir);
         foreach ($objects as $object) {
           if ($object != "." && $object != "..") {
             if (filetype($dir."/".$object) == "dir") self::remove_directory($dir."/".$object); else unlink($dir."/".$object);
           }
         }
         reset($objects);
         rmdir($dir);
         
       }
       return true;
    }*/
  /**
   * Get options for file
   * 
   * @param Repository $repository
   * @param SourceCommit $commit
   * @param string $file
   * @return array
   */
  function ac_gitolite_get_branches() {
    // prepare options for dropdown
     $repo_path = $this->active_repository->getRepositoryPathUrl();
     $branches = ProjectGitolite::get_branches($repo_path);

    $file_options = new NamedList();
    if(is_array($branches))
    {
        foreach ($branches as $key => $value) 
        {
            $file_options->add("branch_$key", array(
            'text' => $value,
            'url' => "#",
            //'onclick' => new FlyoutFormCallback()
            ));
        } 

    }
    else {
        $file_options->add("no_branches", array(
            'text' => "No Branches Added",
            'url' => "#"
            ));
    }

    /*$file_options->add('download', array(
            'text' => lang('Download'),
            'url' => $repository->getFileDownloadUrl($commit, $file)
    ));*/
    return $file_options->toArray();
  } // source_module_get_file_options
  
 function ac_gitolite_get_tags()
 {
    // prepare options for dropdown
     $repo_path = $this->active_repository->getRepositoryPathUrl();
     $branches = ProjectGitolite::get_tags($repo_path);

    $file_options = new NamedList();
    if(is_array($branches))
    {
        foreach ($branches as $key => $value) 
        {
            $file_options->add("tags_$key", array(
            'text' => $value,
            'url' => "#",
            //'onclick' => new FlyoutFormCallback()
            ));
        } 

    }
    else {
        $file_options->add("no_branches", array(
            'text' => "No Tags Added",
            'url' => "#"
            ));
    }

    /*$file_options->add('download', array(
            'text' => lang('Download'),
            'url' => $repository->getFileDownloadUrl($commit, $file)
    ));*/
    return $file_options->toArray();
  }
  
  /**
   * Override add_existing function of parent 
   */
  function add_existing() {
      if(!ProjectSourceRepositories::canAdd($this->logged_user, $this->active_project)) {
            $this->response->forbidden();
      } // if
      $repository_data = $this->request->post('repository');
      if(is_array($repository_data))
      {
          GitoliteAdmin::update_remote_repo($temp_source_repository_id);
          
      }
      parent::add_existing();
      
  }
  
  
  function update() 
  {
        //$repo = $this->active_repository;
        //print_r($repo->getRepositoryPathUrl());
        //$pull_commits = GitoliteAdmin::pull_repo_commits($repo->getRepositoryPathUrl());
        parent::update();
  }
  
  /**
   * Add hooks for a repositories.
   * @throws ValidationErrors
   */
  function add_git_hooks()
  {
        //echo Router::assemble("hookcall");
        $project  = $this->active_project;
        $project_id = $project->getId();
        $logged_user = $this->logged_user;
        $user_id = $logged_user->getId();
        $repo_id = array_var($_GET, 'project_source_repository_id'); //project objects id
        
        
        $repo_obj = new ProjectSourceRepository($repo_id);
        $src_repo_id = $repo_obj->getIntegerField1();
        $urls_exists = ProjectGitolite::urls_exists($src_repo_id);
        
        if(is_array($urls_exists) && count($urls_exists) > 0)
        {
            $url_array = @unserialize($urls_exists["webhook_urls"]);
           
        }
        else
        {
            $url_array = array();
        }
        //$src_repo->find
        $this->response->assign(
                    array(
                        'form_action' =>  Router::assemble('add_hooks_git',array('project_slug' => $this->active_project->getSlug(),
                                             'project_source_repository_id' => $repo_id)),
                        'url_array' => $url_array,
                        'web_user' => $web_user,
                        'test_url' =>  Router::assemble('test_hooks_url',array('project_slug' => $this->active_project->getSlug(),
                                             'project_source_repository_id' => $repo_id)),
                        'webuser_pub_key' => $webuser_pub_key
                        )
                   );
        if($this->request->isSubmitted()) // check for form submission
        {    
            $post_data =  $this->request->post();    
            
            //print_r($post_data);
            try {
                   
                  
                   $errors = new ValidationErrors();    
                   $webhooks_url = $post_data["webhooks"];
                   $array_urls = array();
                   foreach ($webhooks_url as $key => $value) 
                   {
                        if(!filter_var($value, FILTER_VALIDATE_URL) && $value != "")
                        {
                            $errors->addError("$value is not a valid URL.");
                        }
                        else
                        {
                            $array_urls[] = $value;
                        }
                    }
                    if($errors->hasErrors()) 
                    {
                       throw $errors;
                    }
                   
                     DB::beginWork('Add URL @ ' . __CLASS__);
                     
                     if(is_array($array_urls) && count($array_urls) > 0)
                     {
                        $urls_exists = ProjectGitolite::urls_exists($src_repo_id);
                        $array_urls = array_filter($array_urls);
                        $array_urls_str = serialize($array_urls);
                        if(!is_array($urls_exists) || count($urls_exists) == 0)
                        {
                            
                             $web_hooks_add = ProjectGitolite :: insert_urls($array_urls_str,$src_repo_id,$this->logged_user->getId());
                             if(!$web_hooks_add)
                             {
                                  $errors->addError('Problem occured while saving data, please try again.');
                                  throw $errors;
                             }
                         }
                         else
                         {   
                              $web_hooks_update = ProjectGitolite :: update_web_hooks($array_urls_str,$src_repo_id,$this->logged_user->getId());
                             
                         }
                         DB::commit('URL Added @ ' . __CLASS__);
                         $this->response->ok();
                     }
                     else
                     {
                        $errors->addError("Error while saving URL's.");
                        throw $errors;
                     }
                 
                     
                }
                catch (Exception $e)
                {
                    
                     DB::rollback('Failed to add url @ ' . __CLASS__);
                    $this->response->exception($e);
                }
        }
  }
  
  /**
   * Test hook url using cURL, whether it is a valid URL
   */
  function test_hooks_url()
  {
        $url = $_GET["testing_url"];
        $fields = array(
            'repo_name' => urlencode($this->active_repository->getName())
        );
        
        $repo_path = $this->active_repository->getRepositoryPathUrl();
        $array_pay_load = array();
        $get_last_commit_data = exec("cd $repo_path && git show --name-status  --pretty=format:'%H || %an || %ae || %s || %cd ||'",$output_command,$return_var);
        $array_pay_load["repository"] = array(
                                            "url" => $this->active_repository->getViewUrl(),
                                            "name" => $this->active_repository->getName(),
                                            "owner" => array("email" => $this->active_repository->getCreatedByEmail(),
                                                             "name" => $this->active_repository->getCreatedBy()->getName())
                                             );
       
        
        
        if(is_array($output_command) && count($output_command) > 0)
        {
             $output = explode("||",$output_command[0]);
             $out_cnt = count($output_command);
             //print_r($output);
            
             $array_commits["id"] = trim($output[0]);
                                    $array_commits["author"] = array(
                                                                        "email" => trim($output[2]),
                                                                        "name" => trim($output[1])
                                                                    );
                                    $array_commits["message"] = trim($output[3]);
                                    $array_commits["message_title"] = trim($output[3]);
                                    $array_commits["timestamp"] = trim($output[4]);
            $last_commit_payload = $output[0];
            
            for($i = 1;$i<=$out_cnt;$i++)
            {
               
                if(isset($output_command[$i]))
                {
                    $path  = trim($output_command[$i]);
                    $paths_array =  explode(" ",$path);
                    
                    if($paths_array[0] == "A")
                    {
                        $array_added[] = $paths_array[1];
                    }
                    elseif($paths_array[0] == "M")
                    {
                        $array_modified[] = $paths_array[1];
                    }
                    elseif($paths_array[0] == "D")
                    {
                        $array_deleted[] = $paths_array[1];
                    }
                }
            }
            
             $array_commits["added"] = $array_added;
             $array_commits["removed"] = $array_deleted;
             $array_commits["modified"] = $array_modified;
             
             $array_pay_load["commits"][]= $array_commits;
                                    
        }
        else
        {
            $array_pay_load["commits"][]= array();
        }
        
         $array_pay_load["after"] = $last_commit_payload;
         $array_pay_load["ref"] = "refs/heads/master";
        
         //print_r($array_pay_load);
         
        /*print_r($output);
        die();
        */
        /*if(is_array($output) && count($output) > 0)
        {
            foreach ($output as $key_out => $value_out) {
                print_r(explode(":", $value_out));
            }
        }*/
        
        
         
        //foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        //rtrim($fields_string, '&');
        $fields_string = json_encode($array_pay_load);     
        //open connection
        $ch = curl_init();
        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, count($array_pay_load));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch,  CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
                                                   'Content-Type: application/json',                                                                                
                                                'Content-Length: ' . strlen($fields_string))                                                                       
         );   
        //execute post
        $result = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if($http_status == 200)
        {
            die("ok");
        }
        else
        {
            die("Can't connect to URL, error code : $http_status");
        }
        
        //close connection
        curl_close($ch);
        
  }
  
  function add_ftp_connections()
  {
      /*$cus = new CustomFields();
      //$cus->initForType("Users",2);
      $flds = $cus->getCustomFieldsByType("Users");
      
      //$cus->initForType("Project",1);
      if(is_array($flds) && count($flds) > 0)
      {
          foreach ($flds as $key => $value) {
              $settings["$key"]["label"] = "Comments";
              $settings["$key"]["is_enabled"] = "1";
              $cus->setCustomFieldsByType("Users", $settings);
              
          }
      }
      print_r($flds);
      die();*/
      
      //$settings["label"];
      //$cus->setCustomFieldsByType("Project", $settings);
      
      $repo_branches = $this->active_repository;
      $eng = $repo_branches->getEngine($this->active_project->getId());
      //print_r($eng->getBranches);
      $branches_array = $eng->getBranches();
      $repo_branches_str = implode(",",$branches_array);
      $repo_id = array_var($_GET, 'project_source_repository_id'); //project objects id
      
      $repo_obj = new ProjectSourceRepository($repo_id);
      $src_repo_id = $repo_obj->getIntegerField1();
        
        
        if($this->request->isSubmitted()) // check for form submission
        {   
            $post_data =  $this->request->post();    
            //print_r($post_data);
            try {

                 $errors = new ValidationErrors();    
                 $ftpdetials = $post_data["ftpdetials"];
                 $fld_cnt = count($ftpdetials["ftp_domain"]);
                 //print_r($ftpdetials);
                 //die(); 
                 $array_urls = array();

                 for ($i=0;$i<$fld_cnt;$i++) 
                 {
                     if($ftpdetials["ftp_domain"][$i] == "" || $ftpdetials["ftp_port"][$i] == ""  || $ftpdetials["ftp_username"][$i] == ""  || $ftpdetials["ftp_password"][$i] == ""  || $ftpdetials["branches"][$i] == ""  || $ftpdetials["ftp_dir"][$i] == "" )
                     {
                          $errors->addError("Please fill all connection parameters.");
                     }
                     else
                     {
                         $arra_conn[$i] = array("ftp_domain" => $ftpdetials["ftp_domain"][$i],
                                                "ftp_port" => $ftpdetials["ftp_port"][$i],
                                                "ftp_username" => $ftpdetials["ftp_username"][$i],
                                                "ftp_password" => $ftpdetials["ftp_password"][$i],
                                                "branches" => $ftpdetials["branches"][$i],
                                                "ftp_dir" => $ftpdetials["ftp_dir"][$i]
                                                );
                     }
                      /*if(!filter_var($value, FILTER_VALIDATE_URL) && $value != "")
                      {
                          $errors->addError("$value is not a valid URL.");
                      }
                      else
                      {
                          $array_urls[] = $value;
                      }*/
                  }
                  if($errors->hasErrors()) 
                  {
                     throw $errors;
                  }
                  
                 
                   DB::beginWork('Add FTP Details @ ' . __CLASS__);

                   if(is_array($arra_conn) && count($arra_conn) > 0)
                   {
                      $ftp_details_exists = ProjectGitolite::ftp_connections_exists($src_repo_id); 
                      
                      if(is_array($ftp_details_exists) && $ftp_details_exists["ftp_cnt"] > 0)
                      {
                          $ftp_table_name = TABLE_PREFIX . "rt_ftp_connections";
                          DB::execute("DELETE FROM $ftp_table_name where repo_fk = '".$src_repo_id."'");
                      }
                      for($i=0;$i<$fld_cnt;$i++)
                      {
                            
                          $ftp_details_add = ProjectGitolite :: add_ftp_details($arra_conn[$i],$src_repo_id,$this->logged_user->getId());
                          if(!$ftp_details_add)
                          {
                               $errors->addError('Problem occured while saving data, please try again.');
                               throw $errors;
                          }
                          
                      }
                       DB::commit('FTP details Added @ ' . __CLASS__);
                       $this->response->ok();
                   }
                   else
                   {
                      $errors->addError("Error while saving FTP details.");
                      throw $errors;
                   }
                 }
                 catch (Exception $e)
                 {

                      DB::rollback('Failed to add FTP details @ ' . __CLASS__);
                     $this->response->exception($e);
                 }
        } 
            $ftp_details_exists = ProjectGitolite::get_connection_details($src_repo_id);
            $this->response->assign(array(
                       'repo_branches_str' => $repo_branches_str,
                       'branches_array' => $branches_array,
                       'ftp_test_url' =>  Router::assemble('test_ftp_conn',array('project_slug' => $this->active_project->getSlug(),
                                             'project_source_repository_id' => $repo_id)),
                       'form_action' =>  Router::assemble('add_ftp_conn',array('project_slug' => $this->active_project->getSlug(),
                                             'project_source_repository_id' => $repo_id)),
                       "ftp_details_exists" => $ftp_details_exists
                      
                        ));
  }
  
  function test_ftp_connection()
  {
      $host = $_GET["ftp_domain"];
      $port = $_GET["ftp_port"];
      $user = $_GET["ftp_username"];
      $password = $_GET["ftp_password"];
      $ftp_dir = $_GET["ftp_dir"];
      if($host == "" || $port == "" || $user == "" || $password == "" || $ftp_dir == "")
      {
          die("Please fill all connection prameters.");
      }
      $connect = ftp_connect($host, $port);
      //$connect = exec("ftp kasim.rtcamp.info",$output,$return_var);
      //print_r($output);
      
      if(!$connect)
      {
        die("Could not connect to FTP");
      }
      $result = ftp_login($connect, $user, $password);
      if($result)
      {
          if(ftp_chdir($connect,$ftp_dir))
          {
               die("ok");
          }
          else
          {
               die("Directory path not found on server.");
          }
      }
      else
      {
          die("Could not login to FTP");
      }
     
  }
  
}

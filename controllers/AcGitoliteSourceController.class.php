<?php

// We need admin controller
AngieApplication::useController('source_admin',SOURCE_MODULE);

/**
 * Ac Gitolite Source Controller 
 * @package activeCollab.modules.ac_gitolite
 * @subpackage controllers
 * @author rtCamp Software Solutions Pvt Ltd <admin@rtcamp.com>
 * @author Rahul Bansal <rahul.bansal@rtcamp.com>
 * @author Kasim Badami <kasim.badami@rtcamp.com>
 * @author Mitesh Shah <mitesh.shah@rtcamp.com>
 
 */
class AcGitoliteSourceController extends SourceAdminController{


    protected $active_repository;
    /**
     * Prepare controller
     */
    function __before() {
       parent::__before();
       $repository_id = $this->request->getId('source_repository_id');
       if($repository_id) {
        $this->active_repository = SourceRepositories::findById($repository_id);
        
        if($this->active_repository instanceof SourceRepository && !($this->active_repository instanceof GitRepository)) {
          $this->httpError(HTTP_ERR_CONFLICT);
        } // if
      } // if
      if(!($this->active_repository instanceof GitRepository)) {
        $this->active_repository = new GitRepository();
      } // if
      $this->smarty->assign('active_repository', $this->active_repository);
   }
     
    /**
     * 
     * Add custom buttons on Admin Source page
     * @return void
     */
    function index() {
        parent::index();
        $this->wireframe->actions->add('add_source_gitolite_repository', lang('Add Gitolite Repository'), Router::assemble('add_source_gitolite_repository'), array(
                 'onclick' => new FlyoutFormCallback('repository_created',array('width' => 'narrow')),
                 'icon' => AngieApplication::getPreferedInterface() == AngieApplication::INTERFACE_DEFAULT ? AngieApplication::getImageUrl('icons/16X16-git.png', AC_GITOLITE_MODULE) : AngieApplication::getImageUrl('icons/16X16-git.png', AC_GITOLITE_MODULE, AngieApplication::INTERFACE_PHONE))
             );
          
        $this->wireframe->actions->add('clone_source_git', lang('Clone Remote Repository'), Router::assemble('clone_source_git_repository'), array(
                 'onclick' => new FlyoutFormCallback('repository_created',array('width' => '900')),
                 'icon' => AngieApplication::getPreferedInterface() == AngieApplication::INTERFACE_DEFAULT ? AngieApplication::getImageUrl('icons/16X16-git.png', AC_GITOLITE_MODULE) : AngieApplication::getImageUrl('icons/16X16-git.png', AC_GITOLITE_MODULE, AngieApplication::INTERFACE_PHONE))
             );
         
    }
    
    function add_source_gitolite_repository()
    {
         $is_gitolite  = GitoliteAdmin :: is_gitolite();
         $logged_user = $this->logged_user;
         $user_id = $logged_user->getId();
         $no_key_warning = FALSE;
         $view_url = "";
         
         if(AngieApplication::isModuleLoaded("source") && $this->getControllerName() == 'ac_gitolite_source')
         { 
             
              $do_continue = true;
          }
          
          if($do_continue)
          {
              
               // Add Administrator , Leaders and Project Manager in allowed people list
              
              //$role = new Roles();
              //$admins = $role::findAdministrators();
              
              $usrobj = new Users();
              $users_details = $usrobj->findAdministrators();
              
              
              if(is_foreachable($users_details))
              {    
                  
                  foreach ($users_details as $key => $value) 
                  {
                     // check key exists 
                     $user_keys = GitoliteAc::check_keys_added($value->getId());
                     
                     if($user_keys > 0)
                     {
                        $user_detail_permissions[$value->getId()] = 
                                      array('readaccess' => 0 ,
                                            'writeaccess' =>  0,
                                            'writeaccessplus'=> 1,
                                            'user_keys' => $user_keys);
                         $allowed_users[$value->getId()] = $value->getDisplayName();
                     }
                  } 
              }
              
              
              /*foreach ($admins as $key => $value) {
                  echo $value->getDisplayName();
              }*/
              
              /*while ($row = mysql_fetch_assoc($admins->getResource())) {
                   print_r($row);
                   
              }*/
              
              /*if($this->logged_user->isAdministrator() || $this->logged_user->isProjectManager())
              {
                  $objuser = new User($user_id);
                  $user_keys = GitoliteAc::check_keys_added($user_id);
                  if($user_keys)
                  {
                    $user_detail_permissions[$user_id] = 
                                      array('readaccess' => 0 ,
                                            'writeaccess' =>  0,
                                            'writeaccessplus'=> 1,
                                            'user_keys' => $user_keys);
                    $allowed_users[$user_id] = $logged_user->getName();
                  }
                  else
                  {
                      $no_key_warning = TRUE;
                      $view_url = $this->logged_user->getViewUrl();
                  }
              }
             */
              $this->response->assign(
                            array(
                                  'curr_users' => $allowed_users,
                                  'user_detail_permissions' => $user_detail_permissions,
                                  'form_action' => Router::assemble('add_source_gitolite_repository'),
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
                    
                    /*print_r($post_data['access']);
                    die();*/
                    $settings = GitoliteAdmin :: get_admin_settings();
                    
                    //$is_remote = ($settings["git_server_location"] == "local") ? false : true;
                    $is_remote = (!isset($settings["git_server_location"]) || $settings["git_server_location"] != "remote") ? false : true;
                    if(!$is_remote)
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
                      $dup_cnt = ProjectGitolite::check_source_git_dup($repository_data);
                     
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
                    }
                    $repository_data = array_merge($repository_data,$repository_path_url);
                    $clone_url = $settings['gitoliteuser']."@".$settings['gitoliteserveradd'].":".$repo_name;
                    $this->active_repository = new GitRepository();
                    $this->active_repository->setAttributes($repository_data);
                    $this->active_repository->setCreatedBy($this->logged_user);
                    
                    $this->active_repository->save();
                    $repo_fk = $this->active_repository->getId();
                    if($repo_fk)
                    {
                        $repo_id = ProjectGitolite::add_repo_details($repo_fk,0,$user_id,$repo_path,$repository_data,$clone_url);
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
                                   $git_server = $settings['gitoliteuser']."@".$settings['gitoliteserveradd'];
                                   //$command = "cd ".$settings['gitoliteadminpath']." && git clone ".$git_server.":".$repo_name;
                                   chdir(GIT_FILES_PATH);
                                   $command = "git clone ".$git_server.":".$repo_name;
                                   exec($command,$output,$return_var);
                                   
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
                    $this->response->respondWithData($this->active_repository, array(
                                    'as' => 'repository', 
                     ));
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
     * Clone remote repository independent of any project.
     * @throws ValidationErrors
     */
    function clone_source_git_repository()
    {

        $logged_user = $this->logged_user;
        $user_id = $logged_user->getId();
        $web_user = GitoliteAdmin::get_web_user();
        $webuser_pub_key = GitoliteAdmin::get_web_user_key();
        /*echo $webuser_pub_key;
        print_r($webuser_pub_key);
        //die();*/
        $this->response->assign(
                    array(
                        'form_action' => Router::assemble('clone_source_git_repository'),
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
                        // path with folder name which is created as same as repo name to avoid same git repo collision
                        $work_git_path = GIT_FILES_PATH."/";
                        
                        // path with folder name which is created after repo is cloned 
                        $git_ext = strpos($actual_git_repo_name,".git");
                        if($git_ext)
                        {
                            $actual_git_repo_name = substr($actual_git_repo_name, 0,-4);
                        }
                        
                        $actual_repo_path = GIT_FILES_PATH."/".$repo_name."/".$actual_git_repo_name."/";
                        
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
                        
                        $return_status =  GitoliteAdmin::clone_remote_repo($repo_url,$work_git_path,$folder_name);
                        if(!$return_status)
                        {
                            $errors->addError('Problem occured while cloning repository.');
                            throw $errors;
                        }
                       
                        $repository_path_url = array('repository_path_url' => $actual_repo_path);
                       
                        
                        //echo $work_git_path;
                        
                        $repository_data = array_merge($repository_data,$repository_path_url);
                        
                        
                        $this->active_repository = new GitRepository();
                        $this->active_repository->setAttributes($repository_data);
                        $this->active_repository->setCreatedBy($this->logged_user);

                        $this->active_repository->save();
                        $repo_fk = $this->active_repository->getId();
                        
                        if($repo_fk)
                        {
                            $repo_id = ProjectGitolite::add_remote_repo_details($repo_fk,$user_id,$actual_repo_path,$repo_name,$repo_url,$actual_git_repo_name);
                            if($repo_id)
                            {
                                DB::commit('Repository created @ ' . __CLASS__);
                                //$out = $this->update_remote_repo($repo_fk);
                                $out = GitoliteAdmin::update_remote_repo($repo_fk);
                                
                                $this->response->respondWithData($this->active_repository, array(
                                    'as' => 'repository', 
                                ));
                            }
                            else
                            {     
                                 @ProjectGitolite::remove_directory($work_git_path);
                                 $errors->addError('Error while saving repository.');
                                 throw $errors;
                            }
                        }
                        else
                        {
                            @ProjectGitolite::remove_directory($work_git_path);
                            $errors->addError('Error while saving repository.');
                            throw $errors;
                        }
                        
                 }
                catch (Exception $e)
                {  
                    DB::rollback('Failed to create a repository @ ' . __CLASS__);
                    $this->response->exception($e);
                }
                
                }
                catch (Exception $e)
                {
                    DB::rollback('Failed to create a repository @ ' . __CLASS__);
                    $this->response->exception($e);
                }
        }
     
    }
    
    /**
     * Delete selected repository, if repository is gitolite repository or remote repository delete that too.
     */
    function delete_git() {
       
      if(($this->request->isAsyncCall() || $this->request->isApiCall()) && $this->request->isSubmitted()) {
          
        if($this->active_repository->isLoaded()) {
          if($this->active_repository->canDelete($this->logged_user)) {
            try {
                $repo_table_name = TABLE_PREFIX."rt_gitolite_repomaster";
                /*echo "DELETE from $repo_table_name where repo_fk = '".$this->active_repository->getId()."'";
                die();*/
                
              $this->active_repository->delete();
              $repo_table_name = TABLE_PREFIX."rt_gitolite_repomaster";
              $repo_access_table_name = TABLE_PREFIX."rt_gitolite_access_master";
              $remote_repo_table_name = TABLE_PREFIX."rt_remote_repos";
              $repo_fk = $this->active_repository->getId();
              $chk_gitolite = ProjectGitolite::is_gitolite_repo($repo_fk);
              //print_r($chk_gitolite);
              if(is_array($chk_gitolite) && sizeof($chk_gitolite) > 0 && $chk_gitolite['chk_gitolite'] > 0)
              {
                
                 DB::execute("DELETE repo_acc,repo_tb FROM $repo_table_name repo_tb
                        JOIN $repo_access_table_name repo_acc ON repo_acc.repo_id = repo_tb.repo_id
                        WHERE repo_tb.repo_fk = '".$repo_fk."'");
                 $repo_path=GIT_FILES_PATH."/".$chk_gitolite["repo_name"];
                 @ProjectGitolite::remove_directory($repo_path);  
              }
              else
              {
                  $remote_res = DB::execute("SELECT remote_repo_name,actual_repo_name from $remote_repo_table_name where repo_fk = '".$repo_fk."'");
                  if($remote_res)
                  {
                      $remote_name_arr = $remote_res->getRowAt(0);
                      $remote_name = $remote_name_arr["actual_repo_name"];
                  }
                  DB::execute("DELETE from $remote_repo_table_name where repo_fk = '".$repo_fk."'");
                  
                  $repo_path=GIT_FILES_PATH."/".$remote_name;
                  if($remote_name != "")
                  {
                    @ProjectGitolite::remove_directory($repo_path);
                  }
              }
              
              $this->response->respondWithData($this->active_repository, array(
                'as' => 'repository', 
              ));
            } catch (Exception $e) {
              $this->response->exception($e);
            } // if
          } else {
            $this->response->forbidden();
          } // if
        } else {
          $this->response->notFound();
        } // if
      } else {
        $this->response->badRequest();
      } // if
    } // delete
    		
}

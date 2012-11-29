<?php

// We need admin controller
AngieApplication::useController('source_admin',SOURCE_MODULE);

/**
 * Ac Gitolite Admin Controller 
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
     * Display gitolite admin page
     * @return void
     */
    function index() {
        parent::index();
        
         $this->wireframe->actions->add('add_source_git', 'Clone Remote Repository', Router::assemble('clone_source_git_repository'), array(
                 'onclick' => new FlyoutFormCallback('repository_created',array('width' => '900')),
                 'icon' => AngieApplication::getPreferedInterface() == AngieApplication::INTERFACE_DEFAULT ? AngieApplication::getImageUrl('icons/16X16-git.png', AC_GITOLITE_MODULE) : AngieApplication::getImageUrl('icons/16X16-git.png', AC_GITOLITE_MODULE, AngieApplication::INTERFACE_PHONE))
             );
         
        
        /*$this->wireframe->actions->add('need_help', 'Need Help?', Router::assemble('need_help_path'), array(
                 'onclick' => new FlyoutFormCallback('repository_created'),
                 'icon' => AngieApplication::getPreferedInterface() == AngieApplication::INTERFACE_DEFAULT ? AngieApplication::getImageUrl('icons/16X16-git.png', AC_GITOLITE_MODULE) : AngieApplication::getImageUrl('icons/16X16-git.png', AC_GITOLITE_MODULE, AngieApplication::INTERFACE_PHONE))
             );*/
    }
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
                        $work_git_path = GIT_FILES_PATH."/".$repo_name."/";
                        
                        // path with folder name which is created after repo is cloned 
                        
                        //
//                        /echo $actual_git_repo_name;
                        $git_ext = strpos($actual_git_repo_name,".git");
                        if($git_ext)
                        {
                            $actual_git_repo_name = substr($actual_git_repo_name, 0,-4);
                        }
                        
                        $actual_repo_path = GIT_FILES_PATH."/".$repo_name."/".$actual_git_repo_name."/";
                        
                       
                        if(!is_dir($work_git_path))
                        {
                            if(mkdir ($work_git_path))
                            {
                                $return_status =  GitoliteAdmin::clone_remote_repo($repo_url,$work_git_path);
                                 
                                if(!$return_status)
                                {
                                     $errors->addError('Problem occured while cloning repository.');
                                     throw $errors;
                                }
                            }
                            else
                            {
                                $errors->addError('Cannot clone repository.');
                                throw $errors;
                            }
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
                            $repo_id = ProjectGitolite::add_remote_repo_details($repo_fk,$user_id,$actual_repo_path,$repo_name,$repo_url);
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
     * Delete selected repository
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
                  $remote_res = DB::execute("SELECT remote_repo_name from $remote_repo_table_name where repo_fk = '".$repo_fk."'");
                  if($remote_res)
                  {
                      $remote_name_arr = $remote_res->getRowAt(0);
                      $remote_name = $remote_name_arr["remote_repo_name"];
                  }
                  DB::execute("DELETE from $remote_repo_table_name where repo_fk = '".$repo_fk."'");
                  
                  $repo_path=GIT_FILES_PATH."/".$remote_name;
                  @ProjectGitolite::remove_directory($repo_path);
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

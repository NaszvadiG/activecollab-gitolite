<?php

  // Build on top of frontend controller
  AngieApplication::useController('frontend', ENVIRONMENT_FRAMEWORK_INJECT_INTO);

/**
 * Ac Gitolite Admin Controller 
 * @package activeCollab.modules.ac_gitolite
 * @subpackage controllers
 * @author rtCamp Software Solutions Pvt Ltd <admin@rtcamp.com>
 * @author Rahul Bansal <rahul.bansal@rtcamp.com>
 * @author Kasim Badami <kasim.badami@rtcamp.com>
 * @author Mitesh Shah <mitesh.shah@rtcamp.com>
 
 */
class AcGitoliteHooksController  extends FrontendController {


  
    /**
     * Prepare controller
     */
    function __before() {
        parent::__before();
        
         
    }
   
   function hooks_call()
   {
       $src_obj = new SourceRepositories();
       $res = $src_obj->findBySQL("select * from acx_source_repositories where name = '".$_GET["repo_name"]."'");
       $repo_array = $res->getRowAt(0);
       GitoliteAdmin::update_remote_repo($repo_array->getId(),TRUE);
       $this->renderText('Repo Name: '.$repo_id );
       die();
   }		
}

{title}Gitolite Admin{/title}

<div id="gitolite_admin">
	{form action=Router::assemble('gitolite_admin') class='big_form' name ="git_admin"}
                <input type = "hidden" name = "admins" value = "{$gitoliteadmins}" id = "admins">
		<script type="text/javascript">
                    App.widgets.FlyoutDialog.front().setAutoSize(false);
		</script>
                
		<div class="big_form_wrapper one_form_sidebar">
		  <div class="main_form_column">
                      <h2>Gitolite Server</h2>
                    {wrap field=gitoliteuser}
                           {text_field name="gitoliteadmin[gitoliteuser]" id=gitoliteuser  value = {$gitoliteuser} class='title' label="Gitolite User" required=true}
                           <i>e.g. git</i>
                    {/wrap}

                    {wrap field=gitoliteserveradd}
			    {text_field name="gitoliteadmin[gitoliteserveradd]" value = {$gitoliteserveradd} id=gitoliteserveradd class='title' label="Gitolite Server Address" required=true}
                             <i>e.g.domain.com or 115.110.210.123</i>
                    {/wrap}
                    
                    <h2>Gitolite Admin Path</h2>
                     {wrap field=gitoliteadminpath}
                          {text_field name="gitoliteadminpath" value = {$gitoliteadminpath} id=gitoliteadminpath class='title'  disabled = "disabled"}
                           
                     {/wrap}
                     <h2 style="color: red;">Note : </h2>
                     <div>Before Testing your connection please verify your public key (e.g id_rsa.pub) exists on Gitolite server with the {$webuser}.pub name and be sure that following command is executed on Gitolite server
                         <br><i>gitolite setup -pk {$webuser}.pub</i>
                     </div>
		  </div>
		  <!--<div class="form_sidebar form_second_sidebar">
		    {wrap field=GitoliteAdminUsers}
			{select_users name="gitoliteadmin[gitoliteadmins]"  user=$logged_user label='Select Admin Users'}
                    {/wrap} 
		  </div>-->
		</div>
                  
	  {wrap_buttons}
          {submit id="save_settings"}Save Settings{/submit}
            <input type="hidden" value="{$gitolite_repo_test_connection_url}" id="gitolite_repo_test_connection_url" />
            <input type="hidden" value="{$gitoliteadminpath}" id="gitolite_test_dir" name="gitoliteadmin[gitoliteadminpath]" />
            <button type="button" id="test_gitolite_connection" class="default"><span><span>{lang}Test Connection{/lang}</span></span></button>
            <img id="test_connection_loading_img" src="{image_url name="layout/bits/indicator-loading-normal.gif" module=$smarty.const.ENVIRONMENT_FRAMEWORK}" alt='' />    
	  {/wrap_buttons}
	{/form}
</div>

   {literal}
<script type="text/javascript">

    var admins = $('#admins').val();
    admins_arr = admins.split(",");
    if(admins_arr.length > 0)
    {   
        var theForm = document.git_admin;
        for (i=0; i<theForm.elements.length; i++) 
        {
            if (theForm.elements[i].name=="gitoliteadmin[gitoliteadmins][]" && admins_arr.indexOf(theForm.elements[i].value) != -1)
            {
               theForm.elements[i].checked = true;
            }
        }
    }
        
   

    $(document).ready (function () {
	$('#test_connection_loading_img').hide();
	//$('.submit_repository').hide();
            $('#save_settings').hide();
            
	
	$('#test_gitolite_connection').click(function (event) {
		var test_connection_url = $('#gitolite_repo_test_connection_url').val();
                var gitoliteuser = $('#gitoliteuser').val();
                var serveraddress = $('#gitoliteserveradd').val();
		//var repository_url = $('#repositoryUrl').val();
                //var repository_url = "/opt/lampp/htdocs/gitsource3/ac3-tweaks";
                var  admin_dir = $('#gitolite_test_dir').val();
		$('#test_connection_loading_img').show();

		//$.get(test_connection_url,{url: repository_url, engine: "GitRepository", async : true,dir:admin_dir},
                 $.get(test_connection_url,{user: gitoliteuser, engine: "GitRepository", async : true,dir:admin_dir,server:serveraddress},
		function(data){
                        
			$('#test_connection_loading_img').hide();
			if (jQuery.trim(data) == 'ok') {
				$("#test_gitolite_connection").hide();
				$('#save_settings').show();
				App.Wireframe.Flash.success(App.lang("Connection Established"));
			} else {
			 	App.Wireframe.Flash.error(App.lang(data));
			}
		});
	});
  
    //if some field is changed we need to put form in edit mode
	$(".main_form_column").find('input, select, textarea').bind('change keypress', function () {
      if (!$("#test_connection").is(':visible')) {
        $("#test_gitolite_connection").show();
        $('#save_settings').hide();
      };
      
    });
});
</script>
{/literal} 
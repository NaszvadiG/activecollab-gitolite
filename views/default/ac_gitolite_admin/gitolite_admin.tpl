{title}Gitolite Admin{/title}

<div id="gitolite_admin">
  {form action=Router::assemble('gitolite_admin')}
  <input type="hidden" value= "{$setup_script}" id="script_path_default">
  <input type = "hidden" value = "{$gitoliteuser}" id="old_value"> 
    <div class="content_stack_wrapper">
      
        
         <div class="content_stack_element">
        <div class="content_stack_element_info">
          <h3>{lang} Gitolite Server Address{/lang}</h3>
        </div>
        
        <div class="content_stack_element_body">
            {wrap field=gitoliteuser}
            <div class="gl_user_ui_half">
            {text_field name="gitoliteadmin[gitoliteuser]" id=gitoliteuser  class="text_field" value = "{$gitoliteuser}"  label="Gitolite User" required=true}
            <p class="aid">e.g. git</p>
            </div>
            <div class="gl_user_ui_orphan">
                <span id="at">@</span>
            
            <div class="gl_user_ui_half">
            {text_field name="gitoliteadmin[gitoliteserveradd]" value = "{$server_name}" id=gitoliteserveradd label = "Gitolite Server Address" required=true}
            <p class="aid" id="aid_server">e.g.{$server_name}</p>
            </div>
            </div>
             {/wrap}
        </div>
        
      </div>
        
        <div class="content_stack_element">
        <div class="content_stack_element_info">
          <h3>{lang}Need Help?{/lang}</h3>
        </div>
        
        <div class="content_stack_element_body">
            {wrap field=title}
            {label for=pageTitle}Gitolite Setup Script{/label}
            <code id="script_path">
              {$setup_script nofilter}
          </code>
          <p class="aid">{lang}Please login to your remote server and run the above command.{/lang}</p>
          {/wrap}
        </div>
        
      </div>

      <!--<div class="content_stack_element">
        <div class="content_stack_element_info">
          <h3>{lang}Clone gitolite-admin{/lang}</h3>
        </div>
        <div class="content_stack_element_body">
          {wrap field=clone_admin}
            Clone gitolite-admin with PHP's user i.e. {$web_user}
          {/wrap}
        </div>
      </div>-->
      
      <div class="content_stack_element">
        <div class="content_stack_element_info">
          <h3>{lang}Gitolite Admin Path{/lang}</h3>
        </div>
        <div class="content_stack_element_body">
            {wrap field=gitoliteadminpath}
                    {text_field name="gitoliteadminpath" value = {$gitoliteadminpath} id=gitoliteadminpath  disabled = "disabled"}
                {/wrap}
                
        </div>
     </div>
      
    </div>
    
   {wrap_buttons}
          {submit id="save_settings"}Save Settings{/submit}
            <input type="hidden" value="{$gitolite_repo_test_connection_url}" id="gitolite_repo_test_connection_url" />
            <input type="hidden" value="{$gitoliteadminpath}" id="gitolite_test_dir" name="gitoliteadmin[gitoliteadminpath]" />
            <button type="button" id="test_gitolite_connection" class="default"><span><span>{lang}Test Connection{/lang}</span></span></button>
            <img id="test_connection_loading_img" src="{image_url name="layout/bits/indicator-loading-normal.gif" module=$smarty.const.ENVIRONMENT_FRAMEWORK}" alt='' />    
            <!--<button type="button" id="button_cancel" class="default"><span><span>{lang}Cancel{/lang}</span></span></button>-->
	  {/wrap_buttons}
  {/form}
</div>
<!--<div id="">
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
		 
		</div>
                  
	  {wrap_buttons}
          {submit id="save_settings"}Save Settings{/submit}
            <input type="hidden" value="{$gitolite_repo_test_connection_url}" id="gitolite_repo_test_connection_url" />
            <input type="hidden" value="{$gitoliteadminpath}" id="gitolite_test_dir" name="gitoliteadmin[gitoliteadminpath]" />
            <button type="button" id="test_gitolite_connection" class="default"><span><span>{lang}Test Connection{/lang}</span></span></button>
            <img id="test_connection_loading_img" src="{image_url name="layout/bits/indicator-loading-normal.gif" module=$smarty.const.ENVIRONMENT_FRAMEWORK}" alt='' />    
	  {/wrap_buttons}
	{/form}
</div> -->

   {literal}
<script type="text/javascript">


    $(document).ready (function () {
         $("#gitoliteadminpath").width(450);
         
         if($('#gitoliteuser').val() != "")
         {
             var git_user = $('#old_value').val();
             var oldhtml = $('#script_path').html();
             var newhtml = oldhtml.replace(/ git/g," "+$('#gitoliteuser').val());
             $('#script_path').html(newhtml)
         }
         $('#gitoliteuser').blur(function() {
               var git_user = $('#gitoliteuser').val();
               var oldhtml = $('#script_path').html();
               var newhtml = oldhtml.replace(' '+$('#old_value').val()," "+$('#gitoliteuser').val());
               $('#script_path').html(newhtml)
               $('#old_value').val($('#gitoliteuser').val())
        });
         /*$("#button_cancel").click(function(event) {
             App.widgets.FlyoutDialog = function () {
                    var do_close_dialog = function (instance) {
                    var instance_data = instance.data("flyout_data");
                   var flyout_id = instance_data.id;
                   $(window).unbind("keydown." + flyout_id);
                   $(window).unbind(".flyout_dialog");
                   if (instance_data && $.isFunction(instance_data.settings.close)) {
                       instance_data.settings.close.apply(instance_data.content.children(":first")[0])
                   }
               App.Wireframe.Events.unbind(".flyout");
               instance.remove()
                   };
          }();
        });*/
            
        //$('#gitoliteadminpath').css('display', 'none');
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
	$(".content_stack_wrapper").find('input, select, textarea').bind('change keypress', function () {
      if (!$("#test_connection").is(':visible')) {
        $("#test_gitolite_connection").show();
        $('#save_settings').hide();
      };
      
    });
});
</script>
{/literal} 
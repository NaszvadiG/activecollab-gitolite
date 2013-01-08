{title}Gitolite Admin{/title}
   
  {form action=Router::assemble('gitolite_admin_change')}
  <input type="hidden" value= "{$setup_script}" id="script_path_default">
  <input type = "hidden" value = "{$gitoliteuser}" id="old_value"> 
  <input type="hidden" value = "{$is_enable}" id = "is_enabled">
  <input type="hidden" value="{$git_server_location}" id="server_location">
  <input type="hidden" value="{$map_users_url}" id="map_users_url">
   <input type="hidden" value="{$is_remote}" id="is_remote">
   <input type="hidden" value="{$gitolite_admin_url}" id="no_map_url">
  
<div id="gitolite_admin">

  <div class="content_stack_wrapper">
      
      
        <!--<div class="content_stack_element">
            <div class="content_stack_element_info">
              <h3>{lang}Gitolite Server Location{/lang}</h3>
            </div>
            <div class="content_stack_element_body">
                {wrap field=help_improve_application}
                {label}Select Server Location{/label}
           <div>
                {radio_field name='gitoliteadmin[git_server_location]' value="local" pre_selected_value=$git_server_location label='Local'}
                {radio_field name='gitoliteadmin[git_server_location]' value= "remote" pre_selected_value=$git_server_location label='Remote'}</div>
             <p class="aid">{lang}Select "Remote" if you wish to or already have Gitlite on remote server, else if you want to setup on local server select "Local".{/lang}</p>
            {/wrap}
                
        </div>
        </div>-->
        <div class="content_stack_element">
            <div class="content_stack_element_info">
              <h3>{lang} Gitolite Server Address{/lang}</h3>
            </div>
        
            <div class="content_stack_element_body">
                {wrap field=gitoliteuser}
                <div class="gl_user_ui_half">
                 {if $is_enable == 0}
                    {text_field name="gitoliteadmin[gitoliteuser]" id=gitoliteuser class="text_field" value = "{$gitoliteuser}"  label="Gitolite User" required=true}
                 {else}
                     {text_field name="gitoliteuser" id=gitoliteuser class="text_field" value = "{$gitoliteuser}"  label="Gitolite User" required=true}
                     <input type="hidden" name="gitoliteadmin[gitoliteuser]" value="{$gitoliteuser}">
                 {/if}
                <p class="aid">e.g. git</p>
                </div>
                <div class="gl_user_ui_orphan">
                    <span id="at">@</span>

                <div class="gl_user_ui_half">
                {if $is_enable == 0}   
                    {text_field name="gitoliteadmin[gitoliteserveradd]" value = "{$server_name}" id=gitoliteserveradd label = "Gitolite Server Address" required=true}
                 {else}  
                     {text_field name="gitoliteserveradd" id=gitoliteserveradd class="text_field" value = "{$server_name}"  label="Gitolite Server Address" required=true}
                     <input type="hidden" name="gitoliteadmin[gitoliteserveradd]" value="{$server_name}">
                 {/if}
                <p class="aid" id="aid_server">e.g.{$server_name}</p>
                </div>
                
                </div>
               
                 {/wrap}
                 <div>
                     <div class="gl_user_ui_orphan"><strong>Is Remote Setup ?</strong></div>
                     <div class="gl_user_ui_orphan">
                      {if $is_enable == 0}   
                          <input type = "checkbox" id="git_server_location" name="gitoliteadmin[git_server_location]" value="remote" {if $git_server_location == "remote"} checked="checked" {/if}>
                      {else} 
                           <input type="hidden" name="gitoliteadmin[git_server_location]" value="{$git_server_location}">
                           <input type = "checkbox" id="git_server_location" name="gitoliteadmin[git_server_location]" value="remote" {if $git_server_location == "remote"} checked="checked" {/if}>
                      {/if}
                     </div>
                 </div> 
           </div>
                 
            
      </div>    
           
      
            
        <div class="content_stack_element" id = "key_instruction_warning">
            <div class="content_stack_element_info">
              <h3>{lang}Setup Instructions{/lang}</h3>
            </div>

            <div class="content_stack_element_body">
            {wrap field=help_improve_application}
            {label}1. Setup <strong>New</strong> Gitolite Server On Local Machine{/label}
             <div class ="">
                {wrap field=title}
                {label for=pageTitle}Gitolite Setup Script{/label}
                {if $web_user != ""}
                    <code id="script_path">
                      {$setup_script nofilter}
                  </code>
                      <p class="aid">{lang}Please login to your local activecollab server using SSH and run the above command.{/lang}</p>
                 {else}
                     <span class="ssh_code">Web user not found.</span>
                 {/if}
                {/wrap}
              </div>
            {/wrap}
            {wrap field=help_improve_application}
            {label}2. Using Existing Gitolite Setup On Remote Server{/label}
             <div class ="">
                 {wrap field=title}
                {label for=pageTitle}Follow below steps{/label}
                <!--<span class="pubkey_warning">Note: Public key of PHP user (<em>{$web_user}</em>), should be added in your remote server account to access repositories.</span>-->
                {if !is_array($webuser_pub_key) &&  ($webuser_pub_key == "nokey" || $webuser_pub_key == "nodir")}

                         <h3><em>To generate a new SSH key, open your terminal login to your remote server with PHP user (<em>{$web_user}</em>) and use code below. </em></h3>
                         <code class="ssh_code">ssh-keygen -t rsa
                        </code><br /><br />
                         <h3><em>Use code below to dump your public key and add it in your remote server</em></h3>
                         <code class="ssh_code">
                            cat ~/.ssh/id_rsa.pub
                         </code>
                {/if}

                {if is_array($webuser_pub_key) &&  $webuser_pub_key|@count > 0}
                    <ul>
                        <li>
                            <h3>Login to your remote Gitolite server.</h3>
                        </li>
                        <li>
                            <h3>Run following command to login with <span class = "chng_usr">{$gitoliteuser}</span> user.</h3>
                            <code class="ssh_code_wrap">sudo su - <span class = "chng_usr">{$gitoliteuser}</code> </span>
                        </li>
                        <li>
                            <!--<h3>Please add following PHP user key ({$web_user}) on your remote server's .gitolite/keydir with name {$web_user}.pub</h3>-->
                            <h3>Run following command to create public key.</h3>
                            <code class="ssh_code_wrap">echo "{$webuser_pub_key[0]}" > {$web_user}.pub</code><br />
                            
                        </li>
                        <li>
                            <h3>Setup above created key and allow above key to access gitolite-admin using following command.</h3>
                            <code class="ssh_code_wrap">bin/gitolite setup -pk {$web_user}.pub</code>
                        </li>
                        
                        <!--<li>
                            <h3>Assign <strong>write</strong> access to this key on gitolite-admin repository by writing following in gitolite.conf file</h3>
                            <code class="ssh_code_wrap">RW+  = {$web_user}</code><br />
                        </li>-->
                    </ul>
                         
                {/if}
                {/wrap}
              </div>
           
                
            {/wrap}
                
        </div>
        </div>    
      
      <div class="content_stack_element last">
        <div class="content_stack_element_info">
          <h3>{lang}Gitolite Admin Path{/lang}</h3>
        </div>
        <div class="content_stack_element_body">
            {wrap field=gitoliteadminpath}
                    {text_field name="gitoliteadminpath_show" value = {$gitoliteadminpath_show} id="gitoliteadminpath_show"  disabled = "disabled"}
                    <input type="hidden" name="gitoliteadminpath" value="{$gitoliteadminpath}" id="gitoliteadminpath">
            {/wrap}
                
        </div>
     </div>
      
    </div>
       {wrap_buttons}
       {if $is_enable == 0 && $is_remote == 1}
          
       {else}
          
       {/if}
       {submit id="save_settings"}Save Settings{/submit} 
       <button type="button" id="save_settings_remote" class="default"><span>{lang}Save Settings{/lang}</span></button>
            <input type="hidden" value="{$gitolite_repo_test_connection_url}" id="gitolite_repo_test_connection_url" />
            
            <input type="hidden" value="{$save_admin_settings_url}" id="save_admin_settings_url" />
            
            <input type="hidden" value="{$gitoliteadminpath}" id="gitolite_test_dir" name="gitoliteadmin[gitoliteadminpath]" />
            <button type="button" id="test_gitolite_connection" class="default"><span>{lang}Test Connection{/lang}</span></button>
            
            <!--<button type="button" id="test_button" class="default"><span>{lang}Test button{/lang}</span></button>-->
            <img id="test_connection_loading_img" src="{image_url name="layout/bits/indicator-loading-normal.gif" module=$smarty.const.ENVIRONMENT_FRAMEWORK}" alt='' />    
       {/wrap_buttons}
  
</div>
<div id = "maptext">
     <div class="content_stack_wrapper">
         <div class="content_stack_element">
            <div class="content_stack_element_info">
              <h3>{lang}Import Users & Repositories From Gitolite{/lang}</h3>
            </div>
            <div class="content_stack_element_body">
                {wrap field=title}
                
                <em>Your current gitolite setup contains some users and repositories, which you need to map with current activeCollab users and projects.<strong>If you skip this step, old gitolite users and repositories may become inaccesible. </strong></em>
              <p class="aid">{lang}Please click "Start Import (Mapping)" button below to proceed. {/lang}</p>
              {/wrap}
            </div>
        </div>
            {wrap_buttons}
            <button type="button" id="import_button" class="default"><span>{lang}Start Import (Mapping){/lang}</span></button>
            {/wrap_buttons}
     </div>


</div>
     
     <div id = "nomaptext">
     <div class="content_stack_wrapper">
         <div class="content_stack_element">
            <div class="content_stack_element_info">
              <h3>{lang}Import Users & Repositories From Gitolite{/lang}</h3>
            </div>
            <div class="content_stack_element_body">
                {wrap field=title}
                
                <em>Your current gitolite setup seems to be clean, you can proceed with adding new repositories.
                    
                </em>
              <!--<p class="aid">{lang}Please click on import button to map data.{/lang}</p>-->
              {/wrap}
            </div>
        </div>
            {wrap_buttons}
                <button type="button" id="import_button_no_map" class="default"><span>{lang}Continue{/lang}</span></button>
            {/wrap_buttons}
     </div>


</div>
     {/form}
   {literal}
<script type="text/javascript">
   
    $(document).ready (function () {
        
        $("#maptext").hide();
        $("#nomaptext").hide();
        
        if($('#is_enabled').val() == 1)
        {
              $("#gitoliteuser").attr("disabled", "disabled");
              $("#gitoliteserveradd").attr("disabled", "disabled");  
              $("#git_server_location").attr("disabled", "disabled");    
                  
        }
        
        $("#gitoliteadminpath_show").width(450);
         
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
              // change in instructions for login
              var oldhtml = $('#old_value').val();
              var newhtml = oldhtml.replace($('#old_value').val()," "+$('#gitoliteuser').val());   
              $('.chng_usr').html(newhtml)    
        });

        $('#test_connection_loading_img').hide();

        $('#save_settings').hide();
        $('#save_settings_remote').hide();   
	
	$('#test_gitolite_connection').click(function (event) {
		var test_connection_url = $('#gitolite_repo_test_connection_url').val();
                var gitoliteuser = $('#gitoliteuser').val();
                   
                var serveraddress = $('#gitoliteserveradd').val();

                var  admin_dir = $('#gitolite_test_dir').val();
		$('#test_connection_loading_img').show();

		
                $.get(test_connection_url,{user: gitoliteuser, engine: "GitRepository", async : true,dir:admin_dir,server:serveraddress},
		function(data){
			$('#test_connection_loading_img').hide();
			if (jQuery.trim(data) == 'ok') {
				$("#test_gitolite_connection").hide();
                                var git_server_location = $('input[name=gitoliteadmin[git_server_location]]:radio:checked').val();
                                if ($('#git_server_location').is(":checked"))
                                {
                                    var git_server_location = "remote";
                                }
                                else
                                {
                                    var git_server_location = "local";
                                }    
                                if(git_server_location == "remote" && $('#is_enabled').val() == 0)
                                {
        				$('#save_settings_remote').show();
                                }
                                else
                                {
                                        $('#save_settings').show();
                                }
                                App.Wireframe.Flash.success(App.lang("Connection Established"));
                                    
                                    
			} else {
			 	App.Wireframe.Flash.error(App.lang(data));
                                
			}
		});
	});
  
        // save gitolite settings and go for merging steps
            // if not saved already
       //if($('#is_enabled').val() == 0 && $('#is_remote').val() == 1)    
       //{
            $('#save_settings_remote').click(function (event) {
                
            var save_admin_settings_url = $('#save_admin_settings_url').val();
            var gitoliteuser = $('#gitoliteuser').val();

            var serveraddress = $('#gitoliteserveradd').val();
            //var repository_url = $('#repositoryUrl').val();
            //var repository_url = "/opt/lampp/htdocs/gitsource3/ac3-tweaks";
            var  admin_dir = $('#gitolite_test_dir').val();
            $('#test_connection_loading_img').show();

            //var git_server_location = $('input[name=gitoliteadmin[git_server_location]]:radio:checked').val();
            if ($('#git_server_location').is(":checked"))
            {
                var git_server_location = "remote";
            }
            else
            {
                var git_server_location = "local";
            }
            /*git_server_location */       
            //$.get(test_connection_url,{url: repository_url, engine: "GitRepository", async : true,dir:admin_dir},
            $.get(save_admin_settings_url,{user: gitoliteuser, engine: "GitRepository", async : true,dir:admin_dir,server:serveraddress,server_location:git_server_location},
            function(data){
                    $('#test_connection_loading_img').hide();
                    if (jQuery.trim(data) == 'map') {
                            $("#save_settings").hide();
                            //$('#next_step_1').show();
                                //App.Wireframe.Content.set("sadasdasdas")
                                     $("#maptext").show();
                                     
                                     $('#import_button').click(function(){
                                            document.location.href=$('#map_users_url').val();
                                     });
                                         
                                     $("#gitolite_admin").hide();
                                App.Wireframe.Flash.success(App.lang("Admin settings saved successfully."));

                    } else if(jQuery.trim(data) == 'nomap') {
                                $("#save_settings").hide();
                                $("#nomaptext").show();
                                    
                                $('#import_button_no_map').click(function(){
                                        document.location.href=$('#no_map_url').val();
                                });
                                     
                                $("#gitolite_admin").hide();
                        
                               //App.Wireframe.Flash.error(App.lang(data));

                    }else {
                               App.Wireframe.Flash.error(App.lang(data));

                    }
            });
        });
    //}
  
  
  
  
    //if some field is changed we need to put form in edit mode
	$(".content_stack_wrapper").find('input, select, textarea').bind('change keypress', function () {
      if (!$("#test_connection").is(':visible')) {
        $("#test_gitolite_connection").show();
        $('#save_settings').hide();
      };
      
    });
    
    $(".content_stack_wrapper").find('radio').bind('click', function () {
      if (!$("#test_connection").is(':visible')) {
        $("#test_gitolite_connection").show();
        $('#save_settings').hide();
      };
      
    });
        

});
</script>
{/literal} 
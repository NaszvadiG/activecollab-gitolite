
{title}Clone Remote Git Repository{/title}
{add_bread_crumb}Clone Remote Git Repository{/add_bread_crumb}
  
<div id="repository_create_remote">
    {form action=$form_action method=post ask_on_leave=yes autofocus=yes}
    <div id="remote_form">
	
	   <div class="fields_wrapper">
               
		{wrap field=name}
		  {label for=repositoryName required=yes}{lang}Name{/lang}{/label}
		  {text_field name='repository[name]' id=repositoryName class='title required' maxlength="150"}
                   <p class="aid">{lang}Only a-z, A-Z, hypens(-), numbers(1,2..) are allowed (eg. wordpress-project12).{/lang}</p>
		{/wrap}
		
		{wrap field=remote_url}
		  {label for=repositoryURL required=yes}{lang}Remote Git Repository URL{/lang}{/label}
		  {text_field name='remoteurl' id=remoteurl class='title required' maxlength="150"}
                   <p class="aid">{lang}e.g git://github.com/rtCamp/my-repo.git{/lang}</p>
		{/wrap}
                
		<div class="clear"></div>
                
                <div id="sourceAuthenticateWrapper">
                    <div class="col">
                    {wrap field=type}
                        {label for=repositoryUpdateType}{lang}Commit History Update Type{/lang}{/label}
                        <select name='repository[update_type]'>
                            <option value="1">Frequently</option>
                            <option value="2">Hourly</option>
                            <option value="3">Daily</option>
                        </select>
                    {/wrap}
                    </div>
                   
                    <div class="col">
                    {if $logged_user->canSeePrivate()}
                        {wrap field=visibility}
                            {label for=repositoryVisibility}Visibility{/label}
                            {select_visibility name='repository[visibility]' value=$repository_data.visibility}
                        {/wrap}
                    {else}
                        <input type="hidden" name="repository[visibility]" value="1"/>
                    {/if}
                    </div>
            </div>
       </div>
                
                
  </div>
    <div id="key_instruction">
        
        
         <div class ="key_help_remote">
             <span class="pubkey_warning">Note: Public key of PHP user (<em>{$web_user}</em>), should be added in your remote server account to access repositories.</span>
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
                      
                      <h3><em>Please copy following key on your remote server in order to access remote repository.</em></h3>
                      <code class="ssh_code_wrap">{$webuser_pub_key[0]}</code><br /><br />
             {/if}
                  </div>
    </div>
    <div class="clear"></div>
    
    {wrap_buttons}
         {submit}Clone Repository{/submit}
          <!--<button type="button" id="add_repo" class="default"><span>{lang}Add Repository{/lang}</span></button>-->
    {/wrap_buttons}
    {/form}
</div>
 

<script type="text/javascript">
  App.widgets.RepositoryForm.init('repository_create_remote');

</script>
{if $is_gitolite == TRUE}
{title}Add a Gitolite Repository{/title}
{add_bread_crumb}Add a Gitolite Repository{/add_bread_crumb}
  
<div id="repository_create_git">
	{form action=$form_action method=post ask_on_leave=yes autofocus=yes}
	   <div class="fields_wrapper">
		
		
		{wrap field=name}
		  {label for=repositoryName required=yes}{lang}Name{/lang}{/label}
		  {text_field name='repository[name]' id=repositoryName class='title required' maxlength="150"}
                   <p class="aid">{lang}Only a-z, A-Z, hypens(-), numbers(1,2..) are allowed (eg. wordpress-project).{/lang}</p>
		{/wrap}
		
		
                
		<div class="clear"></div>
		
		{wrap field=users}
		  {label}{lang}Peoples on project{/lang}{/label}
                   <table>
                   {if $curr_users}
                      
                           <tr>
                               <td>People Name</td>
                               <td>No Access</td>
                               <td>Read Access</td>
                               <td>Write Access</td>
                           </tr>
                        {foreach from=$curr_users item=entry key=name} 
                              <tr>
                                  <td>{$entry}</td>
                                  <td> <input type ="radio" name="access[{$name}]" value={$noaccess} {if !$user_detail_permissions[$name]['readaccess'] || !$user_detail_permissions[$name]['writeaccess'] || !$user_detail_permissions[$name]['writeaccessplus']}checked="checked"{/if}></td>
                                  <td><input type ="radio" name="access[{$name}]" value={$readaccess} {if $user_detail_permissions[$name]['readaccess']}checked="checked"{/if}></td>
                                  <td><input type ="radio" name="access[{$name}]" value={$manageaccess} {if $user_detail_permissions[$name]['writeaccess'] || $user_detail_permissions[$name]['writeaccessplus']}checked="checked"{/if}></td>
                                  
                              </tr>
                        {/foreach}
                        
                        <tr>
                            <td colspan="4" class="note_keys">&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="note_keys">Note:&nbsp;&nbsp;Some users may not be visible here because public keys for that users is not added.</td>
                        </tr>
                 {else} 
                     
                     <tr>
                         <td colspan="4" style="text-align: center;">No users with public keys found.</td>
                       
                     </tr>
                 {/if}
                  </table>
		{/wrap}
                
  </div>
     {wrap_buttons}
          {submit}Add Repository{/submit}
   {/wrap_buttons}
	{/form}
</div>
{else}
    <div id="repository_create_git">
        <div class="fields_wrapper">
            <strong style="text-align: center">Can't find gitolite admin. Please set settings from Gitollite Admin panel 
                    using Administration or contact administrator.</strong>
        </div>
    </div>
{/if}

<script type="text/javascript">
  App.widgets.RepositoryForm.init('repository_create_git');
</script>
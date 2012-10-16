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
		  {label}{lang}People on project{/lang}{/label}
                  <table>
                   {if $curr_users}
                      
                           <tr>
                               <th>People Name</th>
                               <th>No Access</th>
                               <th>Read Access</th>
                               <th>Write Access</th>
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
                 {else} 
                     
                     <tr>
                         <td colspan="4" style="text-align: center;">No users with public keys found.</td>
                       
                     </tr>
                 {/if}
                  </table>
		{/wrap}
                
                {wrap field=type}
                <select name='repository[update_type]'>
                    <option value="1">Frequently</option>
                    <option value="2">Hourly</option>
                    <option value="3">Daily</option>
                </select>
		{/wrap}
			
		{if $logged_user->canSeePrivate()}
		  {wrap field=visibility}
		    {label for=repositoryVisibility}Visibility{/label}
		    {select_visibility name='repository[visibility]' value=$repository_data.visibility}
		  {/wrap}
		{else}
		  <input type="hidden" name="repository[visibility]" value="1"/>
		{/if}
                
                <div class="note_keys"> Note:&nbsp;&nbsp;Some users may not be visible here because public keys for that users is not added.</div>
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
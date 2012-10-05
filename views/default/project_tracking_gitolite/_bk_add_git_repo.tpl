{$add_error}

{title}Add a Gitolite Repository{/title}
{add_bread_crumb}Add a Gitolite Repository{/add_bread_crumb}
  
<div id="repository_created">
	{form action=$form_action method=post  csfr_protect=true}
	   <div class="fields_wrapper">
		
		
		{wrap field=name}
		  {label for=repositoryName required=yes}{lang}Name{/lang}{/label}
		  {text_field name='repository[name]' id=repositoryName class='title required' maxlength="150"}
                   <p class="aid">{lang}Only hypens(-), numbers(1,2..) are allowed (eg. wordpress-project).{/lang}</p>
		{/wrap}
		
		{wrap field=url}
		  {label}{lang}Gitolite Admin Path{/lang}{/label}
		  {text_field name='repository_path_url' value=$curr_git_admin id=repositoryName }
                  <input type="hidden" value="{$curr_git_admin}" name="repository[repository_path_url]">
                  <p class="aid">{lang}As specified by admin.{/lang}</p>
		{/wrap}

                
		<div class="clear"></div>
		
		{wrap field=users}
		  {label}{lang}Peoples on project{/lang}{/label}
                  
                   {if $curr_users}
                       <table>
                           <tr>
                               <td>People Name</td>
                               <td>No Access</td>
                               <td>Read Access</td>
                               <td>Write Access</td>
                           </tr>
                        {foreach from=$curr_users item=entry key=name} 
                              <tr>
                                  <td>{$entry}</td>
                                  <td> <input type ="radio" name="access[{$name}]" value={$noaccess} {if !$user_detail_permissions[$name]['canaccess'] || !$user_detail_permissions[$name]['readaccess'] || !$user_detail_permissions[$name]['writeaccess']}checked="checked"{/if}></td>
                                  <td><input type ="radio" name="access[{$name}]" value={$readaccess} {if $user_detail_permissions[$name]['canaccess'] || $user_detail_permissions[$name]['readaccess'] }checked="checked"{/if}></td>
                                  <td><input type ="radio" name="access[{$name}]" value={$manageaccess} {if $user_detail_permissions[$name]['writeaccess'] }checked="checked"{/if}></td>
                                  
                              </tr>
                        {/foreach}
                        </table>
                  {/if}
		{/wrap}
		
  </div>
     {wrap_buttons}
          {submit}Add Repository{/submit}
   {/wrap_buttons}
	{/form}
</div>


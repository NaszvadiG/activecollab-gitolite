
{if $is_gitolite == TRUE}

{title}Update Gitolite Repository{/title}
{add_bread_crumb}Add a Gitolite Repository{/add_bread_crumb}
  
<div id="repository_created">
	{form action=$form_action method=post  csfr_protect=true}
	   <div class="fields_wrapper">
		{wrap field=name}
		  {label for=repositoryName required=yes }{lang}Name{/lang}{/label}
		  {text_field name= 'name' disabled = true value = {$repo_details['repo_name']} id=repositoryName class='title required' maxlength="150"}
                  <input type = "hidden" name = 'repository[name]' value="{$repo_details['repo_name']}">
                   <p class="aid">{lang}Only hypens(-), numbers(1,2..) are allowed (eg. wordpress-project).{/lang}</p>
		{/wrap}
		
		
                
		<div class="clear"></div>
		
		{wrap field=users}
		  {label}{lang}People On Project{/lang}{/label}
                  {if $no_key_warning == 1}
                      <span class="pubkey_warning">Please <a target="_blank" href="{$view_url}">add your SSH key here</a> to set permission for yourself.</span>
                  {/if}
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
                                  <td> <input type ="radio" name="access[{$name}]" value={$noaccess} {if !$user_detail_permissions[$name]['readaccess'] || !$user_detail_permissions[$name]['writeaccess'] || !$user_detail_permissions[$name]['writeaccessplus']}checked="checked"{/if}></td>
                                  <td><input type ="radio" name="access[{$name}]" value={$readaccess} {if $user_detail_permissions[$name]['readaccess']}checked="checked"{/if}></td>
                                  <td><input type ="radio" name="access[{$name}]" value={$manageaccess} {if $user_detail_permissions[$name]['writeaccess'] || $user_detail_permissions[$name]['writeaccessplus']}checked="checked"{/if}></td>
                              </tr>
                        {/foreach}
                        </table>
                  {/if}
		{/wrap}
		
  </div>
     {wrap_buttons}
          {submit}Update Repository{/submit}
   {/wrap_buttons}
	{/form}
</div>
{else}
    <div id="repository_created">
        <div class="fields_wrapper">
            <strong style="text-align: center">Can't find gitolite admin. Please set settings from Gitollite Admin panel 
                    using Administration or contact administrator</strong>
        </div>
    </div>
{/if}


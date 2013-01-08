
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
                   <p class="aid">{lang}Only hypens(-), numbers(1,2..) are allowed (eg. wordpress-project12).{/lang}</p>
		{/wrap}
		
		
                
		<div class="clear"></div>
		
		{wrap field=users}
		  {label}{lang}People In Project{/lang}{/label}
                  {if $no_key_warning == 1}
                      <span class="pubkey_warning">Please <a target="_blank" href="{$view_url}">add your SSH key here</a> to set permission for yourself.</span>
                  {/if}
                   {if $curr_users}
                       <table>
                            <tr>
                               <th>Name</th>
                               <th>No Access</th>
                               <th>Read Only</th>
                               <th>Read/Write</th>
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
                            <td colspan="4">&nbsp;</td>
                        </tr><tr>
                            <td colspan="4"><em> Note:&nbsp;&nbsp;Some users may not be visible here because public keys are not added.</em></td>
                        </tr>
                  {else} 
                     <tr>
                         <td colspan="4" style="text-align: center;">No users with public keys found.</td>
                       
                     </tr>
                 {/if}      
                    </table>
                 
		{/wrap}
                
                <div id="sourceAuthenticateWrapper">
                    <div class="col">
                    {wrap field=type}
                        {label for=repositoryUpdateType}{lang}Commit History Update Type{/lang}{/label}
                        <select name='repository[update_type]'>
                            <option value="1" {if $repository_data['updatetype'] == "1"} selected {/if}>Frequently</option>
                            <option value="2"  {if $repository_data['updatetype'] == "2"} selected {/if}>Hourly</option>
                            <option value="3"  {if $repository_data['updatetype'] == "3"} selected {/if}>Daily</option>
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
            {wrap field=name}
              {label for=repositoryNotification required=false}{lang}Disable Notifications{/lang}{/label}
              <input type="checkbox" name="repository[disable_notifications]" value="yes" {if $repo_details['disable_notifications'] == "yes"} checked="checked" {/if}>
                    <p class="aid">{lang}Disable notifications every time commits are made on this repository.{/lang}</p>
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


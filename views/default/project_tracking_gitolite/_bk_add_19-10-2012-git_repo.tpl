{if $is_gitolite == TRUE}
{title}Add a Gitolite Repository{/title}
{add_bread_crumb}Add a Gitolite Repository{/add_bread_crumb}
  
<div id="repository_create_git">
	{form action=$form_action method=post ask_on_leave=yes autofocus=yes}
	   <div class="fields_wrapper">
		
		
		{wrap field=name}
		  {label for=repositoryName required=yes}{lang}Name{/lang}{/label}
		  {text_field name='repository[name]' id=repositoryName class='title required' maxlength="150"}
                   <p class="aid">{lang}Only a-z, A-Z, hypens(-), numbers(1,2..) are allowed (eg. wordpress-project12).{/lang}</p>
		{/wrap}
		
		
                
		<div class="clear"></div>
		
		{wrap field=users}
		  {label}{lang}People In Project{/lang}{/label}
                  
                  {if $no_key_warning == 1}
                      <span class="pubkey_warning">Please <a target="_blank" href="{$view_url}">add your SSH key here</a> to set permission for yourself.</span>
                  {/if}
                  <table>
                   {if $curr_users}
                      
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
  
  
  
  
  
  
  
  
  
  jQuery(document).ready(function(){
    var str = $("form").serialize();
    var step = 0;
    jQuery(".button").click(function() {
        var button = this;
        jQuery.ajax({
            url:  "{$form_action}",
            type: 'POST', 
            data: {
                params: str,
                action: "addrepo"
            },
            success: function(result) {
                if (jQuery.trim(data) == 'ok') {
                    step++
                    alert(str)
               }
            }
        });
    });
});
  
  
  
  
  
  
  
  
  
  
  
  
  
  $.ajax({
    url: "page1.php",
    cache: false,
    success: function(html) {
        // Make another call here
        $.ajax({
            url: "page1.php",
            cache: false,
            success: function(html) {
                // and so on
            }
        });
    }
});
</script>
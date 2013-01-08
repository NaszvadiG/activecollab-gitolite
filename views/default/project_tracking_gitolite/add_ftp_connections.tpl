{title}FTP Connections{/title}
{form action={$form_action} method=post id = "ftp_connections"}
<input type="hidden" value="{$ftp_test_url}" id="ftp_test_url">
<input type="hidden" value="{$repo_branches_str}" id="repo_branches_str">

<div class="content_stack_wrapper">
     <div class="content_stack_element last">
        <div class="content_stack_element_info">
          <h3>{lang}FTP Details{/lang}</h3>
        </div>
        <div id="ftp_div_wrapper" class="content_stack_element_body">
          {wrap field=web_hooks}
            <table class="form form_field validate_callback validate_days_off" id="tb_ftp_connection" style="{if !is_foreachable($ftp_details_exists)}display: none{/if}">
                <tr>
                    <th>Host</th>
                    <th>Port</th>
                    <th>Username</th>
                    <th>Password</th>
                    <th>Branch</th>
                    <th>Action</th>
                    <th></th>
                </tr>
                {if is_foreachable($ftp_details_exists)}
                    {assign var="k" value=900}
                    {foreach from=$ftp_details_exists item=array_details key=name} 
                       
                        <tr class="day_off_row">
                            <td>{text_field name="ftpdetials[ftp_domain][]" value=$array_details["ftp_host_name"] id = "ftp_domain-$k" class = "ftp_domain"}</td>
                            <td>{text_field name="ftpdetials[ftp_port][]" value=$array_details["ftp_port_no"] id = "ftp_port-$k" class = "small_txt"}</td>
                            <td>{text_field name="ftpdetials[ftp_username][]" value=$array_details["ftp_username"] id = "ftp_username-$k" class = "ftp_user_name"}</td>
                            <td>{text_field name="ftpdetials[ftp_password][]" value=$array_details["ftp_password"] id = "ftp_password-$k" class = "ftp_user_name"}</td>
                            <td>
                                <select name=ftpdetials[branches][] id = "ftp_branch-{$k}">
                                    <option {if $array_details["ftp_branches"] == "all"} selected="selected" {/if}>All</option>
                                        {foreach from=$branches_array item=array_branch key=name_branch} 
                                            <option value="$array_branch" {if $array_details["ftp_branches"] == $array_branch} selected="selected" {/if}>{$array_branch}</option>
                                        {/foreach}
                                </select>
                            </td>
                            <td><input type = "button" value="Test" onclick="test_url('{"$k"}','{"$k"}')" id="button-{$k}" ></td>
                            <td class="options right"><a href="#" title="{lang}Remove FTP{/lang}" class="remove_day_off"><img src='{image_url name="icons/12x12/delete.png" module=$smarty.const.ENVIRONMENT_FRAMEWORK}' alt='' /></a></td>
                        </tr>
                        <tr class="day_off_row">
                            <td colspan = "7">
                                {text_field name="ftpdetials[ftp_dir][]" value=$array_details["ftp_dir"] id = "ftp_dir-$k" type = "text" class = "ftp_path"}
                            </td>
                        </tr>
                         {assign var="k" value=$k+1}
                    {/foreach}
               {/if}
            </table>
            <p id="no_days_off_message" style="{if is_foreachable($ftp_details_exists)}display: none{/if}">{lang}There are no FTP account's defined.{/lang}</p>
            <p><a href="#" class="button_add">{lang}New FTP Account{/lang}</a></p>
          {/wrap}
        </div>
      </div>
</div>
        {wrap_buttons}
                {submit}Add FTP Details{/submit}
        {/wrap_buttons}
     {/form}     
      {literal}
        <script>
          App.widgets.FTPConn.init('ftp_div_wrapper');
          function test_url(click_id,click_button) {
           /* alert(click_id);
            return false;*/
                //alert(click_button)
		var test_connection_url = $('#ftp_test_url').val();
                var ftp_domain = $('#ftp_domain-'+click_id).val();
                var ftp_port = $('#ftp_port-'+click_id).val();
                var ftp_username = $('#ftp_username-'+click_id).val();
                var ftp_password = $('#ftp_password-'+click_id).val(); 
                var ftp_branch = $('#ftp_branch-'+click_id).val();  
                 var ftp_dir = $('#ftp_dir-'+click_id).val();      
                
                $('#button-'+click_button).attr("disabled", "disabled");
                $.get(test_connection_url,{ftp_domain: ftp_domain,ftp_port: ftp_port,ftp_username: ftp_username,ftp_password: ftp_password,ftp_branch: ftp_branch,ftp_dir : ftp_dir, async : true},
		function(data){
			$('#test_connection_loading_img').hide();
			if (jQuery.trim(data) == 'ok') {
                                //var git_server_location = $('input[name=gitoliteadmin[git_server_location]]:radio:checked').val();
                                /*if ($('#git_server_location').is(":checked"))
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
                                }*/
                                App.Wireframe.Flash.success(App.lang("Connection Established"));
                                    
                                    
			} else {
			 	App.Wireframe.Flash.error(App.lang(data));
                                
			}
                            $('#button-'+click_button).removeAttr("disabled");  
		});
	}
        </script> 
      {/literal}
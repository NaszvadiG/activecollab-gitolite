{title}WebHook URL's{/title}
{form action={$form_action} method=post id = "gitolite_hooks"}
<input type="hidden" value="{$test_url}" id="test_url_hooks">
<div class="content_stack_wrapper">
     <div class="content_stack_element last">
        <div class="content_stack_element_info">
          <h3>{lang}WebHook URL{/lang}</h3>
        </div>
        <div id="hooks_div_wrapper" class="content_stack_element_body">
          {wrap field=web_hooks}
            <table class="form form_field validate_callback validate_days_off" id="tb_git_hooks" style="{if !is_foreachable($url_array)}display: none{/if}">
              <tr>
                <th class="name">{label required=yes}Hook URL{/label}</th>
                <th>{lang}Test Connection{/lang}</th>
                <th></th>
              </tr>
                {if is_foreachable($url_array)}
                    {assign var="k" value=0}
                    {foreach from=$url_array item=array_details key=name} 
                       
                        <tr class="day_off_row {cycle values='odd,even'}">
                            <td class="name">{text_field name="webhooks[]" value=$array_details id = "autotext-$k"}</td>
                            <td><input type = "button" value="Test" onclick="test_url('{"autotext-$k"}','{"autobutton-$k"}')" id="autobutton-{$k}"></td>
                          <td class="options right"><a href="#" title="{lang}Remove Hook URL{/lang}" class="remove_day_off"><img src='{image_url name="icons/12x12/delete.png" module=$smarty.const.ENVIRONMENT_FRAMEWORK}' alt='' /></a></td>
                          
                        </tr>
                         {assign var="k" value=$k+1}
                    {/foreach}
               {/if}
            </table>
            <p id="no_days_off_message" style="{if is_foreachable($url_array)}display: none{/if}">{lang}There are no WebHook URL's defined.{/lang}</p>
            <p><a href="#" class="button_add">{lang}New WebHook URL{/lang}</a></p>
          {/wrap}
        </div>
      </div>
</div>
        {wrap_buttons}
                {submit}Add Hooks{/submit}
        {/wrap_buttons}
     {/form}     
      {literal}
        <script>
          App.widgets.HooksWrap.init('hooks_div_wrapper');
          function test_url(click_id,click_button) {
           /* alert(click_id);
               
            return false;*/
                //alert(click_button)
		var test_connection_url = $('#test_url_hooks').val();
                var url_to_test = $('#'+click_id).val();
                $('#'+click_button).attr("disabled", "disabled");
                $.get(test_connection_url,{testing_url: url_to_test, async : true},
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
                            $('#'+click_button).removeAttr("disabled");  
		});
	}
        </script> 
      {/literal}
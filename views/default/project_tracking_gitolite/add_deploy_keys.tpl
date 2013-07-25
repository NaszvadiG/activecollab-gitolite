{title}Deploy Key's{/title}
{form action={$form_action} method=post id = "gitolite_deploy_keys"}
<input type="hidden" value="{$test_url}" id="test_url_hooks">
<div class="content_stack_wrapper">
    <div class="content_stack_element last">
        <div class="content_stack_element_info">
            <h3>{lang}Deploy Keys{/lang}</h3>
        </div>
        <div id="key_div_wrapper" class="content_stack_element_body">
            {wrap field=deploy_keys}
            <table class="form form_field validate_callback validate_days_off" id="tb_git_deploy_keys" style="{if !is_foreachable($key_array)}display: none{/if}">
                <tr>
                    <th class="name"><label>Name</label></th>
                    <th class="key"><label>Key</label></th>
                </tr>
                {if is_foreachable($key_array)}
                    {assign var="k" value=0}
                    {foreach from=$key_array item=array_details key=name} 
                        <tr class="day_off_row {cycle values='odd,even'}">
                            <td class="name base_line"><label>{$array_details['name']}</label></td>
                            <td class="key base_line"><label>{$array_details['keys']}</label></td>
                            <td class="options right"><a id="{$del_action}/{$array_details['id']}" href="#" title="{lang}Remove Deploy Key{/lang}" class="remove_day_off"><img src='{image_url name="icons/12x12/delete.png" module=$smarty.const.ENVIRONMENT_FRAMEWORK}' alt='' /></a></td>
                        </tr>
                        {assign var="k" value=$k+1}
                    {/foreach}
                {/if}
            </table>
            <p id="no_days_off_message" style="{if is_foreachable($url_array)}display: none{/if}">{lang}There are no Deploy Key's defined.{/lang}</p>
            <p><a href="#" class="button_add">{lang}New Deploy Key{/lang}</a></p>
            {/wrap}
        </div>
    </div>
</div>
{wrap_buttons}
{submit}Add Deploy Key{/submit}
{/wrap_buttons}
{/form}     
{literal}
<style>
    .name.base_line , .options{
        vertical-align: top;
    }
    .key.base_line {
        width: 465px;
    }
    .key textArea{
        height: auto;
    }
    #tb_git_hooks input[type="text"] {
        width: 300px !important;
    }
    .add-deploy-key-textarea-label {
        vertical-align: top;
    }    
</style>
    <script>
        App.widgets.DeployWrap = function () {
    return {
        init: function (wrapper_id) {
            var wrapper = $("#" + wrapper_id);
            var days_off_table = wrapper.find("table.form");
            var new_row_counter = 0;
            var init_day_off_row = function (row) {                
                row.find("a.remove_day_off").click(function () {                      
                    if (confirm(App.lang("Are you sure that you want to delete this key?"))) {
                        var post_url = $(this).attr('id');                        
                        if(typeof post_url !== 'undefined' && post_url !== false) {
                            var post_url = decodeURIComponent(post_url);                            
                            var deploy_key_id = post_url.substr(post_url.lastIndexOf('/') + 1);
                            post_url = post_url.substr(0,post_url.lastIndexOf('/'));                            
                            $.post(post_url,
                                {
                                    deploy_key_id : deploy_key_id
                                },
                                function (data) {
                                    
                                }
                            );                                
                        }
                        row.remove();
                        if (days_off_table.find("tr.day_off_row").length < 1) {
                            days_off_table.hide();
                            $("#no_days_off_message").show()
                        }
                    }
                    return false;
                })
            };
            days_off_table.find("tr.day_off_row").each(function () {
                init_day_off_row($(this))
            });
            wrapper.find("a.button_add").click(function () {
                //$(".web_hook_url").css({"width": "300"});
                new_row_counter++;
                var row = $('<tr class="day_off_row"> <td colspan = "3"> <div> <label>Title: </label> <input type="text" name="deploykeys['+new_row_counter+'][key_name]" /> <a class="float_right remove_day_off" href="#" title="' + App.lang("Remove Deploy Key") + '" class="remove_day_off"> <img src="' + App.Wireframe.Utils.imageUrl("/icons/12x12/delete.png", "environment") + '" alt="" /> </a> <div class="clear"></div> <label class="add-deploy-key-textarea-label">Key: </label> <textarea rows="8" name="deploykeys['+new_row_counter+'][key]" id = "text-'+new_row_counter+'" type="text" class =""> </textarea>  </div> </td> </tr>');
                days_off_table.append(row);
               
                init_day_off_row(row);
                days_off_table.oddEven({
                    selector: "tr.day_off_row"
                }).show();
                $("#no_days_off_message").hide();
                row.find("input")[0].focus();
                return false;
            })
        }
    }
}();

 
        App.widgets.DeployWrap.init('key_div_wrapper');
        
    </script> 
{/literal}
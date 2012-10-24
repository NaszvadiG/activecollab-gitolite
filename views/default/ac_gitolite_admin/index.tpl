{title}Gitolite Administration{/title}
{add_bread_crumb}Settings{/add_bread_crumb}

<div id="payments_admin" class="wireframe_content_wrapper settings_panel">
  <div class="settings_panel_header">
    <table class="settings_panel_header_cell_wrapper">
      <tr>
        <td class="settings_panel_header_cell">
		      <h2>{lang}Gitolite Settings{/lang}</h2>
		      <div class="properties">
		        <div class="property" id="gitolite_server_details">
		          <div class="label">{lang}Server Address{/lang}</div>
                          <div class="data"><span id="g_user">{$gitoliteuser}</span>@<span id="g_server">{$server_name}</span></div>
                          <input type = "hidden" value = "{$gitoliteuser}" id="gitoliteuser_index">
                          <input type="hidden" value="{$delete_url}" id="delete_url" />
                          <input type="hidden" value="{image_url name="layout/bits/indicator-loading-normal.gif" module=$smarty.const.ENVIRONMENT_FRAMEWORK}" id="loader_image">
		        </div>
		        
		       
                        
                        <div class="property" id="gitolite_admin_path">
		          <div class="label">{lang}Admin Path{/lang}</div>
		          <div class="data">{$gitoliteadminpath}</div>
		        </div>
                        
                         <!--<div class="property" id="gitolite_auto_initialize">
		          <div class="label">{lang}Auto Initialize{/lang}</div>
		          <div class="data" id="show_auto_init">
                          {if $is_auto == "No"}
                              No
                          {else}
                              Yes
                          {/if}
                          </div>
		        </div>-->
                          
                        <!--<br />
                        <h2>{lang}Setup Script{/lang}</h2>
                        <div class="property" id="gitolite_setup_script">
		          <div class="label">{lang}Setup Script{/lang}</div>
		          <div class="data"> 
                              <code id="script_path_index">
                                  {$setup_script nofilter}
                            </code>
                          </div>
		        </div>-->
                        
		      </div>
          <ul class="settings_panel_header_cell_actions">
            <li>{link href=Router::assemble('gitolite_admin_change') mode=flyout_form success_event="gitolite_settings_updated" title="Gitolite Settings" class="link_button_alternative"}Change Settings{/link}</li>
          </ul>
        </td>
      </tr>
    </table>
  </div>
  
  <div class="settings_panel_body">
    
    {if is_foreachable($empty_repositories)}  
      <div id="empty_repos">
          <table cellspacing="0" class="common auto list_items" id="repo_list">
                <thead>
                    <tr>
                        <th colspan="3"><strong>Empty Repositories</strong></th>
                    </tr>
                    <tr>
                        <th>Repository Name</th>
                        <th class="name">Options</th>
                    </tr>
                </thead>

                <tbody>
                
                    {foreach from=$empty_repositories item=repos}   
                        <tr class="list_item gateways" id="row_{$repos['src_repo_id']}">
                            <td>
                                <a href="{$repos['view_url']}" target="_blank" >{$repos['repo_name']}</a>
                            </td>
                            <td class="options">
                                <a class="delete_repo" href="#" id="{$repos['src_repo_id']}"><img src="{image_url name="icons/12x12/delete.png" module=$smarty.const.ENVIRONMENT_FRAMEWORK}" /></a>
                            </td>
                        </tr>
                    {/foreach}
                 
                </tbody>
            </table>
      </div>
      {else}           
        <div id="no_repos">
                <p class="empty_page" style="">
                There are no empty repositories to display
                </p>
        </div>
    {/if}
  </div>
</div>
  {literal}
<script type="text/javascript">
    
    App.Wireframe.Events.bind('gitolite_settings_updated.content', function(event, settings) {
        
     
      $("#g_user").html(settings['gitoliteuser']); 
      $("#g_server").html(settings['gitoliteserveradd']); 
      /*if(settings['initialize_repo'])
      {
            $("#show_auto_init").html(settings['initialize_repo']); 
      }
      else
      {
             $("#show_auto_init").html("No"); 
      }*/
      App.Wireframe.Flash.success(App.lang('Gitolite settings has been changed successfully'));
  });
  
  
  $(document).ready (function () {  
 
   /* if($('#gitoliteuser_index').val() != "")
        {
            var oldhtml = $('#script_path_index').html();
            var newhtml = oldhtml.replace(/ git/g," "+$('#gitoliteuser_index').val());
            $('#script_path_index').html(newhtml)
        }*/
        
        $('table#repo_list').on('click','a.delete_repo',function(e){
            
            if(!confirm('Are you sure that you want to permanently delete this repository? It will also be removed from all the projects.'))
            {
                return false;
            }
            var delete_url = $('#delete_url').val();
            var image_url = $('#loader_image').val();   
            /*alert(delete_url)*/
            
            //return false;
            var id = $(this).attr('id');
            //alert(id)
            $("#" + id).html('<img src = '+image_url+'>');
            $.get(delete_url,{repoid: id,async : true},
            function(data){
                    //$('#delete_loading_img').hide();
                    //$("#" + id).html
                    if (jQuery.trim(data) == 'ok') {
                             $("#row_" + id).remove();
                             if ($(".delete_repo").length == 0 )
                             {
                                 $("#empty_repos").hide(); 
                                 $(".settings_panel_body").html('<div id="no_repos"><p class="empty_page" style="">There are no empty repositories to display</p></div>'); 
                             }
                             App.Wireframe.Flash.success(App.lang("Repository deleted successfully"));
                    } else {
                            App.Wireframe.Flash.error(App.lang(data));

                    }
            });
    });
        
          
    });

</script>
{/literal} 
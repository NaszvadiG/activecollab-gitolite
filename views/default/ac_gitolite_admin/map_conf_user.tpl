{title}Map Users{/title}
{add_bread_crumb}Map Users{/add_bread_crumb}
<div class="settings_panel_body" id="map_users_container">
 <input type="hidden" id="admin_index_url" value = "{$admin_index_url}" class="key_id"> 
 {if is_array($array_pub_keys) &&  $array_pub_keys|@count > 1}    
    {if is_foreachable($array_pub_keys)}  
      <div id="empty_repos">
          <table cellspacing="0" class="common auto list_items" id="mapping_lists" >
                <thead>
                    <tr>
                        <th colspan="3"><strong>Key's List</strong></th>
                    </tr>
                    <tr>
                        <th>Public Keys</th>
                        <th>Members List</th>
                        
                    </tr>
                </thead>
                
                <tbody>
                 
                   {foreach from=$array_pub_keys key=key_id item=key_name} 
                       {if is_numeric($key_id)}
                        <tr class="" >
                            <td>
                             {$key_name}
                            
                               <input type="hidden" name="pub_keys_name[]" value = "{$key_name}" class="key_id"> 
                               <input type="hidden" name="pub_keys_access[]" value = "{$array_pub_keys_access[$key_id]}" class="key_id">
                            </td>
                            <td>
                                 {html_options name="user_id[]" options=$ac_users}
                            </td>
                        </tr>
                       {else}
                           <tr class="" >
                            <td>
                             {$key_id}
                            </td>
                            <td>
                                 {$key_name}
                              
                            </td>
                        </tr>
                       {/if}
                  {/foreach}    
                  
                  <tr>
                      <td colspan="2">
                          <button type="button" id="map_user_button" class="default"><span>{lang}Map Users{/lang}</span></button>
                          <input type="hidden" value = "{$map_users_url}" id="map_users_url">
                          <img id="map_user_loading_img" src="{image_url name="layout/bits/indicator-loading-normal.gif" module=$smarty.const.ENVIRONMENT_FRAMEWORK}" alt='' /> 
                          <button type="button" id="show_map_project" class="default"><span>{lang}Map Projects{/lang}</span></button>
                      </td>
                  </tr>
                
                
                  <!--<tr>
                      <td colspan="2">
                          <em>Note: The keys which are already present on sever will not be visible under "Public Keys" lists </em>
                      </td>-->
                  </tr>
                </tbody>
            </table>
      </div>
      {else}           
       
    {/if}
    
    {else}
      <div id="no_keys">
            <p class="empty_page" style="">
                No keys to map<br/>
                <em>Note: The keys which are already present on sever cannot be mapped. </em>
            </p>
        </div>
     {/if}
  </div>
  
  <div class="settings_panel_body" id="map_repos_container">
      
 {if is_array($array_repos) &&  $array_repos|@count > 0}    
    {if is_foreachable($array_repos)}  
      <div id="empty_repos">
          <table cellspacing="0" class="common auto list_items" id="mapping_lists" >
             
                <thead>
                    <tr>
                        <th colspan="3"><strong>Project's List</strong></th>
                    </tr>
                    <tr>
                        <th>Repository Name</th>
                        <th>Select Project</th>
                    </tr>
                    
                </thead>
                
                <tbody>
                   
                   {foreach from=$array_repos key=repo_key item=repo_name}   
                        <tr class="">
                            <td>
                                {$repo_name}
                                <input type="hidden" name="array_repos[]" value = "{$repo_name}" class="project_id">
                            </td>
                            <td >
                                 {html_options name="prj_id[]" options=$ac_projects}
                                 <img id="repo_{$repo_name}" src="{image_url name="layout/bits/indicator-loading-normal.gif" module=$smarty.const.ENVIRONMENT_FRAMEWORK}" alt='' class="show_succ" />
                                 <span id="data_show_{$repo_name}"></span>
                            </td>
                        </tr>
                  {/foreach}    
                  
                  <tr>
                      <td colspan="2">
                          <button type="button" id="map_project_button" class="default"><span>{lang}Map Projects{/lang}</span></button>
                          <input type="hidden" value = "{$map_projects_url}" id="map_project_url">
                          <input type="hidden" value = "{$render_after_clone_url}" id="render_after_clone_url">
                          
                          <img id="map_project_loading_img" src="{image_url name="layout/bits/indicator-loading-normal.gif" module=$smarty.const.ENVIRONMENT_FRAMEWORK}" alt='' /> 
                          
                      </td>
                  </tr>
                
                
                  <!--<tr>
                      <td colspan="2">
                          <em>Note: The repositories which are already mapped will not be visible under "Repository Name" lists.</em>
                      </td>-->
                  </tr>
                </tbody>
            </table>
      </div>
      {else}           
       
    {/if}
    
    {else}
      <div id="no_keys">
            <p class="empty_page" style="">
                No repositories to map<br/>
                <em>Note: The repositories which are already mapped will not be shown in list. </em>
            </p>
        </div>
     {/if}
  </div>
  
  
  
  
  
  
  
  
  {literal}
<script type="text/javascript">
  
    $(document).ready (function () {
        $('#map_user_loading_img').hide();
        $('#map_repos_container').hide();     
        $('#show_map_project').hide();    
        $('#show_map_project').click(function (event) {
             $('#map_repos_container').show();
             $('#map_users_container').hide();  
        });
	$('#map_user_button').click(function (event) {
	var map_users_url = $('#map_users_url').val();
                
        var videos = $('select[name^=user_id]');
        var postData =  new Array; 
        var postData_user_tmp = new Array; 
        var postData_user_val = new Array;    
        
        $.each(videos, function(index, el) {
            // push the value in the vdo array
            if($('option:selected', $(this)).val() != "") 
            {
                postData.push($(el).val());
                postData_user_tmp.push($('option:selected', $(this)).text());  
                postData_user_val.push($('option:selected', $(this)).val());        
            }
            else
            {
                 postData.push("na");
            }    
           
        });
        
       
                
        var videos_pub = $('input[name^=pub_keys_name]');
        var postData_pub =  new Array; 
        
        var dup_sel = 0;
        $.each(videos_pub, function(index, el) {
            // push the value in the vdo array
               // alert($(el).val());
             postData_pub.push($(el).val());
        });
                
        var keys_access = $('input[name^=pub_keys_access]');
        var postData_key_access =  new Array; 
        
         
        $.each(keys_access, function(index, el) {
            // push the value in the vdo array
               // alert($(el).val());
             postData_key_access.push($(el).val());
        });
            
            
        if(postData_user_tmp.length == 0)
        {
            App.Wireframe.Flash.error("Please select atleast one user to map.");
            return false;
        }
       
         
        
        $('#map_user_loading_img').show();
	//$.get(test_connection_url,{url: repository_url, engine: "GitRepository", async : true,dir:admin_dir},
        $.get(map_users_url,{pub_keys_str: postData_pub,user_ids: postData,pub_key_access : postData_key_access, async : true},
	function(data){
            $('#map_user_loading_img').hide();
            if (jQuery.trim(data) == 'ok') {
                         App.Wireframe.Flash.success(App.lang("Keys mapped successfully with selected users"));
                         $('#map_repos_container').show();
                         $('#map_users_container').hide();  
                         $('#map_users_url').hide();  
                             
                         //App.Wireframe.Content.reload();
                         
            } else {
                    App.Wireframe.Flash.error(App.lang(data));

            }
	});
    });
        
        
        
        
        
    /** Process Repositories **/    
               //$('#kwd_search').val("Search Repository");
        $('#map_project_loading_img').hide();
        $('.show_succ').hide();
	$('#map_project_button').click(function (event) {
           
        var map_project_url = $('#map_project_url').val();
        var render_after_clone_url = $('#render_after_clone_url').val();    
        
        var postData_prjs =  new Array; 
        var postData_prjs_tmp = new Array; 
        var postData_prjs_val = new Array;    

        var projects = $('select[name^=prj_id]');
       
        $.each(projects, function(index, el) {
               if($('option:selected', $(this)).val() != "") 
               {
                    postData_prjs.push($('option:selected', $(this)).val());
                    postData_prjs_tmp.push($('option:selected', $(this)).text());  
                    postData_prjs_val.push($('option:selected', $(this)).val());    

               }
               else
               {
                     postData_prjs.push("na");
               }    
            
        });
                
        var postData_repos =  new Array; 
        var projects_repos = $('input[name^=array_repos]');
            
        $.each(projects_repos , function(index, el) {
            
             postData_repos.push($(el).val());
        });
                
                
        if(postData_prjs_tmp.length == 0)
        {
            App.Wireframe.Flash.error("Please select atleast one project to map.");
            return false;
        }
        $('#map_project_loading_img').show();
           
        //$.get(test_connection_url,{url: repository_url, engine: "GitRepository", async : true,dir:admin_dir},
        var cnt = 0;    
        $.each(postData_prjs, function(index, el) {  
            //alert(postData_prjs[index])
               
           
            if(postData_prjs[index] != "na")
            {    
                //cnt = cnt+1;
                $('#repo_'+postData_repos[index]).show();
                //alert('#repo_'+postData_repos[index])
                $.get(map_project_url,{prj_name: postData_prjs[index],repo_name: postData_repos[index], async : true},
                function(data){
                        //$('#map_project_loading_img').hide();
                        if (jQuery.trim(data) == 'ok') {
                                    //alert("asdas");
                                    /*$.each(videos_pub, function(index, el) {
                                        var optText = $('option:selected', $(this)).val();
                                        if ($.inArray(optText,  postData_pub_val) > -1) {
                                               //alert(optText)
                                               $(this).find('[value='+optText+']').remove();
                                        }
                                    });*/

                                    /*App.Wireframe.Flash.success(App.lang("Projects mapped successfully with selected repositories"));
                                    App.Wireframe.Content.reload();*/
                                        cnt = cnt+1;
                                        //console.log(cnt);
                                                
                                        $('#repo_'+postData_repos[index]).hide();
                                        if(cnt == postData_prjs.length)   
                                        {
                                            $.get(render_after_clone_url,{render: true, async : true},
                                             function(data){
                                                  $('#map_project_loading_img').hide();
                                                  if (jQuery.trim(data) == 'ok') {
                                                          App.Wireframe.Flash.success(App.lang("Projects mapped successfully with selected repositories"));

                                                          App.Wireframe.Content.setFromUrl($('#admin_index_url').val());

                                                  } else 
                                                  {
                                                        App.Wireframe.Flash.error(App.lang(data));

                                                  }
                                          });
                                       }   
                                        //console.log('#repo_'+postData_repos[index])
                                        //alert(postData_repos[index] + "mapped")  
                                            

                        } else {
                                 
                                     $('#data_show_'+postData_repos[index]).text(data);
                                //App.Wireframe.Flash.error(App.lang(data));

                        }
                 });
                     
                     
           }   
         });
          
          //$('#map_project_loading_img').show();  
           
    });
   
        
  
});
</script>
{/literal} 
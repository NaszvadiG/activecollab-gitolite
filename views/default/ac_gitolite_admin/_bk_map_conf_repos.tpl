{title}Map Repositories{/title}
{add_bread_crumb}Map Repositories{/add_bread_crumb}
<div class="settings_panel_body">
 {if is_array($array_repos) &&  $array_repos|@count > 1}    
    {if is_foreachable($array_repos)}  
      <div id="empty_repos">
          <table cellspacing="0" class="common auto list_items" id="mapping_lists" >
             
                <thead>
                    <tr>
                        <th colspan="3"><strong>{lang}Project's List{/lang}</strong></th>
                    </tr>
                    <tr>
                        <th>{lang}Repository Name{/lang}</th>
                        <th>{lang}Select Project{/lang}</th>
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
                            </td>
                        </tr>
                  {/foreach}    
                  
                  <tr>
                      <td colspan="2">
                          <button type="button" id="map_project_button" class="default"><span>{lang}Map Projects{/lang}</span></button>
                          <input type="hidden" value = "{$map_projects_url}" id="map_project_url">
                          <img id="map_project_loading_img" src="{image_url name="layout/bits/indicator-loading-normal.gif" module=$smarty.const.ENVIRONMENT_FRAMEWORK}" alt='' /> 
                          
                      </td>
                  </tr>
                
                
                  <tr>
                      <td colspan="2">
                          <em>{lang}Note: The repositories which are already mapped will not be visible under "Repository Name" lists.{/lang}</em>
                      </td>
                  </tr>
                </tbody>
            </table>
      </div>
      {else}           
       
    {/if}
    
    {else}
      <div id="no_keys">
            <p class="empty_page" style="">
                {lang}No repositories to map{/lang}<br/>
                <em>{lang}Note: The repositories which are already mapped will not be shown in list.{/lang}</em>
            </p>
        </div>
     {/if}
  </div>
  
  {literal}
<script type="text/javascript">
  
    $(document).ready (function () {
        //$('#kwd_search').val("Search Repository");
        $('#map_project_loading_img').hide();
        $('.show_succ').hide();
	$('#map_project_button').click(function (event) {
        var map_project_url = $('#map_project_url').val();
       
           
       // var user_id =  $('.user_id').serialize();
        //var pub_keys =  $('.pub_keys').serialize();
        
        var postData_prjs =  new Array; 
        var postData_prjs_tmp = new Array; 
        var postData_prjs_val = new Array;    

        var projects = $('select[name^=prj_id]');
       
        $.each(projects, function(index, el) {
            // push the value in the vdo array
            
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
            // push the value in the vdo array
               // alert($(el).val());
             //alert(index)
             postData_repos.push($(el).val());
        });
                
                
        if(postData_prjs_tmp.length == 0)
        {
            App.Wireframe.Flash.error(App.lang("Please select atleast one project to map."));
            return false;
        }
            
        /*$.each(postData_repos, function(index, el) {
            alert(postData_repos[index] + "==" +  postData_prjs[index])
        });*/
        
        
        $('#map_project_loading_img').show();
           
        //$.get(test_connection_url,{url: repository_url, engine: "GitRepository", async : true,dir:admin_dir},
        $.each(postData_prjs, function(index, el) {  
            //alert(postData_prjs[index])
               
           
            if(postData_prjs[index] != "na")
            {    
                $('#repo_'+postData_repos[index]).show();
                //alert('#repo_'+postData_repos[index])
                $.get(map_project_url,{prj_name: postData_prjs[index],repo_name: postData_repos[index], async : true},
                function(data){
                        $('#map_project_loading_img').hide();
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
                                        $('#repo_'+postData_repos[index]).hide();
                                        //console.log('#repo_'+postData_repos[index])
                                        //alert(postData_repos[index] + "mapped")  
                                            

                        } else {

                                App.Wireframe.Flash.error(App.lang(data));

                        }
                 });
           }   
         });
    });
  
        
        /*
            quick search table
                
        
        // Write on keyup event of keyword input element
	$("#kwd_search").keyup(function(){
		// When value of the input is not blank
		if( $(this).val() != "")
		{
			// Show only matching TR, hide rest of them
			$("#mapping_lists tbody>tr").hide();
			$("#mapping_lists td:contains-ci('" + $(this).val() + "')").parent("tr").show();
		}
		else
		{
			// When there is no input or clean again, show everything back
			$("#mapping_lists tbody>tr").show();
		}
	});
        
        $("#kwd_search").click(function(){
            if( $(this).val() != "" && $(this).val() == "Search Projects")
            {
                $(this).val("");
            }
            else
            {
                return false;
            }
        });
        $("#kwd_search").blur(function(){
            if( $(this).val() == "")
            {
                $(this).val("Search Projects");
            }
            else
            {
                return false;
            }
        });     
        $.extend($.expr[":"], 
        {
            "contains-ci": function(elem, i, match, array) 
                {
                  return (elem.textContent || elem.innerText || $(elem).text() || "").toLowerCase().indexOf((match[3] || "").toLowerCase()) >= 0;
                }
        });
            */
       
});
// jQuery expression for case-insensitive filter

</script>
{/literal} 
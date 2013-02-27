{title}Map Users{/title}
{add_bread_crumb}Map Users{/add_bread_crumb}
<div class="settings_panel_body">
    
    {if is_foreachable($ac_users)}  
      <div id="empty_repos">
          <table cellspacing="0" class="common auto list_items" id="mapping_lists" >
                <thead>
                    <tr>
                        <th colspan="3"><strong>{lang}Empty Repositories{/lang}</strong></th>
                    </tr>
                    <tr>
                        <th>{lang}User Name{/lang}</th>
                        <th class="name">{lang}Public Keys{/lang}</th>
                    </tr>
                </thead>

                <tbody>
                
                    {foreach from=$ac_users item=users}   
                        <tr class="list_item gateways" id="row_{$repos['src_repo_id']}">
                            <td>
                               {html_options name="foo[]" options=$ac_users}
                            </td>
                            <td class="options">
                                 {html_options name="foo3[]" options=$array_pub_keys}
                            </td>
                        </tr>
                    {/foreach}
                 
                </tbody>
            </table>
      </div>
      {else}           
        <div id="no_repos">
                <p class="empty_page" style="">
                    {lang}There are no empty repositories to display{/lang}
                </p>
        </div>
    {/if}
  </div>
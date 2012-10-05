{if $is_gitolite  == TRUE}
<div id="company">
	  <table class="" cellspacing=0>
              <tr>
                 
                  <th  colspan="3" style="text-align: center;"><img src="{$icon}"> {link href=$add_url
				mode=flyout_form  
				title="Add New Public Key" }
				Add New Key
				{/link}</th>
                 
                  
	    </tr>
	    <tr>
	      
	      <th class="label" width="20%">{lang}Key Name{/lang}</th>
              <th class="label" width="57%">{lang}Key{/lang}</th>
              <th class="label" width="7%">Delete</th>
	    </tr>
            {if is_foreachable($user_public_keys)}
		  {foreach from=$user_public_keys item=userkeys}
		    <tr class="">
		      <td class="name">{$userkeys['key_name']}</td>
                      <td class="name">{$userkeys['public_key']|substr:0:25}.....{$userkeys['public_key']|substr:-30:{$userkeys['public_key']|strlen}}</td>
                      <td class="name"><a  class = "delete"href="{$del_url}/delete-keys/{$userkeys['key_id']}" title="Delete Key"><img src="{$delete_icon}"></a></td>
		    </tr>
		  {/foreach}
             {else}
                <tr>
                    <td colspan="3" style="text-align:center;"><strong>No Keys Added</strong></td>
                </tr>
             {/if}
	  </table>
	
</div>
{else}
    <div id="company">
        <table class="" cellspacing=0>
            <tr>
                <th  colspan="3" style="text-align: center;">&nbsp;</th>
	    </tr>
             <tr>
                 <th class="label" colspan="3" style="text-align: center;"> Can't find gitolite admin</th>
	    </tr>
        
        </table>
       
    </div>
{/if}
<script>
   $(document).ready (function () {
	$('.delete').click (function () {
		return confirm ("Are you sure you want to delete this key") ;
	}) ; 
}) ;
</script>
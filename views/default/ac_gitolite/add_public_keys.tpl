<div id="add_public_keys_wrapper">
    
  {form action="{$form_action}" method=post id="add_user_key" }
  	<div class="content_stack_wrapper">
  	
  		<!-- Enable Scheduled Requests -->
            <div class="content_stack_element" >
	      <div class="content_stack_element_info">
                  <b>Key Name</b>
	      </div>
	      <div class="content_stack_element_body" style="" >
                  {text_field name="key_name" value=''} <strong style="font-size: 10px;color: #B4B4B4;">(eg . pc_name_office. This will be appeded by your email)</strong>
             </div>
             
             
              <div class="content_stack_element_info">
                  <b>Enter Key</b>
	      </div>
	      <div class="content_stack_element_body" style="" >
                    {textarea_field name="public_keys"}{/textarea_field}
             </div>
	    </div>
  		<!-- /Enable Scheduled Requests -->
    {wrap_buttons}
      {submit name="add_keys" }Save Keys{/submit}
    {/wrap_buttons}
  {/form}
</div>
</div>

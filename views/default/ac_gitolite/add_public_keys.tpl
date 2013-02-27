<div id="add_public_keys_wrapper">
    
  {form action="{$form_action}" method=post id="add_user_key" }
  	<div class="content_stack_wrapper">
  	
  		<!-- Enable Scheduled Requests -->
            <div class="content_stack_element" >
	      <div class="content_stack_element_info">
                  <b>{lang}Key Name{/lang}</b>
	      </div>
	      <div class="content_stack_element_body" style="" >
                  {text_field name="key_name" value=''} <strong class="help_text">{lang}Only a-z, A-Z, 0-9 & hypen (-) are allowed.{/lang}</strong>
             </div>
             
             
              <div class="content_stack_element_info">
                  <b>{lang}Public Key{/lang}</b>
	      </div>
	      <div class="content_stack_element_body">
                  <div class="key_field">{textarea_field name="public_keys" class="small_txt_area"}{/textarea_field}</div>
                  <div class ="key_help">
                      
                      <h3><em>{lang}To generate a new SSH key, open your terminal and use code below.{/lang} </em></h3>
                      <code class="ssh_code">ssh-keygen -t rsa -C "{$user_rmail}"
                     </code><br /><br />
                      <h3><em>{lang}Use code below to dump your public key and paste it in Public Key{/lang}</em></h3>
                      <code class="ssh_code">
                         cat ~/.ssh/id_rsa.pub
                      </code>
                  </div>
                  
                 
             </div>
	    </div>
  		<!-- /Enable Scheduled Requests -->
    {wrap_buttons}
      {submit name="add_keys"}{lang}Save Keys{/lang}{/submit}
    {/wrap_buttons}
  {/form}
</div>
</div>

/**
 * Flyout Dialog implementation
 */
App.widgets.RepositoryForm = function () {
  /**
   * Form warpper
   * 
   * @var jQuery
   */
  var wrapper;
  
  var test_connection_wrapper;
  var form_submit_wrapper;
  var test_connection_button;
  var test_connection_indicator;
  
  /**
   * Test connection url
   * 
   * @var test_connection_url
   */
  var test_connection_url;
  
  return {
    init : function (wrapper_id) {
      wrapper = $('#' + wrapper_id);
      
      test_connection_wrapper = wrapper.find('.test_connection').show();
      form_submit_wrapper = wrapper.find('.submit_repository').hide();
      
      test_connection_button = wrapper.find('.test_connection button:first');
      test_connection_indicator = wrapper.find('.test_connection img:first').hide();
      test_connection_url = wrapper.find('.test_connection input[type=hidden]:first').attr('value');
  
      test_connection_button.click(function () {
        test_connection_button.hide();
        test_connection_indicator.show();
        var final_test_url = App.extendUrl(test_connection_url, {
          url       : $('#repositoryUrl').val(),
          user      : $('#repositoryUsername').val(),
          password  : $('#repositoryPassword').val(),
          engine    : $('#repositoryType option:selected').attr('value')
        });
        
        $.ajax({
          url : final_test_url,
          type : 'GET',
          success : function (response) {
            test_connection_button.show();
            test_connection_indicator.hide();
            
            if ($.trim(response) == 'ok') {
              test_connection_wrapper.hide();
              form_submit_wrapper.show();
              App.Wireframe.Flash.success(App.lang('Connection parameters are valid'));
            } else {
              App.Wireframe.Flash.error(App.lang('Could not connect to repository: :response', {response : response}));
            } // if

          },
          error : function (response) {
            test_connection_button.show();
            test_connection_indicator.hide();            
          }
        });
      });
      
      // if some field is changed we need to put form in edit mode
      wrapper.find('input, select, textarea').bind('change keypress', function () {
        if (!test_connection_wrapper.is(':visible')) {
          test_connection_wrapper.show();
          form_submit_wrapper.hide();
        };
      });

    }
  };
}();
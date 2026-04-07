
;if (typeof jQuery != 'undefined') {      
    console.log('RDS, jQuery=',jQuery.fn.jquery)
}else{
  console.log('RDS, jQuery does not exist?')
}


var CHEFS_URL = {
  server:'%[app_server]%',
  base:'%[app_base]%'
};


;!(function($){

  var url = CHEFS_URL.base+'pbl/dist/app.js?ver=%[app_version]%';

  $.ajax({
      url: url,
      dataType: "script",
      success: function(data){    
        App&&App();
      },
      error:function(error){
        console.log('RDS, error load chefsmenu public loader');
        console.log(error)
      }
    });   


})(jQuery);
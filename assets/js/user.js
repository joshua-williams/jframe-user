var user = {
	facebook : {
		init : function(){
  			window.fbAsyncInit = function() {
	        FB.init({
	          appId      : '{{facebook_app_id}}',
	          xfbml      : true,
	          version    : 'v2.0'
	        });
	      };
	
	      (function(d, s, id){
	         var js, fjs = d.getElementsByTagName(s)[0];
	         if (d.getElementById(id)) {return;}
	         js = d.createElement(s); js.id = id;
	         js.src = '//connect.facebook.net/en_US/sdk.js';
	         fjs.parentNode.insertBefore(js, fjs);
	       }(document, 'script', 'facebook-jssdk'));
	       
	       $(function(){
	       	
	       });
	       /**************** END user.facebook.init *****************************/
		},
		
	},
	
}



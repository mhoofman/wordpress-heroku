jQuery(document).ready(function($) {     
		var editor = false;
		
		$('#wmd-button-bar-help').hide();
		$('pre').not('.wmd-help').addClass('prettyprint');

		var help = function () { 
			$('#wmd-button-bar-help').toggle(300,'swing');
		}

		if($('#bbp_reply_content').length>0){
			var id =  "bbp_reply_content";
		}else if($('#bbp_topic_content').length>0){
			var id =  "bbp_topic_content";
		}else{
			var id =  "comment";
		}

		if( $('#wmd-button-bar'+id ).length > 0 ){
			var converter2 = new Markdown.getSanitizingConverter();
			editor = new Markdown.Editor(converter2, id, { handler: help });
		}
		
		if (typeof prettyPrint == 'function') {
			prettyPrint();
			if( editor ){
				editor.hooks.chain("onPreviewRefresh", function () {
			        $('.wmd-preview pre').addClass('prettyprint');
			        prettyPrint();
   			 	});
			}
		}

		if( editor ){
			editor.run();
		}
});

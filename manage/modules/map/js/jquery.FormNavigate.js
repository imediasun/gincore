if (history && history.navigationMode) history.navigationMode = 'compatible';
jQuery.fn.extend({
	FormNavigate: function(o){
		var formdata_original = true;
		jQuery(window).bind('beforeunload', function (){
			if (!formdata_original) return settings.message;
		});

		var def = {
			message: '',
			aOutConfirm: 'a:not([target!=_blank])'
		};
		var settings = jQuery.extend(false, def, o);

		if (o.aOutConfirm && o.aOutConfirm != def.aOutConfirm){
			jQuery('a').addClass('aOutConfirmPlugin');
			jQuery(settings.aOutConfirm).removeClass("aOutConfirmPlugin");
			jQuery(settings.aOutConfirm).click(function(){
				formdata_original = true;
				return true;
			});
		}

		jQuery("a.aOutConfirmPlugin").click(function(){
			if (formdata_original == false)
				if(confirm(settings.message))
					formdata_original = true;
			return formdata_original;
		});

		jQuery(this).find("input[type=text], textarea, input[type='password'], input[type='radio'], input[type='checkbox'], input[type='file']").live('change keypress', function(event){
			formdata_original = false;
		});

		jQuery(this).find(":submit, input[type='image']").click(function(){
			formdata_original = true;
		});
	}
});
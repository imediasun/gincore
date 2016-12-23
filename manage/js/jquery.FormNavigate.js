jQuery.fn.extend({
    FormNavigate: function (o) {
        var formdata_original = true;
        jQuery(window).bind('beforeunload', function () {
            if (!formdata_original) return settings.message;
        });

        var def = {
            message: '',
            aOutConfirm: 'a:not([target!=_blank])'
        };
        var settings = jQuery.extend(false, def, o);

        if (o.aOutConfirm && o.aOutConfirm != def.aOutConfirm) {
            jQuery('a').addClass('aOutConfirmPlugin');
            jQuery(settings.aOutConfirm).removeClass("aOutConfirmPlugin");
            jQuery(settings.aOutConfirm).click(function () {
                formdata_original = true;
                return true;
            });
        }

        jQuery("a.aOutConfirmPlugin").click(function () {
            if (formdata_original == false)
                if (confirm(settings.message))
                    formdata_original = true;
            return formdata_original;
        });

        jQuery(this).find("input[type=text], textarea, input[type='password'], input[type='radio'], input[type='checkbox'], input[type='file']").live('change keypress', function (event) {
            formdata_original = false;
        });

        jQuery(this).find(":submit, input[type='image']").click(function () {
            formdata_original = true;
        });
    },

    ElementNavigate: function (o) {
        //var formdata_original = true;
        var formdata_submitted = false;

        jQuery(window).bind('beforeunload', function (e) {
            if (!formdata_original && !formdata_submitted) {
                return settings.message;
            }
            //click_tab(_this, _this.event);
        });

        var def = {
            message: '',
            aOutConfirm: 'a:not([target!=_blank])'
        };
        var settings = jQuery.extend(false, def, o);

        if (o.aOutConfirm && o.aOutConfirm != def.aOutConfirm) {
            jQuery('a').addClass('aOutConfirmPlugin');
            jQuery(settings.aOutConfirm).removeClass("aOutConfirmPlugin");
            jQuery(settings.aOutConfirm).live('click', function () {
                formdata_original = true;
                return true;
            });
        }

        jQuery("a.aOutConfirmPlugin, a.click_tab").live('click', function () {
            var _this = this;

            if (formdata_original == false) {
                formdata_original = true;
                if (confirm(settings.message)) {
                    click_tab(_this, _this.event);
                } else {
                    click_tab_hash();
                    formdata_original = false;
                }
            }

            return formdata_original;
        });

        jQuery(this).live('change keypress', function (event) {
            formdata_original = false;
        });

        $(document).submit(function(){
            formdata_submitted = true;
        });
    }
});
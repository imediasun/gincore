$(function(){
    var def = '[АаБбЕеИиОоУуЮюЯяВвГгДдКкЛлМмНнПпРрСсТтФф]';
    $.mask.definitions['k'] = def;
    $.mask.definitions['9'] = "";
    $.mask.definitions['d'] = "[0-9]";
    $('.call_code_mask').each(function(){
        var $this = $(this),
            val = $.trim($this.val());
        // для старых кодов, прописанных ранее, не делаем маску
        if(!val || val.search(def+"{2}\-[0-9]{3}") != -1){
            $this.mask("kk-ddd", {
                completed: function(){
                    var $this = this;
                    $.ajax({
                        url: prefix+'services/ajax.php',
                        type: 'POST',
                        data: 'action=check_code&service=crm/calls&code='+$this.val(),
                        dataType: 'json',
                        success: function (data) {
                            $this.siblings('.code_exists,.code_not_exists').addClass('hidden');
                            if(data.state){
                                $this.css({
                                    backgroundColor: '#D8FCD7'
                                });
                                $this.siblings('.code_exists').removeClass('hidden');
                            }else{
                                $this.css({
                                    backgroundColor: '#F0BBC5'
                                });
                                $this.siblings('.code_not_exists').removeClass('hidden');
                            }
                        }
                    });
                }
            });
        }
    });
});
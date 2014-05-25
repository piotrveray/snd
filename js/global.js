jQuery(document).ready(function(){
    
    jQuery("#close").click(function(){
        jQuery("#info_message").colorbox.close();
    });
    
    jQuery('.messages').ready(function(){
        if(jQuery('.messages').html())
        {
            var komunikat = "";
            jQuery('.messages li').each(function(){
                jQuery('ul li', jQuery(this)).each(function(){
                    var pos = 0;
                    pos = komunikat.indexOf(jQuery(this).html()); 
                    if (pos == -1)
                        komunikat = komunikat + "<br />" + jQuery(this).html();
                });
            });
            
            var kod = '<div class="general-popup" style="background:#fff; width: 620px;">';
            kod += '<div class="shape-popup-top"></div>';
            kod += '<div style="margin: 0px 40px 30px 40px;">' + komunikat;
            kod += '<div class="form-container"><div class="buttons-set"><p class="back-link"><a href="#" class="back-link" id="close">Powrót</a></p></div></div>';
            kod += '</div>';
            kod += '<div class="shape-popup-bottom"></div>';
            kod += '</div>';
            
            jQuery('.messages').remove();
            
            jQuery.colorbox({
                width:"620px", 
                opacity: 0.50, 
                closeButton: true,
                id: "info_message",
                html: kod
            });
            
            jQuery("#close").click(function(){
                jQuery("#info_message").colorbox.close();
            });
        }
    });
});


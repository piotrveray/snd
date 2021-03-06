var $s = jQuery.noConflict();
$s(document).ready(function(){
    var galleryImages = $s('div.post-entry a img').closest('a');
    galleryImages.attr('rel', 'fancybox');
    galleryImages.fancybox({
        "padding":10,
        "margin":20,
        "opacity":0,
        "modal":0,
        "cyclic":1,
        "autoScale":1,
        "centerOnScroll":1,
        "hideOnOverlayClick":1,
        "hideOnContentClick":0,
        "overlayShow":1,
        "overlayOpacity":0.3,
        "overlayColor":"#333333",
        "titleShow":false,
        "titlePosition":"float",
        "transitionIn":"fade",
        "transitionOut":"fade",
        "speedIn":300,
        "speedOut":300,
        "changeSpeed":300,
        "changeFade":"fast",
        "easingIn":"swing",
        "easingOut":"swing",
        "showCloseButton":1,
        "showNavArrows":1,
        "enableEscapeButton":1
    })
});
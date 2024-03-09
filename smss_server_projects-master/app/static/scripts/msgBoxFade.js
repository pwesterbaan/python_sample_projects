// $(window).on("load",function(){
$(document).ready(function() {
    var fmb=$("#flash_messages_box");
    if(fmb[0].innerText.length > 0) {
	fmb[0].style.display='inline-block';
        fmb.delay(5000).fadeOut(2500);
    } else {
        fmb[0].style.display='none';
    }
});

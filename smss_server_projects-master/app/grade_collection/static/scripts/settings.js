$(document).ready(function() {
    if($("#info_box").html().length > 0)
    {
        $("#info_box").delay(5000).fadeOut(3000);
    }
    else
    {
        $("#info_box").hide();
    }
});

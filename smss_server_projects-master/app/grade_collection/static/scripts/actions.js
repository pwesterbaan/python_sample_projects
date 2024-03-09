$(document).ready(function() {
    // flash_message();
});

function flash_message() {
    if($("#info_box").html().length > 0) {
        $("#info_box").delay(5000).fadeOut(3000);
    } else {
        $("#info_box").hide();
    }
}

function verify_import()
{
    if($("#only_missing").is(":checked"))
    {
        var missing_str = "(Only missing grades or zeros will be replaced.)";
    }
    else
    {
        var missing_str = "(All grades will be replaced.)";
    }

    return confirm("Are you sure you want to import the MATH 1040 averages from " + $("#semester option:selected").text() + " " + $("#year").val() + " to the item named '" + $("#MATH_1040_avg_item_id option:selected").text() + "' for this semester? " + missing_str);
}

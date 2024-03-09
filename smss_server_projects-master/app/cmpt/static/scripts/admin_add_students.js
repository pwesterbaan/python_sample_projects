function show_section(id)
{
    $(".add_section").hide();
    $("#" + id).show();
}

$(document).ready(function () {
    $(".add_section:not(#single_add)").hide();
    $(".add_section:not(#single_add)").addClass("has_hover");
});

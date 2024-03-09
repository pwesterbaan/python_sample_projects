function clear_content()
{
    $("#content_pane").html("");
}

function try_action()
{
    var course_id = $("#course_id").val();
    var action = $("#action").val();

    if(course_id != null && course_id != "" && action != null && action != "")
    {
        perform_action();
    }
    else
    {
        clear_content();
    }
}


function perform_action()
{
    var data = {form_name: "coord_rolls", semester: $("#semester").val(), year: $("#year").val(), course_id: $("#course_id").val(), action: $("#action").val()};
    $.ajax({
        type: 'post',
        dataType: 'html',
        url: 'coord_rolls',
        async: false,
        data: data,
        success: function (response, status, xml) {
                $("#content_pane").html(response);
        },
        error: function(response, status, xml) {
            alert(response);
        }
    });
}


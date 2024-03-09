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
    $("#content_pane").html("<img src='static/images/loading.gif' width='35'>");
    var semester = $("#semester").val();
    var year = $("#year").val();
    var course_id = $("#course_id").val();
    var action = $("#action").val();
    var data = {form_name: "term_end", course_id: $("#course_id").val(), semester: $("#semester").val(), year: $("#year").val(), action: action};
    $.ajax({
        type: 'post',
        dataType: 'html',
        url: 'coord_term_end',
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

function get_end_of_term_csv(filename)
{
    var semester = $("#semester").val();
    var year = $("#year").val();
    var course_id = $("#course_id").val();
    var action = $("#action").val();
    var data = {form_name: "term_end",
		course_id: $("#course_id").val(),
		semester: $("#semester").val(),
		year: $("#year").val(),
		action: $("#action").val(),
		filename: filename
	       };
    $.ajax({
        type: 'POST',
        dataType: 'html',
        url: 'get_coord_term_end',
        async: false,
        data: data,
	success: download.bind(true, "csv", filename),
        error: function(response, status, xml) {
	    alert("error");
        }
    });
}

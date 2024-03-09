function clear_content()
{
    $("#content_pane").html("");
}

function try_action()
{
    var action = $("#action").val();

    if(action != null && action != "")
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
    $("#content_pane").html("<div style=\"margin-top: 30px;\">Patience grasshopper... <img src='static/images/loading.gif' width='35'></div>");
    var data = {form_name: "term_end", semester: $("#semester").val(), year: $("#year").val(), action: $("#action").val()};
    $.ajax({
        type: 'post',
        dataType: 'html',
        url: 'admin_term_end',
        async: true,
        data: data,
        success: function (response, status, xml) {
        	$("#content_pane").html(response);
        },
        error: function(response, status, xml) {
        	console.log(response);
			alert(status);
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
        url: 'get_admin_term_end',
        async: false,
        data: data,
	success: download.bind(true, "csv", filename),
        error: function(response, status, xml) {
	    alert("error");
        }
    });
}

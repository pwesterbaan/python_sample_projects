$(document).ready(function() {
    get_exams();
});

function get_exams()
{
    var data = {func_name: "get_exams",
		course_id: $("#course_id").val(),
		semester: $("#semester").val(),
		year: $("#year").val()};
    $.ajax({
        type: 'post',
        dataType: 'json',
        url: 'coord_reports_ajax',
        async: false,
        data: data,
        success: function (response, status, xml) {
            if(response.error)
            {
                alert(JSON.stringify(response));
            }
            else
            {
                var old_exam_id = $("#exam_id").val();
                $("#exam_id").empty();

                var option = $("<option value=\"\">-- Choose an item --</option>");
                $("#exam_id").append(option);

                $.each(response.data, function(index, exam) {
                    var selected = "";
                    if(exam.exam_id == old_exam_id)
                    {
                        selected = " selected"
                    }

                    var option = $("<option value=\"" + exam.exam_id + "\"" + selected + ">" + exam.title + "</option>");
                    $("#exam_id").append(option);
                });
                try_action();
            }
        },
        error: function(response, status, xml) {
            alert(JSON.stringify(response));
        }
    });
}

function clear_content()
{
    $("#content_pane").html("");
}

function try_action()
{
    var course_id = $("#course_id").val();
    var exam_id = $("#exam_id").val();
    var action = $("#action").val();

    if(course_id != null && course_id != "" && exam_id != null && exam_id != "" && action != null && action != "")
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
    var data = {form_name: "reports",
		course_id: $("#course_id").val(),
		exam_id: $("#exam_id").val(),
		action: $("#action").val()};
    $.ajax({
        type: 'post',
        dataType: 'html',
        url: 'coord_reports',
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


function get_report_csv(filename)
{
    var semester = $("#semester").val();
    var year = $("#year").val();
    var course_id_text = $("#course_id").val();
    var action = $("#action").val();
    var exam_id_text = $("#exam_id").val();
    var data = {form_name: "term_end",
		course_id: $("#course_id").val(),
		semester: $("#semester").val(),
		year: $("#year").val(),
		action: $("#action").val(),
		exam_id: $("#exam_id").val()};
    $.ajax({
        type: 'POST',
        dataType: 'html',
        url: 'get_coord_reports',
        async: false,
        data: data,
	success: download.bind(true, 'csv', filename),
        error: function(response, status, xml) {
	    alert("error");
        }
    });
}

$(document).ready(function() {
    get_exams();
});

function redirect(url, params)
{
    var form = $("<form action='" + url + "' method='POST'></form>");

    for(var key in params)
    {
        form.append($("<input type='text' name='" + key + "' value='" + params[key] + "'>"));
    }

    $("body").append(form);
    form.submit();
}

function get_exams()
{
    var data = {func_name: "get_exams",
		course_id: $("#course_id").val()};
    $.ajax({
        type: 'POST',
        dataType: 'json',
    	url: 'scantrons_ajax',
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

function get_action()
{
    perform_action($("#course_id").val(), $("#exam_id").val(), $("#action").val());
}

function try_action()
{
    var course_id = $("#course_id").val();
    var exam_id = $("#exam_id").val();
    var action = $("#action").val();

    if(course_id != null && course_id != "" && exam_id != null && exam_id != "" && action != null && action != "")
    {
        perform_action(course_id, exam_id, action);
    }

    // Redundant since action!= "" also includes action== "download_rolls"
    // if(course_id != null && course_id != "" && action != null && action == "download_rolls")
    // {
    //     perform_action(course_id, exam_id, action);
    // }
}

function get_course_rolls(filename)
{
    var course_id = $("#course_id").val();
    var exam_id = $("#exam_id").val();
    var action = $("#action").val();
    var data = {form_name: "manage_scantrons",
		course_id: course_id,
		exam_id: exam_id,
		action: action};
    $.ajax({
        type: 'POST',
        dataType: 'html',
        url: 'get_course_rolls',
        async: false,
        data: data,
	success: download.bind(true, "txt", filename),
        error: function(response, status, xml) {
	    alert("error");
	    console.log(JSON.stringify(response));
        }
    });

    // try_action();
}

function perform_action(course_id, exam_id, action)
{
    // no exam id is needed for downloading the rolls
    if(action != "download_rolls" && (exam_id == null || exam_id == ""))
    {
	if(exam_id == "")
	{
            alert("You need to select an exam for this course.");
	    return false;
	}
	if(exam_id == null)
	{
            alert("No exams available for this course.");
	    return false;
	}
    }

    var data = {form_name: "manage_scantrons",
		course_id: course_id,
		exam_id: exam_id,
		action: action};
     $.ajax({
        type: 'POST',
        dataType: 'html',
        url: 'manage_scantrons',
        async: false,
        data: data,
        success: function (response, status, xml) {
	    $("#content_pane").html(response);
        },
        error: function(response, status, xml) {
	    alert(action);
	    console.log(JSON.stringify(response));
        }
    });
}

function trim(input)
{
    return input.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
}

function submit_scantrons()
{
    $("#scantron_upload_form").submit();
}

function handle_iframe()
{
    var html = trim($("#hidden_form").contents().find("body").html());

    if(html.length > 0)
    {
        $("#content_pane").html(html);
    }
    
    return true;
}

function submit_scantrons_old()
{
    var exam_id = $("#data_exam_id").val();

    var data = {form_name: "submit_scantrons",
		exam_id: exam_id,
		data: $("#data").val()};
    $.ajax({
        type: 'POST',
        dataType: 'html',
        url: 'manage_scantrons',
        async: false,
        data: data,
        success: function (response, status, xml) {
	    $("#content_pane").html(response);
        },
        error: function(response, status, xml) {
            alert(JSON.stringify(response));
        }
    });
}

function toggle_examples()
{
    if($("#examples").is(":visible"))
    {
        $("#example_btn").html("Show Examples");
        $("#example_btn").css("border-bottom-width", "2px");
        $("#examples").hide();
    }
    else
    {
        $("#example_btn").html("Hide Examples");
        $("#example_btn").css("border-bottom-width", "0px");
        $("#examples").show();
    }
}

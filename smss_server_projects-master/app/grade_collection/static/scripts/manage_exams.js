$(document).ready(function() {
    mask_functions();
});

function mask_functions() {
    $(".date").mask("99-99-9999");
    $("#weight").mask("9.9?9?9");
};

function delete_exam(exam_id)
{
    var result = confirm("Are you sure you want to delete this exam?");

    if(result)
    {
        manage_exam_AJAX("delete_exam",exam_id, "");
    }
}

function manage_exam_AJAX(form_name, exam_id, course_id)
{
    var data = {form_name: form_name,
		exam_id: exam_id,
		course_id: course_id};
    $.ajax({
        type: 'post',
        dataType: 'html',
        url: 'manage_exams',
        async: false,
        data: data,
        success: function (response, status, xml) {
            if(response.error)
            {
                alert(response.data);
            }
            else
            {
		if (form_name=="delete_exam") {
		    window.location = "manage_exams";
		} else {
		    $("#main_content").html(response);
		    mask_functions();
		}
            }
        },
	error: function(response, status, xml) {
	    alert(JSON.stringify(status));
            alert(JSON.stringify(response));
	}
    });
}

// error: function(response, status, xml) {
            // alert(JSON.stringify(response));

// error: function(xhr, status, error) {
	    // var err = eval("(" + xhr.responseText + ")");
	    // alert(err.Message);

function submit_form(action, course_id, exam_id)
{
    var data = {action: action,
		course_id: course_id,
		exam_id: exam_id,
		title: $("#title").val(),
		date_given: $("#date_given").val(),
		grades_due: $("#grades_due").val(),
		weight: $("#weight").val()};

    $.ajax({
        type: 'post',
        dataType: 'json',
        url: 'manage_exams',
        async: false,
        data: data,
        success: function (response, status, xml) {
            if(response.error)
            {
                $("#msg").html(response.data);
            }
            else
            {
                window.location = "manage_exams";
            }
        },
        error: function(response, status, xml) {
            alert('error: '+JSON.stringify(response));
        }
    });
}

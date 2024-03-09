$(document).ready(function() {
    get_exams();
});

function get_exams()
{
    var data = {func_name: "get_exams", offer_id: $("#offer_id").val()};
    $.ajax({
        type: 'post',
        dataType: 'json',
        url: 'grades_ajax',
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
    var offer_id = $("#offer_id").val();
    var exam_id = $("#exam_id").val();
    var action = $("#action").val();

    if(offer_id != null && offer_id != "" && exam_id != null && exam_id != "" && action != null && action != "")
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
    if($("#action").val() == "original_mc")
    {
        var result = confirm("Are you sure you want to revert all responses to the original scantron data?");

        if(!result)
        {
            $("#action").val("").attr("selected", "selected");
            return false;
        }
    }

    var data = {form_name: "manage_grades", offer_id: $("#offer_id").val(), exam_id: $("#exam_id").val(), action: $("#action").val()};
    $.ajax({
        type: 'post',
        dataType: 'html',
        url: 'manage_grades',
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

function submit_responses()
{
    var data = {form_name: "submit_responses", offer_id: $("#data_offer_id").val(), exam_id: $("#data_exam_id").val(), response_type: $("#response_type").val(), data: $("#data").val()};
    $.ajax({
        type: 'post',
        dataType: 'html',
        url: 'manage_grades',
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

function save_absences()
{
    var absence_data = [];

    $("input[type=checkbox]:checked.absence_checkbox").each(function() {
        absence_data.push(this.value);
    });

    absence_data = absence_data.join(",");

    var data = {func_name: "save_absences", offer_id: $("#absence_offer_id").val(), exam_id: $("#absence_exam_id").val(), absence_data: absence_data};
    $.ajax({
        type: 'post',
        dataType: 'json',
        url: 'grades_ajax',
        async: false,
        data: data,
        success: function (response, status, xml) {
            if(response.error)
            {
                alert(response.data);
            }
            else
            {
                alert("Absences were saved.");
            }
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

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
    var data = {func_name: "get_exams", course_id: $("#course_id").val()};
    $.ajax({
        type: 'post',
        dataType: 'json',
        url: 'versions_ajax',
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
    else
    {
        clear_content();
    }
}

function perform_action(course_id, exam_id, action)
{
    if(exam_id == null || exam_id == "")
    {
        alert("You need to select an exam for this course.");
        return false;
    }

    var data = {form_name: "manage_versions", course_id: course_id, exam_id: exam_id, action: action};
    $.ajax({
        type: 'post',
        dataType: 'html',
        url: 'manage_versions',
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

function submit_version()
{
    // show that we are working on something
    $("#submit_version_btn").attr("disabled", true);
    var course_id = $("#data_course_id").val();
    var exam_id = $("#data_exam_id").val()
    var data = {func_name: "update_version", exam_id: exam_id, mc_data: $("#mc_data").val(), fr_data: $("#fr_data").val(), old_version: $("#old_version").val(), new_version: $("#new_version").val()};
    $.ajax({
        type: 'post',
        dataType: 'json',
        url: 'versions_ajax',
        async: false,
        data: data,
        success: function (response, status, xml) {
            if(response.error)
            {
                $("#msg").html(response.data);
                $(".msg").show()
                $('html, body').animate({
                    scrollTop: $("#msg").offset().top
                }, 1000);
                $("#submit_version_btn").attr("disabled", false);
            }
            else
            {
                perform_action(course_id, exam_id, "view");
            }
        },
        error: function(response, status, xml) {
            alert(JSON.stringify(response));
            $("#submit_version_btn").attr("disabled", false);
        }
    });
}

function delete_version(course_id, exam_id, version)
{
    var result = confirm("Are you sure you want to delete this version?");

    if(result)
    {
        delete_version_ajax(course_id, exam_id, version);
    }
}

function delete_version_ajax(course_id, exam_id, version)
{
    var data = {func_name: "delete_version", exam_id: exam_id, version: version};
    $.ajax({
        type: 'post',
        dataType: 'json',
        url: 'versions_ajax',
        async: false,
        data: data,
        success: function (response, status, xml) {
            if(response.error)
            {
                alert(response.data);
            }
            else
            {
                perform_action(course_id, exam_id, "view");
            }
        },
        error: function(response, status, xml) {
            alert(JSON.stringify(response));
        }
    });
}

function edit_version(course_id, exam_id, version)
{
    var data = {form_name: "manage_versions", course_id: course_id, old_version: version, exam_id: exam_id, action: "modify_version"};
    $.ajax({
        type: 'post',
        dataType: 'html',
        url: 'manage_versions',
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

function submit_key()
{
    // show that we are working on something
    $("#submit_key_btn").attr("disabled", true);
    var course_id = $("#data_course_id").val();
    var exam_id = $("#data_exam_id").val();
    var key_version = $("#key_version").val();
    var data = {func_name: "update_key", key_version: key_version, exam_id: exam_id, mc_data: $("#mc_data").val(), fr_data: $("#fr_data").val()};

    $.ajax({
        type: 'post',
        dataType: 'json',
        url: 'versions_ajax',
        async: false,
        data: data,
        success: function (response, status, xml) {
            if(response.error)
            {
                $("#submit_key_btn").attr("disabled", false);
            }
            else
            {
                perform_action(course_id, exam_id, "view");
            }
        },
        error: function(response, status, xml) {
            alert(JSON.stringify(response));
            $("#submit_key_btn").attr("disabled", false);
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

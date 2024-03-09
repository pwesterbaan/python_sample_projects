$(document).ready(function() {
    get_exams();
});

function get_offers()
{
    var data = {func_name: "get_offers", semester: $("#semester").val(), year: $("#year").val()};
    $.ajax({
        type: 'post',
        dataType: 'json',
        url: 'reports_ajax',
        async: false,
        data: data,
        success: function (response, status, xml) {
            if(response.error)
            {
                alert(JSON.stringify(response));
            }
            else
            {
                var old_offer_id = $("#offer_id").val();
                $("#offer_id").empty();

                $.each(response.data, function(index, offer) {
                    var selected = "";
                    if(offer.offer_id == old_offer_id)
                    {
                        selected = " selected"
                    }

                    var option = $("<option value=\"" + offer.offer_id + "\"" + selected + ">" + offer.prefix + " " + offer.course_num + "-" + offer.section_num + "</option>");
                    $("#offer_id").append(option);
                });
                try_action();
            }
        },
        error: function(response, status, xml) {
            alert(JSON.stringify(response));
        }
    });

    get_exams();
}

function get_exams()
{
    if($("#offer_id").val() === null)
    {
        $("#exam_id").empty();
        var option = $("<option value=\"\">-- Choose an item --</option>");
        $("#exam_id").append(option);

        return 1;
    }

    var data = {func_name: "get_exams", offer_id: $("#offer_id").val()};
    $.ajax({
        type: 'post',
        dataType: 'json',
        url: 'reports_ajax',
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
            return false;
        }
    }

    var data = {form_name: "reports", offer_id: $("#offer_id").val(), exam_id: $("#exam_id").val(), action: $("#action").val()};
    $.ajax({
        type: 'post',
        dataType: 'html',
        url: 'reports',
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


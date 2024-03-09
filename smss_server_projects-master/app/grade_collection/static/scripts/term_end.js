function clear_content()
{
    $("#content_pane").html("");
}

function try_action()
{
    var offer_id = $("#offer_id").val();
    var action = $("#action").val();

    if(offer_id != null && offer_id != "" && action != null && action != "")
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
    var data = {form_name: "term_end",
		offer_id: $("#offer_id").val(),
		action: $("#action").val()};
    $.ajax({
        type: 'post',
        dataType: 'html',
        url: 'term_end',
        async: true,
        data: data,
        success: function (response, status, xml) {
                $("#content_pane").html(response);
        },
        error: function(response, status, xml) {
            alert(response);
        }
    });
}

function submit_data()
{
    // const formData = new FormData($('form[name="'+formName+'"]')[0]);
    var data = {form_name: "submit_data",
		offer_id: $("#data_offer_id").val(),
		data: $("#data").val()};
    $.ajax({
        type: 'post',
        dataType: 'html',
        url: 'term_end',
        async: true,
        data: data,
        success: function (response, status, xml) {
            $("#content_pane").html(response);
        },
        error: function(response, status, xml) {
            alert(response);
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

function get_end_of_term_csv(filename, form_name)
{
    var data = {form_name: form_name,
		offer_id: $("#offer_id").val(),
		action: $("#action").val(),
	        filename: filename};
    $.ajax({
        type: 'POST',
        dataType: 'html',
        url: 'get_term_end',
        async: false,
        data: data,
	success: download.bind(true, "txt", filename),
        error: function(response, status, xml) {
	    alert("error");
        }
    });
}

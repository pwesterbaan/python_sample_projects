$(document).ready(function() {
});

function add_teacher() {
    username = document.getElementById("employee_username").value;

    add_str = document.getElementById("add_teachers").value;

    // Remove any instructors added from delete list
    delete_str = document.getElementById("delete_teachers").value;
    document.getElementById("delete_teachers").value=delete_str.replace(username,'');
    
    if(add_str.length > 0) {
	add_list = add_str.split(",");

	found = false;
	add_to_list=false;

	for(i = 0; i < add_list.length; ++i) {
	    if(add_list[i] == username) {
		found = true;
		break;
	    }
	}

	if(found) {
	    new_add_str = add_list.join(",");
	} else {
	    add_list.push(username)
	    new_add_str = add_list.join(",");
	    add_to_list=true;
	}
    } else {
	new_add_str = username;
	add_to_list=true;
    }

    document.getElementById("add_teachers").value = new_add_str;

    if(add_to_list) {
	document.getElementById("teacher_list container").innerHTML += "<div><img src=\"static/images/del.png\" style=\"margin-right: 5px; cursor: pointer;\" onClick=\"javascript:delete_teacher('" + username + "', this)\">" + username + "</div>";
    }
}

function clear_content() {
    $("#content_pane").html("");
}

function course_manager_AJAX(formName) {
    const formData = new FormData($('form[name="'+formName+'"]')[0]);
    if(formName.substring(0,13) == "delete_offers") {
	if(formData.get("offer_id")==-1) {
            var response = confirm("Are you sure you want to delete ALL of the courses offerings for " + formData.get("semester") + " " + formData.get("year") + "?");
	    //PW 2022-01-18: Irrelevant part of old message
	    //"\nIf anyone has uploaded a syllabus already this will break the link in the syllabus manager to their class."
	} else {
	    var response = confirm("Are you sure you want to delete this course offering?");
	}
    } else if(formName.substring(0,13) == "delete_coords") {
	if(formData.get("coord_id")==-1) {
	    var response = confirm("Are you sure you want to delete ALL of the course coordinators for " + formData.get("semester") + " " + formData.get("year") + "?");
	} else {
	    var response = confirm("Are you sure you want to delete this course coordinator?");
	}
    } else {
	//Nothing to confirm, so let response default to true;
	response = true;
    }

    if(response) {
	$.ajax({
            type: 'post',
            dataType: 'html',
            url: 'course_manager',
	    data: formData,
	    async: false,
	    contentType: false,
	    cache: false,
	    processData: false,
            success: function(response, status, xml) {
		$("#content_pane").html(response);
		flash_message();
            },
            error: function(response, status, xml) {
		alert('Failure :(');
            }
	});
    }
}

function delete_teacher(username, img) {
    del_str = document.getElementById("delete_teachers").value;

    // Remove instructor being deleted from add list 
    add_str = document.getElementById("add_teachers").value;
    document.getElementById("add_teachers").value=add_str.replace(username,'');
    
    if(del_str.length > 0) {
	del_list = del_str.split(",");
	del_list.push(username)
	new_del_str = del_list.join(",");
    } else {
	new_del_str = username;
    }

    document.getElementById("delete_teachers").value = new_del_str;

    cur_name = img.parentNode;
    cur_name.parentNode.removeChild(cur_name);
}

function flash_message() {
    if($("#info_box").html().length > 0) {
        $("#info_box").delay(5000).fadeOut(3000);
    } else {
        $("#info_box").hide();
    }
}

function perform_action() {
    var data={form_name: $("#form_name").val(),
	      semester: $("#semester").val(),
	      year: $("#year").val(),
	      action: $("#action").val()};
    $.ajax({
        type: 'post',
        dataType: 'html',
        url: 'course_manager',
        async: false,
        data: data,
        success: function (response, status, xml) {
            $("#content_pane").html(response);
	    flash_message();
        },
        error: function(response, status, xml) {
            alert(response);
        }
    });
}

function try_action() {
    var form_name = $("#form_name").val();
    var edit_offer_submit= $("#edit_offer_submit").val();

    if(form_name != null && form_name != "") {
        perform_action();
    } else {
        clear_content();
    }
}

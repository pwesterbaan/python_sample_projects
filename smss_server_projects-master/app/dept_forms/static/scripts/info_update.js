var cur_id = 0;
var changes = {};
var additions = {};
var deletions = {};

function get_new_id()
{
    return cur_id++;
}

function add_new_item(item_type, item_id)
{
    if(item_type in additions)
    {
        additions[item_type].push(item_id);
    }
    else
    {
        additions[item_type] = [item_id];
    }
    //alert(JSON.stringify(additions));
}

function delete_new_item(item_type, item_id)
{
    var added_items = additions[item_type];
    var item_index = $.inArray(item_id, added_items);
    if(item_index != -1)
    {
        added_items.splice(item_index, 1);
    }
    $("#" + item_id).remove();
    //alert(JSON.stringify(additions));
}

function delete_old_item(item_type, item_id, item_description)
{
    if(item_type in changes)
    {
        var changed_items = changes[item_type];
        var item_index = $.inArray(item_id, changed_items);
        if(item_index != -1)
        {
            changed_items.splice(item_index, 1);
        }
        //alert(JSON.stringify(changes));
    }
    
    if(item_type in deletions)
    {
        deletions[item_type].push(item_description);
    }
    else
    {
        deletions[item_type] = [item_description];
    }

    $("#" + item_id).remove();
    //alert(JSON.stringify(deletions));
}

function change_item(item_type, item_id)
{
    // if item is in additions don't flag it as changed
    if(item_type in additions)
    {
        if($.inArray(item_id, additions[item_type]) != -1)
        {
            return true;
        }
    }
    
    if(item_type in changes)
    {
        // don't add it multiple times
        if($.inArray(item_id, changes[item_type]) == -1)
        {
            changes[item_type].push(item_id);
        }
    }
    else
    {
        changes[item_type] = [item_id];
    }
   //alert(JSON.stringify(changes));
}

function generate_updates()
{
    $("#additions").val(JSON.stringify(additions));
    $("#deletions").val(JSON.stringify(deletions));
    $("#changes").val(JSON.stringify(changes));
    
    return true;
}


//==============================================
// functions to handle forms and storing edits
//==============================================

function show_add_mail_address_dialog()
{
    $("#mail_address_form").dialog({
        title: "Add Mail Address",
        autoOpen: false,
        modal: true,
        width: "auto",
        buttons: {
            "Add": function() {
                add_mail_address();
                $(this).dialog("close"); 
            }, 
            "Cancel": function() { 
                $(this).dialog("close");
            } 
        }
    });
    $("#mail_address_value").val("");
    $("#mail_address_type").val("");
    $("#mail_address_form").dialog("open");
}

function show_edit_mail_address_dialog(id)
{
    $("#mail_address_form").dialog({
        title: "Edit Mail Address",
        autoOpen: false,
        modal: true,
        width: "auto",
        buttons: {
            "Save": function() {
                change_item("mail_address", id);
                // update the hidden fields and the display
                $("#" + id + "_preview").html("[" + $("#mail_address_type").val() + "]<br>" + $("#mail_address_value").val().replace(/\n/g, "<br>"));
                
                $("#" + id + "_value").val($("#mail_address_value").val());
                $("#" + id + "_type").val($("#mail_address_type").val());
                $(this).dialog("close"); 
            }, 
            "Cancel": function() { 
                $(this).dialog("close");
            } 
        }
    });
    
    $("#mail_address_value").val($("#" + id + "_value").val());
    $("#mail_address_type").val($("#" + id + "_type").val());
    $("#mail_address_form").dialog("open");
}

function add_mail_address()
{
    var new_id = get_new_id();
    new_id = "new_mail_address_" + new_id;
    
    add_new_item("mail_address", new_id);
    
    var mail_address = $("<div id=\"" + new_id + "\" style=\"margin-bottom: 8px;\"><\/div>");
    
    var description_span = $("<div id=\"" + new_id + "_preview\"><\/div>")
    description_span.html("[" + $("#mail_address_type").val() + "]<br>" + $("#mail_address_value").val().replace(/\n/g, "<br>"));
    
    mail_address.append(description_span);
    mail_address.append(" (");
    mail_address.append($("<span onclick=\"show_edit_mail_address_dialog('" + new_id + "')\" class=\"edit_link\">edit<\/span>"));
    mail_address.append(" ");
    mail_address.append($("<span onclick=\"delete_new_item('mail_address', '" + new_id + "')\" class=\"delete_link\">delete<\/span>"));
    mail_address.append(")");
    
    // add in hidden fields to store data
    mail_address.append("<input type=\"hidden\" name=\"" + new_id + "_value\" id=\"" + new_id + "_value\" value=\"" + $("#mail_address_value").val() + "\">")
    mail_address.append("<input type=\"hidden\" name=\"" + new_id + "_type\" id=\"" + new_id + "_type\" value=\"" + $("#mail_address_type").val() + "\">")
    
    $("#mail_address_container").append(mail_address);
}

function show_add_office_dialog()
{
    $("#office_list").val("");
    $("#office_form").dialog("open");
}

function add_office()
{
    var new_id = get_new_id();
    new_id = "new_office_" + new_id
    
    add_new_item("office", new_id);
    
    var office = $("<div id=\"" + new_id + "\" style=\"margin-bottom: 8px;\"><\/div>");
    
    var description_span = $("<span style=\"margin-right: 10px;\">" + $("#office_list").val() + "<\/span>");
    
    office.append(description_span);
    office.append("(");
    office.append($("<span onclick=\"delete_new_item('office', '" + new_id + "')\" class=\"delete_link\">delete<\/span>"));
    office.append(")");
    
    // add in hidden fields to store data
    office.append("<input type=\"hidden\" name=\"" + new_id + "_description\" id=\"" + new_id + "_description\" value=\"" + $("#office_list").val() + "\">")
    
    $("#office_container").append(office);
}

function show_add_phone_number_dialog()
{
    $("#phone_number_form").dialog({
        title: "Add Phone Number",
        autoOpen: false,
        modal: true,
        width: "auto",
        buttons: {
            "Add": function() {
                add_phone_number();
                $(this).dialog("close"); 
            }, 
            "Cancel": function() { 
                $(this).dialog("close");
            } 
        }
    });
    $("#phone_number_value").val("");
    $("#phone_number_type_list").val("");
    $("#phone_number_form").dialog("open");
}

function show_edit_phone_number_dialog(id)
{
    $("#phone_number_form").dialog({
        title: "Edit Phone Number",
        autoOpen: false,
        modal: true,
        width: "auto",
        buttons: {
            "Save": function() {
                change_item("phone_number", id);
                // update the hidden fields and the display
                $("#" + id + "_preview").html($("#phone_number_value").val() + " [" + $("#phone_number_type_list").val() + "]");
                $("#" + id + "_value").val($("#phone_number_value").val());
                $("#" + id + "_type").val($("#phone_number_type_list").val());
                $(this).dialog("close"); 
            }, 
            "Cancel": function() { 
                $(this).dialog("close");
            } 
        }
    });
    
    $("#phone_number_value").val($("#" + id + "_value").val());
    $("#phone_number_type_list").val($("#" + id + "_type").val());
    $("#phone_number_form").dialog("open");
}

function add_phone_number()
{
    var new_id = get_new_id();
    new_id = "new_phone_number_" + new_id;
    
    add_new_item("phone_number", new_id);
    
    var phone_number = $("<div id=\"" + new_id + "\" style=\"margin-bottom: 8px;\"><\/div>");
    
    var description_span = $("<span id=\"" + new_id + "_preview\">" + $("#phone_number_value").val() + " [" + $("#phone_number_type_list").val() + "]<\/span>");
    
    phone_number.append(description_span);
    phone_number.append(" (");
    phone_number.append($("<span onclick=\"show_edit_phone_number_dialog('" + new_id + "')\" class=\"edit_link\">edit<\/span>"));
    phone_number.append(" ");
    phone_number.append($("<span onclick=\"delete_new_item('phone_number', '" + new_id + "')\" class=\"delete_link\">delete<\/span>"));
    phone_number.append(")");
    
    phone_number.append("<input type=\"hidden\" name=\"" + new_id + "_value\" id=\"" + new_id + "_value\" value=\"" + $("#phone_number_value").val() + "\">")
    phone_number.append("<input type=\"hidden\" name=\"" + new_id + "_type\" id=\"" + new_id + "_type\" value=\"" + $("#phone_number_type_list").val() + "\">")
    
    $("#phone_number_container").append(phone_number);
}

function show_add_education_dialog()
{
    $("#education_form").dialog({
        title: "Add Education",
        autoOpen: false,
        modal: true,
        width: "auto",
        buttons: {
            "Add": function() {
                add_education();
                $(this).dialog("close"); 
            }, 
            "Cancel": function() { 
                $(this).dialog("close");
            } 
        }
    });
    $("#education_school_name").val("");
    $("#education_semester").val("");
    $("#education_year").val("");
    $("#education_degree").val("");
    $("#education_major").val("");
    $("#education_gpa").val("");
    $("#education_form").dialog("open");
}

function show_edit_education_dialog(id)
{
    $("#education_form").dialog({
        title: "Edit Education",
        autoOpen: false,
        modal: true,
        width: "auto",
        buttons: {
            "Save": function() {
                change_item("education", id);
                // update the hidden fields and the display
                $("#" + id + "_preview").html($("#education_semester").val() + " " + $("#education_year").val() + ", " + $("#education_school_name").val() + "<br>" + $("#education_degree").val() + " - " + $("#education_major").val() + " (" + $("#education_gpa").val() + ")");
                
                $("#" + id + "_school_name").val($("#education_school_name").val());
                $("#" + id + "_semester").val($("#education_semester").val());
                $("#" + id + "_year").val($("#education_year").val());
                $("#" + id + "_degree").val($("#education_degree").val());
                $("#" + id + "_major").val($("#education_major").val());
                $("#" + id + "_gpa").val($("#education_gpa").val());
                $(this).dialog("close"); 
            }, 
            "Cancel": function() { 
                $(this).dialog("close");
            } 
        }
    });
    
    $("#education_school_name").val($("#" + id + "_school_name").val());
    $("#education_semester").val($("#" + id + "_semester").val());
    $("#education_year").val($("#" + id + "_year").val());
    $("#education_degree").val($("#" + id + "_degree").val());
    $("#education_major").val($("#" + id + "_major").val());
    $("#education_gpa").val($("#" + id + "_gpa").val());
    $("#education_form").dialog("open");
}

function add_education()
{
    var new_id = get_new_id();
    new_id = "new_education_" + new_id;
    
    add_new_item("education", new_id);
    
    var education = $("<div id=\"" + new_id + "\" style=\"margin-bottom: 8px;\"><\/div>");
    
    var description_span = $("<div id=\"" + new_id + "_preview\"><\/div>")
    description_span.html($("#education_semester").val() + " " + $("#education_year").val() + ", " + $("#education_school_name").val() + "<br>" + $("#education_degree").val() + " - " + $("#education_major").val() + " (" + $("#education_gpa").val() + ")");
    
    education.append(description_span);
    education.append(" (");
    education.append($("<span onclick=\"show_edit_education_dialog('" + new_id + "')\" class=\"edit_link\">edit<\/span>"));
    education.append(" ");
    education.append($("<span onclick=\"delete_new_item('education', '" + new_id + "')\" class=\"delete_link\">delete<\/span>"));
    education.append(")");
    
    // add in hidden fields to store data
    education.append("<input type=\"hidden\" name=\"" + new_id + "_school_name\" id=\"" + new_id + "_school_name\" value=\"" + $("#education_school_name").val() + "\">")
    education.append("<input type=\"hidden\" name=\"" + new_id + "_semester\" id=\"" + new_id + "_semester\" value=\"" + $("#education_semester").val() + "\">")
    education.append("<input type=\"hidden\" name=\"" + new_id + "_year\" id=\"" + new_id + "_year\" value=\"" + $("#education_year").val() + "\">")
    education.append("<input type=\"hidden\" name=\"" + new_id + "_degree\" id=\"" + new_id + "_degree\" value=\"" + $("#education_degree").val() + "\">")
    education.append("<input type=\"hidden\" name=\"" + new_id + "_major\" id=\"" + new_id + "_major\" value=\"" + $("#education_major").val() + "\">")
    education.append("<input type=\"hidden\" name=\"" + new_id + "_gpa\" id=\"" + new_id + "_gpa\" value=\"" + $("#education_gpa").val() + "\">")
    
    $("#education_container").append(education);
}

function show_add_position_dialog()
{
    $("#position_description").val("");
    $("#position_form").dialog("open");
}

function add_position()
{
    var new_id = get_new_id();
    new_id = "new_position_" + new_id
    
    add_new_item("position", new_id);
    
    var position = $("<div id=\"" + new_id + "\" style=\"margin-bottom: 8px;\"><\/div>");
    
    var description_span = $("<span style=\"margin-right: 10px;\">" + $("#position_description").val() + "<\/span>");
    
    position.append(description_span);
    position.append("(");
    position.append($("<span onclick=\"delete_new_item('position', '" + new_id + "')\" class=\"delete_link\">delete<\/span>"));
    position.append(")");
    
    // add in hidden fields to store data
    position.append("<input type=\"hidden\" name=\"" + new_id + "_description\" id=\"" + new_id + "_description\" value=\"" + $("#position_description").val() + "\">")
    
    $("#position_container").append(position);
}

function show_add_student_dialog()
{
    $("#student_name").val("");
    $("#student_form").dialog("open");
}

function add_student()
{
    var new_id = get_new_id();
    new_id = "new_student_" + new_id
    
    add_new_item("student", new_id);
    
    var student = $("<div id=\"" + new_id + "\" style=\"margin-bottom: 8px;\"><\/div>");
    
    var description_span = $("<span style=\"margin-right: 10px;\">" + $("#student_name").val() + "<\/span>");
    
    student.append(description_span);
    student.append("(");
    student.append($("<span onclick=\"delete_new_item('student', '" + new_id + "')\" class=\"delete_link\">delete<\/span>"));
    student.append(")");
    
    // add in hidden fields to store data
    student.append("<input type=\"hidden\" name=\"" + new_id + "_name\" id=\"" + new_id + "_name\" value=\"" + $("#student_name").val() + "\">")
    
    $("#student_container").append(student);
}

function show_add_email_dialog()
{
    $("#email_form").dialog({
        title: "Add Email",
        autoOpen: false,
        modal: true,
        width: "auto",
        buttons: {
            "Add": function() {
                add_email();
                $(this).dialog("close"); 
            }, 
            "Cancel": function() { 
                $(this).dialog("close");
            } 
        }
    });
    $("#email_value").val("");
    $("#email_type").val("");
    $("#email_form").dialog("open");
}

function show_edit_email_dialog(id)
{
    $("#email_form").dialog({
        title: "Edit Email",
        autoOpen: false,
        modal: true,
        width: "auto",
        buttons: {
            "Save": function() {
                change_item("email", id);
                // update the hidden fields and the display
                $("#" + id + "_preview").html($("#email_value").val() + " [" + $("#email_type").val() + "]");
                $("#" + id + "_value").val($("#email_value").val());
                $("#" + id + "_type").val($("#email_type").val());
                $(this).dialog("close"); 
            }, 
            "Cancel": function() { 
                $(this).dialog("close");
            } 
        }
    });
    
    $("#email_value").val($("#" + id + "_value").val());
    $("#email_type").val($("#" + id + "_type").val());
    $("#email_form").dialog("open");
}

function add_email()
{
    var new_id = get_new_id();
    new_id = "new_email_" + new_id;
    
    add_new_item("email", new_id);
    
    var email = $("<div id=\"" + new_id + "\" style=\"margin-bottom: 8px;\"><\/div>");
    
    var description_span = $("<span id=\"" + new_id + "_preview\">" + $("#email_value").val() + " [" + $("#email_type").val() + "]<\/span>");
    
    email.append(description_span);
    email.append(" (");
    email.append($("<span onclick=\"show_edit_email_dialog('" + new_id + "')\" class=\"edit_link\">edit<\/span>"));
    email.append(" ");
    email.append($("<span onclick=\"delete_new_item('email', '" + new_id + "')\" class=\"delete_link\">delete<\/span>"));
    email.append(")");
    
    email.append("<input type=\"hidden\" name=\"" + new_id + "_value\" id=\"" + new_id + "_value\" value=\"" + $("#email_value").val() + "\">")
    email.append("<input type=\"hidden\" name=\"" + new_id + "_type\" id=\"" + new_id + "_type\" value=\"" + $("#email_type").val() + "\">")
    
    $("#email_container").append(email);
}

function show_add_advisor_dialog()
{
    $("#advisor_type").val("");
    $("#advisor_name").val("");
    $("#advisor_form").dialog("open");
}

function add_advisor()
{
    var new_id = get_new_id();
    new_id = "new_advisor_" + new_id
    
    add_new_item("advisor", new_id);
    
    var advisor = $("<div id=\"" + new_id + "\" style=\"margin-bottom: 8px;\"><\/div>");
    
    var description_span = $("<span style=\"margin-right: 10px;\">" + $("#advisor_name").val() + " [" + $("#advisor_type").val() + "]<\/span>");
    
    advisor.append(description_span);
    advisor.append("(");
    advisor.append($("<span onclick=\"delete_new_item('advisor', '" + new_id + "')\" class=\"delete_link\">delete<\/span>"));
    advisor.append(")");
    
    // add in hidden fields to store data
    advisor.append("<input type=\"hidden\" name=\"" + new_id + "_name\" id=\"" + new_id + "_name\" value=\"" + $("#advisor_name").val() + "\">")
    advisor.append("<input type=\"hidden\" name=\"" + new_id + "_type\" id=\"" + new_id + "_type\" value=\"" + $("#advisor_type").val() + "\">")
    
    $("#advisor_container").append(advisor);
}

function show_add_benchmark_dialog()
{
    $("#benchmark_form").dialog({
        title: "Add Benchmark",
        autoOpen: false,
        modal: true,
        width: "auto",
        buttons: {
            "Add": function() {
                add_benchmark();
                $(this).dialog("close"); 
            }, 
            "Cancel": function() { 
                $(this).dialog("close");
            } 
        }
    });
    $("#benchmark_type").val("");
    $("#benchmark_datetime").val("");
    $("#benchmark_passed").val("did not pass");
    $("#benchmark_form").dialog("open");
}

function show_edit_benchmark_dialog(id)
{
    $("#benchmark_form").dialog({
        title: "Edit Benchmark",
        autoOpen: false,
        modal: true,
        width: "auto",
        buttons: {
            "Save": function() {
                change_item("benchmark", id);
                // update the hidden fields and the display
                $("#" + id + "_preview").html($("#benchmark_datetime").val() + " - " + $("#benchmark_type").val() + " [" + $("#benchmark_passed").val() + "]");
                
                $("#" + id + "_datetime").val($("#benchmark_datetime").val());
                $("#" + id + "_type").val($("#benchmark_type").val());
                $("#" + id + "_passed").val($("#benchmark_passed").val());
                $(this).dialog("close"); 
            }, 
            "Cancel": function() { 
                $(this).dialog("close");
            } 
        }
    });
    
    $("#benchmark_datetime").val($("#" + id + "_datetime").val());
    $("#benchmark_type").val($("#" + id + "_type").val());
    $("#benchmark_passed").val($("#" + id + "_passed").val());
    $("#benchmark_form").dialog("open");
}

function add_benchmark()
{
    var new_id = get_new_id();
    new_id = "new_benchmark_" + new_id;
    
    add_new_item("benchmark", new_id);
    
    var benchmark = $("<div id=\"" + new_id + "\" style=\"margin-bottom: 8px;\"><\/div>");
    
    var description_span = $("<span id=\"" + new_id + "_preview\">" + $("#benchmark_datetime").val() + " - " + $("#benchmark_type").val() + " [" + $("#benchmark_passed").val() + "]<\/span>");
    
    benchmark.append(description_span);
    benchmark.append(" (");
    benchmark.append($("<span onclick=\"show_edit_benchmark_dialog('" + new_id + "')\" class=\"edit_link\">edit<\/span>"));
    benchmark.append(" ");
    benchmark.append($("<span onclick=\"delete_new_item('benchmark', '" + new_id + "')\" class=\"delete_link\">delete<\/span>"));
    benchmark.append(")");
    
    benchmark.append("<input type=\"hidden\" name=\"" + new_id + "_datetime\" id=\"" + new_id + "_datetime\" value=\"" + $("#benchmark_datetime").val() + "\">")
    benchmark.append("<input type=\"hidden\" name=\"" + new_id + "_type\" id=\"" + new_id + "_type\" value=\"" + $("#benchmark_type").val() + "\">")
    benchmark.append("<input type=\"hidden\" name=\"" + new_id + "_passed\" id=\"" + new_id + "_passed\" value=\"" + $("#benchmark_passed").val() + "\">")
    
    $("#benchmark_container").append(benchmark);
}

function show_add_speak_test_dialog()
{
    $("#speak_test_form").dialog({
        title: "Add SPEAK test attempt",
        autoOpen: false,
        modal: true,
        width: "auto",
        buttons: {
            "Add": function() {
                add_speak_test();
                $(this).dialog("close"); 
            }, 
            "Cancel": function() { 
                $(this).dialog("close");
            } 
        }
    });
    $("#speak_test_datetime").val("");
    $("#speak_test_passed").val("did not pass");
    $("#speak_test_form").dialog("open");
}

function show_edit_speak_test_dialog(id)
{
    $("#speak_test_form").dialog({
        title: "Edit SPEAK test attempt",
        autoOpen: false,
        modal: true,
        width: "auto",
        buttons: {
            "Save": function() {
                change_item("speak_test", id);
                // update the hidden fields and the display
                $("#" + id + "_preview").html($("#speak_test_datetime").val() + " - " + $("#speak_test_passed").val());
                
                $("#" + id + "_datetime").val($("#speak_test_datetime").val());
                $("#" + id + "_passed").val($("#speak_test_passed").val());
                $(this).dialog("close"); 
            }, 
            "Cancel": function() { 
                $(this).dialog("close");
            } 
        }
    });
    
    $("#speak_test_datetime").val($("#" + id + "_datetime").val());
    $("#speak_test_passed").val($("#" + id + "_passed").val());
    $("#speak_test_form").dialog("open");
}

function add_speak_test()
{
    var new_id = get_new_id();
    new_id = "new_speak_test_" + new_id;
    
    add_new_item("speak_test", new_id);
    
    var speak_test = $("<div id=\"" + new_id + "\" style=\"margin-bottom: 8px;\"><\/div>");
    
    var description_span = $("<span id=\"" + new_id + "_preview\">" + $("#speak_test_datetime").val() + " - " + $("#speak_test_passed").val() + "<\/span>");
    
    speak_test.append(description_span);
    speak_test.append(" (");
    speak_test.append($("<span onclick=\"show_edit_speak_test_dialog('" + new_id + "')\" class=\"edit_link\">edit<\/span>"));
    speak_test.append(" ");
    speak_test.append($("<span onclick=\"delete_new_item('speak_test', '" + new_id + "')\" class=\"delete_link\">delete<\/span>"));
    speak_test.append(")");
    
    speak_test.append("<input type=\"hidden\" name=\"" + new_id + "_datetime\" id=\"" + new_id + "_datetime\" value=\"" + $("#speak_test_datetime").val() + "\">")
    speak_test.append("<input type=\"hidden\" name=\"" + new_id + "_passed\" id=\"" + new_id + "_passed\" value=\"" + $("#speak_test_passed").val() + "\">")
    
    $("#speak_test_container").append(speak_test);
}


//==========================================
//  setup actions once the document is loaded
//==========================================
    
$(function() {
    $( "#tabs" ).tabs();
    
    $("#office_form").dialog({
        autoOpen: false,
        modal: true,
        width: "auto",
        buttons: {
            "Add": function() {
                add_office();
                $(this).dialog("close"); 
            }, 
            "Cancel": function() { 
                $(this).dialog("close"); 
            } 
        }
    });
    
    $("#phone_number_form").dialog({
        autoOpen: false,
        modal: true,
        width: "auto",
        buttons: {
            "Add": function() {
                add_phone_number();
                $(this).dialog("close"); 
            }, 
            "Cancel": function() { 
                $(this).dialog("close"); 
            } 
        }
    });
    
    $("#education_form").dialog({
        autoOpen: false,
        modal: true,
        width: "auto",
        buttons: {
            "Add": function() {
                add_education();
                $(this).dialog("close"); 
            }, 
            "Cancel": function() { 
                $(this).dialog("close"); 
            } 
        }
    });
    
    $("#mail_address_form").dialog({
        autoOpen: false,
        modal: true,
        width: "auto",
        buttons: {
            "Add": function() {
                add_mail_address();
                $(this).dialog("close"); 
            }, 
            "Cancel": function() { 
                $(this).dialog("close"); 
            } 
        }
    });
    
    $("#position_form").dialog({
        autoOpen: false,
        modal: true,
        width: "auto",
        buttons: {
            "Add": function() {
                add_position();
                $(this).dialog("close"); 
            }, 
            "Cancel": function() { 
                $(this).dialog("close"); 
            } 
        }
    });
    
    $("#student_form").dialog({
        autoOpen: false,
        modal: true,
        width: "auto",
        buttons: {
            "Add": function() {
                add_student();
                $(this).dialog("close"); 
            }, 
            "Cancel": function() { 
                $(this).dialog("close"); 
            } 
        }
    });
    
    $("#email_form").dialog({
        autoOpen: false,
        modal: true,
        width: "auto",
        buttons: {
            "Add": function() {
                add_email();
                $(this).dialog("close"); 
            }, 
            "Cancel": function() { 
                $(this).dialog("close"); 
            } 
        }
    });
    
    $("#advisor_form").dialog({
        autoOpen: false,
        modal: true,
        width: "auto",
        buttons: {
            "Add": function() {
                add_advisor();
                $(this).dialog("close"); 
            }, 
            "Cancel": function() { 
                $(this).dialog("close"); 
            } 
        }
    });
    
    $("#benchmark_form").dialog({
        autoOpen: false,
        modal: true,
        width: "auto",
        buttons: {
            "Add": function() {
                add_benchmark();
                $(this).dialog("close"); 
            }, 
            "Cancel": function() { 
                $(this).dialog("close"); 
            } 
        }
    });
    
    $("#speak_test_form").dialog({
        autoOpen: false,
        modal: true,
        width: "auto",
        buttons: {
            "Add": function() {
                add_speak_test();
                $(this).dialog("close"); 
            }, 
            "Cancel": function() { 
                $(this).dialog("close"); 
            } 
        },
        open: function(event, ui) {
            // don't autofocus on the datepicker
            $(this).find(".datepicker").datepicker("enable");
        },
        beforeClose: function(event, ui) {
            // don't autofocus on the datepicker
            $(this).find(".datepicker").datepicker("disable");
        }
    });
   
    // input mask for phone numbers
    $(".phone_number").mask("(999) 999-9999");
    
    // datepicker
    $(".datepicker").datepicker();
    $("#speak_test_datetime").datepicker("disable");
    $(".datepicker_time").datetimepicker({
        ampm: true
    });
});
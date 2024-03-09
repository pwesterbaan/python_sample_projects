$(function(){
    $("select[name='participation']").change(update_questions);
});

function update_questions()
{
    var participation = $("select[name='participation']").val();

    if(participation == -1)
    {
        // possibly make this a validation alert
        alert("You must indicate whether or not you participated in the Bridge Program.");
    }

    // cross everything out to start with
    $(".did_participate .question").css("text-decoration", "line-through");
    $(".did_participate .options").hide();
    $(".did_not_participate .question").css("text-decoration", "line-through");
    $(".did_not_participate .options").hide();

    if(participation == 1)
    {
        // participated 
        $(".did_participate .question").css("text-decoration", "none");
        $(".did_participate .options").show();
    }
    
    if(participation == 0)
    {
        // did not participate
        $(".did_not_participate .question").css("text-decoration", "none");
        $(".did_not_participate .options").show();
    }
    
}

function validate_form()
{
    var valid = true;

    if($("select[name='description']").val() == "")
    {
        alert("Please answer question 1 about your description.");

        valid = false;
    }


    if(valid && $("select[name='participation']").val() == -1)
    {
        alert("You must indicate whether or not you participated in the Bridge Program.");
        valid = false;
    }

    if(valid)
    {
        var result = confirm("Are you sure you want to submit your responses? Answers cannot be changed once they are submitted.");

        if(result)
        {
            return true
        }
        else
        {
            return false;
        }
    }
    else
    {
        return false;
    }
}

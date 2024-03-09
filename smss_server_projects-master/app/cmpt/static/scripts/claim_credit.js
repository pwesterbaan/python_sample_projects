function check_fields()
{
    if(document.getElementById("RealName").value.length == 0)
    {
	alert("Please fill out your full name.");
	return false;
    }

    if(document.getElementById("XID").value.length == 0)
    {
	alert("Please fill out your XID.");
	return false;
    }

    if(document.getElementById("CreditCourse").value == "0")
    {
	alert("Please choose the course that you are claiming credit for.");
	return false;
    }

    var credit_type = document.claimed_credit_form.CreditType;
    if((credit_type[0].checked || credit_type[1].checked || credit_type[2].checked) == false)
    {
	alert("Please choose a transfer credit type.");
	return false;
    }

    if(document.getElementById("Details").value == "")
    {
	alert("Please enter the transfer credit details.");
	return false;
    }

    return true;
}

function set_approval(approval,username,cohort,test_number)
{
    $.post('ajax-set-approval.php', {set_cmpt_approval: "true", approval: approval, username:username, cohort:cohort, test_number:test_number},function(data,status){
	if (status == "success")
	{
	    if(data == 'accepted' || data == 'denied' || data == '')
	    {
		$('td#'+username+'-'+cohort+'-'+test_number).html(data);
	    }
	    else
	    {
		alert('Error updating approval status:'+data);
	    }
	}
	else
	{
	    alert('Error updating approval status:'+data);
	}
    });
    
}

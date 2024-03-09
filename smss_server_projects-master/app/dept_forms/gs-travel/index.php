<?php

include('gs-travel-functions.php');

if (isset($_POST['submit_funding_request']))
{
	//save POST
	$details = $_POST;
	unset($details['submit_funding_request']);
	unset($details['name']);
	unset($details['user_id']);
	unset($details['advisor']);
	$details['departure_date'] = date("Y-m-d", strtotime($details['departure_date']));
	$details['return_date'] = date("Y-m-d", strtotime($details['return_date']));
	//calculate fiscal year
	if (date("n", strtotime($details['departure_date'])) < 7)
	{$details['fy']=date("y", strtotime($details['departure_date']))-1;}
	else
	{$details['fy']=date("y", strtotime($details['departure_date']));}

	//store details
	$save_query = $mthsc_db->prepare('INSERT INTO gs_travel_funding (student_person_id,advisor_person_id,conference_name,participation_type,location_city,location_state_country,departure_date,return_date,fy,registration,transportation_origin,airfare,transportation_destination,personal_vehicle,lodging,parking,per_diem,cgsg_requested,cgsg_secured,conference_sponsors_requested,conference_sponsors_secured,faculty_grant_requested,faculty_grant_secured,other_funding_requested,other_funding_secured,total_request) VALUES (:student_person_id,:advisor_person_id,:conference_name,:participation_type,:location_city,:location_state_country,:departure_date,:return_date,:fy,:registration,:transportation_origin,:airfare,:transportation_destination,:personal_vehicle,:lodging,:parking,:per_diem,:cgsg_requested,:cgsg_secured,:conference_sponsors_requested,:conference_sponsors_secured,:faculty_grant_requested,:faculty_grant_secured,:other_funding_requested,:other_funding_secured,:total_request)');
	$result = $save_query->execute($details);
	if ($result)
	{
		$request_id = $mthsc_db->lastInsertId();
		$message = "Your request has been received and routed to your advisor for pre-approval.";
		//email advisor
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
		$headers .= "From: 'Math Science Grad Studies' <mthgrad@clemson.edu>\r\n";
		$subject = "Approval Required: Grad Student Travel Funding Request";
		$email = "<html><body>";
		$email .= "<p>".$_POST['name']." has submitted a travel funding request to the school. As their advisor, your approval is necessary for the request to be submitted to the school. Please follow this link to approve or dismiss this request:</p>";
		$email .= "<p>https://mthsc.clemson.edu/dept_forms/gs-travel/advisor-approval.php?request=".$request_id."</p>";
		$email .= "<p>Thank you,<br>Graduate Coordinator</p>";
		$email .= "</body></html>";
		
		$advisor_username = get_username_from_person_id($details['advisor_person_id']);
		
		mail ($advisor_username.'@clemson.edu', $subject, $email, $headers); //email user
	}
}

//get previous requests
$person = get_person_info_from_user_id($user_id);
$requests_query = $mthsc_db->prepare('SELECT * FROM gs_travel_funding JOIN dept_info.person ON student_person_id = person.person_id WHERE student_person_id = ? ORDER BY departure_date DESC');
$requests_query->execute(array($person['person_id']));
$requests = $requests_query->fetchAll();

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Grad Student Travel Funding Request</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2018-6-21 -->

<link rel="stylesheet" href="/style/math.css" type="text/css" media="screen" title="no title" charset="utf-8">

<style type="text/css">
.text-center {text-align:center;}

</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
$(document).ready(function() 
{
		
	
});
</script>

</head>
<body>
	<div id="main">
		<div id="header">
			<a href="http://www.clemson.edu/math" title="School Home">
				<img src="/style/math_logo.png" alt="school logo">
			</a>
			<h1><a href="index.php">GS Travel Funding Requests</a></h1>
		</div>
	
		<div id="content">
			<?php echo isset($message) ? '<p id="error">'.$message.'</p>' : ''; ?>
			<h2>Guidelines for Travel Funding Requests</h2>
			<p><ol>
					<li>Please submit the online form well in advance for approval before making travel arrangements. We recommend to submit the online form at least 4-6 weeks in advance for domestic travel and 8-12 weeks in advance for international travel.</li>
					<li>Before filling out the form, please discuss your plan with your advisor and get input about how to seek travel funding in general for example applying for funds from the conference, Clemson Graduate Student Government (GSG)-Professional Enrichment Grant (PEG), other funding source such as your advisor's grant etc.  Learning how to apply for travel funds is a really important skill for graduate students.</li>
					<li>Funding is limited. School funds are best utilized when matched by other means of support (e.g. advisor’s grant, CGSG-PEG funding, funding provided by conference sponsors or other organizations). Priority is given for travel to a meeting at which the student is giving a research presentation and also based on nearness to completion of a graduate degree (and therefore seeking employment).</li>
					<li>Teaching assistants are responsible for making sure their duties are covered. You will need to complete and submit the <a href="https://mthsc.clemson.edu/dept_forms/missed-class.php">missed class notification form</a> as well.</li>
					<li>After securing funding, you will also need to fill out the <a href="https://science.clemson.edu/forms/domestic-travel/index.php">College of Science Domestic Travel form</a> at least 5 days before traveling.</li>
					<li>International travel requires special pre-approval that could take up to 8 weeks. The college international travel form can be found here: <a href="https://science.clemson.edu/forms/international-travel/index.php">College of Science International Travel Form</a>. Funding needs to be secured before filling out the international travel form.</li>
				</ol>
			</p>
			
		<!--
			<p>Beginning December 23, 2022, Concur is used for all travel requests.</strong> For more information visit the procurement page here: <a href="https://www.clemson.edu/procurement/concurtravel/">https://www.clemson.edu/procurement/concurtravel/</a>.</p>
		-->
			
			<br>
			<h3><a href="funding-request-form.php">Submit a request for travel funding</a></h3>
			<br>
			
			<h3>Previous Requests</h3>
			<table>
				<tr>
					<th scope="col">View Request</th>
					<th scope="col">Status</th>
					<th scope="col">Conference</th>
					<th scope="col">Location</th>
					<th scope="col">Dates</th>
					<th scope="col">Requested Amount</th>
					<th scope="col">Approved Amount</th>
				</tr>
			<?php if(count($requests)==0): ?>
				<tr><td class="text-center" colspan="7">No previous requests</td></tr>
			<?php endif; ?>
			<?php foreach ($requests as $request): ?>
				<tr>
					<td class="text-center"><a href="view-request.php?id=<?php echo $request['request_id'];?>">View</a></td>
					<td class="text-center"><?php if ($request['advisor_approval_timestamp'] == '0000-00-00 00:00:00'){echo 'Awaiting Advisor';} else if ($request['advisor_approval']==0){echo 'Advisor Denied';} else if ($request['request_approval_timestamp'] !== '0000-00-00 00:00:00'){echo $request['request_approval'] ? 'Approved':'Denied';}else{echo 'Awaiting School Approval';} ?></td>
					<td><?php echo $request['conference_name']; ?></td>
					<td><?php echo $request['location_city'].', '.$request['location_state_country']; ?></td>
					<td class="text-center"><?php echo date("F j, Y", strtotime($request['departure_date'])).' - '.date("F j, Y", strtotime($request['return_date'])); ?></td>
					<td class="text-center"><?php echo $request['total_request']; ?></td>
					
					<td class="text-center"><?php echo $request['request_approval'] && $request['request_approval_timestamp'] !== '0000-00-00 00:00:00' ? $request['amount_approved'] : ''; ?></td>
					
				</tr>
			<?php endforeach; ?>
			</table>
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
</body>
</html>
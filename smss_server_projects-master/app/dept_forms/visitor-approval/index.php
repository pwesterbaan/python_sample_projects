<?php

include('visitor-approval-functions.php');

if (isset($_POST['submit_approval_form']))
{
	//capture data
	$new_request = $_POST;
	unset($new_request['submit_approval_form']);
	
	$new_request['visit_arrival_date'] = date("Y-m-d",strtotime($new_request['visit_arrival_date']));
	$new_request['visit_departure_date'] = date("Y-m-d",strtotime($new_request['visit_departure_date']));
	$new_request['travel_arrival_date'] = date("Y-m-d",strtotime($new_request['travel_arrival_date']));
	$new_request['travel_departure_date'] = date("Y-m-d",strtotime($new_request['travel_departure_date']));
	$new_request['parking_arrival_date'] = date("Y-m-d",strtotime($new_request['parking_arrival_date']));
	$new_request['parking_departure_date'] = date("Y-m-d",strtotime($new_request['parking_departure_date']));
	
	$fields = array("username","visitor_name","visitor_affiliation","visitor_email","visitor_phone","last_four","residency","visa_type","visit_arrival_date","visit_departure_date","purpose_seminar_speaker","purpose_colloquium_speaker","purpose_collaborative_research","purpose_prospective_student","purpose_recruiting","purpose_potential_donor","purpose_other_purpose","seminar","other_visit_type","expenses_travel_lodging","expenses_parking","mileage","rental_car","shuttle","meals","airfare_directbill","airfare_reimbursement","lodging_directbill","lodging_reimbursement","travel_arrival_date","travel_departure_date","travel_amount","travel_account","parking_arrival_date","parking_departure_date","parking_account","visitor_office","visitor_office_dates","refreshments","refreshments_datetime","other_request_comments");
	
	foreach ($fields as $field)
	{
		if (!isset($new_request[$field]))
		{
			$new_request[$field] = 0;
		}
	}
	
	//echo '<pre>';print_r($new_request);echo '</pre>';
	
	$insert_query = $mthsc_db->prepare('INSERT INTO visitor_approval (username,visitor_name,visitor_affiliation,visitor_email,visitor_phone,last_four,residency,visa_type,visit_arrival_date,visit_departure_date,purpose_seminar_speaker,purpose_colloquium_speaker,purpose_collaborative_research,purpose_prospective_student,purpose_recruiting,purpose_potential_donor,purpose_other_purpose,seminar,other_visit_type,expenses_travel_lodging,expenses_parking,mileage,rental_car,shuttle,meals,airfare_directbill,airfare_reimbursement,lodging_directbill,lodging_reimbursement,travel_arrival_date,travel_departure_date,travel_amount,travel_account,parking_arrival_date,parking_departure_date,parking_account,visitor_office,visitor_office_dates,refreshments,refreshments_datetime,other_request_comments) VALUES (:username,:visitor_name,:visitor_affiliation,:visitor_email,:visitor_phone,:last_four,:residency,:visa_type,:visit_arrival_date,:visit_departure_date,:purpose_seminar_speaker,:purpose_colloquium_speaker,:purpose_collaborative_research,:purpose_prospective_student,:purpose_recruiting,:purpose_potential_donor,:purpose_other_purpose,:seminar,:other_visit_type,:expenses_travel_lodging,:expenses_parking,:mileage,:rental_car,:shuttle,:meals,:airfare_directbill,:airfare_reimbursement,:lodging_directbill,:lodging_reimbursement,:travel_arrival_date,:travel_departure_date,:travel_amount,:travel_account,:parking_arrival_date,:parking_departure_date,:parking_account,:visitor_office,:visitor_office_dates,:refreshments,:refreshments_datetime,:other_request_comments);');
	$result = $insert_query->execute($new_request);
	
	if ($result)
	{
		//get insert id
		$new_request_id = $mthsc_db->lastInsertId();
		
		//send email
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
		$headers .= "From: '".$fullName."' <".$user_id."@clemson.edu>\r\n";
		$subject = "Visitor Approval Request";
		$email_body = '<html><body><p>Visitor Approval Request from</p> <h2>'.$fullName.'</h2>';
		$email_body .= '<p><strong>Visitor Info</strong></p>';
		$email_body .= '<p>'.$new_request['visitor_name'].'<br>';
		$email_body .= $new_request['visitor_affiliation'].'<br>';
		$email_body .= $new_request['visitor_email'].'</p>';
		$email_body .= '<p><strong>Dates of Visit</strong></p>';
		$email_body .= '<p>'.date("M j, Y",strtotime($new_request['visit_arrival_date'])).' - '.date("M j, Y",strtotime($new_request['visit_departure_date'])).'</p>';
		$email_body .= '<p><strong>Purpose of visit:</strong></p>';
		$email_body .= '<ul>';
		if ($new_request['purpose_seminar_speaker']){$email_body .= '<li>Invited Speaker for Research Seminar ('.$new_request['seminar'].')</li>';}
		if ($new_request['purpose_colloquium_speaker']){$email_body .= '<li>Invited Speaker for School Colloquium</li>';}
		if ($new_request['purpose_collaborative_research']){$email_body .= '<li>Collaborative Research</li>';}
		if ($new_request['purpose_prospective_student']){$email_body .= '<li>Prospective student</li>';}
		if ($new_request['purpose_recruiting']){$email_body .= '<li>Employment Opportunities (Recruiting)</li>';}
		if ($new_request['purpose_potential_donor']){$email_body .= '<li>Potential Donor</li>';}
		if ($new_request['purpose_other_purpose']){$email_body .= '<li>Other Type of Visitor: '.$new_request['other_visit_type'].'</li>';}
		$email_body .= '</ul>';
		
		$email_body .= '<p><a href="https://mthsc.clemson.edu/dept_forms/visitor-approval/view-request.php?id='.$new_request_id.'">View the Full Request</a></p>';
		$email_body .= '</body></html>';
		
		mail (implode($notification_list,','), $subject, $email_body, $headers);
		
		
		$message = "Request Received";
	}
	else
	{
		$error = "Something went wrong, your request was not saved";
	}
}

//get users's requests
$get_requests_query = $mthsc_db->prepare("SELECT * FROM visitor_approval WHERE username = ? ORDER BY submitted DESC;");
$get_requests_query->execute(array($user_id));
$requests = $get_requests_query->fetchAll();

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">


<html lang="en-US">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="author" content="hedetni">
	<!-- Date: 2019-5-21 -->
	
	<title>School Forms | Visitor Approval Requests</title>

<link rel="stylesheet" href="/style/math-internal.css" type="text/css" media="screen" charset="utf-8">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<style type="text/css">
p.leader {font-weight:bold;margin-top:1.5em;}
.indent {margin-left:1em;}
#more_parking,#more_travel,#more_honorarium,#account_section {display:none;margin-left:2em;background-color:#efefef;border:1px solid #ddd;}
</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="/style/jquery.validate.js"></script>
<script src="/style/validate-additional-methods.js"></script>

<script type="text/javascript">
$(document).ready(function(){

}); 
</script>

</head>

<body>
	<div id="main">
		<div id="header">
			<div id="app_title">School Forms</div>
			<a href="http://www.clemson.edu/math" title="School Home"><img src="/style/math_logo.png" alt="school logo"></a>
		</div>
		<ul id="nav">
			<?php echo get_nav(); ?>
		</ul>
		<?php echo isset($message) ? '<div id="message">'.$message.'</div>' : '' ?>
		<?php echo isset($error) ? '<div id="error">'.$error.'</div>' : '' ?>
		
		<div id="content">
			<h1>My Visitor Approval Requests</h1>
			
			<?php if (count($requests) > 0): ?>
				<table class="styled">
					<thead>
						<tr>
							<th scope="col">Visitor</th>
							<th scope="col">Affiliation</th>
							<th scope="col">Visit Dates</th>
							<th scope="col">Submitted</th>
							<th scope="col">Full Request</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($requests as $request): ?>
							<tr>
								<td><?php echo $request['visitor_name']; ?></td>
								<td><?php echo $request['visitor_affiliation']; ?></td>
								<td><?php echo date("M j, Y",strtotime($request['visit_arrival_date'])).' - '.date("M j, Y",strtotime($request['visit_departure_date'])); ?></td>
								<td><?php echo date("F j, Y",strtotime($request['submitted'])); ?></td>
								<td><a href="view-request.php?id=<?php echo $request['request_id'];?>">View Full Request</a></td>
							</tr>
						<?php endforeach;?>
					</tbody>
				</table>
			<?php else:?>
				<p>No requests submitted yet</p>
			<?php endif; ?>
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>
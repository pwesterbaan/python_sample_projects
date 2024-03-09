<?php

include('visitor-approval-functions.php');


?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">


<html lang="en-US">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="author" content="hedetni">
	<!-- Date: 2019-6-13 -->
	
	<title>School Forms | Visitor Approval Policy</title>

<link rel="stylesheet" href="/style/math-internal.css" type="text/css" media="screen" charset="utf-8">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<style type="text/css">
p.leader {font-weight:bold;margin-top:1.5em;}
.indent {margin-left:1em;}

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
			<p>To submit a visitor request, read and understand the visitor approval policy, then click the button at the bottom of the page to access the visitor approval form.</p>
			
			<h1>Visitor Approval Policy</h1>
			
			<h2>Prior to Visit</h2>
			
			<p>Prior to inviting a visitor or seminar/colloquium speaker to visit campus, hosting faculty member must:</p>
			
			<ol>
				<li>Know if the visitor is a US citizen/permanent resident or is a foreign national</li>
				<li>Complete and submit a Visitor Approval Form for approval by Department Chair/Director. For Colloquium/Seminar Speakers, expenses must be covered by the allocations to the sub-faculties and colloquium facilitator. No additional funding is expected.</li>
				<li>Because of the no PO no Pay policy - it is necessary to list a dollar amount for travel expenses not to exceed on the form.</li>
			</ol>
				
			<p>Once the visitor is approved, foreign nationals will need to register as vendors to receive reimbursement.</p>
			
			<ol>
				<li>The foreign national visitor will receive an e-mail from Procurement (SciQuest) inviting them to complete the vendor registration process.</li>
				<li>The registration process MUST be completed prior to direct billing airfare or lodging arrangements being made. Reimbursement (any non-payroll payment) from Clemson University will not be made until registration is completed.</li>
				
			</ol>
			
			<p>Hosting faculty member is responsible for coordinating with staff members for the following:</p>
			
			<p>Lynn Callahan or April Haynes:
			<ul>
				<li>Visitor Approval Form</li>
				<li>Visitor Office Reservation</li>
				<li>Departmental/University Calendar</li>
				<li>Parking Passes $7/day</li>
				<li>Direct Bill Lodging*</li>
				<li>Direct Bill Shuttle*</li>
				<li>Refreshments</li>
				<li>Colloquium Announcement</li>
			</ul>
			* Travel Information will need to be supplied (arrival/departure times along with airline and flight numbers)
			</p>
			
			<p>Keshia Kelly:</p>
			<ul>
				<li>Non-Employee Visitor/Guest Travel Reimbursement Form</l>
			</ul>
			<h2>During Visit</h2>
			
			<p>Visitors obtain the <a href="http://www.clemson.edu/procurement/how-to-buy-pay/goods-services/Guest%20Visitor%20Reimbursement.html">Non-Employee Visitor/Guest Travel Reimbursement Form</a></p>
			
			<h2>After Visit</h2>
			
			<p>Visitor will complete the <strong>Non-Employee Visitor/Guest Travel Reimbursement Form</strong> and submit the form along with original receipts for airfare, shuttle, taxi, or rail expenses or lodging if not directly billed to our department. These can be printed and mailed to the department or scanned and emailed to Keshia Kelly (keshias@clemson.edu).</p>
			<br>
			<h2>Submit Visitor Request</h2>
			
			<p>Acknowledge your understanding of the visitor policy by clicking the button below to access the visitor approval form</p>
			
			<form name="policy_acknowledgement_form" method="POST" action="form.php<?php if (isset($_GET['speaker_id'])){echo '?speaker_id='.$_GET['speaker_id'];} ?>">
				<input type="submit" name="policy_acknowledge" value="Submit Visitor Approval Form"></input>
			</form>
			
			<br>
			<p><small>Revised 03/06/2019</small></p>
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>
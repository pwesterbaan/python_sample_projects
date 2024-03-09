<?php
include('fpci-functions.php');


?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">


<html lang="en-US">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="author" content="hedetni">
	<!-- Date: 2019-1-11 -->
	
	<title>FPCI DEMO | Home</title>

<link rel="stylesheet" href="/style/math-internal.css" type="text/css" media="screen" charset="utf-8">

<style type="text/css">
td:not(:first-child) {text-align:center;}

</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>


<script type="text/javascript">
$(document).ready(function(){
	
	
}); 
</script>

</head>

<body>
	<div id="main">
		<div id="header">
			<div id="app_title">FPCI DEMO</div>
			<a href="http://www.clemson.edu/math" title="School Home"><img src="/style/math_logo.png" alt="school logo"></a>
		</div>
		<ul id="nav">
			<?php echo get_nav(); ?>
		</ul>
		<?php echo isset($message) ? '<div id="message">'.$message.'</div>' : '' ?>
		<?php echo isset($error) ? '<div id="error">'.$error.'</div>' : '' ?>
		
		<div id="content">
			<h1>Faculty Performance and Compensation Initiative</h1>
			
			<p>Feedback from faculty through MSC representatives indicates that the preferred performance categories are <u>Teaching</u>, <u>Research</u>, and <u>Service</u>, (without Administration as a separate category – include administrative duties in Service).</p>
			
			<p>MSC discussion resulted in these draft guidelines for percentages (with consideration given on a case by case basis):</p>
			
			<table class="styled">
				<tr>
					<th scope="col">Role</th>
					<th scope="col">Teaching</th>
					<th scope="col">Research</th>
					<th scope="col">Service</th>
				</tr>
				<tr>
					<td>Assistant Professors</td>
					<td>40</td>
					<td>55</td>
					<td>5</td>
				</tr>
				<tr>
					<td>Associate Professors</td>
					<td>40</td>
					<td>45</td>
					<td>15</td>
				</tr>
				<tr>
					<td>Professors</td>
					<td>40</td>
					<td>40</td>
					<td>20</td>
				</tr>
				<tr>
					<td>Non-tenure-track typical</td>
					<td>100</td>
					<td>0</td>
					<td>0</td>
				</tr>
				<tr>
					<td>Non-tenure-track course coordinators</td>
					<td>75</td>
					<td>0</td>
					<td>25</td>
				</tr>
			</table>
			
			<p>The FAS Goals website requires entering percentage effort in 10 areas:</p>
			<ul>
				<li>Coursework</li>
				<li>Other Instructional Activities</li>
				<li>Administrative Duties and Elected Offices</li>
				<li>University Sponsored Public Service</li>
				<li>Librarianship</li>
				<li>Research and Scholarship</li>
				<li>Student Advising/Honors and Graduate Committees</li>
				<li>Committees</li>
				<li>Professional Service and Professional Development</li>
				<li>Personal Community Service and Personal Development</li>
			</ul>
			
			<p>With input from MSC, a mapping from FAS to FP&CI categories has been set up:</p>
			<ul>
				<li>Teaching</li>
				<ul>
					<li>Coursework</li>
					<li>Other Instructional Activities</li>
				</ul>
				<li>Research</li>
				<ul>
					<li>Research and Scholarship</li>
					<li>Student Research Advising</li>
				</ul>
				<li>Service</li>
				<ul>
					<li>Student Curriculum Advising</li>
					<li>Committees (including Graduate Committees)</li>
					<li>University Sponsored Public Service</li>
					<li>Professional Service and Professional Development</li>
					<li>Personal Community Service and Personal Development</li>
					<li>Administrative Duties and Elected Offices</li>
					<li>Librarianship </li>
				</ul>
			</ul>
			
			<p>Individual exceptions can be made – for example serving on a graduate committee without being the major advisor, yet providing significant input to the student’s research so that the effort could be included in Research rather than Service.</p>
			
			<p><a href="https://mthsc.clemson.edu/dept_forms/fpci/activity-percentages.php">Enter your activity percentages</a></p>
			
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>
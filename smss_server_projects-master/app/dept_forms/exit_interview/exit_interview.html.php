<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Math Sciences Exit Questionnaire</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2015-04-29 -->
	
<link rel="stylesheet" href="exit_interview_style.css" type="text/css" media="screen" title="no title" charset="utf-8">
<link rel="shortcut icon" href="/favicon.ico">

<script src="/style/jquery-1.3.min.js" type="text/javascript" charset="utf-8"></script>


<script>
$(document).ready(function(){
$('#ba').click(
	function(){
		$('#undergrad_questions').show();
		$('#grad_questions').hide();
		$('#grad_concentration_area').hide();
		$('#undergrad_concentration_area').hide();
		$('#minor').show();
		
	}
);

$('#bs').click(
	function(){
    	$('#minor').show();
		$('#undergrad_questions').show();
		$('#undergrad_concentration_area').show();
		$('#grad_questions').hide();
		$('#grad_concentration_area').hide();
	}
);

$('#ba-math-seced').click(
	function(){
		$('#undergrad_questions').show();
		$('#grad_questions').hide();
		$('#grad_concentration_area').hide();
		$('#undergrad_concentration_area').hide();
		$('#minor').show();
		
	}
);

$('#abroad-yes').click(
	function(){
    	$('#abroad-further').show();
	}
);
$('#abroad-no').click(
	function(){
    	$('#abroad-further').hide();
	}
);

$('.grad').click(
	function(){
    	$('#grad_questions').show();
		$('#grad_concentration_area').show();
		$('#undergrad_questions').hide();
		$('#undergrad_concentration_area').hide();
		$('#minor').hide();
	}
);
});
</script>
</head>
<body>
<center>
	
	<img src="/style/math_logo_color_large.png" height="100px" alt="Mathsci">
	<h2>Math Sciences Exit Questionnaire</h2>
	
</center>
<form id="questionnaire" action="index.php" method="post">
	<p>As a part of the assessment of our programs, we would like to find out about your experience with the Department of Mathematical Sciences.</p>
	
	<p class="question">Student ID Number (CUID): <input name="CUID" id="cuid" size="10" type="text"></input>
		<input type="hidden" name="userID" value="<?php echo $_SERVER['REMOTE_USER'];?>"></p>

	<p class="question">Degree Program:</br>
	<input type="radio" name="program" id="ba" value="BA">
		<label>BA</label><br />
	<input type="radio" name="program" id="ba-math-seced" value="BA-MATH-SECED">
		<label>BA-MATH/SECED Double Major</label><br />
	<input type="radio" name="program" id="bs" value="BS">
		<label>BS</label><br />
	<input type="radio" name="program" id="ms" class="grad" value="MS">
		<label>MS</label><br />
	<input type="radio" name="program" id="phd" class="grad" value="PhD">
		<label>PhD</label><br />
	</p>
	
	<p class="question" id="grad_concentration_area">Area of Concentration:</br>
		<select name="grad_concentration">
			<option value="">Select...</option>
			<option value="Algebra, Discrete Mathematics and Number Theory">Algebra, Discrete Mathematics and Number Theory</option>
			<option value="Pure and Applied Analysis">Pure and Applied Analysis</option>
			<option value="Computational Mathematics">Computational Mathematics</option>
			<option value="Mathematical Biology">Mathematical Biology</option>
			<option value="Operations Research">Operations Research</option>
			<option value="Statistics and Probability">Statistics and Probability</option>
			<option value="Applied Statistics">Applied Statistics</option>
		</select>
	</p>
	
	<p class="question" id="undergrad_concentration_area">Area of Concentration:</br>
		<select name="undergrad_concentration">
			<option value="">Select...</option>
			<option value="Abstract Math">Abstract Math</option>
			<option value="Actuarial Science/Financial Math">Actuarial Science/Financial Math</option>
			<option value="Applied/Computational Mathematics">Applied/Computational Mathematics</option>
			<option value="Mathematical Biology">Mathematical Biology</option>
			<option value="Operations Research/Management Science">Operations Research/Management Science</option>
			<option value="Statistics">Statistics</option>
			<option value="Computer Science">Computer Science</option>
		</select>
	</p>
	
	<p class="question" id="minor">Minor: <input name="minor" size="10" type="text"></input>
	
	<p class="question">Reason for leaving:</br>
		<input type="radio" name="reason" value="Degree Completion">
			<label>Degree Completion</label><br />
		<input type="radio" name="reason" value="Changing academic programs at Clemson University">
			<label>Changing academic programs at Clemson University</label><br />
		<input type="radio" name="reason" value="Pursuing academic program at another institution<">
			<label>Pursuing academic program at another institution</label><br />
		<input type="radio" name="reason" value="Job opportunity">
			<label>Job opportunity</label><br />
		<input type="radio" name="reason" value="Other">
			<label>Other</label><br />
			
			
	<p><b>Please answer the following questions about your time at Clemson</b></p>
	
	<p class="question">1. State the names of any instructor in the Department of Mathematical Sciences whom you feel did an exceptional job in contributing to your education at Clemson University. Please explain why you named each individual.</p>
	<textarea name="q1" cols="80" rows="6"></textarea>
	
	<p class="question">2. State the names of any instructors in the Department of Mathematical Sciences whom you feel did a less-than-adequate job in contributing to your education. Please explain why and comment on how you feel these individuals can improve upon their performances.</p>
	<textarea name="q2" cols="80" rows="6"></textarea>
	
	<p class="question">3. What aspects of the academic program did you find especially educational and rewarding, and what aspects did you find lacking? Please explain.</p>
	<textarea name="q3" cols="80" rows="6"></textarea>
	
	<p class="question">4. Please make any additional comments which you feel could be useful in improving the educational process in the Department of Mathematical Sciences.</p>
	<textarea name="q4" cols="80" rows="6"></textarea>
	
	<p class="question">5. If you have obtained a position of employment, give the name and location of the company that you are going to work for, a brief description of what you will be doing and your title (if you know). Additionally, and this is optional, we would like to know your starting salary. </p>
	<textarea name="q5" cols="80" rows="6"></textarea>
	
	<p class="question">6. While enrolled at Clemson, did you study abroad?</br>
		<div style="margin-left:30px">
			<input type="radio" name="abroad" id="abroad-yes" value="Yes">
				<label>Yes</label><br />
			<input type="radio" name="abroad" id="abroad-no" value="No">
				<label>No</label><br />
		</div>
	</p>
	
	<div  style="margin-left:15px;" id="abroad-further">
	<p class="question">6a. Where did you study and what courses did you take?</p>
	<textarea name="q6a" cols="60" rows="6"></textarea>
	
	<p class="question">6b. What organization were you sponsored by, if any?</p>
	<textarea name="q6b" cols="60" rows="6"></textarea>
	</div>
	
	<p class="question">7. While you were at Clemson, did you participate in any of these experiential education programs? (Check all that apply)</br> 
		<div style="margin-left:30px">
		<input type="checkbox" name="experience[]" value="Internship"><label>Internship</label></br>
		<input type="checkbox" name="experience[]" value="REU"><label>REU</label></br>
		<input type="checkbox" name="experience[]" value="Student Teaching"><label>Student Teaching</label></br>
		<input type="checkbox" name="experience[]" value="None"><label>None of These</label>
		</div>
	</p>
	
	<div  style="margin-left:15px;">
	<p class="question">If so, where and when did you participate?</p>
	<textarea name="q7b" cols="60" rows="6"></textarea>
	</div>
	
	<p class="question">8. How likely are you to recommend our program to other students? 
		<select name="recommend" style="margin-left:10px;">
			<option value="">Select...</option>
			<option value="Very Likely">Very Likely</option>
			<option value="Likely">Likely</option>
			<option value="Neutral">Neutral</option>
			<option value="Unlikely">Unlikely</option>
			<option value="Very Unlikely">Very Unlikely</option>
		</select>
	</p>
	<div style="margin-left:15px;">
	<p class="question">Why did you select this?</p>
	<textarea name="q8" cols="80" rows="6"></textarea>
	</div>
	
	<p class="question">9. Please rate the following aspects of our program:</p>
	<table style="border: 0px outset gray;border-collapse:collapse;">
		<tr><td>a. Overall experience</td>
			<td><input type="radio" name="q9a" value="Highly Unsatisfactory"><label>Highly Unsatisfactory</label></td>
			<td><input type="radio" name="q9a" value="Unsatisfactory"><label>Unsatisfactory</label></td>
			<td><input type="radio" name="q9a" value="Satisfactory"><label>Satisfactory</label></td>
			<td><input type="radio" name="q9a" value="Highly Satisfactory"><label>Highly Satisfactory</label></td></tr>
		
		<tr><td>b. Training appropriate to current/future occupation</td>
			<td><input type="radio" name="q9b" value="Highly Unsatisfactory"><label>Highly Unsatisfactory</label></td>
			<td><input type="radio" name="q9b" value="Unsatisfactory"><label>Unsatisfactory</label></td>
			<td><input type="radio" name="q9b" value="Satisfactory"><label>Satisfactory</label></td>
			<td><input type="radio" name="q9b" value="Highly Satisfactory"><label>Highly Satisfactory</label></td></tr>
		
		<tr><td>c. Faculty interaction with students</td>
			<td><input type="radio" name="q9c" value="Highly Unsatisfactory"><label>Highly Unsatisfactory</label></td>
			<td><input type="radio" name="q9c" value="Unsatisfactory"><label>Unsatisfactory</label></td>
			<td><input type="radio" name="q9c" value="Satisfactory"><label>Satisfactory</label></td>
			<td><input type="radio" name="q9c" value="Highly Satisfactory"><label>Highly Satisfactory</label></td></tr>
		
		<tr><td>d. Departmental Technology Resources</td>
			<td><input type="radio" name="q9d" value="Highly Unsatisfactory"><label>Highly Unsatisfactory</label></td>
			<td><input type="radio" name="q9d" value="Unsatisfactory"><label>Unsatisfactory</label></td>
			<td><input type="radio" name="q9d" value="Satisfactory"><label>Satisfactory</label></td>
			<td><input type="radio" name="q9d" value="Highly Satisfactory"><label>Highly Satisfactory</label></td></tr>
	</table>
	</br>
	
	
	
	<div id="undergrad_questions">
		
		<h3>Undergraduate Program Questions</h3>
	
		<p class="question">1. If you are continuing on to graduate school, give the name of the university, the field of study, and the degree that you are going to pursue (MS, PhD).</p>
		<textarea name="u1" cols="80" rows="6"></textarea>
	
		<p class="question">2. If you are going to pursue a professional degree, for example, law or medicine, please give the type of program and the name of the institution that you will be attending.</p>
		<textarea name="u2" cols="80" rows="6"></textarea>
	
	</div>
	</br>
	
	
	
	<div id="grad_questions">
		
		<h3>Graduate Program Questions</h3>
	
		<p class="question">1. What are the strengths of our graduate program?</p>
		<textarea name="g1" cols="80" rows="6"></textarea>
	
		<p class="question">2. What are the weaknesses of our graduate program?</p>
		<textarea name="g2" cols="80" rows="6"></textarea>
	
		<p class="question">3. What did you like best about the coursework you have completed?</p>
		<textarea name="g3" cols="80" rows="6"></textarea>
	
		<p class="question">4. What did not you like about the coursework you have completed?</p>
		<textarea name="g4" cols="80" rows="6"></textarea>
	
		<p class="question">5. What benefits do you see from working on the MS project?</p>
		<textarea name="g5" cols="80" rows="6"></textarea>
	
		<p class="question">6. If you already have a job offer, what in our graduate program made you attractive to this employer?</p>
		<textarea name="g6" cols="80" rows="6"></textarea>
	
		<p class="question">7. If you do not have a job offer yet, how will you use your Clemson MS/PhD degree in Mathematical Sciences in marketing yourself to a potential employer?</p>
		<textarea name="g7" cols="80" rows="6"></textarea>
	
	</div>
	
	
	</br>
	<p>Press Submit to record your responses.</p>
	<input type="submit" name="submit" value="Submit Responses">
	<input type="reset" name="reset" value="Reset">
</form>

</body>
</html>

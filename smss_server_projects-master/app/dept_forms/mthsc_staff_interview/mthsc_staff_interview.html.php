<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Math Sciences Exit Questionnaire</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2015-04-29 -->
	
<link rel="stylesheet" href="mthsc_staff_interview_style.css" type="text/css" media="screen" title="no title" charset="utf-8">
<link rel="shortcut icon" href="/favicon.ico">

<script src="../jquery-1.3.min.js" type="text/javascript" charset="utf-8"></script>


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
	
	<img src="/style/math_logo.png" alt="Mathsci">
	<h2>Math Sciences Staff Questionnaire</h2>
	
</center>
<form id="questionnaire" action="index.php" method="post">
	<p>To get a sense of how you would react to different scenarios and see how you operate, please answer the following questions.</p>
	
	<p class="question">Name: <input name="name" id="name" size="30" type="text"></input></p>
	
	<p class="question">1. At times we have large projects with absolute deadlines.  Staff members pitch in and work as a team to achieve the deadline goal.  Describe your level of comfort in working as a team member and provide an example of a team project you have participated in.</p>
	<textarea name="q1" cols="80" rows="6"></textarea>
	
	<p class="question">2.  After the beginning of the semester rush has subsided, how would you manage your time when things are less hectic in the office?</p>
	<textarea name="q2" cols="80" rows="6"></textarea>
	
	<p class="question">3. Give an example of a project you worked on in a previous job that demonstrates your willingness to devote the time necessary to complete the project on time.</p>
	<textarea name="q3" cols="80" rows="6"></textarea>
	
	<p class="question">4.  Our department is comprised of a culturally diverse group of faculty and graduate students. How would you interact with an individual that has a heavy accent and you have difficulty understanding them?</p>
	<textarea name="q4" cols="80" rows="6"></textarea>
	
	
	</br>
	<p>Press 'Submit Responses' to record your responses.</p>
	<input type="submit" name="submit" value="Submit Responses">
	<input type="reset" name="reset" value="Reset">
</form>

</body>
</html>

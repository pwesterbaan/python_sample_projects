<?php



if (isset($_SERVER['REMOTE_USER']))
{
	$user_id = strtoupper($_SERVER['REMOTE_USER']);
}



?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Substitute CMPT Scores</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2016-6-7 -->

<link rel="stylesheet" href="/style/math.css" type="text/css" media="screen" charset="utf-8">

<style type="text/css">


</style>



</head>
<body>
	<div id="main">
		<div id="header">
			<a href="http://www.clemson.edu/math" title="Department Home">
				<img src="/style/math_logo.png" alt="math department logo">
			</a>
			<h1 class="blueRidgeBG"><a href="view_substitute_scores.php">Substitute CMPT Scores</a></h1>
		</div>
	
		<div id="content">
			<?php echo isset($error) ? '<p id="error" class="orangeTxt">'.$error.'</p>' : "" ; ?>
			
			<p><b>Instructions</b>: Use the following form to input CMPT equivalent scores for pre-calculus students. <a href="view_substitute_scores.php">View previously entered substitute scores here.</a>.</p>
			<p>Column format: XID, score (separated by tabs, commas, or both; each student on a new line)</p>
			<form name="submit_scores_form" action="view_substitute_scores.php" method="post">
			<textarea id="score_entry" name="score_entry" style="width: 200px; height: 500px;"></textarea><br><br>
			<input type="submit" name="submit_scores" id="submit_scores" value="Submit Scores">
		</div>	
	</div>
</body>
</html>
<?php

$message = "";

//connects to the database, returns a semi-useful error if not accessible.
$link = mysql_connect("mthsc.clemson.edu", "forms", "d8ta_c0l");
if(!$link){
	echo "Could not connect to database.  Please try again later.";
	exit;
}

//selects the database
else{
	mysql_select_db("forms", $link);
}

mysql_set_charset("utf8-bin",$link);

//get voting results
$resultsRequest = mysql_query('SELECT * FROM forms.gs_ballot');
if (!$resultsRequest)
{
	$message .= "'Error fetching results: '.mysql_error($link).'<br>'";
}
else
{
	$vote_counts = array('Elaine Sotherden' => 0,
						'Hugh Geller' => 0,
						'Scott Scruggs' => 0);
	while ($row = mysql_fetch_array($resultsRequest))
	{
		$vote_counts[$row['selection']] += 1;
	}
}


//check for end of submission period
$accepting_submissions = true;
date_default_timezone_set('America/New_York');
$currentTime = strtotime('now');
//echo $currentTime;

if ($currentTime > mktime(23, 01, 00, 4, 6, 2017))
{
	$accepting_submissions = false;
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Math Graduate Student Ballot Results</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2016-8-9 -->

<link rel="stylesheet" href="/style/math.css" type="text/css" media="screen" title="no title" charset="utf-8">

<style type="text/css">


</style>

<script src="/style/jquery-1.3.min.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
$(document).ready(function() 
{
	$("input:checkbox").click(function() {
		var num_checked = $("input:checkbox[checked]").length;
		if (num_checked == 1 || num_checked == 2)
		{
			$("#vote").attr("disabled", false);
		}
		else
		{
			$("#vote").attr("disabled", true);
		}
		
    });
	
	
});
</script>

</head>
<body>
	<div id="main">
		<div id="header">
			<a href="http://www.clemson.edu/math" title="Department Home">
				<img src="/style/math_logo.png" alt="math department logo">
			</a>
			<h1><a href="index.php">Grad Student Ballot Results</a></h1>
		</div>
	
		<div id="content">
			<h1>Graduate Student Ballot Results</h1>
			
			<?php echo (isset($message) && !isset($success)) ? '<p id="error">'.$message.'</p>' : ""; ?>
			
			<p>Voting <?php echo $accepting_submissions ? "is open." : "is closed."; ?>
			
			<table>
				<tr>
					<th>Candidate</th>
					<th>Number of Votes</th>
				</tr>
				<tr>
					<td>Elaine Sotherden</td>
					<td style="text-align:center;"><?php echo $vote_counts['Elaine Sotherden']?></td>
				</tr>
				<tr>
					<td>Hugh Geller</td>
					<td style="text-align:center;"><?php echo $vote_counts['Hugh Geller']?></td>
				</tr>
				<tr>
					<td>Scott Scruggs</td>
					<td style="text-align:center;"><?php echo $vote_counts['Scott Scruggs']?></td>
				</tr>
		</div>	
	</div>
</body>
</html>
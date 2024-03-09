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

if (isset($_SERVER['REMOTE_USER']))
{
	$user_id = strtoupper($_SERVER['REMOTE_USER']);
	$user_hash = md5($user_id.'gs_ballot_add');
}
else
{
	$user_id = "";
}

//process vote
if (isset($_POST['vote']) && $user_id != "KEVJA")
{
	$votes = $_POST['reps'];
	$num_votes = count($votes);
	
	if ($num_votes == 1)
	{
		$voteResult = mysql_query('INSERT INTO forms.gs_ballot (user_hash,selection) VALUES ("'.$_POST['user_hash'].'","'.$votes[0].'")');
		if (!$voteResult)
		{
			$message .= "'Error casting ballot: '.mysql_error($link).'<br>'";
		}
		else
		{
			$message .= "Ballot Successfully Cast";
			$success = true;
		}
	}
	else if ($num_votes == 2)
	{
		$voteResult2 = mysql_query('INSERT INTO forms.gs_ballot (user_hash,selection) VALUES ("'.$_POST['user_hash'].'","'.$votes[0].'"), ("'.$_POST['user_hash'].'","'.$votes[1].'")');
		if (!$voteResult2)
		{
			$message .= "'Error casting ballot: '.mysql_error($link).'<br>'";
		}
		else
		{
			$message .= "Ballot Successfully Cast";
			$success = true;
		}
	}
}

//Determine Eligibilty

//get person_id
$person_id = 0;

$personIDRequest = mysql_query('SELECT person_id FROM dept_info.person WHERE username="'.$user_id.'" LIMIT 1');
if (!$personIDRequest)
{
	$message .= 'Error fetching person id: '.mysql_error($link).'<br>';
	$eligible_to_vote = false;
}
else
{
	if (mysql_num_rows($personIDRequest) > 0)
	{
		//get person id
		$row = mysql_fetch_array($personIDRequest);
		$person_id = $row['person_id'];
	
		//check student list
		$listRequest = mysql_query('SELECT list_id FROM dept_info.people_to_lists_link where person_id = '.$person_id.' AND list_id=1');
		if (!$listRequest)
		{
			$message .= 'Error fetching list info: '.mysql_error($link).'<br>';
			$eligible_to_vote = false;
		}
		else
		{
			if (mysql_num_rows($listRequest) > 0) //they are listed as a student
			{
				//check to see if already voted
				$votingRecordRequest = mysql_query('SELECT * FROM forms.gs_ballot WHERE user_hash="'.$user_hash.'"');
				if (mysql_num_rows($votingRecordRequest) > 0)
				{
					$eligible_to_vote = false;
				}
				else
				{
					$eligible_to_vote = true;
				}
			}
			else if ($user_id == "KEVJA" || $user_id == "HEDETNI")
			{
				$eligible_to_vote = true;
			}
			else
			{
				$eligible_to_vote = false;
			}
		}
	}
	else
	{
		//not in our database
		$eligible_to_vote = false;
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
	<title>Graduate Student Ballot</title>
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
		if (num_checked > 0 && num_checked < 3 )
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
			<h1><a href="index.php">Grad Student Ballot</a></h1>
		</div>
	
		<div id="content">
			<h1>Graduate Student Ballot</h1>
			
			<?php echo (isset($message) && !isset($success)) ? '<p id="error">'.$message.'</p>' : ""; ?>
			
			<?php if ($eligible_to_vote && $accepting_submissions): ?>
				<form name="voting_form" action="" method="POST">
					<p>Select up to 2 graduate students to serve as a GSG Senator.</p>
				
					<p><input type="checkbox" name="reps[]" value="Elaine Sotherden"> Elaine Sotherden (currently serving as senator and chosen to serve in a leadership role within the senate next year if re-elected)</p>
				
					<p><input type="checkbox" name="reps[]" value="Hugh Geller"> Hugh Geller</p>
				
					<p><input type="checkbox" name="reps[]" value="Scott Scruggs"> Scott Scruggs</p>
					
					<input name="user_hash" type="hidden" id="user_hash" value="<?php echo $user_hash; ?>">
					<input name="vote" id="vote" type="submit" value="Submit Vote" disabled="disabled"/>

				</form>
			<?php elseif (isset($success)): ?>
				<p id="error"><?php echo $message; ?></p>
			<?php else: ?>
				<p>You are ineligible to vote, have already voted, or voting has closed.</p>
			<?php endif; ?>
		</div>	
	</div>
</body>
</html>
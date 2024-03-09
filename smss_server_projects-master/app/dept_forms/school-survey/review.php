<?php

$host = 'mthsc.clemson.edu';
$db   = 'forms';
$user = 'forms';
$pass = 'd8ta_c0l';
$charset = 'utf8';

$dsn = 'mysql:host='.$host.';dbname='.$db.';charset='.$charset;
$opt = array(
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false
);
$mthsc_db = new PDO($dsn, $user, $pass, $opt);

date_default_timezone_set('America/New_York');

if (isset($_SERVER['REMOTE_USER']))
{
	$user_id = strtoupper($_SERVER['REMOTE_USER']);
	$user_hash = md5($user_id.'school_survey42');
}

if (isset($_POST['submit_survey']))
{
	$responses = $_POST;
	unset($responses['submit_survey']);
	$responses['user_hash'] = $user_hash;
	
	if (!isset($responses['role']))
	{
		$responses['role'] = "Prefer Not to Answer";
	}
	
	//insert data
	$insert = $mthsc_db->prepare('INSERT IGNORE INTO school_survey (user_hash,role, question4_ranked_1,question4_ranked_2,question4_ranked_3,question4_ranked_4,question4_ranked_5,question4_other,question2_ranked_1,question2_ranked_2,question2_ranked_3,question2_ranked_4,question2_ranked_5,question2_ranked_6,question2_ranked_7,question2_ranked_8,question2_ranked_9,question2_ranked_10,question2_ranked_11,question2_other,question3_ranked_1,question3_ranked_2,question3_ranked_3,question3_ranked_4,question3_ranked_5,question3_ranked_6,question3_ranked_7,question3_ranked_8,question3_ranked_9,question3_ranked_10,question3_ranked_11,question3_other,question1_ranked_1,question1_ranked_2,question1_ranked_3,question1_ranked_4,other_structure,comments) VALUES (:user_hash,:role, :question4_ranked_1,:question4_ranked_2,:question4_ranked_3,:question4_ranked_4,:question4_ranked_5,:question4_other,:question2_ranked_1,:question2_ranked_2,:question2_ranked_3,:question2_ranked_4,:question2_ranked_5,:question2_ranked_6,:question2_ranked_7,:question2_ranked_8,:question2_ranked_9,:question2_ranked_10,:question2_ranked_11,:question2_other,:question3_ranked_1,:question3_ranked_2,:question3_ranked_3,:question3_ranked_4,:question3_ranked_5,:question3_ranked_6,:question3_ranked_7,:question3_ranked_8,:question3_ranked_9,:question3_ranked_10,:question3_ranked_11,:question3_other,:question1_ranked_1,:question1_ranked_2,:question1_ranked_3,:question1_ranked_4,:other_structure,:comments)');
	$insert->execute($responses);
	$result = $insert->rowCount();
}
else if (isset($_GET['id']))
{
	$survey_id = $_GET['id'];
	$query = $mthsc_db->prepare('SELECT * FROM school_survey WHERE user_hash = ? LIMIT 1');
	$query->execute(array($survey_id));
	$responses = $query->fetch();
	$responses['lookup'] = true;
	$result = 1;
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Review Submission</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2018-M-D -->

<link rel="stylesheet" href="/style/math.css" type="text/css" media="screen" title="no title" charset="utf-8">

<style type="text/css">
table.ranking_options {
	margin-left:1.5em;
	margin-top:0.5em;
}
table.ranking_options {
	cursor:pointer;
}
textarea {
	margin-left:1.5em;
}
label {
	margin-left:1.5em;
}
td.ranking {
	text-align:center;
}
td.handle {border:none;background-color:transparent;}
.ui-state-highlight { height: 1.5em; line-height: 1.2em; }
h3 {margin-top:2em;}
div.comment {
	margin-left:1.5em;border:1px solid gray;padding:0.5em;
}
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
			<a href="http://www.clemson.edu/math" title="Department Home">
				<img src="/style/science-math-logo-white.png" height="73px" alt="math department logo">
			</a>
			<h1><a href="index.php">Mathematical Sciences School Survey</a></h1>
		</div>
	
		<div id="content">
			<?php if (isset($result) && $result > 0): ?>
			
				<?php if (isset($responses) && count($responses)>1): ?>
				
				<?php echo isset($responses['lookup']) ? '<p>Responses from <strong>'.$responses['user_hash'].'</strong></p>' : '<h1>Thank you</h1><h2>Your responses, shown below, have been saved.</h2>' ?>
				
				<p><strong>Role:</strong> <?php echo $responses['role'];?></p> 
				
				<p>1. Please rank the above department/school structures with 1 being the best and 3 being the worst:</p>

				<ul style="list-style-type:none;">
					<?php for ($o=1;$o<5;$o++)
					{
						echo '<li>'.$o.' - '.$responses['question1_ranked_'.$o].'</li>';
					}?>
				</ul>
				<div class="comment">Other Structure: <?php echo isset($responses['question1_other']) ? $responses['question1_other'] : ''; ?></div>
				<br>
		
				<p>2. Please rank the benefits of a school structure with 1 being the most beneficial and 10 being the least:</p>

				<ul style="list-style-type:none;">
					<?php for ($o=1;$o<12;$o++)
					{
						echo '<li>'.$o.' - '.$responses['question2_ranked_'.$o].'</li>';
					}?>
				</ul>
				<div class="comment">Other Benefit: <?php echo isset($responses['question2_other']) ? $responses['question2_other'] : ''; ?></div>

				<br>
		
				<p>3. Please rank the concerns of a school structure with 1 being the most concerning and 10 being the least:</p>

				<ul style="list-style-type:none;">
					<?php for ($o=1;$o<12;$o++)
					{
						echo '<li>'.$o.' - '.$responses['question3_ranked_'.$o].'</li>';
					}?>
				</ul>
				<div class="comment">Other Concern: <?php echo isset($responses['question3_other']) ? $responses['question3_other'] : ''; ?></div>
				<br>

				<p>4. Please rank the following possible names of the school, with 1 being the best and 4 being the worst:</p>

				<ul style="list-style-type:none;">
					<?php for ($o=1;$o<6;$o++)
					{
						echo '<li>'.$o.' - '.$responses['question4_ranked_'.$o].'</li>';
					}?>
				</ul>
				<div class="comment">Other Name: <?php echo isset($responses['question4_other']) ? $responses['question4_other'] : ''; ?></div>

				<br>
		
				<p>5. Please use this space for any additional comments:</p>
				<div class="comment"><?php echo $responses['comments']?></div>
				<br>
				<?php endif; ?>
			<?php else: ?>
				<p>You may only submit this form once. Your initial responses have not been changed.</p>
				
			<?php endif; ?>
		</div>	
		<div id="footer">Click <a href="/dept_forms/website_error.py" target="_blank">here</a> to report a problem with this website.</div>
	</div>
</body>
</html>
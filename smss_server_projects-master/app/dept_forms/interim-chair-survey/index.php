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
	$user_hash = md5($user_id.'interim-chair_survey99');
}

$q4_options = array('School of Mathematical and Statistical Sciences',
					'School of Mathematics and Decision Science',
					'School of Mathematics, Statistics, and Operations Research',
					'School of Mathematical, Data, and Decision Sciences',
					'Other Name');



//$privileged = array('SNHENNI','CYYOUNG','LGEHRIN','MEL');

function isInMath($user_id)
{
	global $mthsc_db;
	global $privileged;
	$stmt = $mthsc_db->prepare("SELECT 1 FROM `dept_info`.`people_to_lists_link` as pll JOIN `dept_info`.`person` as p on pll.person_id = p.person_id WHERE list_id=10 AND username = ?");
	$stmt->execute(array($user_id));
	$userExists = $stmt->fetchColumn();
	if ($userExists){return true;}
	else
	{
		if (in_array(strtoupper($user_id),$privileged))
		{
			return true;
		}
		else
		{
			return false;
		}
	}	
}

function has_submitted($user_hash)
{
	global $mthsc_db;
	$stmt = $mthsc_db->prepare("SELECT submitted FROM `interim_chair_survey` WHERE user_hash = ? ");
	$stmt->execute(array($user_hash));
	$has_submitted = $stmt->fetchColumn();
	if ($has_submitted){return $has_submitted;}
	else {return false;}
}

$accepting_submissions = true;
$currentTime = strtotime('now');
if ($currentTime > mktime(12, 00, 00, 5, 1, 2018) || $currentTime < mktime(16, 30, 00, 4, 24, 2018))
{
	$accepting_submissions = false;
}



?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Interim Chair Survey</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2018-4-25 -->

<link rel="stylesheet" href="/style/math.css" type="text/css" media="screen" title="no title" charset="utf-8">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

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
	margin-left:0.25em;
}
.indent {
	margin-left:1.5em;
}
td.ranking {
	text-align:center;
}
td.handle {border:none;background-color:transparent;}
.ui-state-highlight { height: 1.5em; line-height: 1.2em; }
h3 {margin-top:2em;}

</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="jquery.ui.touch-punch.min.js"></script>
<script type="text/javascript">
$(document).ready(function() 
{
	$( ".ranking_options tbody" ).sortable({
		placeholder: "ui-state-highlight",
		stop: function( event, ui ){
			$(this).find('tr').each(function(i){
				var q = $(this).attr('id');
				var question = q.substring(1,2);
				var ranking = i+1;
				var option = $(this).find('td:last').html()
				$('input#question'+question+'_ranked_'+ranking).val(option);
				$(this).find('td:first').text(i+1);
			});
			//if (ui.item.find('td:last').html() == "Other")
		}
	});
	$( ".ranking_options" ).disableSelection();

	$("input[type='radio']").prop("checked",false);
	$('')
	
});
</script>

</head>
<body>
	<div id="main">
		<div id="header">
			<a href="http://www.clemson.edu/math" title="Department Home">
				<img src="/style/science-math-logo-white.png" height="73px" alt="math department logo">
			</a>
			<h1><a href="index.php">Interim Chair Survey</a></h1>
		</div>
	
		<div id="content">
			<?php if (isInMath($user_id)): ?>
				<h1>Interim Chair Search</h1>
					
				<h2>Candidate Documents</h2>
				
				<p><a href="James-Letter.pdf">Kevin James's Cover Letter</a><br>
					<a href="James-CV.pdf">Kevin James's CV</a><br>
					<a href="https://drive.google.com/file/d/1w_RqjvCgEBXejgHTbRRtCQ5XNDP7x96C/view?usp=sharing">Kevin James's Open Forum Video</a></p>
					
				<p><a href="Kiessler-Letter.pdf">Pete Kiessler's Cover Letter</a><br>
					<a href="Kiessler-CV.pdf">Pete Kiessler's CV</a><br>
					<a href="https://drive.google.com/file/d/1aK4EpcV30zuxLfroNKBnfufLNCEUjRC2/view?usp=sharing">Pete Kiessler's Open Forum Video</a></p>
					
				<p><a href="Rebholz-Letter.pdf">Leo Rebholz's Cover Letter</a><br>
					<a href="Rebholz-CV.pdf">Leo Rebholz's CV</a><br>
					<!--<a href="https://drive.google.com/file/d/1pmgRZ0318Y6-lMcgOYPGhu1OB1HnJDTU/view?usp=sharing">Leo Rebholz's Open Forum Video</a></p>-->
				
				<h2>Survey</h2>
				<?php if ($accepting_submissions): ?>
					<p>The survey will close Tuesday May 1 at Noon.</p>
					<p><a href="submit.php">Click here to complete the survey</a></p>
				<?php else: ?>
					<p>Survey available until Tuesday May 1 at Noon</p>
				<?php endif; ?>
			<?php else: ?>
				<p>You are not eligible to view this content. Contact Kevin Hedetniemi for more information.</p>
			<?php endif; ?>
		</div>	
		<div id="footer">Click <a href="/dept_forms/website_error.py" target="_blank">here</a> to report a problem with this website.</div>
	</div>
</body>
</html>
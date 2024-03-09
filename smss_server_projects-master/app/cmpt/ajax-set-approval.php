<?php

//$host = 'mthsc.clemson.edu'; //old host
$host = 'localhost';
$db   = 'cmpt';
$user = 'math_cmpt';
$pass = 'cmpt_pa$$';
$charset = 'utf8';

$dsn = 'mysql:host='.$host.';dbname='.$db.';charset='.$charset;
$opt = array(
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false
);
$mthsc_db = new PDO($dsn, $user, $pass, $opt);

if (isset($_POST['set_cmpt_approval']))
{
	$set_approval_query = $mthsc_db->prepare('UPDATE cmpt_scores SET approval = ? WHERE username = ? AND ALEKS_class_code = ? AND test_number = ? LIMIT 1;');
	$set_approval_result = $set_approval_query->execute(array($_POST['approval'],$_POST['username'],$_POST['cohort'],$_POST['test_number']));
	
	if ($set_approval_result)
	{
		//get approval status to return
		$get_approval_query = $mthsc_db->prepare('SELECT approval FROM cmpt_scores WHERE username = ? AND ALEKS_class_code = ? AND test_number = ? LIMIT 1');
		$approval_status = $get_approval_query->execute(array($_POST['username'],$_POST['cohort'],$_POST['test_number']));
		if ($approval_status)
		{
			echo $get_approval_query->fetchColumn();
		}
		else
		{
			echo 'get approval';
		}
	}
	else
	{
		echo 'set approval';
	}
}

?>
<?php

//$host = 'mthsc.clemson.edu';
$host = 'localhost';
$db   = 'ug_pages';
$user = 'ug_pages';
$pass = 'crs_info_7';
$charset = 'utf8';

$dsn = 'mysql:host='.$host.';dbname='.$db.';charset='.$charset;
$opt = array(
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false
);
$mthsc_db = new PDO($dsn, $user, $pass, $opt);

if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] != 0)
{
	$get_uploaded_item_data = $mthsc_db->prepare('SELECT * FROM uploaded_items WHERE item_id = ?');
	$get_uploaded_item_data->execute(array($_GET['id']));
	$file_data = $get_uploaded_item_data->fetch();
	
	$file = 'uploaded_items/'.$file_data["filepath"];
	
	if (file_exists($file))
	{
		if(false !== ($handler = fopen($file, 'rb')))
		{
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="'.$file_data["download_filename"].'"');
			header('Content-Transfer-Encoding: binary');
			
			echo fread($handler, filesize($file));
			fclose($handler);
		}
		exit;
	}
	else
	{echo("The file you requested could not be found.");}
	
}
else
{
    echo("The file you requested could not be found.");
}


?>
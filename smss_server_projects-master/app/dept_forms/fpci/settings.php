<?php
include('fpci-functions.php');

if (in_array($user_id,$admin_list))
{
	if (isset($_POST['set_year']))
	{
		$set_year_query = $mthsc_db->prepare("UPDATE fpci_settings SET value = ? WHERE setting = 'current_evaluation_year';");
		$set_year_query->execute(array($_POST['new_evaluation_year']));
		$message = "Evaluation Year Updated";
	}

	if (isset($_POST['set_percentage_cutoff']))
	{
		$new_cutoff = mktime($_POST['cutoff_hour'],0,0,$_POST['cutoff_month'],$_POST['cutoff_day'],$_POST['cutoff_year']);
		$current_time = strtotime("now");
		if ($current_time > $new_cutoff)
		{
			$message = "Requested Cutoff Date has already occurred";
		}
		else
		{
			$set_cutoff_query = $mthsc_db->prepare("UPDATE fpci_settings SET value = ? WHERE setting = 'percentage_entry_cutoff';");
			$set_cutoff_query->execute(array($new_cutoff));
			$message = "Activity Percentage Entry Cutoff Updated";
		}
	}

	if (isset($_POST['remove_percentage_cutoff']))
	{
		$set_cutoff_query = $mthsc_db->prepare("UPDATE fpci_settings SET value = ? WHERE setting = 'percentage_entry_cutoff';");
		$set_cutoff_query->execute(array(0));
		$message = "Activity Percentage Entry Closed";
	}

	$current_evaluation_year = get_current_evaluation_year();
	$percentage_entry_cutoff = get_percentage_entry_cutoff();
}
else
{
	$error = "Access Denied";
}

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">


<html lang="en-US">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="author" content="hedetni">
	<!-- Date: 2019-1-9 -->
	
	<title>FPCI | Settings</title>

<link rel="stylesheet" href="/style/math-internal.css" type="text/css" media="screen" charset="utf-8">

<style type="text/css">


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
			<div id="app_title">FPCI</div>
			<a href="http://www.clemson.edu/math" title="School Home"><img src="/style/math_logo.png" alt="school logo"></a>
		</div>
		<ul id="nav">
			<?php echo get_nav(); ?>
		</ul>
		<?php echo isset($message) ? '<div id="message">'.$message.'</div>' : '' ?>
		<?php echo isset($error) ? '<div id="error">'.$error.'</div>' : '' ?>
		
		<div id="content">
			<?php if (in_array($user_id,$admin_list)): ?>
			<h1>Admin Settings</h1>
			
			<form name="year_setting_form" action="" method="POST">
				<p>Current Evaluation Year: <strong><?php echo $current_evaluation_year-1;echo '-'; echo $current_evaluation_year; ?></strong></p>
				<p>
					<label for="new_evaluation_year">Set New Evaluation Year</label>: <select name="new_evaluation_year" id="new_evaluation_year">
						<option value="<?php echo intval($current_year)-1;?>" <?php echo intval($current_year)-1 == $current_evaluation_year ? 'selected' : ''; ?> ><?php echo intval($current_year)-2; echo '-'; echo intval($current_year)-1;?></option>
						<option value="<?php echo intval($current_year);?>" <?php echo intval($current_year) == $current_evaluation_year ? 'selected' : ''; ?> ><?php echo intval($current_year)-1; echo '-'; echo intval($current_year);?></option>
						<option value="<?php echo intval($current_year)+1;?>" <?php echo intval($current_year)+1 == $current_evaluation_year ? 'selected' : ''; ?> ><?php echo intval($current_year); echo '-'; echo intval($current_year)+1;?></option>
					</select> 
					<input type="submit" name="set_year" value="Set Evaluation Year"></input>
				</p>
			</form>
			
			<hr>
			
			
			<p>Cutoff Date for Activity Percentage Entry:</p>
			<p>
				<?php if ($percentage_entry_cutoff == 0): ?>
					<em>No Cutoff Date Set. Updates are prohibited--allow updates by setting a new cutoff date below.</em>
				<?php else: ?>
					<strong><?php echo date("F j, Y, g:i a", $percentage_entry_cutoff); ?></strong>
				<?php endif; ?>
			</p>
			
			<form name="percentage_cutoff_form" method="POST" action="">
				<p id="percentage_cutoff_chooser">
					Set New Cutoff Date: 
					<label class="hide-label" for="cutoff_month">Cutoff Month</label><select name="cutoff_month" id="cutoff_month" >
					<option value="1" <?php echo $percentage_entry_cutoff != 0 && date('n',$percentage_entry_cutoff) == 1 ? "selected" : "" ; ?>>January</option>
					<option value="2" <?php echo $percentage_entry_cutoff != 0 && date('n',$percentage_entry_cutoff) == 2 ? "selected" : "" ; ?>>February</option>
					<option value="3" <?php echo $percentage_entry_cutoff != 0 && date('n',$percentage_entry_cutoff) == 3 ? "selected" : "" ; ?>>March</option>
					<option value="4" <?php echo $percentage_entry_cutoff != 0 && date('n',$percentage_entry_cutoff) == 4 ? "selected" : "" ; ?>>April</option>
					<option value="5" <?php echo $percentage_entry_cutoff != 0 && date('n',$percentage_entry_cutoff) == 5 ? "selected" : "" ; ?>>May</option>
					<option value="6" <?php echo $percentage_entry_cutoff != 0 && date('n',$percentage_entry_cutoff) == 6 ? "selected" : "" ; ?>>June</option>
					<option value="7" <?php echo $percentage_entry_cutoff != 0 && date('n',$percentage_entry_cutoff) == 7 ? "selected" : "" ; ?>>July</option>
					<option value="8" <?php echo $percentage_entry_cutoff != 0 && date('n',$percentage_entry_cutoff) == 8 ? "selected" : "" ; ?>>August</option>
					<option value="9" <?php echo $percentage_entry_cutoff != 0 && date('n',$percentage_entry_cutoff) == 9 ? "selected" : "" ; ?>>September</option>
					<option value="10" <?php echo $percentage_entry_cutoff != 0 && date('n',$percentage_entry_cutoff) == 10 ? "selected" : "" ; ?>>October</option>
					<option value="11" <?php echo $percentage_entry_cutoff != 0 && date('n',$percentage_entry_cutoff) == 11 ? "selected" : "" ; ?>>November</option>
					<option value="12" <?php echo $percentage_entry_cutoff != 0 && date('n',$percentage_entry_cutoff) == 12 ? "selected" : "" ; ?>>December</option>
				</select>
				<label class="hide-label" for="cutoff_day">Cutoff Day</label>
				<select name="cutoff_day" id="cutoff_day" >
					<?php for ($s_day=1;$s_day<=31;$s_day++) {?>
						<option value="<?php echo $s_day ?>" <?php echo $percentage_entry_cutoff != 0 && date('j',$percentage_entry_cutoff)==$s_day ? "selected" : "" ; ?> ><?php echo $s_day ?></option>
					<?php }?>
				</select>
				<label class="hide-label" for="cutoff_year">Cutoff Year</label>
				<select name="cutoff_year" id="cutoff_year" >
					<option value="<?php echo date("o", strtotime('now')); ?>" <?php echo $percentage_entry_cutoff != 0 && date('o',$percentage_entry_cutoff)==date("o", strtotime('now')) ? "selected" : "" ; ?> ><?php echo date("o", strtotime('now')); ?></option>
					<option value="<?php echo date("o", strtotime('now'))+1; ?>" <?php echo $percentage_entry_cutoff != 0 && date('o',$percentage_entry_cutoff)==date("o", strtotime('now'))+1 ? "selected" : "" ; ?> ><?php echo date("o", strtotime('now'))+1; ?></option>
				</select>
				<label class="hide-label" for="cutoff_hour">Cutoff Hour</label>
				<select name="cutoff_hour" id="cutoff_hour" >
					<?php for ($s_hour=0;$s_hour<=23;$s_hour++) {?>
						<option value="<?php echo $s_hour ?>" <?php echo $percentage_entry_cutoff != 0 && date('H',$percentage_entry_cutoff)==$s_hour ? "selected" : "" ; ?> ><?php echo $s_hour ?>:00</option>
					<?php }?>
				</select>
				</p>
				
				<p>
					<input type="submit" name="set_percentage_cutoff" value="Set Percentage Entry Cutoff"></input>
					<input type="submit" name="remove_percentage_cutoff" value="Close Percentage Entry Now"></input>
				</p>
			</form>
			
			<?php endif; ?>
			
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>
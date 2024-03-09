<?php
include('fpci-functions.php');

if (in_array($user_id,$admin_list))
{
	//if saving evaluations
	if (isset($_POST['save_teaching_loads']))
	{
		$new_teaching_loads = $_POST['teaching_loads'];
		
		//echo '<pre>';print_r($new_teaching_loads);echo '</pre>';
		
		$existing_teaching_loads = get_all_teaching_loads();
		
		$insert_query = $mthsc_db->prepare('INSERT INTO fpci_teaching_loads (person_id, year, teaching_load, last_updated_by) VALUES (?,?,?,?)');
		$update_query = $mthsc_db->prepare('UPDATE fpci_teaching_loads SET teaching_load = ?, last_updated_by = ? WHERE person_id = ? AND year = ?');
		$remove_query = $mthsc_db->prepare('DELETE FROM fpci_teaching_loads WHERE person_id = ? AND year = ?');
		
		$errors = array();
		foreach ($new_teaching_loads as $person_id => $years)
		{
			// go through each year from form
			foreach ($years as $year => $load)
			{
				// if that year is already in the database for this person
				if (isset($existing_teaching_loads[$person_id][$year]))
				{
					// if the value entered is not blank, update it
					if (isset($load) && $load != "")
					{
						$update_result = $update_query->execute(array($load, $user_id, $person_id, $year));
						if (!$update_result){$errors[] = 'Failed to Update: '.get_username_from_person_id($person_id).', '.$year;}
					}
					// else the value entered is blank, remove it
					else
					{
						$remove_result = $remove_query->execute(array($person_id, $year));
						if (!$remove_result){$errors[] = 'Failed to Remove: '.get_username_from_person_id($person_id).', '.$year;}
					}
				}
				// else no load has been entered for that year for this person
				else
				{
					// if the value entered is not blank, insert it
					if (isset($load) && $load != "")
					{
						$insert_result = $insert_query->execute(array($person_id, $year, $load, $user_id));
						if (!$insert_result){$errors[] = 'Failed to Add: '.get_username_from_person_id($person_id).', '.$year;}
					}
					// else the value entered is blank, ignore it
				}
			}
		}
		
		if (count($errors) > 0)
		{
			$error = implode('<br>', $errors);
		}
		else
		{
			$message = "Teaching Loads Updated";
		}
	}


	$current_year = get_current_evaluation_year();
	$start_year = 2018;
	//$people_list = get_all_people_with_percentages();
	$people_list = get_current_evaluation_list();
	
	$teaching_loads = get_all_teaching_loads();
	
	//echo '<pre>';print_r($teaching_loads);echo '</pre>';
	
	$people = array();
	foreach ($people_list as $person_id)
	{
		$person = array();
		
		$person['user_id'] = get_username_from_person_id($person_id);
		$person['person_id'] = $person_id;
		
		//division
		$division = get_division_from_person_id($person_id);
		if ($division == NULL)
		{
			$division = "Not Set";
		}
		$person['division'] = $division;
		
		//teaching loads
		if (isset($teaching_loads[$person_id]))
		{
			$person['teaching_loads'] = $teaching_loads[$person_id];
		}
		else
		{
			$person['teaching_loads'] = array();
		}
		
		$people[] = $person;
		
	}
	//echo '<pre>';print_r($people);echo '</pre>';
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
	<!-- Date: 2019-1-14 -->
	
	<title>FPCI | Admin Enter Teaching Loads</title>

<link rel="stylesheet" href="/style/math-internal.css" type="text/css" media="screen" charset="utf-8">

<style type="text/css">
label.hide_label {display:none;}
input.ok {border-color:lightgreen;border-style:solid;border-width:2px;}
</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>


<script type="text/javascript">


$(document).ready(function(){
{
	
	//this restricts input to numbers to 2 decimal places
	$(":input[type=text]:not([readonly])").on('input', function () {
		this.value = this.value.match(/^[01234567]\.?\d{0,2}/);
	});

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
				<h1>Teaching Loads</h1>
				
				<p><strong>Instructions</strong>: Enter an integer for each year representing the faculty member's annual teaching load.</p>
				
				<form name="enter_teaching_loads_form" method="POST" action="">
				<table id="faculty_table" class="styled">
					
					<tr>
						<td colspan="2"></td>
						<?php for ($y = $current_year+1; $y >= $start_year; $y--){ ?>
							<th id="year_<?php echo $y; ?>_heading"><?php $prev_year = $y-1; echo $prev_year.'-'.$y; ?></th>
						<?php } ?>
					</tr>
					<?php foreach ($people as $index => $person):?>
						<?php if ($index%10 == 0 && $index != 0): ?>
							<tr>
								<td colspan="2"></td>
								<?php for ($y = $current_year+1; $y >= $start_year; $y--){ ?>
									<td><?php $prev_year = $y-1; echo $prev_year.'-'.$y; ?></td>
								<?php } ?>
							</tr>
						<?php endif; ?>
						<?php if (in_array($user_id,$evaluators[$person['division']])): ?>
						<tr>
							<td id="person_<?php echo $person['person_id'];?>"><?php echo get_name_from_person_id($person['person_id']); ?><br><small><?php echo get_division_from_person_id($person['person_id']); ?></small></td>
							<td><?php echo $person['user_id']; ?></td>
							<?php for ($y = $current_year+1; $y >= $start_year; $y--){ ?>
								<td><input type="number" name="teaching_loads[<?php echo $person['person_id'];?>][<?php echo $y;?>]" id="teaching_load_<?php echo $person['person_id'];?>_<?php echo $y;?>" aria-labelledby="person_<?php echo $person['person_id'];?> year_<?php echo $y; ?>_heading" style="width:4em;" value="<?php echo isset($person['teaching_loads'][$y]) ? $person['teaching_loads'][$y] : ''; ?>"></input></td>
							<?php } ?>
						</tr>
						<?php endif; ?>
					<?php endforeach; ?>
				</table>
				
				<input type="submit" name="save_teaching_loads" value="Save Teaching Loads"></input>
			<?php endif; ?>
			
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>
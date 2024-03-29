<?php
include('fpci-functions.php');

if (in_array($user_id,$admin_list))
{
	//if saving evaluations
	if (isset($_POST['save_bulk_evaluation']))
	{
		$evaluations = $_POST['evaluations'];
		
		$existing_teaching_loads = get_all_teaching_loads();
		$projected_teaching_loads = $_POST['projected_teaching_loads'];
		
		//echo '<pre>';print_r($evaluation);echo '</pre>';
		
		$update_query = $mthsc_db->prepare('INSERT INTO fpci_evaluations (percentage_entry_id,year,person_id,user_id,teaching_rating,research_rating,service_rating,teaching_score,research_score,service_score,total_score,display_to_instructor) VALUES (:percentage_entry_id,:year,:person_id,:user_id,:teaching_rating,:research_rating,:service_rating,:teaching_score,:research_score,:service_score,:total_score,:display_to_instructor) ON DUPLICATE KEY UPDATE teaching_rating=VALUES(teaching_rating), research_rating=VALUES(research_rating), service_rating=VALUES(service_rating), teaching_score=VALUES(teaching_score), research_score=VALUES(research_score), service_score=VALUES(service_score), total_score=VALUES(total_score), display_to_instructor=VALUES(display_to_instructor);');
		
		
		$insert_load_query = $mthsc_db->prepare('INSERT INTO fpci_teaching_loads (person_id, year, teaching_load, last_updated_by) VALUES (?,?,?,?)');
		$update_load_query = $mthsc_db->prepare('UPDATE fpci_teaching_loads SET teaching_load = ?, last_updated_by = ? WHERE person_id = ? AND year = ?');
		$remove_load_query = $mthsc_db->prepare('DELETE FROM fpci_teaching_loads WHERE person_id = ? AND year = ?');
		
		foreach ($evaluations as $evaluation)
		{
			if ($evaluation['total_score'] !== "")
			{
				if (!isset($evaluation['display_to_instructor']))
				{$evaluation['display_to_instructor'] = 0;}
				
				$result = $update_query->execute($evaluation);
			}
			
			// save projected teaching load
			$projected_load = $projected_teaching_loads[$evaluation['person_id']];
			if (isset($existing_teaching_loads[$evaluation['person_id']][$evaluation['year']+1]))
			{
				// if the value entered is not blank, update it
				if (isset($projected_load) && $projected_load != "")
				{
					$update_load_query->execute(array($projected_load, $user_id, $evaluation['person_id'], $evaluation['year']+1));
				}
				// else the value entered is blank, remove it
				else
				{
					$remove_load_query->execute(array($evaluation['person_id'], $evaluation['year']));
				}
			}
			// else no load has been entered for that year for this person
			else
			{
				// if the value entered is not blank, insert it
				if (isset($projected_load) && $projected_load != "")
				{
					$insert_load_query->execute(array($evaluation['person_id'], $evaluation['year']+1, $projected_load, $user_id));
				}
				// else the value entered is blank, ignore it
			}
			
		}
	}

	
	$displayed_year = get_current_evaluation_year();
	//$displayed_year = 2018;
	//get list of people who need to be evaluated
	$evaluation_list = get_current_evaluation_list();
	$teaching_loads = get_all_teaching_loads();
	
	$people = array();
	foreach ($evaluation_list as $person_id)
	{
		$person = array();
		
		//percentages
		$person_percentages = get_percentages($person_id,$displayed_year);
		if ($person_percentages == NULL)
		{
			$person['user_id'] = get_username_from_person_id($person_id);
			$person['person_id'] = $person_id;
		}
		else
		{
			foreach ($person_percentages as $field => $value)
			{
				$person[$field] = $value;
			}
		}
		
		//division
		$division = get_division_from_person_id($person_id);
		if ($division == NULL)
		{
			$division = "Not Set";
		}
		$person['division'] = $division;
		
		//evaluation
		if ($person_percentages != NULL)
		{
			$evaluation = get_evaluation_from_percentage_entry_id($person_percentages['entry_id']);
			if ($evaluation != NULL)
			{
				foreach ($evaluation as $field => $value)
				{
					$person[$field] = $value;
				}
			}
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
	
	<title>FPCI | Admin View Activity Entries</title>

<link rel="stylesheet" href="/style/math-internal.css" type="text/css" media="screen" charset="utf-8">

<style type="text/css">
label.hide_label {display:none;}
input.ok {border-color:lightgreen;border-style:solid;border-width:2px;}
</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>


<script type="text/javascript">
function calculate_scores(entry_id)
{
	//teaching
	var teaching_percentage = parseFloat($('#teaching_percentage_'+entry_id).html());
	var teaching_rating = parseFloat($('input[name="evaluations['+entry_id+'][teaching_rating]"]').val());
	if (!isNaN(teaching_rating))
	{
		var teaching_score = (teaching_percentage*teaching_rating)/100;
		$('input[name="evaluations['+entry_id+'][teaching_score]"]').val(teaching_score.toFixed(2));
	}
	else
	{
		$('input[name="evaluations['+entry_id+'][teaching_score]"]').val("");
	}

	//research
	var research_percentage = parseFloat($('#research_percentage_'+entry_id).html());
	var research_rating = parseFloat($('input[name="evaluations['+entry_id+'][research_rating]"]').val());
	if (!isNaN(research_rating))
	{
		var research_score = (research_percentage*research_rating)/100;
		$('input[name="evaluations['+entry_id+'][research_score]"]').val(research_score.toFixed(2));
	}
	else
	{
		$('input[name="evaluations['+entry_id+'][research_score]"]').val("");
	}
	
	//service
	var service_percentage = parseFloat($('#service_percentage_'+entry_id).html());
	var service_rating = parseFloat($('input[name="evaluations['+entry_id+'][service_rating]"]').val());
	if (!isNaN(service_rating))
	{
		var service_score = (service_percentage*service_rating)/100;
		$('input[name="evaluations['+entry_id+'][service_score]"]').val(service_score.toFixed(2));
	}
	else
	{
		$('input[name="evaluations['+entry_id+'][service_score]"]').val("");
	}
	
	
	//total
	if (!isNaN(teaching_rating) && !isNaN(research_rating) && !isNaN(service_rating))
	{
		//calculate total score
		var total_score = teaching_score + research_score + service_score;
		$('input[name="evaluations['+entry_id+'][total_score]"]').val(total_score.toFixed(2));
		$('input[name="evaluations['+entry_id+'][total_score]"]').addClass('ok');
	}
	else
	{
		$('input[name="evaluations['+entry_id+'][total_score]"]').val("");
		$('input[name="evaluations['+entry_id+'][total_score]"]').removeClass('ok');
	}
}

function check_all_display()
{
	$('input.display_to_instructor').prop('checked',true);
}
function uncheck_all_display()
{
	$('input.display_to_instructor').prop('checked',false);
}
function reset_display_checks()
{
	$('form[name="bulk_evaluate_form"]')[0].reset();
}
$(document).ready(function(){

	$(":input[type=text]:not([readonly])").css({"border-color":"#109DC0","border-style":"solid","border-width":"2px"});
	
	//this restricts input to numbers to 2 decimal places
	$(":input[type=text]:not([readonly])").on('input', function () {
        this.value = this.value.match(/^[01234567]\.?\d{0,2}/);
    });

	$(".total_score_input").each(function(){
		if ($(this).val() != "")
		{
			$(this).addClass('ok');
		}
	});

	//on every key press in an input element, get the entry id and calculate the score for that person
	$(":input[type=text]:not([readonly])").keyup(function(){
		var changed_element_name = $(this).attr('name');
		entry_id = changed_element_name.substring(changed_element_name.indexOf("[")+1,changed_element_name.indexOf("]"));
		calculate_scores(entry_id);
	})
	
	
	$('td.display_cell').click(function(){
		if (!$(event.target).is('input')) {
			var checkbox = $(this).children('input[type="checkbox"]');
			checkbox.prop('checked', !checkbox.prop("checked"));
		}
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
				<h1>Bulk Evaluate for <?php echo $displayed_year-1;echo '-';echo$displayed_year; ?></h1>
				
				<p><strong>Instructions</strong>: Instructors who have entered activity percentages are shown below. Enter ratings from 0-7 (to 2 decimal places) for each activity. Scores are calculated automatically. Only complete entries (those with a total score, outlined in green) will be saved, all others will be disregarded. Be sure to click save at the bottom of the page. </p>
				
				<form name="bulk_evaluate_form" method="POST" action="">
				<p><a href="javascript:reset_display_checks()">Reset All Fields</a></p>
				<table id="faculty_table" class="styled">
					
					<?php foreach ($people as $index => $person):?>
						<?php if ($index%10 == 0): ?>
							<tr>
								<td colspan="2"></td>
								<th>Teaching Load</th>
								<th colspan="3" scope="col">Teaching</th>
								<th colspan="3" scope="col">Research</th>
								<th colspan="3" scope="col">Service</th>
								<th>Total Score</th>
								<th>Projected Teaching Load</th>
								<th id="display_to_instructor_label">Display to Instructor?</th>
							</tr>
							<tr>
								<td colspan="2"></td>
								<td class="text-center"><?php echo $displayed_year-1;echo '-';echo$displayed_year; ?></td>
								<td class="text-center">%</td>
								<td class="text-center">Rating</td>
								<td class="text-center">Score</td>
								<td class="text-center">%</td>
								<td class="text-center">Rating</td>
								<td class="text-center">Score</td>
								<td class="text-center">%</td>
								<td class="text-center">Rating</td>
								<td class="text-center">Score</td>
								<td></td>
								<td class="text-center"><?php echo $displayed_year;echo '-';echo$displayed_year+1; ?></td>
								<td class="text-center"><a href="javascript:check_all_display();">Check All</a> | <a href="javascript:uncheck_all_display();">Uncheck All</a></td>
							</tr>
						<?php endif; ?>
						<?php if (in_array($user_id,$evaluators[$person['division']])): ?>
						<tr>
							<td><?php echo get_name_from_person_id($person['person_id']); ?><br><small><?php echo get_division_from_person_id($person['person_id']); ?></small></td>
							<td><?php echo $person['user_id']; ?>
								<?php if (isset($person['entry_id'])): ?>
									<input type="hidden" name="evaluations[<?php echo $person['entry_id'];?>][percentage_entry_id]" value="<?php echo $person['entry_id'];?>"></input>
									<input type="hidden" name="evaluations[<?php echo $person['entry_id'];?>][year]" value="<?php echo $person['year'];?>"></input>
									<input type="hidden" name="evaluations[<?php echo $person['entry_id'];?>][person_id]" value="<?php echo $person['person_id'];?>"></input>
									<input type="hidden" name="evaluations[<?php echo $person['entry_id'];?>][user_id]" value="<?php echo $person['user_id'];?>"></input>
								<?php endif; ?>
							</td>
							<td class="text-center"><?php echo isset($teaching_loads[$person['person_id']][$displayed_year]) ? $teaching_loads[$person['person_id']][$displayed_year] : ""; ?></td>
							
							<!-- Teaching -->
							<td class="text-center"><?php echo isset($person['overall_teaching_percentage']) ? '<span id="teaching_percentage_'.$person['entry_id'].'">'.$person['overall_teaching_percentage'].'</span>%' : "--"; ?></td>
							<td class="text-center">
								<?php if (isset($person['entry_id'])): ?>
									<label class="hide_label" for="evaluations[<?php echo $person['entry_id'];?>][teaching_rating]"><?php echo $person['user_id']; ?> teaching rating</label>
									<input type="text" name="evaluations[<?php echo $person['entry_id'];?>][teaching_rating]" id="evaluations[<?php echo $person['entry_id'];?>][teaching_rating]" size="2" value="<?php echo isset($person['teaching_rating']) ? $person['teaching_rating'] : ''; ?>"></input>
								<?php endif; ?>
							</td>
							<td class="text-center">
								<?php if (isset($person['entry_id'])): ?>
									<label class="hide_label" for="evaluations[<?php echo $person['entry_id'];?>][teaching_score]"><?php echo $person['user_id']; ?> teaching score</label>
									<input type="text" name="evaluations[<?php echo $person['entry_id'];?>][teaching_score]" id="evaluations[<?php echo $person['entry_id'];?>][teaching_score]" size="2" value="<?php echo isset($person['teaching_score']) ? $person['teaching_score'] : ''; ?>" tabindex="-1" readonly></input>
								<?php endif; ?>
							</td>
							
							<!-- Research -->
							<td class="text-center"><?php echo isset($person['overall_research_percentage']) ? '<span id="research_percentage_'.$person['entry_id'].'">'.$person['overall_research_percentage'].'</span>%' : "--"; ?></td>
							<td class="text-center">
								<?php if (isset($person['entry_id'])): ?>
									<label class="hide_label" for="evaluations[<?php echo $person['entry_id'];?>][research_rating]"><?php echo $person['user_id']; ?> research rating</label>
									<input type="text" name="evaluations[<?php echo $person['entry_id'];?>][research_rating]" id="evaluations[<?php echo $person['entry_id'];?>][research_rating]" size="2" value="<?php echo isset($person['research_rating']) ? $person['research_rating'] : ''; ?>"></input>
								<?php endif; ?>
							</td>
							<td class="text-center">
								<?php if (isset($person['entry_id'])): ?>
									<label class="hide_label" for="evaluations[<?php echo $person['entry_id'];?>][research_score]"><?php echo $person['user_id']; ?> research score</label>
									<input type="text" name="evaluations[<?php echo $person['entry_id'];?>][research_score]" id="evaluations[<?php echo $person['entry_id'];?>][research_score]" size="2"  value="<?php echo isset($person['research_score']) ? $person['research_score'] : ''; ?>" tabindex="-1" readonly></input>
								<?php endif; ?>
							</td>
							
							<!-- Service -->
							<td class="text-center"><?php echo isset($person['overall_service_percentage']) ? '<span id="service_percentage_'.$person['entry_id'].'">'.$person['overall_service_percentage'].'</span>%' : "--"; ?></td>
							<td class="text-center">
								<?php if (isset($person['entry_id'])): ?>
									<label class="hide_label" for="evaluations[<?php echo $person['entry_id'];?>][service_rating]"><?php echo $person['user_id']; ?> service rating</label>
									<input type="text" name="evaluations[<?php echo $person['entry_id'];?>][service_rating]" id="evaluations[<?php echo $person['entry_id'];?>][service_rating]" size="2" value="<?php echo isset($person['service_rating']) ? $person['service_rating'] : ''; ?>"></input>
								<?php endif; ?>
							</td>
							<td class="text-center">
								<?php if (isset($person['entry_id'])): ?>
									<label class="hide_label" for="evaluations[<?php echo $person['entry_id'];?>][service_score]"><?php echo $person['user_id']; ?> service score</label>
									<input type="text" name="evaluations[<?php echo $person['entry_id'];?>][service_score]" id="evaluations[<?php echo $person['entry_id'];?>][service_score]" size="2" value="<?php echo isset($person['service_score']) ? $person['service_score'] : ''; ?>" tabindex="-1" readonly></input>
								<?php endif; ?>
							</td>
							
							<!-- Total -->
							<td class="text-center">
								<?php if (isset($person['entry_id'])): ?>
									<label class="hide_label" for="evaluations[<?php echo $person['entry_id'];?>][total_score]"><?php echo $person['user_id']; ?> total score</label>
									<input type="text" class="total_score_input" name="evaluations[<?php echo $person['entry_id'];?>][total_score]" id="evaluations[<?php echo $person['entry_id'];?>][total_score]" size="2"  value="<?php echo isset($person['total_score']) ? $person['total_score'] : ''; ?>" tabindex="-1" readonly></input>
								<?php endif; ?>
							</td>
							
							<!-- Projected Teaching Load -->
							<td class="text-center">
								<label class="hide_label" for="projected_teaching_loads[<?php echo $person['person_id'];?>]"><?php echo $person['user_id']; ?> teaching load for <?php echo $displayed_year;echo '-';echo$displayed_year+1; ?></label>
								<input type="number" style="width:4em;" name="projected_teaching_loads[<?php echo $person['person_id'];?>]" id="projected_teaching_loads[<?php echo $person['person_id'];?>]" value="<?php echo isset($teaching_loads[$person['person_id']][$displayed_year+1]) ? $teaching_loads[$person['person_id']][$displayed_year+1] : ""; ?>"></input>
							</td>
							
							<td class="text-center display_cell">
								<?php if (isset($person['entry_id'])): ?>
									<input type="checkbox" class="display_to_instructor" name="evaluations[<?php echo $person['entry_id'];?>][display_to_instructor]" value="1" aria-labelledby="display_to_instructor_label" <?php echo isset($person['display_to_instructor']) && $person['display_to_instructor'] ? 'checked' : ''; ?>></input>
								<?php endif; ?>
							</td>
						</tr>
						<?php endif; ?>
					<?php endforeach; ?>
				</table>
				
				<input type="submit" name="save_bulk_evaluation" value="Save Evaluations"></input>
			<?php endif; ?>
			
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>
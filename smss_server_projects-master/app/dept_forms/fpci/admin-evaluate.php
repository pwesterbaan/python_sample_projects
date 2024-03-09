<?php
include('fpci-functions.php');

if (in_array($user_id,$admin_list))
{
	if (isset($_GET['entry']) && $_GET['entry'] != 0 && is_numeric($_GET['entry']))
	{
		$entry_id = $_GET['entry'];
		$percentages = get_percentages_from_entry_id($entry_id);
		
		//get evaluation if it exists
		$evaluation = get_evaluation_from_percentage_entry_id($entry_id);
		
		//get teaching load
		$get_teaching_load_query = $mthsc_db->prepare('SELECT teaching_load FROM fpci_teaching_loads WHERE person_id = ? AND year = ?');
		$get_teaching_load_query->execute(array($percentages['person_id'], $percentages['year']));
		$teaching_load = $get_teaching_load_query->fetchColumn();
		
		$get_teaching_load_query->execute(array($percentages['person_id'], $percentages['year']+1));
		$projected_teaching_load = $get_teaching_load_query->fetchColumn();
	}
	else
	{
		$error = "Invalid ID.";
	}
	
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
	<!-- Date: 2019-1-18 -->
	
	<title>FPCI | Evaluate</title>

<link rel="stylesheet" href="/style/math-internal.css" type="text/css" media="screen" charset="utf-8">

<style type="text/css">
label.hide_label {display:none;}
</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>
<script src="/style/jquery.validate.js" type="text/javascript" charset="utf-8"></script>

<script type="text/javascript">
function calculate_scores()
{
	var teaching_percentage = parseFloat($('#teaching_percentage').html());
	var teaching_rating = parseFloat($('#teaching_rating').val());
	if (!isNaN(teaching_rating))
	{
		var teaching_score = (teaching_percentage*teaching_rating)/100;
		$('#teaching_score').val(teaching_score.toFixed(2));
	}
	else
	{
		$('#teaching_score').val("");
	}

	var research_percentage = parseFloat($('#research_percentage').html());
	var research_rating = parseFloat($('#research_rating').val());
	if (!isNaN(research_rating))
	{
		var research_score = (research_percentage*research_rating)/100;
		$('#research_score').val(research_score.toFixed(2));
	}
	else
	{
		$('#research_score').val("");
	}
	
	var service_percentage = parseFloat($('#service_percentage').html());
	var service_rating = parseFloat($('#service_rating').val());
	if (!isNaN(service_rating))
	{
		var service_score = (service_percentage*service_rating)/100;
		$('#service_score').val(service_score.toFixed(2));
	}
	else
	{
		$('#service_score').val("");
	}
	
	if (!isNaN(teaching_rating) && !isNaN(research_rating) && !isNaN(service_rating))
	{
		//calculate total score
		var total_score = teaching_score + research_score + service_score;
		$('#total_score').val(total_score.toFixed(2));
		$("#submit_evaluation").prop('disabled',false);
	}
	else
	{
		$('#total_score').val("");
		$("#submit_evaluation").prop('disabled',true);
	}
}
$(document).ready(function(){
	$('form').validate({
		debug:false,
		errorContainer: "#messageBox",
		errorLabelContainer: "#messageBox ul",
		wrapper: "li",
		rules: {
			teaching_rating: {
				number: true,
				range: [0,7]
			},
			research_rating: {
				number: true,
				range: [0,7]
			},
			service_rating: {
				number: true,
				range: [0,7]
			}
		},
		messages: {
			teaching_rating: "Teaching rating must be between 0 and 7",
			research_rating: "Research rating must be between 0 and 7",
			service_rating: "Service rating must be between 0 and 7"
		}
	});
	
	$(":input[type=text]:not([readonly])").css({"border-color":"#109DC0","border-style":"solid","border-width":"2px"});
	
	$(":input[type=text]:not([readonly])").on('input', function () {
        this.value = this.value.match(/^[01234567]\.?\d{0,2}/);
    });

	$(":input[type=text]:not([readonly])").keyup(function(){
		calculate_scores();
	})
	
	if ($('#total_score').val() == "")
	{
		$("#submit_evaluation").prop('disabled',true);
	}
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
				<?php if (isset($percentages) && $percentages != NULL): ?>
					<form name="evaluate_form" method="POST" action="admin-view-faculty.php">
						<h1><?php echo get_name_from_person_id($percentages['person_id']); ?></h1>
						<p><?php echo get_subfaculty_from_person_id($percentages['person_id']); ?></p>

						<h2>Evaluation for <?php echo $percentages['year']-1; echo ' - '; echo $percentages['year']; ?></h2>

						<p>Teaching Load: <?php echo $teaching_load; ?></p>
						
						<h2>FPCI Ratings</h2>
						<?php if ($percentages['year'] == get_current_evaluation_year()): ?>
							<p>Enter ratings from 0-7 (to 2 decimal places) for each activity. Scores are calculated automatically.</p>
						<?php endif;?>
						<table class="styled">
							<tr>
								<th scope="col">Activity</th>
								<th scope="col">Percentages</th>
								<th scope="col">FPCI Rating</th>
								<th scope="col">FPCI Score</th>
							</tr>
							<tr>
								<td>Teaching</td>
								<td class="text-center"><span id="teaching_percentage"><?php echo $percentages['overall_teaching_percentage']?></span>%</td>
								<td class="text-center"><label class="hide_label" for="teaching_rating">Teaching Rating</label><input type="text" name="teaching_rating" id="teaching_rating" size="3" value="<?php echo $evaluation['teaching_rating'];?>"></input></td>
								<td class="text-center"><label class="hide_label" for="teaching_score">Teaching Score</label><input type="text" name="teaching_score" id="teaching_score" size="3" value="<?php echo $evaluation['teaching_score'];?>" tabindex="-1" readonly></input></td>
							</tr>
							<tr>
								<td>Research</td>
								<td class="text-center"><span id="research_percentage"><?php echo $percentages['overall_research_percentage']?></span>%</td>
								<td class="text-center"><label class="hide_label" for="research_rating">Research Rating</label><input type="text" name="research_rating" id="research_rating" size="3" value="<?php echo $evaluation['research_rating'];?>"></input></td>
								<td class="text-center"><label class="hide_label" for="research_score">Research Score</label><input type="text" name="research_score" id="research_score" size="3" value="<?php echo $evaluation['research_score'];?>" tabindex="-1" readonly></input></td>
							</tr>
							<tr>
								<td>Service</td>
								<td class="text-center"><span id="service_percentage"><?php echo $percentages['overall_service_percentage']?></span>%</td>
								<td class="text-center"><label class="hide_label" for="service_rating">Service Rating</label><input type="text" name="service_rating" id="service_rating" size="3" value="<?php echo $evaluation['service_rating'];?>"></input></td>
								<td class="text-center"><label class="hide_label" for="service_score">Service Score</label><input type="text" name="service_score" id="service_score" size="3" value="<?php echo $evaluation['service_score'];?>" tabindex="-1" readonly></input></td>
							</tr>
							<tr>
								<td colspan="3" style="text-align:right;">Overall Score</td>
								<td class="text-center"><label class="hide_label" for="total_score">Total Score</label><input name="total_score" id="total_score" size="3" value="<?php echo $evaluation['total_score'];?>" tabindex="-1" readonly></input></td>
						</table>
						<div id="messageBox">
							<ul>
							</ul>
						</div>
						<!--
						<br>
						<h2><label for="FAS_rating">eForm3 Rating</label></h2>
						<p><select name="FAS_rating" id="FAS_rating">
							<option value="">Select a rating...</option>
							<option value="Unsatisfactory" <?php //echo isset($evaluation['FAS_rating']) && $evaluation['FAS_rating'] == "Unsatisfactory" ? 'selected' : ''; ?> >Unsatisfactory</option>
							<option value="Marginal" <?php //echo isset($evaluation['FAS_rating']) && $evaluation['FAS_rating'] == "Marginal" ? 'selected' : ''; ?> >Marginal</option>
							<option value="Fair" <?php //echo isset($evaluation['FAS_rating']) && $evaluation['FAS_rating'] == "Fair" ? 'selected' : ''; ?> >Fair</option>
							<option value="Good" <?php //echo isset($evaluation['FAS_rating']) && $evaluation['FAS_rating'] == "Good" ? 'selected' : ''; ?> >Good</option>
							<option value="Very Good" <?php //echo isset($evaluation['FAS_rating']) && $evaluation['FAS_rating'] == "Very Good" ? 'selected' : ''; ?> >Very Good</option>
							<option value="Excellent" <?php //echo isset($evaluation['FAS_rating']) && $evaluation['FAS_rating'] == "Excellent" ? 'selected' : ''; ?> >Excellent</option>
						</select></p>
						<br>
						<h2><label for="FAS_evaluation">eForm3 Evaluation</label></h2>
						<p><textarea name="FAS_evaluation" id="FAS_evaluation" placeholder="eForm3 Evaluation will still need to be entered into FAS separately" rows="6" cols="80"><?php echo isset($evaluation['FAS_evaluation']) ? $evaluation['FAS_evaluation'] : ''; ?></textarea></p>
						
						<br>
						<p>Note that eForm3 rating and evaluation are included here only for completeness. This utility does not send any data to FAS.</p>-->
						
						
						
						<?php if ($percentages['year'] == get_current_evaluation_year() || $allow_edits_to_previous_years_evaluations): ?>
						<p>
							<input type="hidden" name="percentage_entry_id" value="<?php echo $percentages['entry_id'];?>"></input>
							<input type="hidden" name="year" value="<?php echo $percentages['year'];?>"></input>
							<input type="hidden" name="person_id" value="<?php echo $percentages['person_id'];?>"></input>
							<input type="hidden" name="user_id" value="<?php echo $percentages['user_id'];?>"></input>
							
							<input type="checkbox" name="display_to_instructor" id="display_to_instructor" value="1" <?php echo $evaluation['display_to_instructor'] ? 'checked' : ''; ?>></input>
							<label for="display_to_instructor">Allow instructor to view these ratings/scores</label>
						</p>
						
						<p>
							<label for="projected_teaching_load">Teaching Load for <?php echo $percentages['year']; echo '-'; echo $percentages['year']+1;?></label>: 
							<input type="number" name="projected_teaching_load" id="projected_teaching_load" value="<?php echo $projected_teaching_load;?>" style="width:4em;">
						</p>
						
						<p>
							<input type="submit" name="submit_evaluation" id="submit_evaluation" value="Save Evaluation"></input>
						</p>
						<?php endif; ?>
					</form>
					
				<?php endif; ?>
			<?php endif; ?>
			
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>
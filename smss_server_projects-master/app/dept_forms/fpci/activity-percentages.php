<?php

include('fpci-functions.php');

$person_id = get_person_id($user_id);

if (isset($_POST['submit_percentages']))
{
	if ($_POST['division'] == "")
	{
		$error = "No Division found. Please contact an admin assistant to set your primary subfaculty.";
	}
	else
	{
		$submission = $_POST;
		unset($submission['submit_percentages']);
		unset($submission['update']);
	
		//echo '<pre>';print_r($submission);echo '</pre>';
	
		if ($_POST['update'] == 1) //they've already submitted, just updating
		{
			unset($submission['user_id']);
			$update_query = $mthsc_db->prepare("UPDATE fpci_percentages SET summer_course_credits = :summer_course_credits, summer_teaching_percentage = :summer_teaching_percentage, summer_research_days = :summer_research_days, summer_research_percentage = :summer_research_percentage, summer_service_days = :summer_service_days, summer_service_percentage = :summer_service_percentage, fall_teaching_percentage = :fall_teaching_percentage, fall_research_percentage = :fall_research_percentage, fall_service_percentage = :fall_service_percentage, spring_teaching_percentage = :spring_teaching_percentage, spring_research_percentage = :spring_research_percentage, spring_service_percentage = :spring_service_percentage, overall_teaching_percentage = :overall_teaching_percentage, overall_research_percentage = :overall_research_percentage, overall_service_percentage = :overall_service_percentage, division = :division WHERE year = :year AND person_id = :person_id;");
			$result = $update_query->execute($submission);
			if ($result) {$message = "Percentages Saved";}else {$message = "Something went wrong, percentages not saved";}
		}
		if ($_POST['update'] == 0) //new submission for the year
		{
			$insert_query = $mthsc_db->prepare("INSERT INTO fpci_percentages (year, person_id, user_id, division, summer_course_credits, summer_teaching_percentage, summer_research_days, summer_research_percentage, summer_service_days, summer_service_percentage, fall_teaching_percentage, fall_research_percentage, fall_service_percentage, spring_teaching_percentage, spring_research_percentage, spring_service_percentage, overall_teaching_percentage, overall_research_percentage, overall_service_percentage) VALUES (:year, :person_id, :user_id, :division, :summer_course_credits, :summer_teaching_percentage, :summer_research_days, :summer_research_percentage, :summer_service_days, :summer_service_percentage, :fall_teaching_percentage, :fall_research_percentage, :fall_service_percentage, :spring_teaching_percentage, :spring_research_percentage, :spring_service_percentage, :overall_teaching_percentage, :overall_research_percentage, :overall_service_percentage)");
			$result = $insert_query->execute($submission);
			if ($result) {$message = "Percentages Saved";}else {$message = "Something went wrong, percentages not saved";}
		}
	}
}


if ($person_id != 0)
{
	//see if allowing edits
	$percentage_entry_cutoff = get_percentage_entry_cutoff();
	if ($percentage_entry_cutoff > strtotime("now"))
	{
		$accepting_edits = true;
	}
	else
	{
		$accepting_edits = false;
	}
	
	//check for eligibility
	//We use the "Faculty Evaluation List" list to see if they are eligible (and required) to submit
	$on_evaluation_list = is_person_on_evaluation_list($person_id);
	if ($on_evaluation_list)
	{$eligible_for_entry = true;}
	else
	{$eligible_for_entry = false;}
	
	//check for existing submission for current year
	$year = get_current_evaluation_year();
	$percentages = get_percentages($person_id,$year);
	$division = get_division_from_person_id($person_id);
	if ($percentages)
	{
		$update = 1; //they've already submitted this year
	}
	else
	{
		$update = 0; //new submission for the year
	}
}
else
{
	$error = "This form is for School of Mathematical and Statistical Sciences faculty only.";
}

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">


<html lang="en-US">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="author" content="hedetni">
	<!-- Date: 2018-11-28 -->
	
	<title>FPCI | Activity Percentage Entry</title>

<link rel="stylesheet" href="/style/math-internal.css" type="text/css" media="screen" charset="utf-8">

<style type="text/css">
.text-right {text-align:right;}
input.error {border-color:red !important; border-style:solid;}
input {text-align:right;}
label.hide_label {display:none;}
</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>
<script src="/style/jquery.validate.js" type="text/javascript" charset="utf-8"></script>


<script type="text/javascript">
function toggle_explanation()
{
	$("#explanation").slideToggle();
}
function update_totals()
{
	var summer_total = parseFloat($("#summer_teaching_percentage").val())+parseFloat($("#summer_research_percentage").val())+parseFloat($("#summer_service_percentage").val());
	$("#summer_percentage_total").val(summer_total);
	
	var fall_total = parseFloat($("#fall_teaching_percentage").val())+parseFloat($("#fall_research_percentage").val())+parseFloat($("#fall_service_percentage").val());
	$("#fall_percentage_total").val(fall_total);
	
	var spring_total = parseFloat($("#spring_teaching_percentage").val())+parseFloat($("#spring_research_percentage").val())+parseFloat($("#spring_service_percentage").val());
	$("#spring_percentage_total").val(spring_total);
	
	/*
	var overall_total = parseFloat($("#overall_teaching_percentage").val())+parseFloat($("#overall_research_percentage").val())+parseFloat($("#overall_service_percentage").val());
	$("#overall_percentage_total").val(overall_total);*/
}

function update_overall_percentages()
{
	//teaching
	var teaching_top = $("#summer_teaching_percentage").val()*0.66;
	teaching_top = teaching_top+parseFloat($("#fall_teaching_percentage").val())+parseFloat($("#spring_teaching_percentage").val());
	var teaching_bottom = $("#summer_percentage_total").val()*0.66;
	teaching_bottom = teaching_bottom+200;
	overall_teaching = 100*(teaching_top/teaching_bottom);
	$("#overall_teaching_percentage").val(overall_teaching.toFixed(0));
	
	//research
	var research_top = $("#summer_research_percentage").val()*0.66;
	research_top = research_top+parseFloat($("#fall_research_percentage").val())+parseFloat($("#spring_research_percentage").val());
	var research_bottom = $("#summer_percentage_total").val()*0.66;
	research_bottom = research_bottom+200;
	overall_research = 100*(research_top/research_bottom);
	$("#overall_research_percentage").val(overall_research.toFixed(0));
	
	//service
	var service_top = $("#summer_service_percentage").val()*0.66;
	service_top = service_top+parseFloat($("#fall_service_percentage").val())+parseFloat($("#spring_service_percentage").val());
	var service_bottom = $("#summer_percentage_total").val()*0.66;
	service_bottom = service_bottom+200;
	overall_service = 100*(service_top/service_bottom);
	$("#overall_service_percentage").val(overall_service.toFixed(0));
	
	overall_total = overall_teaching+overall_research+overall_service;
	$("#overall_percentage_total").val(overall_total);
}

function check_percentage_totals()
{
	if (parseInt($("#overall_teaching_percentage").val()) > 100 || $("#overall_teaching_percentage").val() == "NaN")
	{$("#overall_teaching_percentage").addClass('error');$("#submit_percentages").attr("disabled",true);}else{$("#overall_teaching_percentage").removeClass('error');$("#submit_percentages").attr("disabled",false);}
	
	if (parseInt($("#overall_research_percentage").val()) > 100 || $("#overall_research_percentage").val() == "NaN")
	{$("#overall_research_percentage").addClass('error');$("#submit_percentages").attr("disabled",true);}else{$("#overall_research_percentage").removeClass('error');$("#submit_percentages").attr("disabled",false);}
	
	if (parseInt($("#overall_service_percentage").val()) > 100 || $("#overall_service_percentage").val() == "NaN")
	{$("#overall_service_percentage").addClass('error');$("#submit_percentages").attr("disabled",true);}else{$("#overall_service_percentage").removeClass('error');$("#submit_percentages").attr("disabled",false);}
	
	if (parseInt($("#summer_percentage_total").val()) > 100 || $("#summer_percentage_total").val() == "NaN")
	{$("#summer_percentage_total").addClass('error');$("#submit_percentages").attr("disabled",true);}else{$("#summer_percentage_total").removeClass('error');$("#submit_percentages").attr("disabled",false);}
	
	if (parseInt($("#fall_percentage_total").val()) > 100 || $("#fall_percentage_total").val() == "NaN")
	{$("#fall_percentage_total").addClass('error');$("#submit_percentages").attr("disabled",true);}else{$("#fall_percentage_total").removeClass('error');$("#submit_percentages").attr("disabled",false);}
	
	if (parseInt($("#spring_percentage_total").val()) > 100 || $("#spring_percentage_total").val() == "NaN")
	{$("#spring_percentage_total").addClass('error');$("#submit_percentages").attr("disabled",true);}else{$("#spring_percentage_total").removeClass('error');$("#submit_percentages").attr("disabled",false);}
	
	if (parseInt($("#overall_percentage_total").val()) > 100 || $("#overall_percentage_total").val() == "NaN")
	{$("#overall_percentage_total").addClass('error');$("#submit_percentages").attr("disabled",true);}else{$("#overall_percentage_total").removeClass('error');$("#submit_percentages").attr("disabled",false);}
}

$(document).ready(function(){
	$('form').validate({
		debug:false,
		errorContainer: "#messageBox",
		errorLabelContainer: "#messageBox ul",
		wrapper: "li",
		rules: {
			summer_course_credits: {
				required: true,
				number: true,
				max: 10,
			},
			summer_research_days: {
				required: true,
				number: true,
				max: 64
			},
			summer_service_days: {
				required: true,
				number: true,
				max: 64
			},
			fall_teaching_percentage: {
				required: true,
				number: true,
				range: [0,100]
			},
			fall_research_percentage: {
				required: true,
				number: true,
				range: [0,100]
			},
			fall_service_percentage: {
				required: true,
				number: true,
				range: [0,100]
			},
			spring_teaching_percentage: {
				required: true,
				number: true,
				range: [0,100]
			},
			spring_research_percentage: {
				required: true,
				number: true,
				range: [0,100]
			},
			spring_service_percentage: {
				required: true,
				number: true,
				range: [0,100]
			},
			fall_percentage_total: {
				required: true,
				number: true,
				min: 100,
				max: 100
			},
		},
		messages: {
			summer_course_credits: "Max of 10 summer course credits",
			summer_research_days: "Max of 64 days of paid research",
			summer_service_days: "Max of 64 days of paid research",
			fall_teaching_percentage: "Number between 0 and 100 required",
			fall_research_percentage: "Number between 0 and 100 required",
			fall_service_percentage: "Number between 0 and 100 required",
			spring_teaching_percentage: "Number between 0 and 100 required",
			spring_research_percentage: "Number between 0 and 100 required",
			spring_service_percentage: "Number between 0 and 100 required",
		}
	});
	
	$('#summer_course_credits').keyup(function() {
		var credits = parseInt($('#summer_course_credits').val());
		if ($.isNumeric(credits))
		{
			percentage = credits*10;
			$("#summer_teaching_percentage").val(percentage);
		}
		update_totals();
		update_overall_percentages();
		check_percentage_totals();
	});
	
	$('#summer_research_days').keyup(function() {
		var credits = parseInt($('#summer_research_days').val());
		if ($.isNumeric(credits))
		{
			percentage = credits*1.57;
			$("#summer_research_percentage").val(percentage);
		}
		update_totals();
		update_overall_percentages();
		check_percentage_totals();
	});
	
	$('#summer_service_days').keyup(function() {
		var credits = parseInt($('#summer_service_days').val());
		if ($.isNumeric(credits))
		{
			percentage = credits*1.57;
			$("#summer_service_percentage").val(percentage);
		}
		update_totals();
		update_overall_percentages();
		check_percentage_totals();
	});
	
	$('input.fall').keyup(function(){
		update_totals();
		update_overall_percentages();
		check_percentage_totals();
	});
	
	$('input.spring').keyup(function(){
		update_totals();
		update_overall_percentages();
		check_percentage_totals();
	});
	
	$('input').click(function(){
		$(this).select();
	});
	
	$(":input[type=text]:not([readonly])").css({"border-color":"#109DC0","border-style":"solid","border-width":"2px"});
	$(":input[readonly]").css("background-color","#eee");
	
	update_totals();
	update_overall_percentages();
	check_percentage_totals();
	
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
			<h1>FPCI Activity Percentage Entry for <?php echo $year-1;echo '-'; echo $year; ?></h1>
			
			<?php if ($person_id != 0 && $eligible_for_entry): ?>
				<?php if ($accepting_edits || $person_id == 254): ?>
					<p>Fill in the blue bordered boxes. All other numbers are calculated automatically. The semester percentage totals for fall and spring as well as the overall percentage total should each equal 100%. Summer percentage totals have been disabled for the present time. 
						
					<p>The cutoff date for changes is <?php echo date("F j, Y, g:i a",$percentage_entry_cutoff); ?>.</p>
					
					<p><a href="javascript:toggle_explanation();">How overall percentage is calculated</a></p>
					<div id="explanation" style="display:none;">
						
						<!--<h2>Summer</h2>
						
						<ul>
							<li>Each credit hour of a summer class is ~ 6.24 days</li>
							<li>One month summer support is ~ 22 days, so ½ month is 11 days (this is slightly higher than what is used in practice)</li>
							<li>Summer pay is maxed out one of the following:
								<ul>
									<li>10 credit hours of teaching (e.g. two 3-hour courses and one 4 hour course), or</li>
									<li>64 days paid of research or service, or</li>
									<li>a combination of teaching and research and service that totals 64 days, counting each credit hour taught as 6.24 days.</li>
								</ul></li>
							<li>Summer percentage total is the sum of:
								<ul>
									<li>10 x number of credit hours taught, (referred to as T%<sub>summer</sub> below)</li>
									<li>1.57 x [research days + service days] (since 1.57 is approximately 100 x [1/64])</li>
									<li>The percentage total should be no more than 100</li>
								</ul></li>
						</ul>
					-->
						<h2>Overall</h2>
						
						<ul>
							<li>The formula used to calculate overall percentage for teaching is:<br>
								<table style="margin-left:auto;margin-right:auto;">
									<tr><td rowspan="2">T% = </td>
									<td style="border-bottom:2px solid black;text-align:center;">(0.66 x T%<sub>summer</sub>) + T%<sub>fall</sub> + T%<sub>spring</sub></td></tr>
									<tr><td style="text-align:center;"> (0.66 x Total%<sub>summer</sub>) + 200</td></tr>
								</table>
								where Total%<sub>summer</sub> = T%<sub>summer</sub> + R%<sub>summer</sub> + S%<sub>summer</sub> = [10 x number of credit hours taught] + [1.57 x research days] + [1.57 x service days]</li>
							<li>Summer pay is maxed out with 33% of the nine-month salary, i.e. 66% of a regular semester salary, resulting in the 0.66 weighting for the summer percentages in the formula above</li>
							<li>R% and S% represent research and service percentages, respectively, and are calculated using the same formula as above, replacing T% with R% and S%, respectively.</li>
					</div>
					
					<!--<p>Note: the number of summer days for research should be the number of days that a faculty member received support in some form in return for research related activities.</p>-->
					
					<form name="fpci_percentage_form" method="POST" action="">
					<table class="styled">
						<tr>
							<th scope="col">Activity</th>
							<th scope="col">Summer (disabled)</th>
							<th scope="col">Fall</th>
							<th scope="col">Spring</th>
							<th scope="col">Overall</th>
						</tr>
						<!-- Teaching -->
						<tr>
							<td>Teaching</td>
							<td class="text-right">
								<label for="summer_course_credits">Summer course credits</label> <input type="text" size="2" name="summer_course_credits" id="summer_course_credits" value="<?php echo isset($percentages['summer_course_credits']) ? $percentages['summer_course_credits'] : "0";?>" readonly> 
								<label for="summer_teaching_percentage" class="hide_label">Summer Teaching Percentage</label><input type="text" size="2" name="summer_teaching_percentage" id="summer_teaching_percentage" value="<?php echo isset($percentages['summer_teaching_percentage']) ? $percentages['summer_teaching_percentage'] : "0";?>" tabindex="-1" readonly></input>%
							</td>
							<td>
								<label for="fall_teaching_percentage" class="hide_label">Fall Teaching Percentage</label><input type="text" size="2" name="fall_teaching_percentage" id="fall_teaching_percentage" class="fall teaching" value="<?php echo isset($percentages['fall_teaching_percentage']) ? $percentages['fall_teaching_percentage'] : "0";?>"></input>%
							</td>
							<td>
								<label for="spring_teaching_percentage" class="hide_label">Spring Teaching Percentage</label><input type="text" size="2" name="spring_teaching_percentage" id="spring_teaching_percentage" class="spring teaching" value="<?php echo isset($percentages['spring_teaching_percentage']) ? $percentages['spring_teaching_percentage'] : "0";?>"></input>%
							</td>
							<td>
								<label for="overall_teaching_percentage" class="hide_label">Overall Teaching Percentage</label><input type="text" size="2" name="overall_teaching_percentage" id="overall_teaching_percentage" value="<?php echo isset($percentages['overall_teaching_percentage']) ? $percentages['overall_teaching_percentage'] : "0";?>"tabindex="-1" readonly></input>%
							</td>
						</tr>
				
						<tr>
							<td>Research</td>
							<td class="text-right">
								<label for="summer_research_days">Summer research days</label> <input type="text" size="2" name="summer_research_days" id="summer_research_days" value="<?php echo isset($percentages['summer_research_days']) ? $percentages['summer_research_days'] : "0";?>" readonly> 
								<label for="summer_research_percentage" class="hide_label">Summer Research Percentage</label><input type="text" size="2" name="summer_research_percentage" id="summer_research_percentage" value="<?php echo isset($percentages['summer_research_percentage']) ? $percentages['summer_research_percentage'] : "0";?>" tabindex="-1" readonly></input>%</td>
							<td><label for="fall_research_percentage" class="hide_label">Fall Research Percentage</label><input type="text" size="2" name="fall_research_percentage" id="fall_research_percentage" class="fall research" value="<?php echo isset($percentages['fall_research_percentage']) ? $percentages['fall_research_percentage'] : "0";?>"></input>%</td>
							<td><label for="spring_research_percentage" class="hide_label">Spring Research Percentage</label><input type="text" size="2" name="spring_research_percentage" id="spring_research_percentage" class="spring research" value="<?php echo isset($percentages['spring_research_percentage']) ? $percentages['spring_research_percentage'] : "0";?>"></input>%</td>
							<td><label for="overall_research_percentage" class="hide_label">Overall Research Percentage</label><input type="text" size="2" name="overall_research_percentage" id="overall_research_percentage" value="<?php echo isset($percentages['overall_research_percentage']) ? $percentages['overall_research_percentage'] : "0";?>" tabindex="-1" readonly></input>%</td>
						</tr>
				
						<tr>
							<td>Service</td>
							<td class="text-right">
								<label for="summer_service_days">Summer service days</label> <input type="text" size="2" name="summer_service_days" id="summer_service_days" value="<?php echo isset($percentages['summer_service_days']) ? $percentages['summer_service_days'] : "0";?>" readonly> 
								<label for="summer_service_percentage" class="hide_label">Summer Service Percentage</label><input type="text" size="2" name="summer_service_percentage" id="summer_service_percentage" value="<?php echo isset($percentages['summer_service_percentage']) ? $percentages['summer_service_percentage'] : "0";?>" tabindex="-1" readonly></input>%</td>
							<td><label for="fall_service_percentage" class="hide_label">Fall Service Percentage</label><input type="text" size="2" name="fall_service_percentage" id="fall_service_percentage" class="fall service" value="<?php echo isset($percentages['fall_service_percentage']) ? $percentages['fall_service_percentage'] : "0";?>"></input>%</td>
							<td><label for="spring_service_percentage" class="hide_label">Spring Service Percentage</label><input type="text" size="2" name="spring_service_percentage" id="spring_service_percentage" class="fall service" value="<?php echo isset($percentages['spring_service_percentage']) ? $percentages['spring_service_percentage'] : "0";?>"></input>%</td>
							<td><label for="overall_service_percentage" class="hide_label">Overall Service Percentage</label><input type="text" size="2" name="overall_service_percentage" id="overall_service_percentage" value="<?php echo isset($percentages['overall_service_percentage']) ? $percentages['overall_service_percentage'] : "0";?>" tabindex="-1" readonly></input>%</td>
						</tr>
				
						<tr>
							<td>Semester Totals</td>
							<td class="text-right"><label for="summer_percentage_total" class="hide_label">Summer Percentage Total</label><input type="text" size="2" id="summer_percentage_total" value="0" tabindex="-1" readonly></input>%</td>
							<td><label for="fall_percentage_total" class="hide_label">Fall Percentage Total</label><input type="text" size="2" id="fall_percentage_total" value="0" tabindex="-1" readonly></input>%</td>
							<td><label for="spring_percentage_total" class="hide_label">Spring Percentage Total</label><input type="text" size="2" id="spring_percentage_total" value="0" tabindex="-1" readonly></input>%</td>
							<td><label for="overall_percentage_total" class="hide_label">Overall Percentage Total</label><input type="text" size="2" id="overall_percentage_total" value="0" tabindex="-1" readonly></input>%</td>
						</tr>
					</table>
			
					<div id="messageBox">
						<ul>
						</ul>
					</div>
			
					<p>
						<input type="hidden" name="update" value="<?php echo $update;?>"></input>
						<input type="hidden" name="user_id" value="<?php echo $user_id;?>"></input>
						<input type="hidden" name="person_id" value="<?php echo $person_id;?>"></input>
						<input type="hidden" name="year" value="<?php echo $year;?>"></input>
						<input type="hidden" name="division" value="<?php echo $division;?>"></input>
						<input type="submit" name="submit_percentages" id="submit_percentages" value="Save Percentages"></input>
					</p>
			
					</form>
					
					
				<?php else: ?>
					<p>We are not accepting edits to activity percentages at this time.</p>
				<?php endif; ?>
			<?php else: ?>
				<p>This form is for eligible faculty members of the School of Mathematical and Statistical Sciences only.</p>
			<?php endif;?>
			
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>
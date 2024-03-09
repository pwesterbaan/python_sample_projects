<?php

date_default_timezone_set('America/New_York');
$currentTime = strtotime('now');
$currentDateTime = date("Y-m-d H:i:s");

$admins = array('HEDETNI');

//connects to the database, returns a semi-useful error if not accessible.
$link = mysql_connect("mthsc.clemson.edu", "survey", "VmHTSSlejdCUswK4");
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
}

//returns true or false depending on whether the logged in user is part of the sci_0800_budctr_workgroup
function isInCoS()
{
	$inCOS = null;
	if (isset($_SERVER['clemsonGroup']))
	{
		$groupString = $_SERVER['clemsonGroup'];
		$groups = explode(";",$groupString);
		if (in_array("sci_0800_budctr_workgroup",$groups))
		{return true;}
		else
		{return false;}
	}
	else
	{return false;}
}


//----------
// ASSEMBLE FUNCTIONS
//----------

//main question assembly function
function assemble_question($question_type,$question_id,$question_statement)
{
	switch($question_type)
	{
		case "likert3":
			return assemble_likert3($question_id,$question_statement);
			break;
		case "likert5":
			return assemble_likert5($question_id,$question_statement);
			break;
		case "likert7":
			return assemble_likert7($question_id,$question_statement);
			break;
		case "free_response":
			return assemble_free_response($question_id,$question_statement);
			break;
		case "select_one":
			return assemble_select_one($question_id,$question_statement);
			break;
		case "select_many":
			return assemble_select_many($question_id,$question_statement);
			break;
	}
}

//accepts question id (to use in POST form name) and question statement
//returns html of the assembled question
function assemble_likert5($question_id,$question_statement)
{
	$html = '<p>'.$question_statement.'<br>';
	$html .= '<table class="likert5">
				<tr>
					<td class="center">Strongly Agree<br><input type="radio" name="question_'.$question_id.'" value="Strongly Agree"></td>
					<td class="center">Agree<br><input type="radio" name="question_'.$question_id.'" value="Agree"></td>
					<td class="center">Neutral<br><input type="radio" name="question_'.$question_id.'" value="Neutral"></td>
					<td class="center">Disagree<br><input type="radio" name="question_'.$question_id.'" value="Disagree"></td>
					<td class="center">Strongly Disagree<br><input type="radio" name="question_'.$question_id.'" value="Strongly Disagree"></td>
				</tr>
			</table></p>';
	return $html;
}

//accepts question id (to use in POST form name) and question statement
//returns html of the assembled question
function assemble_likert3($question_id,$question_statement)
{
	$html = '<p>'.$question_statement.'<br>';
	$html .= '<table class="likert3">
				<tr>
					<td class="center">Agree<br><input type="radio" name="question_'.$question_id.'" value="Agree"></td>
					<td class="center">Neutral<br><input type="radio" name="question_'.$question_id.'" value="Neutral"></td>
					<td class="center">Disagree<br><input type="radio" name="question_'.$question_id.'" value="Disagree"></td>
				</tr>
			</table></p>';
	return $html;
}

function assemble_free_response($question_id,$question_statement)
{
	$html = '<p>'.$question_statement.'<br>';
	$html .= '<textarea name="question_'.$question_id.'" rows="5" cols="80"></textarea>';
	return $html;
}


//----------
// GET RESPONSES FUNCTIONS
//----------

//main question assembly function
function get_question_responses($question_type,$question_id,$question_statement,$survey_id)
{
	switch($question_type)
	{
		case "likert3":
			return get_likert3_responses($question_id,$question_statement,$survey_id);
			break;
		case "likert5":
			return get_likert5_responses($question_id,$question_statement,$survey_id);
			break;
		case "likert7":
			return get_likert7_responses($question_id,$question_statement,$survey_id);
			break;
		case "free_response":
			return get_free_response_responses($question_id,$question_statement,$survey_id);
			break;
		case "select_one":
			return get_select_one_responses($question_id,$question_statement,$survey_id);
			break;
		case "select_many":
			return get_select_many_responses($question_id,$question_statement,$survey_id);
			break;
	}
}

//accepts question id, question statement, and candidate id
//returns html of the assembled question with response data
function get_likert3_responses($question_id,$question_statement,$survey_id)
{
	//get response data
	$getResponses = mysql_query('SELECT * FROM responses WHERE survey_id='.$survey_id.' AND question_id='.$question_id.';');
	$responses = array();
	while ($row = mysql_fetch_array($getResponses))
	{
		$responses[] = $row['question_response'];
	}
	$responseCounts = array_count_values($responses);
	if (!isset($responseCounts['Agree'])){$responseCounts['Agree'] = 0;}
	if (!isset($responseCounts['Neutral'])){$responseCounts['Neutral'] = 0;}
	if (!isset($responseCounts['Disagree'])){$responseCounts['Disagree'] = 0;}
	$html = '<p>'.$question_statement.'<br>';
	$html .= '<table class="likert3">
				<tr>
					<td class="center">Agree<br>'.$responseCounts["Agree"].'</td>
					<td class="center">Neutral<br>'.$responseCounts["Neutral"].'</td>
					<td class="center">Disagree<br>'.$responseCounts["Disagree"].'</td>
				</tr>
			</table></p>';
	return $html;
}

//accepts question id, question statement, and survey id
//returns html of the assembled question with response data
function get_likert5_responses($question_id,$question_statement,$survey_id)
{
	//get response data
	$getResponses = mysql_query('SELECT * FROM responses WHERE survey_id='.$survey_id.' AND question_id='.$question_id.';');
	$responses = array();
	while ($row = mysql_fetch_array($getResponses))
	{
		$responses[] = $row['question_response'];
	}
	$responseCounts = array_count_values($responses);
	if (!isset($responseCounts['Strongly Agree'])){$responseCounts['Strongly Agree'] = 0;}
	if (!isset($responseCounts['Agree'])){$responseCounts['Agree'] = 0;}
	if (!isset($responseCounts['Neutral'])){$responseCounts['Neutral'] = 0;}
	if (!isset($responseCounts['Disagree'])){$responseCounts['Disagree'] = 0;}
	if (!isset($responseCounts['Strongly Disagree'])){$responseCounts['Strongly Disagree'] = 0;}
	$html = '<p>'.$question_statement.'<br>';
	$html .= '<table class="likert5">
				<tr>
					<td class="center">Strongly Agree<br>'.$responseCounts["Strongly Agree"].'</td>
					<td class="center">Agree<br>'.$responseCounts["Agree"].'</td>
					<td class="center">Neutral<br>'.$responseCounts["Neutral"].'</td>
					<td class="center">Disagree<br>'.$responseCounts["Disagree"].'</td>
					<td class="center">Strongly Disagree<br>'.$responseCounts["Strongly Disagree"].'</td>
				</tr>
			</table></p>';
	return $html;
}

//accepts question id, question statement, and survey id
//returns html of the assembled question with response data
function get_free_response_responses($question_id,$question_statement,$survey_id)
{
	//get response data
	$getResponses = mysql_query('SELECT * FROM responses WHERE survey_id='.$survey_id.' AND question_id='.$question_id.';');
	$responses = array();
	while ($row = mysql_fetch_array($getResponses))
	{
		$responses[] = $row['question_response'];
	}
	$html = '<p>'.$question_statement.'<br>';
	foreach ($responses as $response)
	{
		$html .= '<div class="free_response">'.$response.'</div>';
	}
	return $html;
}

?>
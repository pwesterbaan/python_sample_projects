<?php

if (isset($_POST['fetch_data']))
{
	$banner_user = 'math_sciences';
	$banner_pass = 'Abel1aN!sleiN';
	$banner_charset = 'utf8';

	$banner_dsn = 'oci:dbname=//unidb02.clemson.edu:1521/pedwsods;charset='.$banner_charset;
	$banner_opt = array(
	   	PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
	   	PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	   	PDO::ATTR_EMULATE_PREPARES   => false
		);
	$banner_db = new PDO($banner_dsn, $banner_user, $banner_pass, $banner_opt);
	date_default_timezone_set('America/New_York');
	
	$term = $_POST['term'];
	$subject = strtoupper($_POST['subject']);
	$course = $_POST['course'];
	
	$dfw = array('D','F','NP','W');
	$lettergrades = array('A','B','C','D','F','W');
	$passfail = array('P','NP','W');
	
	// get grades
	$grades_query = $banner_db->prepare('SELECT reg.term_code,reg.xid,(CASE grade when \'FGF\' THEN \'F\' when \'FGD\' THEN \'D\' when \'A-\' THEN \'A\' when \'B\+\' THEN \'B\' when \'B-\' THEN \'B\' when \'C\+\' THEN \'C\' when \'C-\' THEN \'C\' ELSE grade END) AS grade,reg.section_number,student_type_desc,bridge_crosser FROM SIS_MTHSC_STUDENT_REGISTRATION reg, SIS_MTHSC_STUDENT_HISTORY hist, SIS_MTHSC_ACCEPTED_STUDENTS acc WHERE reg.xid = hist.xid AND reg.xid = acc.xid(+) AND EFFECTIVE_TERM_BEG <= ? AND EFFECTIVE_TERM_END > ? AND reg.term_code = ? AND reg.subject_code = ? AND reg.course_number = ? ORDER BY xid');
	$grades_result = $grades_query->execute(array($term,$term,$term,$subject,$course));
	$grades_data = $grades_query->fetchAll();
	
	// set up arrays
	$grades_by_grade = array();
	$grades_by_student_type = array();
	$dfw_by_student_type = array();
	$bridge_students_by_grade = array();
	$total_students = 0;
	$total_dfw = 0;
	$total_bridge = 0;
	$total_bridge_dfw = 0;
	foreach($grades_data as $record)
	{
		if (!isset($grades_by_student_type[$record['STUDENT_TYPE_DESC']]))
		{
			$grades_by_student_type[$record['STUDENT_TYPE_DESC']] = array();
		}
		
		if ( in_array($record['GRADE'],$lettergrades) ||  in_array($record['GRADE'],$passfail) )
		{
			$total_students++;
			if (in_array($record['GRADE'],$dfw))
			{
				$total_dfw++;
			}
			if (!isset($grades_by_grade[$record['GRADE']]))
			{
				$grades_by_grade[$record['GRADE']] = array();
			}
			if (!isset($grades_by_student_type[$record['STUDENT_TYPE_DESC']][$record['GRADE']]))
			{
				$grades_by_student_type[$record['STUDENT_TYPE_DESC']][$record['GRADE']] = array();
			}
			$grades_by_grade[$record['GRADE']][] = $record['STUDENT_TYPE_DESC'];
			$grades_by_student_type[$record['STUDENT_TYPE_DESC']][$record['GRADE']][] = $record['XID'];
			
			if ( in_array($record['GRADE'],$dfw) )
			{
				$dfw_by_student_type[$record['STUDENT_TYPE_DESC']][] = $record['XID'];
			}
			
			// check for bridge
			if (!isset($bridge_students_by_grade[$record['GRADE']]))
			{
				$bridge_students_by_grade[$record['GRADE']] = array();
			}
			if ($record['BRIDGE_CROSSER'] == 'Y')
			{
				$bridge_students_by_grade[$record['GRADE']][] = $record['XID'];
				$total_bridge++;
				if (in_array($record['GRADE'],$dfw))
				{
					$total_bridge_dfw++;
				}
			}
		}
	}
	
	
	// sort arrays by letter/type and fill in holes
	if (!array_key_exists('P',$grades_by_grade))
	{
		ksort($grades_by_grade);
		ksort($grades_by_student_type);
		
		foreach ($lettergrades as $lettergrade)
		{
			if (!isset($grades_by_grade[$lettergrade]))
			{
				$grades_by_grade[$lettergrade] = array();
			}
			foreach ($grades_by_student_type as $student_type => $data)
			{
				if (!isset($grades_by_student_type[$student_type][$lettergrade]))
				{
					$grades_by_student_type[$student_type][$lettergrade] = array();
				}
			}
		}
	}
	else
	{
		ksort($grades_by_student_type);
		
		foreach ($passfail as $passoption)
		{
			if (!isset($grades_by_grade[$passoption]))
			{
				$grades_by_grade[$passoption] = array();
			}
			foreach ($grades_by_student_type as $student_type => $data)
			{
				if (!isset($grades_by_student_type[$student_type][$passoption]))
				{
					$grades_by_student_type[$student_type][$passoption] = array();
				}
			}
		}
	}
	
	// set up counts array
	$student_type_counts = array();
	
	//sort types by letter grade AND add to counts AND fill in dfw holes
	foreach($grades_by_student_type as $student_type => $data)
	{
		ksort($grades_by_student_type[$student_type]);
		if (!isset($student_type_counts[$student_type]))
		{
			$student_type_counts[$student_type] = 0;
		}
		foreach ($data as $letter_grade)
		{
			$student_type_counts[$student_type] += count($letter_grade);
		}
		if (!isset($dfw_by_student_type[$student_type]))
		{
			$dfw_by_student_type[$student_type] = array();
		}
	}
	
	
	$rows = 'Term,Subject,Course,Section,XID,Grade,Student Type,Bridge';
	$rows .= "\n";
	foreach ($grades_data as $record)
	{
		$rows .= '"'.$record['TERM_CODE'].'",';
		$rows .= '"'.$subject.'",';
		$rows .= '"'.$course.'",';
		$rows .= '"'.$record['SECTION_NUMBER'].'",';
		$rows .= '"'.$record['XID'].'",';
		$rows .= '"'.$record['GRADE'].'",';
		$rows .= '"'.$record['STUDENT_TYPE_DESC'].'",';
		$rows .= '"'.$record['BRIDGE_CROSSER'].'",';
		$rows .= "\r\n";
	}
	$file_basename = "Grades-".$subject.'-'.$course.'-'.$term;
	
	//echo '<pre>';print_r($grades_data);echo '</pre>';
	//echo '<pre>';print_r($student_type_counts);echo '</pre>';
	//echo '<pre>';print_r($grades_by_grade);echo '</pre>';
	//echo '<pre>';print_r($grades_by_student_type);echo '</pre>';
}



?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">


<html lang="en-US">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="author" content="hedetni">
	<!-- Date: 2019-11-26 -->
	
	<title>Grade Stats | Distribution by Student Type</title>

<link rel="stylesheet" href="/style/math-internal.css" type="text/css" media="screen" charset="utf-8">
<link rel="stylesheet" href="/dept-info/dept-info-style.css" type="text/css" media="screen" charset="utf-8">

<style type="text/css">


</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>


<script type="text/javascript">
function generate_csv()
{
	var csv = <?php echo json_encode($rows); ?>;
	var link = document.createElement('a');
	var d = new Date();
	link.download = '<?php echo $file_basename; ?>'+'.csv';
	link.href = 'data:text/csv;charset=utf-8,'+escape(csv);
	document.body.appendChild(link);
	link.click();
	link.remove();
}
$(document).ready(function(){
	
	
}); 
</script>

</head>

<body>
	<div id="main">
		<div id="header">
			<div id="app_title">Grade Collection</div>
			<a href="http://www.clemson.edu/math" title="School Home"><img src="/style/math_logo.png" alt="school logo"></a>
		</div>
		<?php echo isset($message) ? '<div id="message">'.$message.'</div>' : '' ?>
		<?php echo isset($error) ? '<div id="error">'.$error.'</div>' : '' ?>
		
		<div id="content" role="main">
			<h1>Grade Distribution by Student Type</h1>
			<form method="POST" action="">
				<p>Input a term code, subject, and course number to see a grade distribution by student type.</p>
				<p><label for="term">Term</label>: <input type="text" name="term" id="term" value="<?php echo isset($_POST['term']) ? $_POST['term'] : '';?>" placeholder="Term Code, i.e. 201908"></input></p>
				<p><label for="subject">Subject</label>: <input type="text" name="subject" id="subject" value="<?php echo isset($_POST['subject']) ? $_POST['subject'] : '';?>" placeholder="Subject Code, i.e. MATH"></input>
					<label for="course">Course</label>: <input type="text" name="course" id="course" value="<?php echo isset($_POST['course']) ? $_POST['course'] : '';?>" placeholder="Course Number, i.e. 2060"></input></p>
				<p><input type="submit" name="fetch_data" value="Submit"></input>
			</form>
			
			
			<?php if(isset($_POST['fetch_data'])): ?>
				
				<hr>
				
				<p>Note: Students are organized here by student type as it was listed during the term in which they took the course. Students who entered Clemson as Transfer students, but were labeled as Continuing Undergrad when they took the course are counted here under Continuing Undergrad. Similarly, "Bridge Students" may include students from outside the "Transfer" category. The calculations include forgiven grades, but do not include incompletes.</p>
				<br>
				<p><a href="javascript:generate_csv();">Download raw data</a></p>
				<table class="styled">
					<caption>Grade Distribution for <?php echo $_POST['subject']; ?> <?php echo $_POST['course']; ?> in <?php echo $_POST['term']; ?></caption>
					<tr>
						<th scope="col">Student Type</th>
						<?php if (array_key_exists('P',$grades_by_grade)): ?>
							<?php foreach ($passfail as $letter): ?>
								<th scope="col"><?php echo $letter; ?></th>
							<?php endforeach; ?>
						<?php else: ?>
							<?php foreach ($grades_by_grade as $letter => $students): ?>
								<th scope="col"><?php echo $letter; ?></th>
							<?php endforeach; ?>
						<?php endif; ?>
						<th scope="col">Total</th>
						<th scope="col">DFW</th>
					</tr>
					
					<?php foreach ($grades_by_student_type as $student_type => $data): ?>
						<tr>
							<td><?php echo $student_type; ?></td>
							<?php if (array_key_exists('P',$grades_by_grade)): ?>
								<?php foreach ($passfail as $letter): ?>
									<td class="text-center"><?php echo count($data[$letter]); ?></td>
								<?php endforeach; ?>
							<?php else: ?>
								<?php foreach ($grades_by_grade as $letter => $students): ?>
									<td class="text-center"><?php echo count($data[$letter]); ?></td>
								<?php endforeach; ?>
							<?php endif; ?>
							<td class="text-center"><?php echo $student_type_counts[$student_type]; ?></td>
							<td class="text-center"><?php echo round(100*count($dfw_by_student_type[$student_type])/$student_type_counts[$student_type],2); ?>%</td>
						</tr>
					<?php endforeach; ?>
					<tr>
						<th>Overall</th>
						<?php if (array_key_exists('P',$grades_by_grade)): ?>
							<?php foreach ($passfail as $letter): ?>
								<th><?php echo count($grades_by_grade[$letter]); ?></th>
							<?php endforeach; ?>
						<?php else: ?>
							<?php foreach ($grades_by_grade as $letter => $students): ?>
								<th><?php echo count($grades_by_grade[$letter]); ?></th>
							<?php endforeach; ?>
						<?php endif; ?>
						<th><?php echo $total_students; ?></th>
						<th><?php echo round(100*$total_dfw/$total_students,2); ?>%</th>
					</tr>
					
					<tr>
						<td>Bridge Students</td>
						<?php if (array_key_exists('P',$grades_by_grade)): ?>
							<?php foreach ($passfail as $letter): ?>
								<td class="text-center"><?php echo count($bridge_students_by_grade[$letter]); ?></td>
							<?php endforeach; ?>
						<?php else: ?>
							<?php foreach ($grades_by_grade as $letter => $students): ?>
								<td class="text-center"><?php echo count($bridge_students_by_grade[$letter]); ?></td>
							<?php endforeach; ?>
						<?php endif; ?>
						<td class="text-center"><?php echo $total_bridge; ?></td>
						<td class="text-center"><?php echo round(100*$total_bridge_dfw/$total_bridge,2); ?>%</td>
					</tr>
				</table>
				
			<?php endif; ?>
			
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>
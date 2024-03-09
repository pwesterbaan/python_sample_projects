<?php


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



// get students
$students_query = $banner_db->prepare('SELECT xid FROM SIS_MTHSC_STUDENT_REGISTRATION WHERE subject_code = \'MATH\' AND course_number = \'2060\' AND grade != \' \' AND xid IN (
SELECT xid FROM SIS_MTHSC_TRANSFER_CREDIT WHERE EQUIV_SUBJECT_CODE = \'MATH\' AND EQUIV_COURSE_NUMBER = \'1080\' AND xid IN 
(
SELECT xid from SIS_MTHSC_STUDENT_REGISTRATION WHERE subject_code = \'MATH\' AND course_number = \'1060\' AND grade NOT IN (\' \')
)
) order by xid');
$students_result = $students_query->execute();
$xids = $students_query->fetchAll(PDO::FETCH_COLUMN);

$data = array();
$ignored = array();
$course_query = $banner_db->prepare('SELECT * from SIS_MTHSC_STUDENT_REGISTRATION WHERE subject_code = \'MATH\' AND course_number = ? AND xid = ? ORDER BY grade');
$query_1080 = $banner_db->prepare('SELECT * FROM SIS_MTHSC_TRANSFER_CREDIT WHERE EQUIV_SUBJECT_CODE = \'MATH\' AND EQUIV_COURSE_NUMBER = \'1080\' AND xid = ? order by TERM_CODE DESC');
$school_name_query = $banner_db->prepare('SELECT TRANSFER_SCHOOL_NAME FROM SIS_MTHSC_TRANSFER_CREDIT WHERE TRANSFER_SCHOOL_CODE = ? AND TRANSFER_SCHOOL_NAME != \' \'');
foreach ($xids as $xid)
{
	// 1080
	$query_1080->execute(array($xid));
	$trans_1080 = $query_1080->fetch();
	if ($trans_1080['TRANSFER_SCHOOL_CODE'] == 'AP0001')
	{
		// did they take 1080 at Clemson?
		$course_query->execute(array('1080',$xid));
		$info_1080 = $course_query->fetch();
		if (isset($info_1080['GRADE']))
		{
			//ignore them
			$ignored[] = $info_1080;
		}
		else
		{
			$data[$xid] = array();
	
			// 1060
			$course_query->execute(array('1060',$xid));
			$info_1060 = $course_query->fetch();
			$data[$xid]['1060_term_code'] = $info_1060['TERM_CODE'];
			$data[$xid]['1060_grade'] = $info_1060['GRADE'];
			
			// 1080
			$data[$xid]['1080_term_code'] = $trans_1080['TERM_CODE'];
			$data[$xid]['1080_grade'] = $trans_1080['TRANSFER_COURSE_GRADE'];
			$data[$xid]['1080_transfer_date'] = $trans_1080['TRANSFER_DATE'];
			$data[$xid]['1080_school'] = $trans_1080['TRANSFER_COURSE_NAME'];
			
			// 2060
			$course_query->execute(array('2060',$xid));
			$info_2060 = $course_query->fetch();
			$data[$xid]['2060_term_code'] = $info_2060['TERM_CODE'];
			$data[$xid]['2060_grade'] = $info_2060['GRADE'];
		}
	}
	else
	{
		// They took it somewhere else
		$data[$xid] = array();
	
		// 1060
		$course_query->execute(array('1060',$xid));
		$info_1060 = $course_query->fetch();
		$data[$xid]['1060_term_code'] = $info_1060['TERM_CODE'];
		$data[$xid]['1060_grade'] = $info_1060['GRADE'];
		
		// 1080
		$data[$xid]['1080_term_code'] = $trans_1080['TERM_CODE'];
		$data[$xid]['1080_grade'] = $trans_1080['TRANSFER_COURSE_GRADE'];
		$data[$xid]['1080_transfer_date'] = $trans_1080['TRANSFER_DATE'];
		if ($trans_1080['TRANSFER_SCHOOL_NAME'] == '')
		{
			// try to get name
			$school_name_query->execute(array($trans_1080['TRANSFER_SCHOOL_CODE']));
			$name = $school_name_query->fetch(PDO::FETCH_COLUMN);
			$data[$xid]['1080_school'] = $name;
		}
		else
		{
			$data[$xid]['1080_school'] = $trans_1080['TRANSFER_SCHOOL_NAME'];
		}
		
		// 2060
		$course_query->execute(array('2060',$xid));
		$info_2060 = $course_query->fetch();
		$data[$xid]['2060_term_code'] = $info_2060['TERM_CODE'];
		$data[$xid]['2060_grade'] = $info_2060['GRADE'];
		
	}
	
	
}

$rows = 'XID,1060 Term,1060 Grade,1080 Term,1080 Transfer Date,1080 School,1080 Grade,2060 Term,2060 Grade';
$rows .= "\n";
foreach ($data as $xid => $record)
{
	$rows .= $xid.',';
	$rows .= '"'.$record['1060_term_code'].'",';
	$rows .= '"'.$record['1060_grade'].'",';
	$rows .= '"'.$record['1080_term_code'].'",';
	$rows .= '"'.$record['1080_transfer_date'].'",';
	$rows .= '"'.$record['1080_school'].'",';
	$rows .= '"'.$record['1080_grade'].'",';
	$rows .= '"'.$record['2060_term_code'].'",';
	$rows .= '"'.$record['2060_grade'].'",';
	$rows .= "\r\n";
}
$file_basename = "Results";


//echo '<pre>';print_r($data);echo '</pre>';
//echo '<pre>';print_r($ignored);echo '</pre>';
//echo '<pre>';print_r($grades_by_student_type);echo '</pre>';



?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">


<html lang="en-US">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="author" content="hedetni">
	<!-- Date: 2019-12-2 -->
	
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
	link.download = '<?php echo $file_basename; ?>'+'-'+d.getMonth()+'-'+d.getDate()+'-'+d.getFullYear()+'.csv';
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
			<h1>1060/1080/2060 Pipeline Data</h1>
			
			<p><?php echo count($data);?> Students found who took 1060 at Clemson, 1080 somewhere else, and 2060 at Clemson.</p>
			
			<p><a href="javascript:generate_csv();">Download results</a></p>
			
			<table class="styled">
				<tr>
					<th scope="col">XID</th>
					<th scope="col">1060 Term</th>
					<th scope="col">1060 Grade</th>
					<th scope="col">1080 Term</th>
					<th scope="col">1080 Transfer Date</th>
					<th scope="col">1080 School</th>
					<th scope="col">1080 Grade</th>
					<th scope="col">2060 Term</th>
					<th scope="col">2060 Grade</th>
				</tr>
				<?php foreach ($data as $xid => $record): ?>
					<tr>
						<td><?php echo $xid; ?></td>
						<td><?php echo $record['1060_term_code']; ?></td>
						<td><?php echo $record['1060_grade']; ?></td>
						<td><?php echo $record['1080_term_code']; ?></td>
						<td><?php echo $record['1080_transfer_date']; ?></td>
						<td><?php echo $record['1080_school']; ?></td>
						<td><?php echo $record['1080_grade']; ?></td>
						<td><?php echo $record['2060_term_code']; ?></td>
						<td><?php echo $record['2060_grade']; ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>
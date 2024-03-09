<?php

//print_r($_ENV);

//echo getenv('mthscID');

//echo '<pre>'.print_r($_SERVER).'</pre>';
echo '<table>';
foreach ($_SERVER as $x => $y)
{
	//echo $x.' => '.$y.'<br>';
	echo '<tr><td>'.$x.'</td><td>'.$y.'</td></tr>';
}
echo '</table>';

?>
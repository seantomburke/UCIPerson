<?php
require_once 'UCIPerson.class.php';

$ucinetid = $_GET['ucinetid'];

$person = new UCIPerson($ucinetid);

$not = ($person->isEngineer())? '':'not';
$major = ($person->isEngineer())? 'And it looks like you\'re in the field of '.$person->getMajor().'!':'Sorry!';
if($ucinetid)
{
	if($person->isValid() == true)
	{
	
	$output = ($person->getName()) ? '<p>If your name is '.$person->getName().', then you are '.$not.' an Engineer! ' .$major : 'No Name';
	$output .= '</p>';
	}
	else
	{
		$output = '<p>This is not a valid UCInetID</p>';
	}
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<header>
<title>UCInetID Example</title>
</header>
<h1>UCInetID</h1>
<h2>Are you an Engineer at UCI? Find out!</h2>
<p>Enter your UCInetID here.</p>
<form name="input" action="<?php echo $_SERVER['PHP_SELF'];?>" method="get">
<input type="text" name="ucinetid" value="<?php echo $ucinetid; ?>" placeholder="UCInetID"/>
<input type="submit" value="Submit"/>
<?php echo $output ?>
</form>
</html>
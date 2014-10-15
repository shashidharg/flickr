<html>
<head>
	<title>Flickr Photo Color Details</title>
</head>
<body>

<?php
$delta = 24;
$reduce_brightness = true;
$reduce_gradients = true;
$num_results = 20;

include_once("colors.inc.php");
include_once("flickr.php");

$photoId = (isset($_GET['photo_id'])) ? trim($_GET['photo_id']) : '';
$colors = array();

if ($photoId) {
	$f = new flickr();

	$photoUrl = $f->getPhotoUrl($photoId);
	$ex=new GetMostCommonColors();
	$colors=$ex->Get_Color($photoUrl, $num_results, $reduce_brightness, $reduce_gradients, $delta);
}
$getColor = ($_GET['colors']) ? $_GET['colors'] : '000000';
$getColor = explode(',', $getColor);
$useColor = array();
for ($i = 0; $i < count($getColor); $i++) {
	$useColor[] = colorUtils::ColorLuminance($getColor[$i]);
}

?>

<a href="http://www.emanueleferonato.com/2009/09/08/color-difference-algorithm-part-2/">Click here for Color Difference Logic</a>
<table border="1" cellspacing="0">
	<tr>
		<th>Color</th>
		<th>Color Code</th>
		<th>Percentage</th>
		<?php for ($i = 0; $i < count($useColor); $i++) :?>
		<th style="<?php echo ($useColor[$i]) ? 'background-color:'.$useColor[$i]  : ''; ?>" >Difference between <?php echo $useColor[$i];?> and photo color</th>
		<?php endfor; ?>
		<th rowspan="<?php echo (($num_results > 0)?($num_results+1):22500);?>">
			<img src="<?php echo $photoUrl;?>" width="400"/>
		</th>
	</tr>
<?php
$tCount = 0;
foreach ( $colors as $hex => $count ) {
	echo "<tr>\n";
	echo "<td style=\"background-color:#".$hex.";\"></td>\n";
	echo "<td>#".$hex." </td>\n";
	echo "<td>$count</td>\n";
	for ($i =0; $i < count($useColor); $i++) {
		$diff = colorUtils::getDifference($useColor[$i], '#'.$hex);
		echo "<td style='".(($diff < 20 && !is_nan($diff)) ? 'background-color:green; color:white' : '')."'>". $diff ."</td>\n";
	}
	echo "</tr>\n";
	$tCount += $count;
}
if ($tCount) {
	echo "<tr><td colspan='2'>Total</td><td colspan='4'>$tCount</td></tr>";
}
?>
</table>
</body>
</html>

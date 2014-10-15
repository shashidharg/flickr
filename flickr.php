<?php

include_once("colors.inc.php");
mysql_connect('localhost', 'root') or die ('Failed to connect');
mysql_select_db('flickr') or die ('Failed to select DB');

class flickr
{
	private $searchAPI = 'https://api.flickr.com/services/rest/?method=flickr.photos.search&api_key=<API_KEY>&text=<TEXT>&privacy_filter=1&safe_search=1&content_type=1&is_getty=true&page=<PAGE>&per_page=20&format=json&nojsoncallback=1';
	private $photoAPI = 'http://api.flickr.com/services/rest/?method=flickr.photos.getSizes&api_key=<API_KEY>&photo_id=<PHOTO_ID>&format=json&nojsoncallback=1';
	private $photoURL = 'https://farm<FARM_NUM>.staticflickr.com/<SERVER>/<PHOTO_ID>_<SECRET_NUM>_z.jpg';
	private $photoIds = array();
	private $apiKey = '3153103a56ffc62e63b3eee4ae85943f';
	private $color = null;
	private $delta = 24;
	private $reduce_brightness = true;
	private $reduce_gradients = true;
	private $num_results = 20;
	private $commonColors = array();

	public function __construct() {
		$this->color = new GetMostCommonColors();

		$serilizedCommonColor = 'a:12:{s:3:"red";a:4:{s:3:"hex";s:7:"#CC0000";s:3:"rgb";a:3:{i:0;s:3:"204";i:1;s:3:"000";i:2;s:3:"000";}s:3:"xyz";a:3:{i:0;d:24.901839454394132;i:1;d:12.837369224064483;i:2;d:1.165386763990802;}s:3:"lab";a:3:{i:0;d:42.517172519486905;i:1;d:67.710171312444004;i:2;d:56.815897773127247;}}s:6:"orange";a:4:{s:3:"hex";s:7:"#FB940B";s:3:"rgb";a:3:{i:0;s:3:"251";i:1;s:3:"148";i:2;s:3:"011";}s:3:"xyz";a:3:{i:0;d:50.433970397454083;i:1;d:41.71320074594761;i:2;d:5.7099008707109205;}s:3:"lab";a:3:{i:0;d:70.672733651080364;i:1;d:31.202318884772929;i:2;d:74.575230044205171;}}s:6:"yellow";a:4:{s:3:"hex";s:7:"#FFFF00";s:3:"rgb";a:3:{i:0;s:3:"255";i:1;s:3:"255";i:2;s:3:"000";}s:3:"xyz";a:3:{i:0;d:77;i:1;d:92.780000000000001;i:2;d:13.85;}s:3:"lab";a:3:{i:0;d:97.138246981297286;i:1;d:-21.555908334832285;i:2;d:94.482485446444613;}}s:5:"green";a:4:{s:3:"hex";s:7:"#00CC00";s:3:"rgb";a:3:{i:0;s:3:"000";i:1;s:3:"204";i:2;s:3:"000";}s:3:"xyz";a:3:{i:0;d:21.592865637466879;i:1;d:43.185731274933758;i:2;d:7.1976218791556263;}s:3:"lab";a:3:{i:0;d:71.68084944389129;i:1;d:-72.845172876974118;i:2;d:70.306571709297089;}}s:4:"teal";a:4:{s:3:"hex";s:7:"#03C0C6";s:3:"rgb";a:3:{i:0;s:3:"003";i:1;s:3:"192";i:2;s:3:"198";}s:3:"xyz";a:3:{i:0;d:29.080231931618503;i:1;d:41.795849812680075;i:2;d:59.960798336901547;}s:3:"lab";a:3:{i:0;d:70.729939319821156;i:1;d:-36.918766801609216;i:2;d:-14.398389774553323;}}s:4:"blue";a:4:{s:3:"hex";s:7:"#0000FF";s:3:"rgb";a:3:{i:0;s:3:"000";i:1;s:3:"000";i:2;s:3:"255";}s:3:"xyz";a:3:{i:0;d:18.050000000000001;i:1;d:7.2199999999999998;i:2;d:95.049999999999997;}s:3:"lab";a:3:{i:0;d:32.302586667249486;i:1;d:79.196661789309346;i:2;d:-107.86368104495168;}}s:6:"purple";a:4:{s:3:"hex";s:7:"#762CA7";s:3:"rgb";a:3:{i:0;s:3:"000";i:1;s:3:"000";i:2;s:3:"255";}s:3:"xyz";a:3:{i:0;d:18.050000000000001;i:1;d:7.2199999999999998;i:2;d:95.049999999999997;}s:3:"lab";a:3:{i:0;d:32.302586667249486;i:1;d:79.196661789309346;i:2;d:-107.86368104495168;}}s:4:"pink";a:4:{s:3:"hex";s:7:"#FF98BF";s:3:"rgb";a:3:{i:0;s:3:"255";i:1;s:3:"152";i:2;s:3:"191";}s:3:"xyz";a:3:{i:0;d:61.872206486654257;i:1;d:47.478060819166757;i:2;d:55.193374696512429;}s:3:"lab";a:3:{i:0;d:74.494526787787805;i:1;d:43.271020768912649;i:2;d:-3.4424905849800957;}}s:5:"white";a:4:{s:3:"hex";s:7:"#FFFFFF";s:3:"rgb";a:3:{i:0;s:3:"255";i:1;s:3:"255";i:2;s:3:"255";}s:3:"xyz";a:3:{i:0;d:95.049999999999997;i:1;d:100;i:2;d:108.89999999999999;}s:3:"lab";a:3:{i:0;d:100;i:1;d:0.0052604999583039103;i:2;d:-0.010408184525267927;}}s:4:"grey";a:4:{s:3:"hex";s:7:"#999999";s:3:"rgb";a:3:{i:0;s:3:"153";i:1;s:3:"153";i:2;s:3:"153";}s:3:"xyz";a:3:{i:0;d:30.277871260789979;i:1;d:31.854677812509181;i:2;d:34.689744137822501;}s:3:"lab";a:3:{i:0;d:63.222594552359169;i:1;d:0.0035926763391480598;i:2;d:-0.0071083050230402733;}}s:5:"black";a:4:{s:3:"hex";s:7:"#000000";s:3:"rgb";a:3:{i:0;s:3:"000";i:1;s:3:"000";i:2;s:3:"000";}s:3:"xyz";a:3:{i:0;d:0;i:1;d:0;i:2;d:0;}s:3:"lab";a:3:{i:0;d:0;i:1;d:0;i:2;d:0;}}s:5:"brown";a:4:{s:3:"hex";s:7:"#885418";s:3:"rgb";a:3:{i:0;s:3:"136";i:1;s:3:"084";i:2;s:3:"024";}s:3:"xyz";a:3:{i:0;d:13.488536238585459;i:1;d:11.640835640797095;i:2;d:2.4001354287186221;}s:3:"lab";a:3:{i:0;d:40.639495327530618;i:1;d:16.666978883984136;i:2;d:41.576814156239536;}}}';
		$this->commonColors = unserialize($serilizedCommonColor);
//		$this->commonColors['red'] = array('hex' => '#CC0000');
//		$this->commonColors['orange'] = array('hex' => '#FB940B');
//		$this->commonColors['yellow'] = array('hex' => '#FFFF00');
//		$this->commonColors['green'] = array('hex' => '#00CC00');
//		$this->commonColors['teal'] = array('hex' => '#03C0C6');
//		$this->commonColors['blue'] = array('hex' => '#0000FF');
//		$this->commonColors['purple'] = array('hex' => '#762CA7');
//		$this->commonColors['pink'] = array('hex' => '#FF98BF');
//		$this->commonColors['white'] = array('hex' => '#FFFFFF');
//		$this->commonColors['grey'] = array('hex' => '#999999');
//		$this->commonColors['black'] = array('hex' => '#000000');
//		$this->commonColors['brown'] = array('hex' => '#885418');
//		foreach($this->commonColors as $color => &$params) {
//			 $params['rgb'] = colorUtils::getRGB($params['hex']);
//			 $params['xyz'] = colorUtils::rgb_to_xyz($params['rgb']);
//			 $params['lab'] = colorUtils::xyz_to_lab($params['xyz']);
//		}
//		echo serialize($this->commonColors);
	}

	public function serchByText($query, $colors = '', $page = 1) {
		// fetch YQL query
		if (!is_int($page)) {
			$page = 1;
		}
		$api = str_replace(array('<API_KEY>', '<TEXT>', '<PAGE>'), array($this->apiKey, $query, $page), $this->searchAPI);
		$result = json_decode(file_get_contents($api));

		$photoids['photos'] = array();
		for ($i = 0; $i < $result->photos->perpage; $i++) {
			$photoData = $result->photos->photo[$i];
			$thisPhoto = $photoData->id;

			$photoUrl = str_replace(array('<FARM_NUM>', '<SERVER>', '<PHOTO_ID>', '<SECRET_NUM>'),
									array($photoData->farm, $photoData->server, $photoData->id, $photoData->secret),
									$this->photoURL);
			$this->processPhoto($thisPhoto, $photoUrl);
			if (!empty($colors) && !$this->isdominantColors($colors, $thisPhoto)) {
				continue;
			}
			$photoids['photos'][$thisPhoto] = $photoUrl;

		}
		return $photoids;
	}

	public function getPhotoUrl($photoId)
	{
		$api = str_replace(array('<API_KEY>', '<PHOTO_ID>'), array($this->apiKey, $photoId), $this->photoAPI);
		$photoSizes = json_decode(file_get_contents($api));
		foreach ($photoSizes->sizes->size as $thisPhotoSize) {
			if ('640' == $thisPhotoSize->width) {
				break;
			}
		}
//		$this->processPhoto($photoId, $thisPhotoSize->source);
		return $thisPhotoSize->source;
	}

	private function processPhoto($photoId, $photoUrlPath) {
		$sQuery = "SELECT COUNT(*) AS count FROM photo_colors where photo_flickr_id = '$photoId'";
		$res = mysql_query($sQuery);
		$records = mysql_fetch_assoc($res);
		if ($records['count'] == 0) {
			$photoColors= $this->color->Get_Color($photoUrlPath, $this->num_results, $this->reduce_brightness, $this->reduce_gradients, $this->delta);

			$num_results = $this->num_results;
			foreach($photoColors as $hex => $hexPer) {
				$rgb = colorUtils::getRGB($hex);
				$xyz = colorUtils::rgb_to_xyz($rgb);
				$lab = colorUtils::xyz_to_lab($xyz);

				foreach($this->commonColors as $key => $params) {
					${$key} = colorUtils::getDifference($params['hex'], $hex);
				}

				$iQuery = "INSERT INTO photo_colors (photo_flickr_id, perc_of_hexa, hexa_codes, sequence_order, rgb, xyz, lab, photo_url, red, orange, yellow, green, teal, blue, purple, pink, white, grey, black, brown) VALUES
							( '$photoId', '$hexPer', '$hex', '".$num_results--."', '".serialize($rgb)."', '".serialize($xyz)."', '".serialize($lab)."', '$photoUrlPath', '$red', '$orange', '$yellow', '$green', '$teal', '$blue', '$purple', '$pink', '$white', '$grey', '$black', '$brown')";
				mysql_query($iQuery);
			}
		}
	}

	public function isdominantColors($colors, $photoId) {
		$isWithinMostDominantRage = $this->num_results - 5;
		$colors = explode(',', $colors);
		$resultColor = array();
		foreach ($this->commonColors as $colorName => $colorParams) {
			$commonColor = str_replace('#', '', $this->commonColors[$colorName]['hex']);
			if (in_array($commonColor, $colors)) {
				$sQuery = "SELECT count(*) total, max(sequence_order) sequence FROM photo_colors WHERE $colorName < 20 AND $colorName > 0 AND photo_flickr_id = '$photoId'";
				$res = mysql_query($sQuery);
				$resultColor[$colorName] = mysql_fetch_assoc($res);
			}
		}

		if (!empty($resultColor)) {
			foreach ($resultColor as $colorName => $result) {
				if (($result['total'] > 1) || (1 == $result['total'] && $result['sequence'] > $isWithinMostDominantRage)) {
				} else {
					return false;
				}
			}
			// If it reached here then all the colors are dominant colors in the photo
			return true;
		}
		return false;
	}
}


$text = (isset($_GET['text'])) ? trim($_GET['text']) : '';
$page = (isset($_GET['page'])) ? trim($_GET['page']) : 1;
$colors = (isset($_GET['colors'])) ? trim($_GET['colors']) : '';

if (!empty($text)) {
	$f = new flickr();
	$photos = $f->serchByText($text, $colors, $page);
	echo json_encode($photos);
}

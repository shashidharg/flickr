<?php
/**
 * This class can be used to get the most common colors in an image.
 * It needs one parameter:
 * 	$image - the filename of the image you want to process.
 * Optional parameters:
 *
 *	$count - how many colors should be returned. 0 mmeans all. default=20
 *	$reduce_brightness - reduce (not eliminate) brightness variants? default=true
 *	$reduce_gradients - reduce (not eliminate) gradient variants? default=true
 *	$delta - the amount of gap when quantizing color values.
 *		Lower values mean more accurate colors. default=16
 *
 * Author: 	Csongor Zalatnai
 *
 * Modified By: Kepler Gelotte - Added the gradient and brightness variation
 * 	reduction routines. Kudos to Csongor for an excellent class. The original
 * 	version can be found at:
 *
 *	http://www.phpclasses.org/browse/package/3370.html
 *
 * References
 * 1. Original Code http://www.coolphptools.com/color_extract
 * 2. ColorLuminance http://www.sitepoint.com/javascript-generate-lighter-darker-color/
 */
class GetMostCommonColors
{
	var $PREVIEW_WIDTH    = 150;
	var $PREVIEW_HEIGHT   = 150;

	var $error;

	/**
	 * Returns the colors of the image in an array, ordered in descending order, where the keys are the colors, and the values are the count of the color.
	 *
	 * @return array
	 */
	function Get_Color( $img, $count=20, $reduce_brightness=true, $reduce_gradients=true, $delta=16 )
	{
		// making it read from url image path
		if (true)//is_readable( $img ))
		{
			if ( $delta > 2 )
			{
				$half_delta = $delta / 2 - 1;
			}
			else
			{
				$half_delta = 0;
			}
			// WE HAVE TO RESIZE THE IMAGE, BECAUSE WE ONLY NEED THE MOST SIGNIFICANT COLORS.
			$size = getimagesize($img);
			$scale = 1;
			if ($size[0]>0)
			$scale = min($this->PREVIEW_WIDTH/$size[0], $this->PREVIEW_HEIGHT/$size[1]);
			if ($scale < 1)
			{
				$width = floor($scale*$size[0]);
				$height = floor($scale*$size[1]);
			}
			else
			{
				$width = $size[0];
				$height = $size[1];
			}
			$image_resized = imagecreatetruecolor($width, $height);
			if ($size[2] == 1)
			$image_orig = imagecreatefromgif($img);
			if ($size[2] == 2)
			$image_orig = imagecreatefromjpeg($img);
			if ($size[2] == 3)
			$image_orig = imagecreatefrompng($img);
			// WE NEED NEAREST NEIGHBOR RESIZING, BECAUSE IT DOESN'T ALTER THE COLORS
			imagecopyresampled($image_resized, $image_orig, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);
			$im = $image_resized;
			$imgWidth = imagesx($im);
			$imgHeight = imagesy($im);
			$total_pixel_count = 0;
			for ($y=0; $y < $imgHeight; $y++)
			{
				for ($x=0; $x < $imgWidth; $x++)
				{
					$total_pixel_count++;
					$index = imagecolorat($im,$x,$y);
					$colors = imagecolorsforindex($im,$index);
					// ROUND THE COLORS, TO REDUCE THE NUMBER OF DUPLICATE COLORS
					if ( $delta > 1 )
					{
						$colors['red'] = intval((($colors['red'])+$half_delta)/$delta)*$delta;
						$colors['green'] = intval((($colors['green'])+$half_delta)/$delta)*$delta;
						$colors['blue'] = intval((($colors['blue'])+$half_delta)/$delta)*$delta;
						if ($colors['red'] >= 256)
						{
							$colors['red'] = 255;
						}
						if ($colors['green'] >= 256)
						{
							$colors['green'] = 255;
						}
						if ($colors['blue'] >= 256)
						{
							$colors['blue'] = 255;
						}

					}

					$hex = substr("0".dechex($colors['red']),-2).substr("0".dechex($colors['green']),-2).substr("0".dechex($colors['blue']),-2);

					if ( ! isset( $hexarray[$hex] ) )
					{
						$hexarray[$hex] = 1;
					}
					else
					{
						$hexarray[$hex]++;
					}
				}
			}

			// Reduce gradient colors
			if ( $reduce_gradients )
			{
				// if you want to *eliminate* gradient variations use:
				// ksort( &$hexarray );
				arsort( $hexarray, SORT_NUMERIC );

				$gradients = array();
				foreach ($hexarray as $hex => $num)
				{
					if ( ! isset($gradients[$hex]) )
					{
						$new_hex = $this->_find_adjacent( $hex, $gradients, $delta );
						$gradients[$hex] = $new_hex;
					}
					else
					{
						$new_hex = $gradients[$hex];
					}

					if ($hex != $new_hex)
					{
						$hexarray[$hex] = 0;
						$hexarray[$new_hex] += $num;
					}
				}
			}

			// Reduce brightness variations
			if ( $reduce_brightness )
			{
				// if you want to *eliminate* brightness variations use:
				// ksort( &$hexarray );
				arsort( $hexarray, SORT_NUMERIC );

				$brightness = array();
				foreach ($hexarray as $hex => $num)
				{
					if ( ! isset($brightness[$hex]) )
					{
						$new_hex = $this->_normalize( $hex, $brightness, $delta );
						$brightness[$hex] = $new_hex;
					}
					else
					{
						$new_hex = $brightness[$hex];
					}

					if ($hex != $new_hex)
					{
						$hexarray[$hex] = 0;
						$hexarray[$new_hex] += $num;
					}
				}
			}

			arsort( $hexarray, SORT_NUMERIC );

			// convert counts to percentages
			foreach ($hexarray as $key => $value)
			{
				$hexarray[$key] = (float)$value / $total_pixel_count;
			}

			if ( $count > 0 )
			{
				// only works in PHP5
				// return array_slice( $hexarray, 0, $count, true );

				$arr = array();
				foreach ($hexarray as $key => $value)
				{
					if ($count == 0)
					{
						break;
					}
					$count--;
					$arr[$key] = $value;
				}
				return $arr;
			}
			else
			{
				return $hexarray;
			}

		}
		else
		{
			$this->error = "Image ".$img." does not exist or is unreadable";
			return false;
		}
	}

	function _normalize( $hex, $hexarray, $delta )
	{
		$lowest = 255;
		$highest = 0;
		$colors['red'] = hexdec( substr( $hex, 0, 2 ) );
		$colors['green']  = hexdec( substr( $hex, 2, 2 ) );
		$colors['blue'] = hexdec( substr( $hex, 4, 2 ) );

		if ($colors['red'] < $lowest)
		{
			$lowest = $colors['red'];
		}
		if ($colors['green'] < $lowest )
		{
			$lowest = $colors['green'];
		}
		if ($colors['blue'] < $lowest )
		{
			$lowest = $colors['blue'];
		}

		if ($colors['red'] > $highest)
		{
			$highest = $colors['red'];
		}
		if ($colors['green'] > $highest )
		{
			$highest = $colors['green'];
		}
		if ($colors['blue'] > $highest )
		{
			$highest = $colors['blue'];
		}

		// Do not normalize white, black, or shades of grey unless low delta
		if ( $lowest == $highest )
		{
			if ($delta <= 32)
			{
				if ( $lowest == 0 || $highest >= (255 - $delta) )
				{
					return $hex;
				}
			}
			else
			{
				return $hex;
			}
		}

		for (; $highest < 256; $lowest += $delta, $highest += $delta)
		{
			$new_hex = substr("0".dechex($colors['red'] - $lowest),-2).substr("0".dechex($colors['green'] - $lowest),-2).substr("0".dechex($colors['blue'] - $lowest),-2);

			if ( isset( $hexarray[$new_hex] ) )
			{
				// same color, different brightness - use it instead
				return $new_hex;
			}
		}

		return $hex;
	}

	function _find_adjacent( $hex, $gradients, $delta )
	{
		$red = hexdec( substr( $hex, 0, 2 ) );
		$green  = hexdec( substr( $hex, 2, 2 ) );
		$blue = hexdec( substr( $hex, 4, 2 ) );

		if ($red > $delta)
		{
			$new_hex = substr("0".dechex($red - $delta),-2).substr("0".dechex($green),-2).substr("0".dechex($blue),-2);
			if ( isset($gradients[$new_hex]) )
			{
				return $gradients[$new_hex];
			}
		}
		if ($green > $delta)
		{
			$new_hex = substr("0".dechex($red),-2).substr("0".dechex($green - $delta),-2).substr("0".dechex($blue),-2);
			if ( isset($gradients[$new_hex]) )
			{
				return $gradients[$new_hex];
			}
		}
		if ($blue > $delta)
		{
			$new_hex = substr("0".dechex($red),-2).substr("0".dechex($green),-2).substr("0".dechex($blue - $delta),-2);
			if ( isset($gradients[$new_hex]) )
			{
				return $gradients[$new_hex];
			}
		}

		if ($red < (255 - $delta))
		{
			$new_hex = substr("0".dechex($red + $delta),-2).substr("0".dechex($green),-2).substr("0".dechex($blue),-2);
			if ( isset($gradients[$new_hex]) )
			{
				return $gradients[$new_hex];
			}
		}
		if ($green < (255 - $delta))
		{
			$new_hex = substr("0".dechex($red),-2).substr("0".dechex($green + $delta),-2).substr("0".dechex($blue),-2);
			if ( isset($gradients[$new_hex]) )
			{
				return $gradients[$new_hex];
			}
		}
		if ($blue < (255 - $delta))
		{
			$new_hex = substr("0".dechex($red),-2).substr("0".dechex($green),-2).substr("0".dechex($blue + $delta),-2);
			if ( isset($gradients[$new_hex]) )
			{
				return $gradients[$new_hex];
			}
		}

		return $hex;
	}
}

class colorUtils {
	/**
	 * Code from http://www.sitepoint.com/javascript-generate-lighter-darker-color/
	 * @param string $hex, hexa color code.
	 * @param float $lum, percentage to lighter or darken the given hexa color code
	 * @return string $rbg, final calculated hexa color code
	 */
	static public function ColorLuminance ($hex, $lum = 0)
	{
		// validate $hex
		$hex = preg_replace('/[^0-9a-f]/i', '', $hex);

		if (strlen($hex) < 6) {
			$hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
		}

		$rgb = '#';
		$color = '';
		for ($i = 0; $i < 3; $i++) {
			$color = hexdec(substr($hex, ($i * 2), 2));
			$color = dechex(round(min( max(0, $color + ($color * $lum)), 255)));
			$rgb .= substr("00".$color, strlen($color));
		}
		return $rgb;
	}

	static public function getDifference ($searchColor, $originalColor)
	{
		$originalColor = self::getRGB($originalColor);
		$searchColor = self::getRGB($searchColor);

		$min_xyz = self::rgb_to_xyz($originalColor);
		$search_xyz = self::rgb_to_xyz($searchColor);

		$min_lab = self::xyz_to_lab($min_xyz);
		$search_lab = self::xyz_to_lab($search_xyz);

		return self::de_1994($min_lab, $search_lab);
	}

	static public function getRGB($hex)
	{
		$hex = str_replace('#', '', self::ColorLuminance($hex));

		$rgb = '';
		for($i = 0; $i < 3; $i++) {
			$rgb [$i]= str_pad(hexdec(substr($hex, ($i * 2), 2)), 3, '0', STR_PAD_LEFT);
		}
		return $rgb;
	}

	// http://www.emanueleferonato.com/2009/09/08/color-difference-algorithm-part-2/
	static public function rgb_to_xyz($rgb)
	{
		$red = $rgb[0];
		$green = $rgb[1];
		$blue = $rgb[2];
		$_red = $red/255;
		$_green = $green/255;
		$_blue = $blue/255;
		if($_red>0.04045){
			$_red = ($_red+0.055)/1.055;
			$_red = pow($_red,2.4);
		} else {
			$_red = $_red/12.92;
		}
		if($_green>0.04045){
			$_green = ($_green+0.055)/1.055;
			$_green = pow($_green,2.4);
		} else{
			$_green = $_green/12.92;
		}
		if($_blue>0.04045){
			$_blue = ($_blue+0.055)/1.055;
			$_blue = pow($_blue,2.4);
		} else{
			$_blue = $_blue/12.92;
		}
		$_red *= 100;
		$_green *= 100;
		$_blue *= 100;
		$x = $_red * 0.4124 + $_green * 0.3576 + $_blue * 0.1805;
		$y = $_red * 0.2126 + $_green * 0.7152 + $_blue * 0.0722;
		$z = $_red * 0.0193 + $_green * 0.1192 + $_blue * 0.9505;
		return(array($x,$y,$z));
	}

	static public function xyz_to_lab($xyz){
		$x = $xyz[0];
		$y = $xyz[1];
		$z = $xyz[2];
		$_x = $x/95.047;
		$_y = $y/100;
		$_z = $z/108.883;
		if($_x>0.008856){
			$_x = pow($_x,1/3);
		} else{
			$_x = 7.787*$_x + 16/116;
		}
		if($_y>0.008856){
			$_y = pow($_y,1/3);
		} else{
			$_y = (7.787*$_y) + (16/116);
		}
		if($_z>0.008856){
			$_z = pow($_z,1/3);
		} else{
			$_z = 7.787*$_z + 16/116;
		}
		$l= 116*$_y -16;
		$a= 500*($_x-$_y);
		$b= 200*($_y-$_z);
		return(array($l,$a,$b));
	}

	static public function de_1994($lab1,$lab2){
		$c1 = sqrt($lab1[1]*$lab1[1]+$lab1[2]*$lab1[2]);
		$c2 = sqrt($lab2[1]*$lab2[1]+$lab2[2]*$lab2[2]);
		$dc = $c1-$c2;
		$dl = $lab1[0]-$lab2[0];
		$da = $lab1[1]-$lab2[1];
		$db = $lab1[2]-$lab2[2];
		$dh = sqrt(($da*$da)+($db*$db)-($dc*$dc));
		$first = $dl;
		$second = $dc/(1+0.045*$c1);
		$third = $dh/(1+0.015*$c1);
		return(sqrt($first*$first+$second*$second+$third*$third));
	}
}
?>

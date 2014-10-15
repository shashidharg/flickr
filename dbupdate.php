<?php
mysql_connect('localhost', 'root') or die ('Failed to connect');
mysql_select_db('flickr') or die ('Failed to select DB');
include "flickr.php";
class dbupdate extends flickr
{
        private $tagAPI = 'http://api.flickr.com/services/rest/?method=flickr.photos.search&api_key=<API_KEY>&tags=<TAGS>&privacy_filter=1&safe_search=1&content_type=1&page=<PAGE>&per_page=10&format=json&sort=date-posted-asc&nojsoncallback=1';
        private $photoAPI = 'http://api.flickr.com/services/rest/?method=flickr.photos.getSizes&api_key=<API_KEY>&photo_id=<PHOTO_ID>&format=json&nojsoncallback=1';
		private $tags = array('animals','architecture','art','asia','australia','autumn','baby','band','barcelona','beach','berlin','bike','bird','birds','birthday','black','blackandwhite','blue','bw','california','canada','canon','car','cat','chicago','china','christmas','church','city','clouds','color','concert','dance','day','de','dog','england','europe','fall','family','fashion','festival','film','florida','flower','flowers','food','football','france','friends','fun','garden','geotagged','germany','girl','graffiti','green','halloween','hawaii','holiday','house','india','instagramapp','iphone','iphoneography','island','italia','italy','japan','kids','la','lake','landscape','light','live','london','love','macro','me','mexico','model','museum','music','nature','new','newyork','newyorkcity','night','nikon','nyc','ocean','old','paris','park','party','people','photo','photography','photos','portrait','raw','red','river','rock','san','sanfrancisco','scotland','sea','seattle','show','sky','snow','spain','spring','square','squareformat','street','summer','sun','sunset','taiwan','texas','thailand','tokyo','travel','tree','trees','trip','uk','unitedstates','urban','usa','vacation','vintage','washington','water','wedding','white','winter','woman','yellow','zoo');


        public function serchByTag($query, $color = '', $page = 1) {
                // fetch YQL query
                if (!is_int($page)) {
                        $page = 1;
                }
				foreach($this->tags as $i)
				{
		                	$api = str_replace(array('<API_KEY>', '<TAGS>', '<PAGE>'), array($this->apiKey, $this->tags[$i], $page), $this->tagAPI);
		                	$result = json_decode(file_get_contents($api));

		                	$photoids['photos'] = array();
		                	for ($i = 0; $i < $result->photos->perpage; $i++) {
		                        	$thisPhoto = $result->photos->photo[$i]->id;

		                        	$api = str_replace(array('<API_KEY>', '<PHOTO_ID>'), array($this->apiKey, $thisPhoto), $this->photoAPI);
		                        	$photoSizes = json_decode(file_get_contents($api));

		                        	foreach ($photoSizes->sizes->size as $thisPhotoSize) {
		                                	if ('640' == $thisPhotoSize->width) {
		                                        	$this->processPhoto($thisPhoto, $thisPhotoSize->source);
		                                        	break;
		                               	 	}
		                        	}
		                        	if (!empty($color) && !$this->isdominantColor($color, $thisPhoto)) {
		                                	continue;
		                        	}
		                        	$photoids['photos'][$thisPhoto] = $thisPhotoSize->source;
		                	}
				}
                return $photoids;
        }

}

?>

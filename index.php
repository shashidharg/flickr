<!DOCTYPE html>
<html>
<head>
	<title>Color Flickr</title>
	<link href="http://yui.yahooapis.com/combo?gallery-2011.09.14-20-40/build/gallery-colorpicker/assets/gallery-colorpicker-core.css" type="text/css" rel="stylesheet" />
	<style type="text/css">
		body {margin: 0;}
		#queryContainer label { margin: 0; padding: 0; font-size: smaller; }
		#container { background-color: #000000; height: 50px; position:fixed; width: 100%;}
		#searchForm { padding: 0px 15px; }
		.searchQuery {
			display: inline-block;
			margin: 10px 0px;
			color: #fff;
			padding: 5px;
			padding-left: 10px;
			padding-right: 10px;
			border-radius: 5px;
			-webkit-border-radius: 5px;
			-moz-border-radius: 5px;
			text-decoration: none;
			cursor: pointer;
		}
		.searchQuery.colors { width:50px; border: 1px solid #FFFFFF;text-align: center;}
		.searchQuery.red { background-color: #CC0000; }
		.searchQuery.orange { background-color: #FB940B; }
		.searchQuery.yellow { background-color: #FFFF00; color:#000000; }
		.searchQuery.green { background-color: #00CC00; }
		.searchQuery.teal { background-color: #03C0C6; }
		.searchQuery.blue { background-color: #0000FF; }
		.searchQuery.purple { background-color: #762CA7; }
		.searchQuery.pink { background-color: #FF98BF; }
		.searchQuery.white { background-color: #FFFFFF; color:#000000;}
		.searchQuery.grey { background-color: #999999; }
		.searchQuery.black { background-color: #000000; }
		.searchQuery.brown { background-color: #885418; }
		.searchQuery.btPink { background-color: #FE0182; }
		.searchQuery.btPink:hover { background-color: #cb0168; }
		.searchQuery.btBlue { background-color: #0062DA; }
		.searchQuery.btBlue:hover { background-color: #004eae; }
		#searchResult {padding-top:60px;}
		#searchResult img { padding: 5px; height:200px;}
		#searchResult div { text-align:center; background-color:#6A966A; padding:5px;color:#ffffff;}
		#loading {
			position : fixed;
			top:50%;
			left:40%;
			background-color:black;
			color: white;
			padding : 10px;
			border-radius: 5px;
			-webkit-border-radius: 5px;
			-moz-border-radius: 5px;
			opacity: 0.80;
		}
	</style>
	<script src="http://yui.yahooapis.com/3.4.0/build/yui/yui-min.js"></script>
	<script type="text/javascript">
		YUI({
			gallery: 'gallery-2011.09.14-20-40'
		}).use('node', 'gallery-colorpicker', 'io-base', 'json-parse', function (Y) {

			function ioComplete(id, o) {
				jsonData = o.responseText.toString().replace(/<!--.*/, '');
				photosData = Y.JSON.parse(jsonData);
				Y.one('#searchResult').setContent('');
				colorInfo = Y.all('.searchQuery.colors.selectedColor');
				if (colorInfo.size()) {
					colorCode = '';
					colorInfo.each(function(n){
						colorCode += n.getAttribute('color') + ',';
					});
					colorCode = colorCode.substring(0, colorCode.length - 1);
				} else {
					// default yellow color
					colorCode = 'FFFF00';
				}
				for (var key in photosData.photos) {
					anchorNode = Y.Node.create('<a href="\photo.php?photo_id='+key+'&colors='+colorCode+'" target="_blank"></a>');
					imageNode = Y.Node.create('<img src="'+photosData.photos[key]+'" />');
					anchorNode.append(imageNode);
					Y.one('#searchResult').append(anchorNode);
				}

				if ('' == Y.one('#searchResult').getContent()) {
					Y.one('#searchResult').setContent('<div>No matches found...</div>');
				} else {
					if (Y.all('.searchQuery.colors.selectedColor').size() > 0) {
						backgroundColor = Y.one('.searchQuery.colors.selectedColor').getComputedStyle('backgroundColor');
						color = Y.one('.searchQuery.colors.selectedColor').getComputedStyle('color');
						Y.one('#searchResult').one('*').insert('<div style="background-color:'+backgroundColor+';color:'+color+'">Click on the photo to know why it is picked </div> ', 'before');
					}
				}
				Y.one('#loading').hide();
			}
			Y.on('io:complete', ioComplete, Y);
			Y.one('.searchQuery.btPink').on('click', function(e){
				searchText = Y.one('#searchText').get('value');
				if ('' != searchText) {
					makeXHRCall(e);
				} else {
					e.halt();
				}
			});
			Y.one('#searchForm').on('submit', function(e){
				makeXHRCall(e);
			});
			Y.all('.searchQuery.colors').on('click', function(e){
				isLoading = Y.one('#loading').getComputedStyle('display');
				if ('none' != isLoading) {
					return;
				}
				if (e.currentTarget.hasClass('selectedColor')) {
					e.currentTarget.setContent('&nbsp;');
				} else {
					e.currentTarget.setContent('&radic;');
				}
				e.currentTarget.toggleClass('selectedColor');

				selectedColor = e.currentTarget.getAttribute('color');
				searchText = Y.one('#searchText').get('value');
				if ('' != searchText) {
					makeXHRCall(e);
				} else {
					e.halt();
				}
			});
			function makeXHRCall(e) {
				isLoading = Y.one('#loading').getComputedStyle('display');
				if ('none' != isLoading) {
					return;
				}
				e.halt();
				xhrUrl = '/flickr/flickr.php?text='+Y.one('#searchText').get('value');
				colorInfo = Y.all('.searchQuery.colors.selectedColor');
				colorMsg = '';
				if (colorInfo.size() > 0) {
					colorCode = '';
					colorNames = '';
					colorInfo.each(function(n){
						colorCode += n.getAttribute('color') + ',';
						colorNames += n.getAttribute('class').replace('searchQuery', '').replace('colors', '').replace('selectedColor', '').replace(' ', '') + ' and ';
					});
					colorCode = colorCode.substring(0, colorCode.length - 1);
					colorNames = colorNames.substring(0, colorNames.length - 6);

					xhrUrl += '&colors='+colorCode;
					colorMsg = ' with ' + colorNames + ' colors';
				}

				Y.io(xhrUrl);
				Y.one('#userQuery').setContent(Y.one('#searchText').get('value') + colorMsg );
				Y.one('#loading').show();
			}
		});
	</script>
</head>
<body>

<div id='container'>
	<div id='queryContainer'>
		<form id='searchForm'>
			<input type="text" name="search" size="30" id="searchText" />
			<a class='searchQuery btPink'>Search query</a>
			<a class='searchQuery btBlue'>Pick a color</a>
			<a class='searchQuery colors red' color="CC0000">&nbsp;</a>
			<a class='searchQuery colors orange' color="FB940B">&nbsp;</a>
			<a class='searchQuery colors yellow' color="FFFF00">&nbsp;</a>
			<a class='searchQuery colors green' color="00CC00">&nbsp;</a>
			<a class='searchQuery colors teal' color="03C0C6">&nbsp;</a>
			<a class='searchQuery colors blue' color="0000FF">&nbsp;</a>
			<a class='searchQuery colors purple' color="762CA7">&nbsp;</a>
			<a class='searchQuery colors pink' color="FF98BF">&nbsp;</a>
			<a class='searchQuery colors white' color="FFFFFF">&nbsp;</a>
			<a class='searchQuery colors grey' color="999999">&nbsp;</a>
			<a class='searchQuery colors black' color="000000">&nbsp;</a>
			<a class='searchQuery colors brown' color="885418">&nbsp;</a>
		</form>
	</div>
</div>

<div id="searchResult" ></div>
<div id="loading" style="display:none;"> <img alt="" src="ajax-loader.gif"> <span>Searching for : </span><span id="userQuery"></span> </div>

</body>
</html>

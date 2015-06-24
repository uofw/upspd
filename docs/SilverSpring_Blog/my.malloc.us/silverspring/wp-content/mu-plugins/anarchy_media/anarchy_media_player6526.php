/*
Anarchy Media Player 2.0
http://an-archos.com/anarchy-media-player
Makes any mp3, Flash flv, Quicktime mov, mp4, m4v, m4a, m4b and 3gp as well as wmv, avi and asf links playable directly on your webpage while optionally hiding the download link. Flash movies, including YouTube etc, use SWFObject javascript embeds - usage examples at http://blog.deconcept.com/swfobject/#examples
Anarchy.js is based on various hacks of excellent scripts - Del.icio.us mp3 Playtagger javascript (http://del.icio.us/help/playtagger) as used in Taragana's Del.icio.us mp3 Player Plugin (http://blog.taragana.com/index.php/archive/taraganas-delicious-mp3-player-wordpress-plugin/) - Jeroen Wijering's Flv Player (http://www.jeroenwijering.com/?item=Flash_Video_Player) with Tradebit modifications (http://www.tradebit.com) - EMFF inspired WP Audio Player mp3 player (http://www.1pixelout.net/code/audio-player-wordpress-plugin). Flash embeds via Geoff Stearns' excellent standards compliant Flash detection and embedding JavaScript (http://blog.deconcept.com/swfobject/).
Distributed under GNU General Public License.

For non-WP pages call script in <HEAD>:
<script type="text/javascript" src="http://PATH TO PLAYER DIRECTORY/anarchy_media/anarchy.js"></script>
*/
// Configure plugin options below

var anarchy_url = 'http://my.malloc.us/silverspring/wp-content/mu-plugins/anarchy_media' // http address for the anarchy-media plugin folder (no trailing slash).
var accepted_domains=new Array("") 	// OPTIONAL - Restrict script use to your domains. Add root domain name (minus 'http' or 'www') in quotes, add extra domains in quotes and separated by comma.
var viddownloadLink = 'none'	// Download link for flv and wmv links: One of 'none' (to turn downloading off) or 'inline' to display the link. ***Use $qtkiosk for qt***.

// MP3 Flash player options
var playerloop = 'no'		// Loop the music ... yes or no?
var mp3downloadLink = 'none'	// Download for mp3 links: One of 'none' (to turn downloading off) or 'inline' to display the link.

// Hex colours for the MP3 Flash Player (minus the #)
var playerbg ='DDDDDD'				// Background colour
var playerleftbg = 'BBBBBB'		// Left background colour
var playerrightbg = 'BBBBBB'		// Right background colour
var playerrightbghover = '666666'	// Right background colour (hover)
var playerlefticon = '000000'		// Left icon colour
var playerrighticon = '000000'		// Right icon colour
var playerrighticonhover = 'FFFFFF'	// Right icon colour (hover)
var playertext = '333333'			// Text colour
var playerslider = '666666'		// Slider colour
var playertrack = '999999'			// Loader bar colour
var playerloader = '666666'		// Progress track colour
var playerborder = '333333'		// Progress track border colour

// Flash video player options
var flvwidth = '400' 	// Width of the flv player
var flvheight = '320'	// Height of the flv player (allow 20px for controller)
var flvfullscreen = 'true' // Show fullscreen button, true or false (no auto return on Safari, double click in IE6)

//Quicktime player options
var qtloop = 'false'		// Loop Quicktime movies: true or false.
var qtwidth = '400'		// Width of your Quicktime player
var qtheight = '316'	// Height of your Quicktime player (allow 16px for controller)
var qtkiosk = 'true'		// Allow downloads, false = yes, true = no.
// Required Quicktime version ='6' - To set the minimum version go to Quicktime player section below and edit (quicktimeVersion >= 6) lines 228 and 266.

//WMV player options
var wmvwidth = '400'	// Width of your WMV player
var wmvheight = '372'	// Height of your WMV player (allow 45px for WMV controller or 16px if QT player - ignored by WinIE)

// CSS styles
var mp3playerstyle = 'vertical-align:bottom; margin:10px 0 5px 2px;'	// Flash mp3 player css style
var mp3imgmargin = '0.5em 0.5em -4px 5px'		// Mp3 button image css margins
var vidimgmargin = '0'		// Video image placeholder css margins

/* ------------------ End configuration options --------------------- */

/* --------------------- Domain Check ----------------------- */
//Lite protection only, you can also use .htaccss if you're paranoid - see http://evolt.org/node/60180
var domaincheck=document.location.href //retrieve the current URL of user browser
var accepted_ok=false //set acess to false by default

if (domaincheck.indexOf("http")!=-1){ //if this is a http request
for (r=0;r<accepted_domains.length;r++){
if (domaincheck.indexOf(accepted_domains[r])!=-1){ //if a match is found
accepted_ok=true //set access to true, and break out of loop
break
}
}
}
else
accepted_ok=true

if (!accepted_ok){
alert("You\'re not allowed to directly link to this .js file on our server!")
history.back(-1)
}

/* --------------------- Flash MP3 audio player ----------------------- */
if(typeof(Anarchy) == 'undefined') Anarchy = {}
Anarchy.Mp3 = {
	playimg: null,
	player: null,
	go: function() {
		var all = document.getElementsByTagName('a')
		for (var i = 0, o; o = all[i]; i++) {
			if(o.href.match(/\.mp3$/i) && o.className!="amplink") {
				o.style.display = mp3downloadLink
				var img = document.createElement('img')
				img.src = anarchy_url+'/images/audio_mp3_play.gif'; img.title = 'Click to listen'
				img.style.margin = mp3imgmargin
				img.style.border = 'none'
				img.style.cursor = 'pointer'
				img.onclick = Anarchy.Mp3.makeToggle(img, o.href)
				o.parentNode.insertBefore(img, o)
	}}},
	toggle: function(img, url) {
		if (Anarchy.Mp3.playimg == img) Anarchy.Mp3.destroy()
		else {
			if (Anarchy.Mp3.playimg) Anarchy.Mp3.destroy()
			img.src = anarchy_url+'/images/audio_mp3_stop.gif'; Anarchy.Mp3.playimg = img;
			Anarchy.Mp3.player = document.createElement('span')
			Anarchy.Mp3.player.innerHTML = '<br /><object style="'+mp3playerstyle+'" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"' +
			'codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0"' +
			'width="290" height="24" id="player" align="middle">' +
			'<param name="wmode" value="transparent" />' +
			'<param name="allowScriptAccess" value="sameDomain" />' +
			'<param name="flashVars" value="bg=0x'+playerbg+'&amp;leftbg=0x'+playerleftbg+'&amp;rightbg=0x'+playerrightbg+'&amp;rightbghover=0x'+playerrightbghover+'&amp;lefticon=0x'+playerlefticon+'&amp;righticon=0x'+playerrighticon+'&amp;righticonhover=0x'+playerrighticonhover+'&amp;text=0x'+playertext+'&amp;slider=0x'+playerslider+'&amp;track=0x'+playertrack+'&amp;loader=0x'+playerloader+'&amp;border=0x'+playerborder+'&amp;autostart=yes&amp;loop='+playerloop+'&amp;soundFile='+url+'" />' +
			'<param name="movie" value="'+anarchy_url+'/player.swf" /><param name="quality" value="high" />' +
			'<embed style="'+mp3playerstyle+'" src="'+anarchy_url+'/player.swf" flashVars="bg=0x'+playerbg+'&amp;leftbg=0x'+playerleftbg+'&amp;rightbg=0x'+playerrightbg+'&amp;rightbghover=0x'+playerrightbghover+'&amp;lefticon=0x'+playerlefticon+'&amp;righticon=0x'+playerrighticon+'&amp;righticonhover=0x'+playerrighticonhover+'&amp;text=0x'+playertext+'&amp;slider=0x'+playerslider+'&amp;track=0x'+playertrack+'&amp;loader=0x'+playerloader+'&amp;border=0x'+playerborder+'&amp;autostart=yes&amp;loop='+playerloop+'&amp;soundFile='+url+'" '+
			'quality="high" wmode="transparent" width="290" height="24" name="player"' +
			'align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash"' +
			' pluginspage="http://www.macromedia.com/go/getflashplayer" /></object><br />'
			img.parentNode.insertBefore(Anarchy.Mp3.player, img.nextSibling)
	}},
	destroy: function() {
		Anarchy.Mp3.playimg.src = anarchy_url+'/images/audio_mp3_play.gif'; Anarchy.Mp3.playimg = null
		Anarchy.Mp3.player.removeChild(Anarchy.Mp3.player.firstChild); Anarchy.Mp3.player.parentNode.removeChild(Anarchy.Mp3.player); Anarchy.Mp3.player = null
	},
	makeToggle: function(img, url) { return function(){ Anarchy.Mp3.toggle(img, url) }}
}

/* ----------------- Flash flv video player ----------------------- */

if(typeof(Anarchy) == 'undefined') Anarchy = {}
Anarchy.FLV = {
	go: function() {
		var all = document.getElementsByTagName('a')
		for (var i = 0, o; o = all[i]; i++) {
			if(o.href.match(/\.flv$/i) && o.className!="amplink") {
			o.style.display = viddownloadLink
			url = o.href
			var flvplayer = document.createElement('span')
			flvplayer.innerHTML = '<object type="application/x-shockwave-flash" wmode="transparent" data="'+anarchy_url+'/flvplayer.swf?click='+anarchy_url+'/images/flvplaybutton.jpg&file='+url+'&showfsbutton='+flvfullscreen+'" height="'+flvheight+'" width="'+flvwidth+'">' +
			'<param name="movie" value="'+anarchy_url+'/flvplayer.swf?click='+anarchy_url+'/images/flvplaybutton.jpg&file='+url+'&showfsbutton='+flvfullscreen+'"> <param name="wmode" value="transparent">' +
			'<embed src="'+anarchy_url+'/flvplayer.swf?file='+url+'&click='+anarchy_url+'/images/flvplaybutton.jpg&&showfsbutton='+flvfullscreen+'" ' + 
			'width="'+flvwidth+'" height="'+flvheight+'" name="flvplayer" align="middle" ' + 
			'play="true" loop="false" quality="high" allowScriptAccess="sameDomain" ' +
			'type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer">' + 
			'</embed></object><br />'
			o.parentNode.insertBefore(flvplayer, o)
	}}}}

/* ----------------------- QUICKTIME DETECT --------------------------- 
// Bits of code by Chris Nott (chris[at]dithered[dot]com) and
// Geoff Stearns (geoff@deconcept.com, http://www.deconcept.com/)
--------------------------------------------------------------------- */

function getQuicktimeVersion() {
var n=navigator;
var nua=n.userAgent;
var saf=(nua.indexOf('Safari')!=-1);
var quicktimeVersion = 0;

if (saf) {
quicktimeVersion='9.0';
}
else {	
var agent = navigator.userAgent.toLowerCase(); 
	
	// NS3+, Opera3+, IE5+ Mac (support plugin array):  check for Quicktime plugin in plugin array
	if (navigator.plugins != null && navigator.plugins.length > 0) {
      for (i=0; i < navigator.plugins.length; i++ ) {
         var plugin =navigator.plugins[i];
         if (plugin.name.indexOf("QuickTime") > -1) {
            quicktimeVersion = parseFloat(plugin.name.substring(18));
         }
      }
	}
   	else if (window.ActiveXObject) {
		execScript('on error resume next: qtObj = IsObject(CreateObject("QuickTime.QuickTime.4"))','VBScript');
			if (qtObj = true) {
				quicktimeVersion = 100;
				}
			else {
				quicktimeVersion = 0;
			}
		}
	}
	return quicktimeVersion;
}

/* ----------------------- Quicktime player ------------------------ */

if(typeof(Anarchy) == 'undefined') Anarchy = {}
Anarchy.MOV = {
	playimg: null,
	player: null,
	go: function() {
		var all = document.getElementsByTagName('a')
		Anarchy.MOV.preview_images = { }
		for (var i = 0, o; o = all[i]; i++) {
			if(o.href.match(/\.mov$|\.mp4$|\.m4v$|\.m4b$|\.3gp$/i) && o.className!="amplink") {
				o.style.display = 'none'
				var img = document.createElement('img')
				Anarchy.MOV.preview_images[i] = document.createElement('img') ;
				Anarchy.MOV.preview_images[i].src = o.href + '.jpg' ;
				Anarchy.MOV.preview_images[i].defaultImg = img ;
				Anarchy.MOV.preview_images[i].replaceDefault = function() {
				  this.defaultImg.src = this.src ; 
				}
				Anarchy.MOV.preview_images[i].onload = Anarchy.MOV.preview_images[i].replaceDefault ;
				img.src = anarchy_url+'/images/vid_play.gif'
				img.title = 'Click to play video'
				img.style.margin = vidimgmargin
				img.style.padding = '0px'
				img.style.display = 'block'
				img.style.border = 'none'
				img.style.cursor = 'pointer'
				img.height = qtheight
				img.width = qtwidth
				img.onclick = Anarchy.MOV.makeToggle(img, o.href)
				o.parentNode.insertBefore(img, o)
	}}},
	toggle: function(img, url) {
		if (Anarchy.MOV.playimg == img) Anarchy.MOV.destroy()
		else {
			if (Anarchy.MOV.playimg) Anarchy.MOV.destroy()
			img.src = anarchy_url+'/images/vid_play.gif'
			img.style.display = 'none'; 
			Anarchy.MOV.playimg = img;
			Anarchy.MOV.player = document.createElement('p')
			var quicktimeVersion = getQuicktimeVersion()
			if (quicktimeVersion >= 6) {
			Anarchy.MOV.player.innerHTML = '<embed src="'+url+'" width="'+qtwidth+'" height="'+qtheight+'" loop="'+qtloop+'" autoplay="true" controller="true" border="0" type="video/quicktime" kioskmode="'+qtkiosk+'" scale="tofit"></embed><br />'
          img.parentNode.insertBefore(Anarchy.MOV.player, img.nextSibling)
          }
		else
			Anarchy.MOV.player.innerHTML = '<a href="http://www.apple.com/quicktime/download/" target="_blank"><img src="'+anarchy_url+'/images/getqt.jpg"></a>'
          img.parentNode.insertBefore(Anarchy.MOV.player, img.nextSibling)
	}},
	destroy: function() {
	},
	makeToggle: function(img, url) { return function(){ Anarchy.MOV.toggle(img, url) }}
}

/* --------------------- MPEG 4 Audio Quicktime player ---------------------- */

if(typeof(Anarchy) == 'undefined') Anarchy = {}
Anarchy.M4a = {
	playimg: null,
	player: null,
	go: function() {
		var all = document.getElementsByTagName('a')
		for (var i = 0, o; o = all[i]; i++) {
			if(o.href.match(/\.m4a$/i) && o.className!="amplink") {
				o.style.display = 'none'
				var img = document.createElement('img')
				img.src = anarchy_url+'/images/audio_mp4_play.gif'; img.title = 'Click to listen'
				img.style.margin = mp3imgmargin
				img.style.border = 'none'
				img.style.cursor = 'pointer'
				img.onclick = Anarchy.M4a.makeToggle(img, o.href)
				o.parentNode.insertBefore(img, o)
	}}},
	toggle: function(img, url) {
		if (Anarchy.M4a.playimg == img) Anarchy.M4a.destroy()
		else {
			if (Anarchy.M4a.playimg) Anarchy.M4a.destroy()
			img.src = anarchy_url+'/images/audio_mp4_stop.gif'; Anarchy.M4a.playimg = img;
			Anarchy.M4a.player = document.createElement('p')
			var quicktimeVersion = getQuicktimeVersion()
			if (quicktimeVersion >= 6) {
			Anarchy.M4a.player.innerHTML = '<embed src="'+url+'" width="160" height="16" loop="'+qtloop+'" autoplay="true" controller="true" border="0" type="video/quicktime" kioskmode="'+qtkiosk+'" ></embed><br />'
          img.parentNode.insertBefore(Anarchy.M4a.player, img.nextSibling)
          }
		else
			Anarchy.M4a.player.innerHTML = '<a href="http://www.apple.com/quicktime/download/" target="_blank"><img src="'+anarchy_url+'/images/getqt.jpg"></a>'
          img.parentNode.insertBefore(Anarchy.M4a.player, img.nextSibling)
	}},
	destroy: function() {
		Anarchy.M4a.playimg.src = anarchy_url+'/images/audio_mp4_play.gif'; Anarchy.M4a.playimg = null
		Anarchy.M4a.player.removeChild(Anarchy.M4a.player.firstChild); Anarchy.M4a.player.parentNode.removeChild(Anarchy.M4a.player); Anarchy.M4a.player = null
	},
	makeToggle: function(img, url) { return function(){ Anarchy.M4a.toggle(img, url) }}
}

/* ----------------------- WMV player -------------------------- */

if(typeof(Anarchy) == 'undefined') Anarchy = {}
Anarchy.WMV = {
	playimg: null,
	player: null,
	go: function() {
		var all = document.getElementsByTagName('a')
		for (var i = 0, o; o = all[i]; i++) {
			if(o.href.match(/\.asf$|\.avi$|\.wmv$/i) && o.className!="amplink") {
				o.style.display = viddownloadLink
				var img = document.createElement('img')
				img.src = anarchy_url+'/images/vid_play.gif'; img.title = 'Click to play video'
				img.style.margin = '0px'
				img.style.padding = '0px'
				img.style.display = 'block'
				img.style.border = 'none'
				img.style.cursor = 'pointer'
				img.height = qtheight
				img.width = qtwidth
				img.onclick = Anarchy.WMV.makeToggle(img, o.href)
				o.parentNode.insertBefore(img, o)
	}}},
	toggle: function(img, url) {
		if (Anarchy.WMV.playimg == img) Anarchy.WMV.destroy()
		else {
			  if (Anarchy.WMV.playimg) Anarchy.WMV.destroy()
			  img.src = anarchy_url+'/images/vid_play.gif'
			  img.style.display = 'none'; 
			  Anarchy.WMV.playimg = img;
			  Anarchy.WMV.player = document.createElement('span')
			  if(navigator.userAgent.indexOf('Mac') != -1) {
			  Anarchy.WMV.player.innerHTML = '<embed src="'+url+'" width="'+qtwidth+'" height="'+qtheight+'" loop="'+qtloop+'" autoplay="true" controller="true" border="0" type="video/quicktime" kioskmode="'+qtkiosk+'" scale="tofit" pluginspage="http://www.apple.com/quicktime/download/"></embed><br />'
			  img.parentNode.insertBefore(Anarchy.WMV.player, img.nextSibling)
			  } else {
			  if (navigator.plugins && navigator.plugins.length) {
			  Anarchy.WMV.player.innerHTML = '<embed type="application/x-mplayer2" src="'+url+'" ' +
			  'showcontrols="1" ShowStatusBar="1" autostart="1" displaySize="4"' +
			  'pluginspage="http://www.microsoft.com/Windows/Downloads/Contents/Products/MediaPlayer/"' +
			  'width="'+wmvwidth+'" height="'+wmvheight+'">' +
			  '</embed><br />'
			  img.parentNode.insertBefore(Anarchy.WMV.player, img.nextSibling)
			  } else {
				Anarchy.WMV.player.innerHTML = '<object classid="CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6" width="'+wmvwidth+'" height="'+wmvheight+'" id="player"> ' +
			  '<param name="url" value="'+url+'" /> ' +
			  '<param name="autoStart" value="True" /> ' +
			  '<param name="stretchToFit" value="True" /> ' +
			  '<param name="showControls" value="True" /> ' +
			  '<param name="ShowStatusBar" value="True" /> ' +
			  '<embed type="application/x-mplayer2" src="'+url+'" ' +
			  'showcontrols="1" ShowStatusBar="1" autostart="1" displaySize="4"' +
			  'pluginspage="http://www.microsoft.com/Windows/Downloads/Contents/Products/MediaPlayer/"' +
			  'width="'+wmvwidth+'" height="'+wmvheight+'">' +
			  '</embed>'
			  '</object><br />'
			  img.parentNode.insertBefore(Anarchy.WMV.player, img.nextSibling)
			  }}
	}},
	destroy: function() {
		Anarchy.WMV.playimg.src = anarchy_url+'/images/vid_play.gif'
		Anarchy.WMV.playimg.style.display = 'inline'; Anarchy.WMV.playimg = null
		Anarchy.WMV.player.removeChild(Anarchy.WMV.player.firstChild); 
		Anarchy.WMV.player.parentNode.removeChild(Anarchy.WMV.player); 
		Anarchy.WMV.player = null
	},
	makeToggle: function(img, url) { return function(){ Anarchy.WMV.toggle(img, url) }}
}

/* ----------------- Trigger players onload ----------------------- */

Anarchy.addLoadEvent = function(f) { var old = window.onload
	if (typeof old != 'function') window.onload = f
	else { window.onload = function() { old(); f() }}
}

Anarchy.addLoadEvent(Anarchy.Mp3.go)
Anarchy.addLoadEvent(Anarchy.FLV.go)
Anarchy.addLoadEvent(Anarchy.MOV.go)
Anarchy.addLoadEvent(Anarchy.M4a.go)
Anarchy.addLoadEvent(Anarchy.WMV.go)

/**
 * SWFObject v1.5: Flash Player detection and embed - http://blog.deconcept.com/swfobject/
 *
 * SWFObject is (c) 2006 Geoff Stearns and is released under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 *
 */
if(typeof deconcept=="undefined"){var deconcept=new Object();}if(typeof deconcept.util=="undefined"){deconcept.util=new Object();}if(typeof deconcept.SWFObjectUtil=="undefined"){deconcept.SWFObjectUtil=new Object();}deconcept.SWFObject=function(_1,id,w,h,_5,c,_7,_8,_9,_a){if(!document.getElementById){return;}this.DETECT_KEY=_a?_a:"detectflash";this.skipDetect=deconcept.util.getRequestParameter(this.DETECT_KEY);this.params=new Object();this.variables=new Object();this.attributes=new Array();if(_1){this.setAttribute("swf",_1);}if(id){this.setAttribute("id",id);}if(w){this.setAttribute("width",w);}if(h){this.setAttribute("height",h);}if(_5){this.setAttribute("version",new deconcept.PlayerVersion(_5.toString().split(".")));}this.installedVer=deconcept.SWFObjectUtil.getPlayerVersion();if(!window.opera&&document.all&&this.installedVer.major>7){deconcept.SWFObject.doPrepUnload=true;}if(c){this.addParam("bgcolor",c);}var q=_7?_7:"high";this.addParam("quality",q);this.setAttribute("useExpressInstall",false);this.setAttribute("doExpressInstall",false);var _c=(_8)?_8:window.location;this.setAttribute("xiRedirectUrl",_c);this.setAttribute("redirectUrl","");if(_9){this.setAttribute("redirectUrl",_9);}};deconcept.SWFObject.prototype={useExpressInstall:function(_d){this.xiSWFPath=!_d?"expressinstall.swf":_d;this.setAttribute("useExpressInstall",true);},setAttribute:function(_e,_f){this.attributes[_e]=_f;},getAttribute:function(_10){return this.attributes[_10];},addParam:function(_11,_12){this.params[_11]=_12;},getParams:function(){return this.params;},addVariable:function(_13,_14){this.variables[_13]=_14;},getVariable:function(_15){return this.variables[_15];},getVariables:function(){return this.variables;},getVariablePairs:function(){var _16=new Array();var key;var _18=this.getVariables();for(key in _18){_16.push(key+"="+_18[key]);}return _16;},getSWFHTML:function(){var _19="";if(navigator.plugins&&navigator.mimeTypes&&navigator.mimeTypes.length){if(this.getAttribute("doExpressInstall")){this.addVariable("MMplayerType","PlugIn");this.setAttribute("swf",this.xiSWFPath);}_19="<embed type=\"application/x-shockwave-flash\" src=\""+this.getAttribute("swf")+"\" width=\""+this.getAttribute("width")+"\" height=\""+this.getAttribute("height")+"\"";_19+=" id=\""+this.getAttribute("id")+"\" name=\""+this.getAttribute("id")+"\" ";var _1a=this.getParams();for(var key in _1a){_19+=[key]+"=\""+_1a[key]+"\" ";}var _1c=this.getVariablePairs().join("&");if(_1c.length>0){_19+="flashvars=\""+_1c+"\"";}_19+="/>";}else{if(this.getAttribute("doExpressInstall")){this.addVariable("MMplayerType","ActiveX");this.setAttribute("swf",this.xiSWFPath);}_19="<object id=\""+this.getAttribute("id")+"\" classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" width=\""+this.getAttribute("width")+"\" height=\""+this.getAttribute("height")+"\">";_19+="<param name=\"movie\" value=\""+this.getAttribute("swf")+"\" />";var _1d=this.getParams();for(var key in _1d){_19+="<param name=\""+key+"\" value=\""+_1d[key]+"\" />";}var _1f=this.getVariablePairs().join("&");if(_1f.length>0){_19+="<param name=\"flashvars\" value=\""+_1f+"\" />";}_19+="</object>";}return _19;},write:function(_20){if(this.getAttribute("useExpressInstall")){var _21=new deconcept.PlayerVersion([6,0,65]);if(this.installedVer.versionIsValid(_21)&&!this.installedVer.versionIsValid(this.getAttribute("version"))){this.setAttribute("doExpressInstall",true);this.addVariable("MMredirectURL",escape(this.getAttribute("xiRedirectUrl")));document.title=document.title.slice(0,47)+" - Flash Player Installation";this.addVariable("MMdoctitle",document.title);}}if(this.skipDetect||this.getAttribute("doExpressInstall")||this.installedVer.versionIsValid(this.getAttribute("version"))){var n=(typeof _20=="string")?document.getElementById(_20):_20;n.innerHTML=this.getSWFHTML();return true;}else{if(this.getAttribute("redirectUrl")!=""){document.location.replace(this.getAttribute("redirectUrl"));}}return false;}};deconcept.SWFObjectUtil.getPlayerVersion=function(){var _23=new deconcept.PlayerVersion([0,0,0]);if(navigator.plugins&&navigator.mimeTypes.length){var x=navigator.plugins["Shockwave Flash"];if(x&&x.description){_23=new deconcept.PlayerVersion(x.description.replace(/([a-zA-Z]|\s)+/,"").replace(/(\s+r|\s+b[0-9]+)/,".").split("."));}}else{if(navigator.userAgent&&navigator.userAgent.indexOf("Windows CE")>=0){var axo=1;var _26=3;while(axo){try{_26++;axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash."+_26);_23=new deconcept.PlayerVersion([_26,0,0]);}catch(e){axo=null;}}}else{try{var axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7");}catch(e){try{var axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6");_23=new deconcept.PlayerVersion([6,0,21]);axo.AllowScriptAccess="always";}catch(e){if(_23.major==6){return _23;}}try{axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash");}catch(e){}}if(axo!=null){_23=new deconcept.PlayerVersion(axo.GetVariable("$version").split(" ")[1].split(","));}}}return _23;};deconcept.PlayerVersion=function(_29){this.major=_29[0]!=null?parseInt(_29[0]):0;this.minor=_29[1]!=null?parseInt(_29[1]):0;this.rev=_29[2]!=null?parseInt(_29[2]):0;};deconcept.PlayerVersion.prototype.versionIsValid=function(fv){if(this.major<fv.major){return false;}if(this.major>fv.major){return true;}if(this.minor<fv.minor){return false;}if(this.minor>fv.minor){return true;}if(this.rev<fv.rev){return false;}return true;};deconcept.util={getRequestParameter:function(_2b){var q=document.location.search||document.location.hash;if(_2b==null){return q;}if(q){var _2d=q.substring(1).split("&");for(var i=0;i<_2d.length;i++){if(_2d[i].substring(0,_2d[i].indexOf("="))==_2b){return _2d[i].substring((_2d[i].indexOf("=")+1));}}}return "";}};deconcept.SWFObjectUtil.cleanupSWFs=function(){var _2f=document.getElementsByTagName("OBJECT");for(var i=_2f.length-1;i>=0;i--){_2f[i].style.display="none";for(var x in _2f[i]){if(typeof _2f[i][x]=="function"){_2f[i][x]=function(){};}}}};if(deconcept.SWFObject.doPrepUnload){deconcept.SWFObjectUtil.prepUnload=function(){__flash_unloadHandler=function(){};__flash_savedUnloadHandler=function(){};window.attachEvent("onunload",deconcept.SWFObjectUtil.cleanupSWFs);};window.attachEvent("onbeforeunload",deconcept.SWFObjectUtil.prepUnload);}if(Array.prototype.push==null){Array.prototype.push=function(_32){this[this.length]=_32;return this.length;};}if(!document.getElementById&&document.all){document.getElementById=function(id){return document.all["id"];};}var getQueryParamValue=deconcept.util.getRequestParameter;var FlashObject=deconcept.SWFObject;var SWFObject=deconcept.SWFObject;

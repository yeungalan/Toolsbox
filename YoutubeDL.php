<?php
include_once '../auth.php';
?>
<?php if(!isset($_GET["opr"])){ ?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" href="../script/tocas/tocas.css">
		<script src="../script/tocas/tocas.js"></script>
		<script src="../script/jquery.min.js"></script>
        <title>ArOZ YT Downloader</title>
		<style>
			button#downloadbtn {
				width: 25% !important;
			}
		</style>
	</head>
	<body>
		<br>
		<div class="ts container">
			<div class="tablet or large device only">
				<div class="ts huge heading padded slate">
					<i class="youtube play symbol icon"></i>
					<span class="header">Youtube Downloader</span>
					<span class="description">Download Youtube video here easily.</span>
				</div>
			</div>
			<div class="mobile only">
				<div class="ts slate">
					<span class="header"><i class="youtube play icon"></i>Youtube Downloader</span>
				</div>
			</div>
			<br>
			<div class="ts action fluid input">
				<input type="text" id="yturl" placeholder="https://www.youtube.com/watch?v=">
				<button class="ts button" id="ytbtn" onclick="fetch()">Download</button>
			</div>
			<br><br><br>
			<div id="result">&nbsp;</div>
			
			
			
		</div>
	</body>
	<script>
	$(document).keypress(function(e) {
		if(e.which == 13) {
			fetch();
		}
	});
	
	function fetch(){	
		$("#yturl").attr("disabled","disabled");
		$("#ytbtn").attr("disabled","disabled");
		var filename = "";
		var htmlstructure = '<div class="ts items"><div class="item"><div class="image"><img src="{%image%}"></div><div class="content"><div class="header">{%author%}</div><div class="description">{%description%}<br><br><div class="ts form"><div class="inline field"><select id="quality"><option>Loading...</option></select><button class="ts basic button" filename="{%filename%}" id="downloadbtn" onclick="download()">Download</button></div></div></div></div></div></div>';
		
		$.get("index.php?opr=2&ytlink=" + $("#yturl").val() , function(result){
			if(result.length > 2){
				var data = JSON.parse(result);
				filename = data["title"] + "-" + data["author_name"]
				htmlstructure = htmlstructure.replace("{%image%}",data["thumbnail_url"]);
				htmlstructure = htmlstructure.replace("{%author%}",data["author_name"]);
				htmlstructure = htmlstructure.replace("{%description%}",data["title"]);
				htmlstructure = htmlstructure.replace("{%filename%}",filename);
				$("#result").html(htmlstructure);
			}else{
				htmlstructure = htmlstructure.replace("{%image%}","");
				htmlstructure = htmlstructure.replace("{%author%}","Error");
				htmlstructure = htmlstructure.replace("{%description%}","Error while fetching video information.");
				htmlstructure = htmlstructure.replace("{%filename%}","");
				$("#result").html(htmlstructure);
			}
			$("#downloadbtn").attr("disabled","disabled");
			$("#quality").attr("disabled","disabled");
			$.get("index.php?opr=1&ytlink=" + $("#yturl").val() , function(data){
				console.log(data);
				$("#quality").html("");
				var JSdataArray = JSON.parse(data);
				if(data.length > 2){
					$.each(JSdataArray,function(val) {
						$("#quality").append('<option id="' + val + '">' + JSdataArray[val]["format"] + '</option>');
						$("#downloadbtn").removeAttr("disabled");
						$("#quality").removeAttr("disabled");
					});
				}else{
					$("#quality").append('<option>Not found.</option>');
				}
			});
		});
	}
	
	function download(){
			$(".ts.button").attr("disabled","disabled");
			$("#downloadbtn").html('<i class="notched circle loading icon"></i>Downloading');
			var filename = encodeURI($("#downloadbtn").attr("filename"));
			console.log(filename + "...");
			var quality = $("#quality").find(":selected").attr("id");;
			$.get("index.php?opr=3&quality=" + quality  + "&ytlink=" + $("#yturl").val() + "&filename=" + filename, function(data){
				$("#downloadbtn").text("Downloaded");
			});
	}
	
	</script>
</html>
<?php
}

$subfilename = array(
        5 => "flv",
        6 => "flv",
        13 => "3gp",
        17 => "3gp",
        18 => "mp4",
        22 => "mp4",
        34 => "flv",
        35 => "flv",
        36 => "3gp",
        37 => "mp4",
        38 => "mp4",
        43 => "webm",
        44 => "webm",
        45 => "webm",
        46 => "webm",
        59 => "mp4",
        78 => "mp4",
        82 => "mp4",
        83 => "mp4",
        84 => "mp4",
        85 => "mp4",
        91 => "mp4",
        92 => "mp4",
        93 => "mp4",
        94 => "mp4",
        95 => "mp4",
        96 => "mp4",
        100 => "webm",
        101 => "webm",
        102 => "webm",
        120 => "webm",
        127 => "ts",
        128 => "ts"
    );
	
if($_GET["opr"] == 1){
	$yt = new YouTubeDownloader();
	$videolink = $yt->getDownloadLinks($_GET["ytlink"]);
	echo json_encode($videolink);
}else if($_GET["opr"] == 2){
	$details = file_get_contents("https://www.youtube.com/oembed?url=".$_GET["ytlink"]."&format=json");
	if($details == ""){
		$details = "[]";
	}
	echo $details;
}else if($_GET["opr"] == 3){
	$filename = $_GET["filename"];
	$yt = new YouTubeDownloader();
	$videolink = $yt->getDownloadLinks($_GET["ytlink"]);
	if(!ctype_alnum($filename)){
		$filename = "inith".bin2hex($filename);
	}
	file_put_contents("./files/".$filename.".".$subfilename[$_GET["quality"]], fopen($videolink[$_GET["quality"]]["url"], 'r'));

}else if(isset($_GET["opr"])){
	echo "[]";
}

// YouTube is capitalized twice because that's how youtube itself does it:
// https://developers.google.com/youtube/v3/code_samples/php
class YouTubeDownloader
{
    private $storage_dir;
    private $cookie_dir;
    private $client;
    private $itag_info = array(
        5 => "FLV 400x240",
        6 => "FLV 450x240",
        13 => "3GP Mobile",
        17 => "3GP 144p",
        18 => "MP4 360p",
        22 => "MP4 720p (HD)",
        34 => "FLV 360p",
        35 => "FLV 480p",
        36 => "3GP 240p",
        37 => "MP4 1080",
        38 => "MP4 3072p",
        43 => "WebM 360p",
        44 => "WebM 480p",
        45 => "WebM 720p",
        46 => "WebM 1080p",
        59 => "MP4 480p",
        78 => "MP4 480p",
        82 => "MP4 360p 3D",
        83 => "MP4 480p 3D",
        84 => "MP4 720p 3D",
        85 => "MP4 1080p 3D",
        91 => "MP4 144p",
        92 => "MP4 240p HLS",
        93 => "MP4 360p HLS",
        94 => "MP4 480p HLS",
        95 => "MP4 720p HLS",
        96 => "MP4 1080p HLS",
        100 => "WebM 360p 3D",
        101 => "WebM 480p 3D",
        102 => "WebM 720p 3D",
        120 => "WebM 720p 3D",
        127 => "TS Dash Audio 96kbps",
        128 => "TS Dash Audio 128kbps"
    );
    function __construct()
    {
        $this->storage_dir = sys_get_temp_dir();
        $this->cookie_dir = sys_get_temp_dir();
        $this->client = null;
    }
    function setStorageDir($dir)
    {
        $this->storage_dir = $dir;
    }
    // if URL: download it
    private function toHtml($html)
    {
    }
    // what identifies each request? user agent, cookies...
    public function curl($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:49.0) Gecko/20100101 Firefox/49.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //curl_setopt($ch, CURLOPT_COOKIEJAR, $tmpfname);
        //curl_setopt($ch, CURLOPT_COOKIEFILE, $tmpfname);
        //curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    // TODO: remove this as it required PECL extension
    public static function head($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        return http_parse_headers($result);
    }
    // accepts either raw HTML or url
    // <script src="//s.ytimg.com/yts/jsbin/player-fr_FR-vflHVjlC5/base.js" name="player/base"></script>
    public function getPlayerUrl($video_html)
    {
        if (strpos($video_html, 'http') === 0) {
            $video_html = $this->curl($video_html);
        }
        $player_url = null;
        // check what player version that video is using
        if (preg_match('@<script\s*src="([^"]+player[^"]+js)@', $video_html, $matches)) {
            $player_url = $matches[1];
            // relative protocol?
            if (strpos($player_url, '//') === 0) {
                $player_url = 'http://' . substr($player_url, 2);
            } elseif (strpos($player_url, '/') === 0) {
                // relative path?
                $player_url = 'http://www.youtube.com' . $player_url;
            }
        }
        return $player_url;
    }
    // Do not redownload player.js everytime - cache it
    public function getPlayerHtml($video_html)
    {
        $player_url = $this->getPlayerUrl($video_html);
        $cache_path = sprintf('%s/%s', $this->storage_dir, md5($player_url));
        if (file_exists($cache_path)) {
            $contents = file_get_contents($cache_path);
            //return unserialize($contents);
        }
        $contents = $this->curl($player_url);
        // cache it too!
        file_put_contents($cache_path, serialize($contents));
        return $contents;
    }
    /*
     * Youtube Sep2018 Changes
    deDE:
        var aL={NI:function(a,b){a.splice(0,b)},jl:function(a){a.reverse()},l5:function(a,b){var c=a[0];a[0]=a[b%a.length];a[b%a.length]=c}}
        bL=function(a){a=a.split("");aL.jl(a,58);aL.NI(a,2);aL.l5(a,35);aL.NI(a,2);aL.jl(a,45);aL.l5(a,4);aL.jl(a,46);return a.join("")};
    ->$L=function(a,b,c){b=void 0===b?"":b;c=void 0===c?"":c;var d=new g.cL(a);a.match(/https:\/\/yt.akamaized.net/)||d.set("alr","yes");c&&d.set(b,bL(c));return d};
    */
    public function getSigDecodeFunctionName($player_html)
    {
        $pattern = '@yt\.akamaized\.net\/\)\s*\|\|\s*.*?\s*c\s*&&\s*d\.set\([^,]+\s*,\s*\([^\)]+\)\(([a-zA-Z0-9$]+)@is';
        if (preg_match($pattern, $player_html, $matches)) {
            $func_name = $matches[1];
            $func_name = preg_quote($func_name);
            return $func_name;
        }
        return null;
    }
    // convert JS code for signature decipher to PHP code
    public function getSigDecodeInstructions($player_html, $func_name)
    {
        // extract code block from that function
        // single quote in case function name contains $dollar sign
        // xm=function(a){a=a.split("");wm.zO(a,47);wm.vY(a,1);wm.z9(a,68);wm.zO(a,21);wm.z9(a,34);wm.zO(a,16);wm.z9(a,41);return a.join("")};
        if (preg_match('/' . $func_name . '=function\([a-z]+\){(.*?)}/', $player_html, $matches)) {
            $js_code = $matches[1];
            // extract all relevant statements within that block
            // wm.vY(a,1);
            if (preg_match_all('/([a-z0-9]{2})\.([a-z0-9]{2})\([^,]+,(\d+)\)/i', $js_code, $matches) != false) {
                // must be identical
                $obj_list = $matches[1];
                //
                $func_list = $matches[2];
                // extract javascript code for each one of those statement functions
                preg_match_all('/(' . implode('|', $func_list) . '):function(.*?)\}/m', $player_html, $matches2, PREG_SET_ORDER);
                $functions = array();
                // translate each function according to its use
                foreach ($matches2 as $m) {
                    if (strpos($m[2], 'splice') !== false) {
                        $functions[$m[1]] = 'splice';
                    } elseif (strpos($m[2], 'a.length') !== false) {
                        $functions[$m[1]] = 'swap';
                    } elseif (strpos($m[2], 'reverse') !== false) {
                        $functions[$m[1]] = 'reverse';
                    }
                }
                // FINAL STEP! convert it all to instructions set
                $instructions = array();
                foreach ($matches[2] as $index => $name) {
                    $instructions[] = array($functions[$name], $matches[3][$index]);
                }
                return $instructions;
            }
        }
        return null;
    }
    public function decodeSignature($signature, $video_html)
    {
        $player_html = $this->getPlayerHtml($video_html);
        $func_name = $this->getSigDecodeFunctionName($player_html);
        // PHP instructions
        $instructions = $this->getSigDecodeInstructions($player_html, $func_name);
        foreach ($instructions as $opt) {
            $command = $opt[0];
            $value = $opt[1];
            if ($command == 'swap') {
                $temp = $signature[0];
                $signature[0] = $signature[$value % strlen($signature)];
                $signature[$value] = $temp;
            } elseif ($command == 'splice') {
                $signature = substr($signature, $value);
            } elseif ($command == 'reverse') {
                $signature = strrev($signature);
            }
        }
        return trim($signature);
    }
    // this is in beta mode!!
    // TODO: move this to its own HttpClient class
    public function stream($id)
    {
        $links = $this->getDownloadLinks($id, "mp4");
        if (count($links) == 0) {
            die("no url found!");
        }
        // grab first available MP4 link
        $url = $links[0]['url'];
        // request headers
        $headers = array(
            'User-Agent: Mozilla/5.0 (Windows NT 6.3; WOW64; rv:49.0) Gecko/20100101 Firefox/49.0'
        );
        if (isset($_SERVER['HTTP_RANGE'])) {
            $headers[] = 'Range: ' . $_SERVER['HTTP_RANGE'];
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        // we deal with this ourselves
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        // whether request to video success
        $headers = '';
        $headers_sent = false;
        $success = false;
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($ch, $data) use (&$headers, &$headers_sent) {
            $headers .= $data;
            // this should be first line
            if (preg_match('@HTTP\/\d\.\d\s(\d+)@', $data, $matches)) {
                $status_code = $matches[1];
                // status=ok or partial content
                if ($status_code == 200 || $status_code == 206) {
                    $headers_sent = true;
                    header(rtrim($data));
                }
            } else {
                // only headers we wish to forward back to the client
                $forward = array('content-type', 'content-length', 'accept-ranges', 'content-range');
                $parts = explode(':', $data, 2);
                if ($headers_sent && count($parts) == 2 && in_array(trim(strtolower($parts[0])), $forward)) {
                    header(rtrim($data));
                }
            }
            return strlen($data);
        });
        // if response is empty - this never gets called
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($curl, $data) use (&$headers_sent) {
            if ($headers_sent) {
                echo $data;
                flush();
            }
            return strlen($data);
        });
        $ret = @curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        // if we are still here by now, return status_code
        return true;
    }
    // extract youtube video_id from any piece of text
    public function extractVideoId($str)
    {
        if (preg_match('/[a-z0-9_-]{11}/i', $str, $matches)) {
            return $matches[0];
        }
        return false;
    }
    // selector by format: mp4 360,
    private function selectFirst($links, $selector)
    {
        $result = array();
        $formats = preg_split('/\s*,\s*/', $selector);
        // has to be in this order
        foreach ($formats as $f) {
            foreach ($links as $l) {
                if (stripos($l['format'], $f) !== false || $f == 'any') {
                    $result[] = $l;
                }
            }
        }
        return $result;
    }
    // some of the data may need signature decoding
    public function parseStreamMap($video_html, $video_id)
    {
        $stream_map = array();
        $result = array();
        // http://stackoverflow.com/questions/35608686/how-can-i-get-the-actual-video-url-of-a-youtube-live-stream
        if (preg_match('@url_encoded_fmt_stream_map["\']:\s*["\']([^"\'\s]*)@', $video_html, $matches)) {
            $stream_map = $matches[1];
        } else {
            $gvi = $this->curl("https://www.youtube.com/get_video_info?el=embedded&eurl=https%3A%2F%2Fwww.youtube.com%2Fwatch%3Fv%3D" . urlencode($video_id) . "&video_id={$video_id}");
            if (preg_match('@url_encoded_fmt_stream_map=([^\&\s]+)@', $gvi, $matches_gvi)) {
                $stream_map = urldecode($matches_gvi[1]);
            }
        }
        if ($stream_map) {
            $parts = explode(",", $stream_map);
            foreach ($parts as $p) {
                $query = str_replace('\u0026', '&', $p);
                parse_str($query, $arr);
                $result[] = $arr;
            }
            return $result;
        }
        // TODO:
        // elseif (strpos($html, 'player-age-gate-content') !== false) { // age-gate
        // youtube must have changed something
        return $result;
    }
    // options | deep_links | append_redirector
    // TODO: make it accept video_html too
    public function getDownloadLinks($video_id, $selector = false)
    {
        // you can input HTML of /watch? page directory instead of id
        $video_id = $this->extractVideoId($video_id);
        $video_html = $this->curl("https://www.youtube.com/watch?v={$video_id}");
        $result = array();
        $url_map = $this->parseStreamMap($video_html, $video_id);
        foreach ($url_map as $arr) {
            $url = $arr['url'];
            if (isset($arr['sig'])) {
                $url = $url . '&signature=' . $arr['sig'];
            } elseif (isset($arr['signature'])) {
                $url = $url . '&signature=' . $arr['signature'];
            } elseif (isset($arr['s'])) {
                $signature = $this->decodeSignature($arr['s'], $video_html);
                $url = $url . '&signature=' . $signature;
            }
            // redirector.googlevideo.com
            //$url = preg_replace('@(\/\/)[^\.]+(\.googlevideo\.com)@', '$1redirector$2', $url);
            $itag = $arr['itag'];
            $format = isset($this->itag_info[$itag]) ? $this->itag_info[$itag] : 'Unknown';
            $result[$itag] = array(
                'url' => $url,
                'format' => $format
            );
        }
        // do we want all links or just select few?
        if ($selector) {
            return $this->selectFirst($result, $selector);
        }
        return $result;
    }
}

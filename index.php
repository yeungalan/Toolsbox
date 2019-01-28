<html>
<head>
<!-- Tocas UI：CSS 與元件 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tocas-ui/2.3.3/tocas.css">
<!-- Tocas JS：模塊與 JavaScript 函式 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tocas-ui/2.3.3/tocas.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
</head>
<body>
<br>
<div class="ts container">
	<div class="ts segment">
		<div class="ts fluid slate">
			<h2 class="ts header">
				<i class="settings icon"></i>
				<div class="content">
					<span id="header">{FileName}</span>
					<div class="sub header" id="subheader">{Author}&nbsp;{signed}</div>
				</div>
			</h2>
		</div>
		<br>
		<div class="ts segment">
			<div class="ts grid" style="font-size: 1.14286rem;">
				<div class="eight wide column" style="text-align: right;">
					<span style="color:#b5b7b9">Description:&nbsp;</span><br>
					<span style="color:#b5b7b9">Received:&nbsp;</span>
				</div>
				<div class="eight wide column"  style="text-align: left;" id="first">
					{Received}<br>{Received}<br>
				</div>
			</div>
		</div>
		<div class="ts segment">
			<div class="ts grid" style="font-size: 1.14286rem;">
				<div class="eight wide column" style="text-align: right;">
					<span style="color:#b5b7b9">Settings:&nbsp;</span>
				</div>
				<div class="eight wide column"  style="text-align: left;" id="second">
				</div>
			</div>
		</div>
		<p>DETAILS</p>
		<div class="ts segment" id="details">
			<!-- repeat 
			<span style="font-weight: bold;font-size: 1.4rem">{Setting Name}</span>
			<div class="ts divider"></div>
			<div class="ts grid" style="font-size: 1.14286rem;">
				<div class="eight wide column" style="text-align: right;">
					<span style="color:#b5b7b9">{Description}:&nbsp;</span>
				</div>
				<div class="eight wide column"  style="text-align: left;">
					{Item}<br>
				</div>
			</div>
			repeat end -->
		</div>
	</div>
</div>
</body>
<script>
var filename = "<?php echo $_GET["filename"]; ?>";
var htmlstructure = '<br><span style="font-weight: bold;font-size: 1.4rem">{Setting Name}</span><div class="ts divider"></div><div class="ts grid" style="font-size: 1.14286rem;">{Description}</div>';

$.get( "opr.php?filename=" + filename, function( str ) {
	var data = JSON.parse(str);
	$("#header").text(data["PayloadDisplayName"]);
	$("#subheader").text(data["PayloadOrganization"] + " " + "Unsigned");
	$("#first").html(data["PayloadDescription"] + "<br>" + new Date());
	$.each( data["PayloadContent"], function( key, value ) {
		$("#second").append( value["PayloadDisplayName"] + '<br><span style="color:#b5b7b9">' + value["PayloadDescription"] + '</span><br>');
		
		var tmp = htmlstructure;
			tmp = tmp.replace("{Setting Name}",value["PayloadDisplayName"]);
		var tmpDescription = "";
		var tmpItem = "";
		$.each( value, function( SubKey, SubValue ) {
			if(!Array.isArray(SubValue) && !isObject(SubValue) && !SubKey.includes("Payload")){
				tmpDescription = tmpDescription + '<div class="eight wide column" style="text-align: right;"><span style="color:#b5b7b9" attr="subkey" properties="' + value["PayloadType"] + '" key="' + SubKey + '">' + SubKey + ':&nbsp;</span></div>' + '<div class="eight wide column" style="text-align: left;">' + SubValue + '</div>';
			}else if(!SubKey.includes("Payload") || (SubKey == "PayloadContent" && (Array.isArray(SubValue) || isObject(SubValue)))){
				tmpDescription = tmpDescription + '<div class="eight wide column" style="text-align: right;"><span style="color:#b5b7b9" attr="subkey" properties="' + value["PayloadType"] + '" key="' + SubKey + '">' + SubKey + ':&nbsp;</span></div>' + '<div class="eight wide column" style="text-align: left;">' + JSON.stringify(SubValue) + '</div>';
			}
		});
		tmp = tmp.replace("{Description}",tmpDescription);
		//tmp = tmp.replace("{Item}",tmpItem);
		$("#details").append(tmp);
	});
	
	$.each( $("span[attr='subkey']"), function( index, element ) {
		$.get("opr.php?filename=./translate/" + $(element).attr("properties") + ".plist" , function( str ) {
			var data = JSON.parse(str);
			$.each( data["pfm_subkeys"], function( key, value ) {
				console.log($(element).text());
				if(value["pfm_name"] == $(element).attr("key")){
					console.log(value["pfm_title"]);
					$(element).text(value["pfm_title"]);
				}
			});
		});
	});
});

function countLines(name) {
   var el = document.getElementById(name);
   var divHeight = el.offsetHeight
   var lineHeight = parseInt(el.style.lineHeight);
   var lines = divHeight / lineHeight;
   alert("Lines: " + lines);
}

function isObject (value) {
	return value.constructor === Object;
}
</script>
</html>
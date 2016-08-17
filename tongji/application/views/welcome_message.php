<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<meta name="robots" content="noindex, nofollow">
	<meta name="googlebot" content="noindex, nofollow">
	<script type="text/javascript" src="http://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
	<style type="text/css">
	</style>
	<title>微博抓取统计</title>
	<script type='text/javascript'>//<![CDATA[

		$(function () {
            Highcharts.setOptions({global:{useUTC: false}});
			$('#container').highcharts({
				xAxis: {
					type: 'datetime'
				},
				legend: {
					layout: 'vertical',
					align: 'right',
					verticalAlign: 'middle',
					borderWidth: 0
				},

				series: <?=$json?>
			});
		});
		//]]>

	</script>
</head>

<body>
<script src="https://code.highcharts.com/highcharts.js"></script>

<div id="container" style="height: 600px"></div>

</body>

</html>



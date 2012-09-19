<?php
	header("Content-type: image/svg+xml");

	$url = $_GET['url'];
?>
<?xml version="1.0" encoding="utf-8" standalone="yes"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg xmlns="http://www.w3.org/2000/svg" version="1.1" baseProfile="full"
	width="800" height="600" viewBox="100 0 800 600" xmlns:svg="http://www.w3.org/2000/svg">
	<style type="text/css"> text { font-size:40px; font-family:DejaVu Sans; font-style:italic; } </style>
	<circle cx="400" cy="300" r="250" stroke="#000000" stroke-width="2" fill="none" />
	<polyline fill="none" stroke-width="4" stroke="#FF0000" points="650,300 649.68,287.5 648.75,275.04 647.19,262.64 645.01,250.33 642.22,238.14 638.83,226.11 634.84,214.27 630.26,202.64 625.11,191.25 619.39,180.14 613.13,169.32 606.33,158.83 599.02,148.7 591.21,138.94 582.92,129.59 574.17,120.66 564.99,112.17 555.4,104.16 545.42,96.64 535.07,89.63 " />
	<polyline fill="none" stroke-width="3" stroke="#FF4400" points="541,120 535.076,89.632 565,90" />
	<polyline fill="none" stroke-width="4" stroke="#004400" points="450,300 449.93,297.5 449.75,295 449.43,292.52 449,290.06 448.44,287.62 447.76,285.22 446.96,282.85 446.05,280.52 445.02,278.25 443.87,276.02 442.62,273.86 441.26,271.76 439.8,269.74 438.24,267.78 436.58,265.91 434.83,264.13 432.99,262.43 431.08,260.83 429.08,259.32 427.01,257.92 " />
	<polyline fill="none" stroke-width="2" stroke="#000000" points="650,300 400,300 535.076,89.632" />

	<text x="450" y="270" fill="#004400">φ</text>
	<text x="650" y="180" fill="#FF0000">s = b = rφ</text>
</svg>
#!/bin/bash
#Requires PEAR PHP_CodeSniffer: pear install PHP_CodeSniffer
if [ $# -eq 0 ] ; then
	phpcs -v --standard=./Coding_Standard/ruleset.xml ../classes/
	#phpcs --standard=./coding_standard --ignore=adodb,bitmask,cache_lite,fpdf,fpdi,icalcreator,Image_Barcode,jpgraph,misc,pear,SabreAMF,smarty,upload,tcpdf ../../interface/html5
else
	phpcs -v --standard=./Coding_Standard/ruleset.xml $1
fi;


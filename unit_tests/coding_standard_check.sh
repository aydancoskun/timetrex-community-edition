#!/bin/bash

script_dir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

#Requires PEAR PHP_CodeSniffer: pear install PHP_CodeSniffer
if [ $# -eq 0 ] ; then
	phpcs -v --report-width=180 --standard=$script_dir/TTCodeStandard/ruleset.xml ../classes/
	#phpcs --standard=./coding_standard --ignore=adodb,bitmask,cache_lite,fpdf,fpdi,icalcreator,Image_Barcode,jpgraph,misc,pear,SabreAMF,smarty,upload,tcpdf ../../interface/html5
elif [ $1 == "diff" ] ; then
	phpcs --report=diff --report-width=180 --standard=$script_dir/TTCodeStandard/ruleset.xml "${@:2}"
else
	phpcs -v --report-width=180 --standard=$script_dir/TTCodeStandard/ruleset.xml $@
fi;


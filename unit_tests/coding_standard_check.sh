#!/bin/bash -x
script_dir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

#Requires PEAR PHP_CodeSniffer: pear install PHP_CodeSniffer
if [ $# -eq 0 ] ; then
	phpcs -v --report-width=180 --standard=$script_dir/TTCodeStandard/ruleset.xml ../classes/
	#phpcs --standard=./coding_standard --ignore=adodb,bitmask,cache_lite,fpdf,fpdi,icalcreator,Image_Barcode,jpgraph,misc,pear,SabreAMF,smarty,upload,tcpdf ../../interface/html5
elif [ $1 == "diff" ] ; then
	phpcs --report=diff --report-width=180 --standard=$script_dir/TTCodeStandard/ruleset.xml "${@:2}"
elif [ $1 == "modified" ] ; then
    #Must be run from the unit_tests dir.
    #files=();
    for file in `git diff --name-only` ; do
        #files+="../$file";
        files=$files" ../$file";
    done;

    phpcs -v --report-width=180 --standard=$script_dir/TTCodeStandard/ruleset.xml $files
else
	phpcs -v --report-width=180 --standard=$script_dir/TTCodeStandard/ruleset.xml $@
fi;


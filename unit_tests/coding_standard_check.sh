#!/bin/bash
script_dir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

#
# Examples:
# coding_standard_check.sh modified
# coding_standard_check.sh fix ../includes/global.inc.php
#

#Requires PEAR PHP_CodeSniffer: pear install PHP_CodeSniffer
if [ $# -eq 0 ] ; then
    phpcs -v --report-width=180 --standard=$script_dir/TTCodeStandard/ruleset.xml ../classes/
    #phpcs --standard=./coding_standard --ignore=adodb,bitmask,cache_lite,fpdf,fpdi,icalcreator,Image_Barcode,jpgraph,misc,pear,SabreAMF,smarty,upload,tcpdf ../../interface/html5
elif [ $1 == "diff" ] || [ $1 == "diff" ] || [ $1 == "fix" ] ; then
    temp_file=$(mktemp)
    phpcs --report=diff --report-diff=$temp_file --report-width=180 --standard=$script_dir/TTCodeStandard/ruleset.xml "${@:2}"	
    echo "------------------------------------------------";
    echo "DIFF";
    echo "------------------------------------------------";
    cat $temp_file
    echo "------------------------------------------------";
    read -p "Press [Enter] key to apply diff..."
    cwd=$(pwd)
    cd /
    patch -p0 -ui $temp_file
    rm -f $temp_file
    cd $cwd
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


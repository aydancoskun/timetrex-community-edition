#!/bin/bash

##$License$##
##
# File Contributed By: Open Source Consulting, S.A.   San Jose, Costa Rica.
# http://osc.co.cr
##

# This script generates the master .pot file containing english strings
# for translation.
#
# These strings are parsed from:
#   * smarty templates between {t}some text{/t} blocks
#   * PHP Files containing TTi18n::gettext("some text") function calls.

# We could also parse from static DB text, but so far see no need for
# doing so.

DEPTH=../..
LOCALE_ROOT=$DEPTH/interface/locale
POT_FILENAME=messages.pot

#---- Ensure pot file exists ----
touch $LOCALE_ROOT/$POT_FILENAME

#---- Extract strings from templates ----
#echo "Parsing templates..."
#TMP_FILE=/tmp/gen_master_pot_tmp.txt
#find $DEPTH/templates -name "*.tpl" | grep -v "\.git" | xargs -i php tsmarty2c.php \{\} | xgettext --from-code=UTF-8 --no-wrap --language=C --no-location -s --output-dir=$LOCALE_ROOT -o $POT_FILENAME -

#---- Extract strings from PHP Files ----
# Note that we want to extract from TTi18n::gettext() calls.
# xgettext ignores the "TTi18n::" bit and sees the gettext(). So it works.
#  When we removed the SMARTY parsing, also remove --join-existing option as this is now the first parse command that is run.
echo "Parsing PHP Files..."
find $DEPTH/ -name "*.php" | grep -v templates_c | grep -v "\.git" > /tmp/xgettext_php.files
xgettext --from-code=UTF-8 --no-wrap --keyword=getText --language=PHP -s -f /tmp/xgettext_php.files --output-dir=$LOCALE_ROOT -o $POT_FILENAME
rm -f /tmp/xgettext_php.files

echo "Parsing JS Files..."
#find $DEPTH/ -name "*.js" | grep -v "\.git" | xargs cat | sed 's/<br>/ /g' | sed 's/\n/ /g' | xgettext --from-code=UTF-8 --no-wrap --keyword=_ --join-existing --language=Javascript --no-location -s --output-dir=$LOCALE_ROOT -o $POT_FILENAME -
find $DEPTH/ -name "*.js" | egrep -v "\.git|/tools/compile|/interface/html5/framework" > /tmp/xgettext_js.files
xgettext --from-code=UTF-8 --no-wrap --keyword=_ --join-existing --language=Javascript -s -f /tmp/xgettext_js.files --output-dir=$LOCALE_ROOT -o $POT_FILENAME
rm -f /tmp/xgettext_js.files

#---- Extract strings from DB Tables with static strings ----
###  Not necessary for TimeTrex at this time ###


#---- Done ----
echo "Done!  POT File is in " $LOCALE_ROOT/$POT_FILENAME

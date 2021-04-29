<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4: */
//
// +----------------------------------------------------------------------+
// | PHP version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Piotr Klaban <makler@man.torun.pl>                          |
// |          Andrey Demenev <demenev@on-line.jar.ru>                     |
// +----------------------------------------------------------------------+
//
// $Id: lang.ru.php,v 1.2 2005/09/18 19:52:22 makler Exp $
//
// Numbers_Words class extension to spell numbers in Russian language.
//

/**
 * Class for translating numbers into Russian.
 *
 * @author Andrey Demenev
 * @package Numbers_Words
 */

/**
 * Include needed files
 */
require_once("Numbers/Words.php");

/**
 * Class for translating numbers into Russian.
 *
 * @author Andrey Demenev
 * @package Numbers_Words
 */
class Numbers_Words_ru extends Numbers_Words
{

    // {{{ properties

    /**
     * Locale name
     * @var string
     * @access public
     */
    var $locale      = 'ru';

    /**
     * Language name in English
     * @var string
     * @access public
     */
    var $lang        = 'Russian';

    /**
     * Native language name
     * @var string
     * @access public
     */
    var $lang_native = '�������';

    /**
     * The word for the minus sign
     * @var string
     * @access private
     */
    var $_minus = '�����'; // minus sign

    /**
     * The sufixes for exponents (singular)
     * Names partly based on:
     * http://home.earthlink.net/~mrob/pub/math/largenum.html
     * http://mathforum.org/dr.math/faq/faq.large.numbers.html
     * http://www.mazes.com/AmericanNumberingSystem.html
     * @var array
     * @access private
     */
    var $_exponent = array(
        0 => '',
        6 => '�������',
        9 => '��������',
       12 => '��������',
       15 => '�����������',
       18 => '�����������',
       21 => '�����������',
       24 => '����������',
       27 => '���������',
       30 => '���������',
       33 => '���������',
       36 => '�����������',
       39 => '������������',
       42 => '������������',
       45 => '����������������',
       48 => '�������������',
       51 => '�������������',
       54 => '���������������',
       57 => '�������������',
       60 => '��������������',
       63 => '������������',
       66 => '��������������',
       69 => '���������������',
       72 => '���������������',
       75 => '�������������������',
       78 => '����������������',
       81 => '����������������',
       84 => '������������������',
       87 => '����������������',
       90 => '�����������������',
       93 => '�������������',
       96 => '���������������',
       99 => '����������������',
       102 => '����������������',
       105 => '�������������������',
       108 => '�����������������',
       111 => '�����������������',
       114 => '�������������������',
       117 => '�����������������',
       120 => '������������������',
       123 => '����������������',
       126 => '������������������',
       129 => '�������������������',
       132 => '�������������������',
       135 => '����������������������',
       138 => '��������������������',
       141 => '��������������������',
       144 => '����������������������',
       147 => '��������������������',
       150 => '���������������������',
       153 => '�����������������',
       156 => '������������������',
       159 => '�������������������',
       162 => '�������������������',
       165 => '����������������������',
       168 => '��������������������',
       171 => '��������������������',
       174 => '����������������������',
       177 => '��������������������',
       180 => '���������������������',
       183 => '���������������',
       186 => '�����������������',
       189 => '������������������',
       192 => '������������������',
       195 => '���������������������',
       198 => '�������������������',
       201 => '�������������������',
       204 => '���������������������',
       207 => '�������������������',
       210 => '��������������������',
       213 => '���������������',
       216 => '�����������������',
       219 => '������������������',
       222 => '������������������',
       225 => '���������������������',
       228 => '�������������������',
       231 => '�������������������',
       234 => '���������������������',
       237 => '�������������������',
       240 => '��������������������',
       243 => '��������������',
       246 => '����������������',
       249 => '�����������������',
       252 => '�����������������',
       255 => '��������������������',
       258 => '������������������',
       261 => '������������������',
       264 => '������������������',
       267 => '������������������',
       270 => '�������������������',
       273 => '��������������',
       276 => '����������������',
       279 => '�����������������',
       282 => '�����������������',
       285 => '��������������������',
       288 => '������������������',
       291 => '������������������',
       294 => '��������������������',
       297 => '������������������',
       300 => '�������������������',
       303 => '����������'
        );

    /**
     * The array containing the teens' :) names
     * @var array
     * @access private
     */
    var $_teens = array(
        11=>'�����������',
        12=>'����������',
        13=>'����������',
        14=>'������������',
        15=>'����������',
        16=>'�����������',
        17=>'����������',
        18=>'������������',
        19=>'������������'
        );

    /**
     * The array containing the tens' names
     * @var array
     * @access private
     */
    var $_tens = array(
        2=>'��������',
        3=>'��������',
        4=>'�����',
        5=>'���������',
        6=>'����������',
        7=>'���������',
        8=>'�����������',
        9=>'���������'
        );

    /**
     * The array containing the hundreds' names
     * @var array
     * @access private
     */
    var $_hundreds = array(
        1=>'���',
        2=>'������',
        3=>'������',
        4=>'���������',
        5=>'�������',
        6=>'��������',
        7=>'�������',
        8=>'���������',
        9=>'���������'
        );

    /**
     * The array containing the digits
     * for neutral, male and female
     * @var array
     * @access private
     */
    var $_digits = array(
        array('����', '����', '���', '���', '������','����', '�����', '����', '������', '������'),
        array('����', '����', '���', '���', '������','����', '�����', '����', '������', '������'),
        array('����', '����', '���', '���', '������','����', '�����', '����', '������', '������')
    );

    /**
     * The word separator
     * @var string
     * @access private
     */
    var $_sep = ' ';

    /**
     * The currency names (based on the below links,
     * informations from central bank websites and on encyclopedias)
     *
     * @var array
     * @link http://www.jhall.demon.co.uk/currency/by_abbrev.html World currencies
     * @link http://www.rusimpex.ru/Content/Reference/Refinfo/valuta.htm Foreign currencies names
     * @link http://www.cofe.ru/Finance/money.asp Currencies names
     * @access private
     */
    var $_currency_names = array(
      'ALL' => array(
                array(1,'���','����','�����'),
                array(2,'��������','��������','��������')
               ),
      'AUD' => array(
                array(1,'������������� ������','������������� �������','������������� ��������'),
                array(1,'����','�����','������')
               ),
      'BGN' => array(
                array(1,'���','����','�����'),
                array(2,'��������','��������','��������')
               ),
      'BRL' => array(
                array(1,'����������� ����','����������� �����','����������� ������'),
                array(1,'�������','�������','�������')
               ),
      'BYR' => array(
                array(1,'����������� �����','����������� �����','����������� ������'),
                array(2,'�������','�������','������')
               ),
      'CAD' => array(
                array(1,'��������� ������','��������� �������','��������� ��������'),
                array(1,'����','�����','������')
               ),
      'CHF' => array(
                array(1,'����������� �����','����������� ������','����������� �������'),
                array(1,'������','�������','��������')
               ),
      'CYP' => array(
                array(1,'�������� ����','�������� �����','�������� ������'),
                array(1,'����','�����','������')
               ),
      'CZK' => array(
                array(2,'������� �����','������� �����','������� ����'),
                array(1,'������','�������','��������')
               ),
      'DKK' => array(
                array(2,'������� �����','������� �����','������� ����'),
                array(1,'���','���','���')
               ),
      'EEK' => array(
                array(2,'��������� �����','��������� �����','��������� ����'),
                array(1,'�����','�����','�����')
               ),
      'EUR' => array(
                array(1,'����','����','����'),
                array(1,'��������','���������','����������')
               ),
      'CYP' => array(
                array(1,'���� ����������','����� ����������','������ ����������'),
                array(1,'����','�����','������')
               ),
      'CAD' => array(
                array(1,'����������� ������','����������� �������','����������� ��������'),
                array(1,'����','�����','������')
               ),
      'HRK' => array(
                array(2,'���������� ����','���������� ����','���������� ���'),
                array(2,'����','����','���')
               ),
      'HUF' => array(
                array(1,'���������� ������','���������� �������','���������� ��������'),
                array(1,'������','�������','��������')
               ),
      'ISK' => array(
                array(2,'���������� �����','���������� �����','���������� ����'),
                array(1,'���','���','���')
               ),
      'JPY' => array(
                array(2,'����','����','���'),
                array(2,'����','����','���')
               ),
      'LTL' => array(
                array(1,'���','����','�����'),
                array(1,'����','�����','������')
               ),
      'LVL' => array(
                array(1,'���','����','�����'),
                array(1,'������','�������','��������')
               ),
      'MKD' => array(
                array(1,'����������� �����','����������� ������','����������� �������'),
                array(1,'����','����','����')
               ),
      'MTL' => array(
                array(2,'����������� ����','����������� ����','����������� ���'),
                array(1,'������','�������','��������')
               ),
      'NOK' => array(
                array(2,'���������� �����','���������� �����','���������� ����'),
                array(0,'���','���','���')
               ),
      'PLN' => array(
                array(1,'������','������','������'),
                array(1,'����','�����','������')
               ),
      'ROL' => array(
                array(1,'��������� ���','��������� ���','��������� ���'),
                array(1,'����','����','����')
               ),
       // both RUR and RUR are used, I use RUB for shorter form
      'RUB' => array(
                array(1,'�����','�����','������'),
                array(2,'�������','�������','������')
               ),
      'RUR' => array(
                array(1,'���������� �����','���������� �����','���������� ������'),
                array(2,'�������','�������','������')
               ),
      'SEK' => array(
                array(2,'�������� �����','�������� �����','�������� ����'),
                array(1,'���','���','���')
               ),
      'SIT' => array(
                array(1,'���������� �����','���������� ������','���������� �������'),
                array(2,'�������','�������','������')
               ),
      'SKK' => array(
                array(2,'��������� �����','��������� �����','��������� ����'),
                array(0,'','','')
               ),
      'TRL' => array(
                array(2,'�������� ����','�������� ����','�������� ���'),
                array(1,'������','�������','��������')
               ),
      'UAH' => array(
                array(2,'������','������','������'),
                array(1,'����','�����','������')
               ),
      'USD' => array(
                array(1,'������ ���','������� ���','�������� ���'),
                array(1,'����','�����','������')
               ),
      'YUM' => array(
                array(1,'����������� �����','����������� ������','����������� �������'),
                array(1,'����','����','����')
               ),
      'ZAR' => array(
                array(1,'����','�����','������'),
                array(1,'����','�����','������')
               )
    );

    /**
     * The default currency name
     * @var string
     * @access public
     */
    var $def_currency = 'RUB'; // Russian rouble

    // }}}
    // {{{ toWords()

    /**
     * Converts a number to its word representation
     * in Russian language
     *
     * @param  integer $num   An integer between -infinity and infinity inclusive :)
     *                        that need to be converted to words
     * @param  integer $gender Gender of string, 0=neutral, 1=male, 2=female.
     *                         Optional, defaults to 1.
     *
     * @return string  The corresponding word representation
     *
     * @access private
     * @author Andrey Demenev <demenev@on-line.jar.ru>
     */
    function toWords($num, $gender = 1)
    {
        return $this->_toWordsWithCase($num, $dummy, $gender);
    }

    /**
     * Converts a number to its word representation
     * in Russian language and determines the case of string.
     *
     * @param  integer $num   An integer between -infinity and infinity inclusive :)
     *                        that need to be converted to words
     * @param  integer $case A variable passed by reference which is set to case
     *                       of the word associated with the number
     * @param  integer $gender Gender of string, 0=neutral, 1=male, 2=female.
     *                         Optional, defaults to 1.
     *
     * @return string  The corresponding word representation
     *
     * @access private
     * @author Andrey Demenev <demenev@on-line.jar.ru>
     */
    function _toWordsWithCase($num, &$case, $gender = 1)
    {
      $ret = '';
      $case = 3;

      $num = trim($num);

      $sign = "";
      if (substr($num, 0, 1) == '-') {
        $sign = $this->_minus . $this->_sep;
        $num = substr($num, 1);
      }

      while (strlen($num) % 3) $num = '0' . $num;
      if ($num == 0 || $num == '') {
        $ret .= $this->_digits[$gender][0];
      }

      else {
        $power = 0;
        while ($power < strlen($num)) {
            if (!$power) {
                $groupgender = $gender;
            } elseif ($power == 3) {
                $groupgender = 2;
            } else {
                $groupgender = 1;
            }
            $group = $this->_groupToWords(substr($num,-$power-3,3),$groupgender,$_case);
            if (!$power) {
                $case = $_case;
            }
            if ($power == 3) {
                if ($_case == 1) {
                    $group .= $this->_sep . '������';
                } elseif ($_case == 2) {
                    $group .= $this->_sep . '������';
                } else {
                    $group .= $this->_sep . '�����';
                }
            } elseif ($group && $power>3 && isset($this->_exponent[$power])) {
                $group .= $this->_sep . $this->_exponent[$power];
                if ($_case == 2) {
                    $group .= '�';
                } elseif ($_case == 3) {
                    $group .= '��';
                }
            }
            if ($group) {
                $ret = $group . $this->_sep . $ret;
            }
            $power+=3;
        }
      }

      return $sign . $ret;
    }

    // }}}
    // {{{ _groupToWords()

    /**
     * Converts a group of 3 digits to its word representation
     * in Russian language.
     *
     * @param  integer $num   An integer between -infinity and infinity inclusive :)
     *                        that need to be converted to words
     * @param  integer $gender Gender of string, 0=neutral, 1=male, 2=female.
     * @param  integer $case A variable passed by reference which is set to case
     *                       of the word associated with the number
     *
     * @return string  The corresponding word representation
     *
     * @access private
     * @author Andrey Demenev <demenev@on-line.jar.ru>
     */
    function _groupToWords($num, $gender, &$case)
    {
      $ret = '';
      $case = 3;
      if ((int)$num == 0) {
          $ret = '';
      } elseif ($num < 10) {
          $ret = $this->_digits[$gender][(int)$num];
          if ($num == 1) $case = 1;
          elseif ($num < 5) $case = 2;
          else $case = 3;
      } else {
          $num = str_pad($num,3,'0',STR_PAD_LEFT);
          $hundreds = (int)$num[0];
          if ($hundreds) {
              $ret = $this->_hundreds[$hundreds];
              if (substr($num,1) != '00') {
                  $ret .= $this->_sep;
              }
              $case = 3;
          }
          $tens=(int)$num[1];
          $ones=(int)$num[2];
          if ($tens || $ones) {
              if ($tens == 1 && $ones == 0) $ret .= '������';
              elseif ($tens < 2) $ret .= $this->_teens[$ones+10];
              else {
                  $ret .= $this->_tens[(int)$tens];
                  if ($ones > 0) {
                      $ret .= $this->_sep
                          .$this->_digits[$gender][$ones];
                      if ($ones == 1) {
                          $case = 1;
                      } elseif ($ones < 5) {
                          $case = 2;
                      } else {
                          $case = 3;
                      }
                  }
              }
          }
      }
      return $ret;
    }
    // }}}
    // {{{ toCurrencyWords()

    /**
     * Converts a currency value to its word representation
     * (with monetary units) in Russian language
     *
     * @param  integer $int_curr An international currency symbol
     *                 as defined by the ISO 4217 standard (three characters)
     * @param  integer $decimal A money total amount without fraction part (e.g. amount of dollars)
     * @param  integer $fraction Fractional part of the money amount (e.g. amount of cents)
     *                 Optional. Defaults to false.
     * @param  integer $convert_fraction Convert fraction to words (left as numeric if set to false).
     *                 Optional. Defaults to true.
     *
     * @return string  The corresponding word representation for the currency
     *
     * @access public
     * @author Andrey Demenev <demenev@on-line.jar.ru>
     */
    function toCurrencyWords($int_curr, $decimal, $fraction = false, $convert_fraction = true)
    {
        $int_curr = strtoupper($int_curr);
        if (!isset($this->_currency_names[$int_curr])) {
            $int_curr = $this->def_currency;
        }
        $curr_names = $this->_currency_names[$int_curr];
        $ret = trim($this->_toWordsWithCase($decimal, $case, $curr_names[0][0]));
        $ret .= $this->_sep . $curr_names[0][$case];

        if ($fraction !== false) {
            if ($convert_fraction) {
                $ret .= $this->_sep . trim($this->_toWordsWithCase($fraction, $case, $curr_names[1][0]));
            } else {
                $ret .= $this->_sep . $fraction;
            }
            $ret .= $this->_sep . $curr_names[1][$case];
        }
        return $ret;
    }
    // }}}

}

?>

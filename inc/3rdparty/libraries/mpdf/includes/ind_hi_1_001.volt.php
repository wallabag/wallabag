<?php
	$volt = array (
  0 => 
  array (
    'match' => '0915 094D 0937',
    'replace' => 'E028',
  ),
  1 => 
  array (
    'match' => '091C 094D 091E',
    'replace' => 'E029',
  ),
  2 => 
  array (
    'match' => '0926 094D 092F',
    'replace' => 'E128',
  ),
  3 => 
  array (
    'match' => '094D (200D)',
    'replace' => 'E00E \\1',
  ),
  4 => 
  array (
    'match' => '094D ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029))',
    'replace' => 'E00E \\1',
  ),
  5 => 
  array (
    'match' => '094D (25CC)',
    'replace' => 'E00E \\1',
  ),
  6 => 
  array (
    'match' => '094D 200C',
    'replace' => 'E00C',
  ),
  7 => 
  array (
    'match' => '((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029|0958|0959|095A|E02A|E02B|E02C|E02D|095B|E02E|E02F|E030|E031|095C|095D|E032|E033|E034|E035|E036|0929|E037|095E|E038|E039|E03A|095F|0931|E03B|0934|E03C|E03D|E03E|E03F|E040|E041|E042|E08B|E08C|E08D|E08E|E08F|E090|E091|E092|E093|E094|E095|E096|E097|E098|E099|E09A|E09B|E09C|E09D|E09E|E09F|E0A0|E0A1|E0A2|E0A3|E0A4|E0A5|E0A6|E0A7|E0A8|E0A9|E0AA|E0AB|E0AC|E0AD|E0AE|E0AF|E0B0|E0B1|E0B2|E0B3|E0B4|E0B5|E0B6|E0B7|E0B8|E0B9|E0BA|E0BB|E0BC|E0BD|E0BE|E0BF|E0C0|E0C1|E0C2|E0C3|E0C4|E0C5|E0C6|E0C7|E0C8|E0C9|E0CA|E0CB|E0CC|E0CD|E0CE|E0CF|E0D0|E0D1|E0D2|E11E|E11F|E120|E121|E122|E123|E124|E125|E126|E127|E128|E129|E12A|E12B|E12C|E12D|E12E|E12F|E130|E131|E132|E133)) E00E 0930',
    'replace' => '\\1 E013',
  ),
  8 => 
  array (
    'match' => '(093C) E00E 0930',
    'replace' => '\\1 E013',
  ),
  9 => 
  array (
    'match' => '(200D) E00E 0930',
    'replace' => '\\1 E013',
  ),
  10 => 
  array (
    'match' => '(25CC) E00E 0930',
    'replace' => '\\1 E013',
  ),
  11 => 
  array (
    'match' => '(0020) E00E 0930',
    'replace' => '\\1 E013',
  ),
  12 => 
  array (
    'match' => '((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029|0958|0959|095A|E02A|E02B|E02C|E02D|095B|E02E|E02F|E030|E031|095C|095D|E032|E033|E034|E035|E036|0929|E037|095E|E038|E039|E03A|095F|0931|E03B|0934|E03C|E03D|E03E|E03F|E040|E041|E042|E08B|E08C|E08D|E08E|E08F|E090|E091|E092|E093|E094|E095|E096|E097|E098|E099|E09A|E09B|E09C|E09D|E09E|E09F|E0A0|E0A1|E0A2|E0A3|E0A4|E0A5|E0A6|E0A7|E0A8|E0A9|E0AA|E0AB|E0AC|E0AD|E0AE|E0AF|E0B0|E0B1|E0B2|E0B3|E0B4|E0B5|E0B6|E0B7|E0B8|E0B9|E0BA|E0BB|E0BC|E0BD|E0BE|E0BF|E0C0|E0C1|E0C2|E0C3|E0C4|E0C5|E0C6|E0C7|E0C8|E0C9|E0CA|E0CB|E0CC|E0CD|E0CE|E0CF|E0D0|E0D1|E0D2|E11E|E11F|E120|E121|E122|E123|E124|E125|E126|E127|E128|E129|E12A|E12B|E12C|E12D|E12E|E12F|E130|E131|E132|E133)) 0930 094D',
    'replace' => '\\1 E015',
  ),
  13 => 
  array (
    'match' => '((E013|E015|0941|0942|0943|0962|093E|0940|0949|094A|094B|094C|0945|0946|0947|0948|0901)) 0930 094D',
    'replace' => '\\1 E015',
  ),
  14 => 
  array (
    'match' => '(093C) 0930 094D',
    'replace' => '\\1 E015',
  ),
  15 => 
  array (
    'match' => '(25CC) 0930 094D',
    'replace' => '\\1 E015',
  ),
  16 => 
  array (
    'match' => '(E128) 0930 094D',
    'replace' => '\\1 E015',
  ),
  17 => 
  array (
    'match' => '0915 093C',
    'replace' => '0958',
  ),
  18 => 
  array (
    'match' => '0916 093C',
    'replace' => '0959',
  ),
  19 => 
  array (
    'match' => '0917 093C',
    'replace' => '095A',
  ),
  20 => 
  array (
    'match' => '0918 093C',
    'replace' => 'E02A',
  ),
  21 => 
  array (
    'match' => '0919 093C',
    'replace' => 'E02B',
  ),
  22 => 
  array (
    'match' => '091A 093C',
    'replace' => 'E02C',
  ),
  23 => 
  array (
    'match' => '091B 093C',
    'replace' => 'E02D',
  ),
  24 => 
  array (
    'match' => '091C 093C',
    'replace' => '095B',
  ),
  25 => 
  array (
    'match' => '091D 093C',
    'replace' => 'E02E',
  ),
  26 => 
  array (
    'match' => '091E 093C',
    'replace' => 'E02F',
  ),
  27 => 
  array (
    'match' => '091F 093C',
    'replace' => 'E030',
  ),
  28 => 
  array (
    'match' => '0920 093C',
    'replace' => 'E031',
  ),
  29 => 
  array (
    'match' => '0921 093C',
    'replace' => '095C',
  ),
  30 => 
  array (
    'match' => '0922 093C',
    'replace' => '095D',
  ),
  31 => 
  array (
    'match' => '0923 093C',
    'replace' => 'E032',
  ),
  32 => 
  array (
    'match' => '0924 093C',
    'replace' => 'E033',
  ),
  33 => 
  array (
    'match' => '0925 093C',
    'replace' => 'E034',
  ),
  34 => 
  array (
    'match' => '0926 093C',
    'replace' => 'E035',
  ),
  35 => 
  array (
    'match' => '0927 093C',
    'replace' => 'E036',
  ),
  36 => 
  array (
    'match' => '0928 093C',
    'replace' => '0929',
  ),
  37 => 
  array (
    'match' => '092A 093C',
    'replace' => 'E037',
  ),
  38 => 
  array (
    'match' => '092B 093C',
    'replace' => '095E',
  ),
  39 => 
  array (
    'match' => '092C 093C',
    'replace' => 'E038',
  ),
  40 => 
  array (
    'match' => '092D 093C',
    'replace' => 'E039',
  ),
  41 => 
  array (
    'match' => '092E 093C',
    'replace' => 'E03A',
  ),
  42 => 
  array (
    'match' => '092F 093C',
    'replace' => '095F',
  ),
  43 => 
  array (
    'match' => '0930 093C',
    'replace' => '0931',
  ),
  44 => 
  array (
    'match' => '0932 093C',
    'replace' => 'E03B',
  ),
  45 => 
  array (
    'match' => '0933 093C',
    'replace' => '0934',
  ),
  46 => 
  array (
    'match' => '0935 093C',
    'replace' => 'E03C',
  ),
  47 => 
  array (
    'match' => '0936 093C',
    'replace' => 'E03D',
  ),
  48 => 
  array (
    'match' => '0937 093C',
    'replace' => 'E03E',
  ),
  49 => 
  array (
    'match' => '0938 093C',
    'replace' => 'E03F',
  ),
  50 => 
  array (
    'match' => '0939 093C',
    'replace' => 'E040',
  ),
  51 => 
  array (
    'match' => 'E028 093C',
    'replace' => 'E041',
  ),
  52 => 
  array (
    'match' => 'E029 093C',
    'replace' => 'E042',
  ),
  53 => 
  array (
    'match' => '0915 E013',
    'replace' => 'E08B',
  ),
  54 => 
  array (
    'match' => '0916 E013',
    'replace' => 'E08C',
  ),
  55 => 
  array (
    'match' => '0917 E013',
    'replace' => 'E08D',
  ),
  56 => 
  array (
    'match' => '0918 E013',
    'replace' => 'E08E',
  ),
  57 => 
  array (
    'match' => '0919 E013',
    'replace' => 'E08F',
  ),
  58 => 
  array (
    'match' => '091A E013',
    'replace' => 'E090',
  ),
  59 => 
  array (
    'match' => '091B E013',
    'replace' => 'E091',
  ),
  60 => 
  array (
    'match' => '091C E013',
    'replace' => 'E092',
  ),
  61 => 
  array (
    'match' => '091D E013',
    'replace' => 'E093',
  ),
  62 => 
  array (
    'match' => '091E E013',
    'replace' => 'E094',
  ),
  63 => 
  array (
    'match' => '091F E013',
    'replace' => 'E095',
  ),
  64 => 
  array (
    'match' => '0920 E013',
    'replace' => 'E096',
  ),
  65 => 
  array (
    'match' => '0921 E013',
    'replace' => 'E097',
  ),
  66 => 
  array (
    'match' => '0922 E013',
    'replace' => 'E098',
  ),
  67 => 
  array (
    'match' => '0923 E013',
    'replace' => 'E099',
  ),
  68 => 
  array (
    'match' => '0924 E013',
    'replace' => 'E09A',
  ),
  69 => 
  array (
    'match' => '0925 E013',
    'replace' => 'E09B',
  ),
  70 => 
  array (
    'match' => '0926 E013',
    'replace' => 'E09C',
  ),
  71 => 
  array (
    'match' => '0927 E013',
    'replace' => 'E09D',
  ),
  72 => 
  array (
    'match' => '0928 E013',
    'replace' => 'E09E',
  ),
  73 => 
  array (
    'match' => '092A E013',
    'replace' => 'E09F',
  ),
  74 => 
  array (
    'match' => '092B E013',
    'replace' => 'E0A0',
  ),
  75 => 
  array (
    'match' => '092C E013',
    'replace' => 'E0A1',
  ),
  76 => 
  array (
    'match' => '092D E013',
    'replace' => 'E0A2',
  ),
  77 => 
  array (
    'match' => '092E E013',
    'replace' => 'E0A3',
  ),
  78 => 
  array (
    'match' => '092F E013',
    'replace' => 'E0A4',
  ),
  79 => 
  array (
    'match' => '0930 E013',
    'replace' => 'E0A5',
  ),
  80 => 
  array (
    'match' => '0932 E013',
    'replace' => 'E0A6',
  ),
  81 => 
  array (
    'match' => '0933 E013',
    'replace' => 'E0A7',
  ),
  82 => 
  array (
    'match' => '0935 E013',
    'replace' => 'E0A8',
  ),
  83 => 
  array (
    'match' => '0936 E013',
    'replace' => 'E0A9',
  ),
  84 => 
  array (
    'match' => '0937 E013',
    'replace' => 'E0AA',
  ),
  85 => 
  array (
    'match' => '0938 E013',
    'replace' => 'E0AB',
  ),
  86 => 
  array (
    'match' => '0939 E013',
    'replace' => 'E0AC',
  ),
  87 => 
  array (
    'match' => 'E028 E013',
    'replace' => 'E0AD',
  ),
  88 => 
  array (
    'match' => 'E029 E013',
    'replace' => 'E0AE',
  ),
  89 => 
  array (
    'match' => '0958 E013',
    'replace' => 'E0AF',
  ),
  90 => 
  array (
    'match' => '0959 E013',
    'replace' => 'E0B0',
  ),
  91 => 
  array (
    'match' => '095A E013',
    'replace' => 'E0B1',
  ),
  92 => 
  array (
    'match' => 'E02A E013',
    'replace' => 'E0B2',
  ),
  93 => 
  array (
    'match' => 'E02B E013',
    'replace' => 'E0B3',
  ),
  94 => 
  array (
    'match' => 'E02C E013',
    'replace' => 'E0B4',
  ),
  95 => 
  array (
    'match' => 'E02D E013',
    'replace' => 'E0B5',
  ),
  96 => 
  array (
    'match' => '095B E013',
    'replace' => 'E0B6',
  ),
  97 => 
  array (
    'match' => 'E02E E013',
    'replace' => 'E0B7',
  ),
  98 => 
  array (
    'match' => 'E02F E013',
    'replace' => 'E0B8',
  ),
  99 => 
  array (
    'match' => 'E030 E013',
    'replace' => 'E0B9',
  ),
  100 => 
  array (
    'match' => 'E031 E013',
    'replace' => 'E0BA',
  ),
  101 => 
  array (
    'match' => '095C E013',
    'replace' => 'E0BB',
  ),
  102 => 
  array (
    'match' => '095D E013',
    'replace' => 'E0BC',
  ),
  103 => 
  array (
    'match' => 'E032 E013',
    'replace' => 'E0BD',
  ),
  104 => 
  array (
    'match' => 'E033 E013',
    'replace' => 'E0BE',
  ),
  105 => 
  array (
    'match' => 'E034 E013',
    'replace' => 'E0BF',
  ),
  106 => 
  array (
    'match' => 'E035 E013',
    'replace' => 'E0C0',
  ),
  107 => 
  array (
    'match' => 'E036 E013',
    'replace' => 'E0C1',
  ),
  108 => 
  array (
    'match' => '0929 E013',
    'replace' => 'E0C2',
  ),
  109 => 
  array (
    'match' => 'E037 E013',
    'replace' => 'E0C3',
  ),
  110 => 
  array (
    'match' => '095E E013',
    'replace' => 'E0C4',
  ),
  111 => 
  array (
    'match' => 'E038 E013',
    'replace' => 'E0C5',
  ),
  112 => 
  array (
    'match' => 'E039 E013',
    'replace' => 'E0C6',
  ),
  113 => 
  array (
    'match' => 'E03A E013',
    'replace' => 'E0C7',
  ),
  114 => 
  array (
    'match' => '095F E013',
    'replace' => 'E0C8',
  ),
  115 => 
  array (
    'match' => '0931 E013',
    'replace' => 'E0C9',
  ),
  116 => 
  array (
    'match' => 'E03B E013',
    'replace' => 'E0CA',
  ),
  117 => 
  array (
    'match' => '0934 E013',
    'replace' => 'E0CB',
  ),
  118 => 
  array (
    'match' => 'E03C E013',
    'replace' => 'E0CC',
  ),
  119 => 
  array (
    'match' => 'E03D E013',
    'replace' => 'E0CD',
  ),
  120 => 
  array (
    'match' => 'E03E E013',
    'replace' => 'E0CE',
  ),
  121 => 
  array (
    'match' => 'E03F E013',
    'replace' => 'E0CF',
  ),
  122 => 
  array (
    'match' => 'E040 E013',
    'replace' => 'E0D0',
  ),
  123 => 
  array (
    'match' => 'E041 E013',
    'replace' => 'E0D1',
  ),
  124 => 
  array (
    'match' => 'E042 E013',
    'replace' => 'E0D2',
  ),
  125 => 
  array (
    'match' => 'E028 E013',
    'replace' => 'E0AD',
  ),
  126 => 
  array (
    'match' => 'E029 E013',
    'replace' => 'E0AE',
  ),
  127 => 
  array (
    'match' => '0915 E00E',
    'replace' => 'E043',
  ),
  128 => 
  array (
    'match' => '0916 E00E',
    'replace' => 'E044',
  ),
  129 => 
  array (
    'match' => '0917 E00E',
    'replace' => 'E045',
  ),
  130 => 
  array (
    'match' => '0918 E00E',
    'replace' => 'E046',
  ),
  131 => 
  array (
    'match' => '0919 E00E',
    'replace' => 'E047',
  ),
  132 => 
  array (
    'match' => '091A E00E',
    'replace' => 'E048',
  ),
  133 => 
  array (
    'match' => '091B E00E',
    'replace' => 'E049',
  ),
  134 => 
  array (
    'match' => '091C E00E',
    'replace' => 'E04A',
  ),
  135 => 
  array (
    'match' => '091D E00E',
    'replace' => 'E04B',
  ),
  136 => 
  array (
    'match' => '091E E00E',
    'replace' => 'E04C',
  ),
  137 => 
  array (
    'match' => '091F E00E',
    'replace' => 'E04D',
  ),
  138 => 
  array (
    'match' => '0920 E00E',
    'replace' => 'E04E',
  ),
  139 => 
  array (
    'match' => '0921 E00E',
    'replace' => 'E04F',
  ),
  140 => 
  array (
    'match' => '0922 E00E',
    'replace' => 'E050',
  ),
  141 => 
  array (
    'match' => '0923 E00E',
    'replace' => 'E051',
  ),
  142 => 
  array (
    'match' => '0924 E00E',
    'replace' => 'E052',
  ),
  143 => 
  array (
    'match' => '0925 E00E',
    'replace' => 'E053',
  ),
  144 => 
  array (
    'match' => '0926 E00E',
    'replace' => 'E054',
  ),
  145 => 
  array (
    'match' => '0927 E00E',
    'replace' => 'E055',
  ),
  146 => 
  array (
    'match' => '0928 E00E',
    'replace' => 'E056',
  ),
  147 => 
  array (
    'match' => '092A E00E',
    'replace' => 'E057',
  ),
  148 => 
  array (
    'match' => '092B E00E',
    'replace' => 'E058',
  ),
  149 => 
  array (
    'match' => '092C E00E',
    'replace' => 'E059',
  ),
  150 => 
  array (
    'match' => '092D E00E',
    'replace' => 'E05A',
  ),
  151 => 
  array (
    'match' => '092E E00E',
    'replace' => 'E05B',
  ),
  152 => 
  array (
    'match' => '092F E00E',
    'replace' => 'E05C',
  ),
  153 => 
  array (
    'match' => '0930 E00E',
    'replace' => 'E05D',
  ),
  154 => 
  array (
    'match' => '0932 E00E',
    'replace' => 'E05E',
  ),
  155 => 
  array (
    'match' => '0933 E00E',
    'replace' => 'E05F',
  ),
  156 => 
  array (
    'match' => '0935 E00E',
    'replace' => 'E060',
  ),
  157 => 
  array (
    'match' => '0936 E00E',
    'replace' => 'E061',
  ),
  158 => 
  array (
    'match' => '0937 E00E',
    'replace' => 'E062',
  ),
  159 => 
  array (
    'match' => '0938 E00E',
    'replace' => 'E063',
  ),
  160 => 
  array (
    'match' => '0939 E00E',
    'replace' => 'E064',
  ),
  161 => 
  array (
    'match' => 'E028 E00E',
    'replace' => 'E065',
  ),
  162 => 
  array (
    'match' => 'E029 E00E',
    'replace' => 'E066',
  ),
  163 => 
  array (
    'match' => 'E08B E00E',
    'replace' => 'E0D3',
  ),
  164 => 
  array (
    'match' => 'E08C E00E',
    'replace' => 'E0D4',
  ),
  165 => 
  array (
    'match' => 'E08D E00E',
    'replace' => 'E0D5',
  ),
  166 => 
  array (
    'match' => 'E08E E00E',
    'replace' => 'E0D6',
  ),
  167 => 
  array (
    'match' => 'E08F E00E',
    'replace' => 'E0D7',
  ),
  168 => 
  array (
    'match' => 'E090 E00E',
    'replace' => 'E0D8',
  ),
  169 => 
  array (
    'match' => 'E091 E00E',
    'replace' => 'E0D9',
  ),
  170 => 
  array (
    'match' => 'E092 E00E',
    'replace' => 'E0DA',
  ),
  171 => 
  array (
    'match' => 'E093 E00E',
    'replace' => 'E0DB',
  ),
  172 => 
  array (
    'match' => 'E094 E00E',
    'replace' => 'E0DC',
  ),
  173 => 
  array (
    'match' => 'E095 E00E',
    'replace' => 'E0DD',
  ),
  174 => 
  array (
    'match' => 'E096 E00E',
    'replace' => 'E0DE',
  ),
  175 => 
  array (
    'match' => 'E097 E00E',
    'replace' => 'E0DF',
  ),
  176 => 
  array (
    'match' => 'E098 E00E',
    'replace' => 'E0E0',
  ),
  177 => 
  array (
    'match' => 'E099 E00E',
    'replace' => 'E0E1',
  ),
  178 => 
  array (
    'match' => 'E09A E00E',
    'replace' => 'E0E2',
  ),
  179 => 
  array (
    'match' => 'E09B E00E',
    'replace' => 'E0E3',
  ),
  180 => 
  array (
    'match' => 'E09C E00E',
    'replace' => 'E0E4',
  ),
  181 => 
  array (
    'match' => 'E09D E00E',
    'replace' => 'E0E5',
  ),
  182 => 
  array (
    'match' => 'E09E E00E',
    'replace' => 'E0E6',
  ),
  183 => 
  array (
    'match' => 'E09F E00E',
    'replace' => 'E0E7',
  ),
  184 => 
  array (
    'match' => 'E0A0 E00E',
    'replace' => 'E0E8',
  ),
  185 => 
  array (
    'match' => 'E0A1 E00E',
    'replace' => 'E0E9',
  ),
  186 => 
  array (
    'match' => 'E0A2 E00E',
    'replace' => 'E0EA',
  ),
  187 => 
  array (
    'match' => 'E0A3 E00E',
    'replace' => 'E0EB',
  ),
  188 => 
  array (
    'match' => 'E0A4 E00E',
    'replace' => 'E0EC',
  ),
  189 => 
  array (
    'match' => 'E0A5 E00E',
    'replace' => 'E0ED',
  ),
  190 => 
  array (
    'match' => 'E0A6 E00E',
    'replace' => 'E0EE',
  ),
  191 => 
  array (
    'match' => 'E0A7 E00E',
    'replace' => 'E0EF',
  ),
  192 => 
  array (
    'match' => 'E0A8 E00E',
    'replace' => 'E0F0',
  ),
  193 => 
  array (
    'match' => 'E0A9 E00E',
    'replace' => 'E0F1',
  ),
  194 => 
  array (
    'match' => 'E0AA E00E',
    'replace' => 'E0F2',
  ),
  195 => 
  array (
    'match' => 'E0AB E00E',
    'replace' => 'E0F3',
  ),
  196 => 
  array (
    'match' => 'E0AC E00E',
    'replace' => 'E0F4',
  ),
  197 => 
  array (
    'match' => 'E0AD E00E',
    'replace' => 'E0F5',
  ),
  198 => 
  array (
    'match' => 'E0AE E00E',
    'replace' => 'E0F6',
  ),
  199 => 
  array (
    'match' => 'E0AF E00E',
    'replace' => 'E0F7',
  ),
  200 => 
  array (
    'match' => 'E0B0 E00E',
    'replace' => 'E0F8',
  ),
  201 => 
  array (
    'match' => 'E0B1 E00E',
    'replace' => 'E0F9',
  ),
  202 => 
  array (
    'match' => 'E0B2 E00E',
    'replace' => 'E0FA',
  ),
  203 => 
  array (
    'match' => 'E0B3 E00E',
    'replace' => 'E0FB',
  ),
  204 => 
  array (
    'match' => 'E0B4 E00E',
    'replace' => 'E0FC',
  ),
  205 => 
  array (
    'match' => 'E0B5 E00E',
    'replace' => 'E0FD',
  ),
  206 => 
  array (
    'match' => 'E0B6 E00E',
    'replace' => 'E0FE',
  ),
  207 => 
  array (
    'match' => 'E0B7 E00E',
    'replace' => 'E0FF',
  ),
  208 => 
  array (
    'match' => 'E0B8 E00E',
    'replace' => 'E100',
  ),
  209 => 
  array (
    'match' => 'E0B9 E00E',
    'replace' => 'E101',
  ),
  210 => 
  array (
    'match' => 'E0BA E00E',
    'replace' => 'E102',
  ),
  211 => 
  array (
    'match' => 'E0BB E00E',
    'replace' => 'E103',
  ),
  212 => 
  array (
    'match' => 'E0BC E00E',
    'replace' => 'E104',
  ),
  213 => 
  array (
    'match' => 'E0BD E00E',
    'replace' => 'E105',
  ),
  214 => 
  array (
    'match' => 'E0BE E00E',
    'replace' => 'E106',
  ),
  215 => 
  array (
    'match' => 'E0BF E00E',
    'replace' => 'E107',
  ),
  216 => 
  array (
    'match' => 'E0C0 E00E',
    'replace' => 'E108',
  ),
  217 => 
  array (
    'match' => 'E0C1 E00E',
    'replace' => 'E109',
  ),
  218 => 
  array (
    'match' => 'E0C2 E00E',
    'replace' => 'E10A',
  ),
  219 => 
  array (
    'match' => 'E0C3 E00E',
    'replace' => 'E10B',
  ),
  220 => 
  array (
    'match' => 'E0C4 E00E',
    'replace' => 'E10C',
  ),
  221 => 
  array (
    'match' => 'E0C5 E00E',
    'replace' => 'E10D',
  ),
  222 => 
  array (
    'match' => 'E0C6 E00E',
    'replace' => 'E10E',
  ),
  223 => 
  array (
    'match' => 'E0C7 E00E',
    'replace' => 'E10F',
  ),
  224 => 
  array (
    'match' => 'E0C8 E00E',
    'replace' => 'E110',
  ),
  225 => 
  array (
    'match' => 'E0C9 E00E',
    'replace' => 'E111',
  ),
  226 => 
  array (
    'match' => 'E0CA E00E',
    'replace' => 'E112',
  ),
  227 => 
  array (
    'match' => 'E0CB E00E',
    'replace' => 'E113',
  ),
  228 => 
  array (
    'match' => 'E0CC E00E',
    'replace' => 'E114',
  ),
  229 => 
  array (
    'match' => 'E0CD E00E',
    'replace' => 'E115',
  ),
  230 => 
  array (
    'match' => 'E0CE E00E',
    'replace' => 'E116',
  ),
  231 => 
  array (
    'match' => 'E0CF E00E',
    'replace' => 'E117',
  ),
  232 => 
  array (
    'match' => 'E0D0 E00E',
    'replace' => 'E118',
  ),
  233 => 
  array (
    'match' => 'E0D1 E00E',
    'replace' => 'E119',
  ),
  234 => 
  array (
    'match' => 'E0D2 E00E',
    'replace' => 'E11A',
  ),
  235 => 
  array (
    'match' => '0958 E00E',
    'replace' => 'E067',
  ),
  236 => 
  array (
    'match' => '0959 E00E',
    'replace' => 'E068',
  ),
  237 => 
  array (
    'match' => '095A E00E',
    'replace' => 'E069',
  ),
  238 => 
  array (
    'match' => 'E02A E00E',
    'replace' => 'E06A',
  ),
  239 => 
  array (
    'match' => 'E02B E00E',
    'replace' => 'E06B',
  ),
  240 => 
  array (
    'match' => 'E02C E00E',
    'replace' => 'E06C',
  ),
  241 => 
  array (
    'match' => 'E02D E00E',
    'replace' => 'E06D',
  ),
  242 => 
  array (
    'match' => '095B E00E',
    'replace' => 'E06E',
  ),
  243 => 
  array (
    'match' => 'E02E E00E',
    'replace' => 'E06F',
  ),
  244 => 
  array (
    'match' => 'E02F E00E',
    'replace' => 'E070',
  ),
  245 => 
  array (
    'match' => 'E030 E00E',
    'replace' => 'E071',
  ),
  246 => 
  array (
    'match' => 'E031 E00E',
    'replace' => 'E072',
  ),
  247 => 
  array (
    'match' => '095C E00E',
    'replace' => 'E073',
  ),
  248 => 
  array (
    'match' => '095D E00E',
    'replace' => 'E074',
  ),
  249 => 
  array (
    'match' => 'E032 E00E',
    'replace' => 'E075',
  ),
  250 => 
  array (
    'match' => 'E033 E00E',
    'replace' => 'E076',
  ),
  251 => 
  array (
    'match' => 'E034 E00E',
    'replace' => 'E077',
  ),
  252 => 
  array (
    'match' => 'E035 E00E',
    'replace' => 'E078',
  ),
  253 => 
  array (
    'match' => 'E036 E00E',
    'replace' => 'E079',
  ),
  254 => 
  array (
    'match' => '0929 E00E',
    'replace' => 'E07A',
  ),
  255 => 
  array (
    'match' => 'E037 E00E',
    'replace' => 'E07B',
  ),
  256 => 
  array (
    'match' => '095E E00E',
    'replace' => 'E07C',
  ),
  257 => 
  array (
    'match' => 'E038 E00E',
    'replace' => 'E07D',
  ),
  258 => 
  array (
    'match' => 'E039 E00E',
    'replace' => 'E07E',
  ),
  259 => 
  array (
    'match' => 'E03A E00E',
    'replace' => 'E07F',
  ),
  260 => 
  array (
    'match' => '095F E00E',
    'replace' => 'E080',
  ),
  261 => 
  array (
    'match' => '0931 E00E',
    'replace' => 'E081',
  ),
  262 => 
  array (
    'match' => 'E03B E00E',
    'replace' => 'E082',
  ),
  263 => 
  array (
    'match' => '0934 E00E',
    'replace' => 'E083',
  ),
  264 => 
  array (
    'match' => 'E03C E00E',
    'replace' => 'E084',
  ),
  265 => 
  array (
    'match' => 'E03D E00E',
    'replace' => 'E085',
  ),
  266 => 
  array (
    'match' => 'E03E E00E',
    'replace' => 'E086',
  ),
  267 => 
  array (
    'match' => 'E03F E00E',
    'replace' => 'E087',
  ),
  268 => 
  array (
    'match' => 'E040 E00E',
    'replace' => 'E088',
  ),
  269 => 
  array (
    'match' => 'E041 E00E',
    'replace' => 'E089',
  ),
  270 => 
  array (
    'match' => 'E042 E00E',
    'replace' => 'E08A',
  ),
  271 => 
  array (
    'match' => 'E043 0924',
    'replace' => 'E11B',
  ),
  272 => 
  array (
    'match' => 'E044 0928',
    'replace' => 'E11C',
  ),
  273 => 
  array (
    'match' => 'E047 092E',
    'replace' => 'E11D',
  ),
  274 => 
  array (
    'match' => 'E052 0924',
    'replace' => 'E11E',
  ),
  275 => 
  array (
    'match' => 'E052 0928',
    'replace' => 'E11F',
  ),
  276 => 
  array (
    'match' => 'E054 0917',
    'replace' => 'E120',
  ),
  277 => 
  array (
    'match' => 'E054 0918',
    'replace' => 'E121',
  ),
  278 => 
  array (
    'match' => 'E054 0926',
    'replace' => 'E122',
  ),
  279 => 
  array (
    'match' => 'E054 0927',
    'replace' => 'E123',
  ),
  280 => 
  array (
    'match' => 'E054 0928',
    'replace' => 'E124',
  ),
  281 => 
  array (
    'match' => 'E054 092C',
    'replace' => 'E125',
  ),
  282 => 
  array (
    'match' => 'E054 092D',
    'replace' => 'E126',
  ),
  283 => 
  array (
    'match' => 'E054 E05C 092D',
    'replace' => 'E127',
  ),
  284 => 
  array (
    'match' => 'E054 092F',
    'replace' => 'E128',
  ),
  285 => 
  array (
    'match' => 'E054 0935',
    'replace' => 'E129',
  ),
  286 => 
  array (
    'match' => 'E057 0924',
    'replace' => 'E12A',
  ),
  287 => 
  array (
    'match' => 'E061 0928',
    'replace' => 'E12B',
  ),
  288 => 
  array (
    'match' => 'E061 091A',
    'replace' => 'E12C',
  ),
  289 => 
  array (
    'match' => 'E061 0932',
    'replace' => 'E12D',
  ),
  290 => 
  array (
    'match' => 'E061 0935',
    'replace' => 'E12E',
  ),
  291 => 
  array (
    'match' => 'E062 091F',
    'replace' => 'E12F',
  ),
  292 => 
  array (
    'match' => 'E062 0920',
    'replace' => 'E130',
  ),
  293 => 
  array (
    'match' => 'E062 E095',
    'replace' => 'E131',
  ),
  294 => 
  array (
    'match' => 'E062 E096',
    'replace' => 'E132',
  ),
  295 => 
  array (
    'match' => 'E063 E09A',
    'replace' => 'E133',
  ),
  296 => 
  array (
    'match' => 'E064 0928',
    'replace' => 'E134',
  ),
  297 => 
  array (
    'match' => 'E064 092E',
    'replace' => 'E135',
  ),
  298 => 
  array (
    'match' => 'E064 092F',
    'replace' => 'E136',
  ),
  299 => 
  array (
    'match' => 'E064 0923',
    'replace' => 'E137',
  ),
  300 => 
  array (
    'match' => 'E064 0932',
    'replace' => 'E138',
  ),
  301 => 
  array (
    'match' => 'E064 0935',
    'replace' => 'E139',
  ),
  302 => 
  array (
    'match' => 'E044 E056',
    'replace' => 'E13B',
  ),
  303 => 
  array (
    'match' => 'E052 E052',
    'replace' => 'E13D',
  ),
  304 => 
  array (
    'match' => 'E052 E056',
    'replace' => 'E13E',
  ),
  305 => 
  array (
    'match' => 'E054 E05B',
    'replace' => 'E13F',
  ),
  306 => 
  array (
    'match' => 'E057 E052',
    'replace' => 'E140',
  ),
  307 => 
  array (
    'match' => 'E061 E056',
    'replace' => 'E141',
  ),
  308 => 
  array (
    'match' => 'E061 E048',
    'replace' => 'E142',
  ),
  309 => 
  array (
    'match' => 'E061 E05E',
    'replace' => 'E143',
  ),
  310 => 
  array (
    'match' => 'E061 E060',
    'replace' => 'E144',
  ),
  311 => 
  array (
    'match' => 'E063 E0E2',
    'replace' => 'E145',
  ),
  312 => 
  array (
    'match' => 'E047 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) E015)',
    'replace' => 'E047 E015 \\1',
  ),
  313 => 
  array (
    'match' => 'E04D ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) E015)',
    'replace' => 'E04D E015 \\1',
  ),
  314 => 
  array (
    'match' => 'E04E ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) E015)',
    'replace' => 'E04E E015 \\1',
  ),
  315 => 
  array (
    'match' => 'E04F ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) E015)',
    'replace' => 'E04F E015 \\1',
  ),
  316 => 
  array (
    'match' => 'E050 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) E015)',
    'replace' => 'E050 E015 \\1',
  ),
  317 => 
  array (
    'match' => 'E054 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) E015)',
    'replace' => 'E054 E015 \\1',
  ),
  318 => 
  array (
    'match' => 'E06B ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) E015)',
    'replace' => 'E06B E015 \\1',
  ),
  319 => 
  array (
    'match' => 'E071 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) E015)',
    'replace' => 'E071 E015 \\1',
  ),
  320 => 
  array (
    'match' => 'E072 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) E015)',
    'replace' => 'E072 E015 \\1',
  ),
  321 => 
  array (
    'match' => 'E073 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) E015)',
    'replace' => 'E073 E015 \\1',
  ),
  322 => 
  array (
    'match' => 'E074 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) E015)',
    'replace' => 'E074 E015 \\1',
  ),
  323 => 
  array (
    'match' => 'E078 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) E015)',
    'replace' => 'E078 E015 \\1',
  ),
  324 => 
  array (
    'match' => 'E0FB ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) E015)',
    'replace' => 'E0FB E015 \\1',
  ),
  325 => 
  array (
    'match' => 'E101 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) E015)',
    'replace' => 'E101 E015 \\1',
  ),
  326 => 
  array (
    'match' => 'E102 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) E015)',
    'replace' => 'E102 E015 \\1',
  ),
  327 => 
  array (
    'match' => 'E103 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) E015)',
    'replace' => 'E103 E015 \\1',
  ),
  328 => 
  array (
    'match' => 'E104 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) E015)',
    'replace' => 'E104 E015 \\1',
  ),
  329 => 
  array (
    'match' => 'E108 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) E015)',
    'replace' => 'E108 E015 \\1',
  ),
  330 => 
  array (
    'match' => 'E047 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (E015|0947|E1A8|E1A7|E199|E1B2|E1B1|0946|E202|E201|E1A3|E1C6|E1C5|0945|E200|E1FF|0948|E1AA|E1A9|E19A|E1B4|E1B3|0902|E00F|0901|0953|0954|0951) E015)',
    'replace' => 'E047 E015 \\1',
  ),
  331 => 
  array (
    'match' => 'E04D ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (E015|0947|E1A8|E1A7|E199|E1B2|E1B1|0946|E202|E201|E1A3|E1C6|E1C5|0945|E200|E1FF|0948|E1AA|E1A9|E19A|E1B4|E1B3|0902|E00F|0901|0953|0954|0951) E015)',
    'replace' => 'E04D E015 \\1',
  ),
  332 => 
  array (
    'match' => 'E04E ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (E015|0947|E1A8|E1A7|E199|E1B2|E1B1|0946|E202|E201|E1A3|E1C6|E1C5|0945|E200|E1FF|0948|E1AA|E1A9|E19A|E1B4|E1B3|0902|E00F|0901|0953|0954|0951) E015)',
    'replace' => 'E04E E015 \\1',
  ),
  333 => 
  array (
    'match' => 'E04F ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (E015|0947|E1A8|E1A7|E199|E1B2|E1B1|0946|E202|E201|E1A3|E1C6|E1C5|0945|E200|E1FF|0948|E1AA|E1A9|E19A|E1B4|E1B3|0902|E00F|0901|0953|0954|0951) E015)',
    'replace' => 'E04F E015 \\1',
  ),
  334 => 
  array (
    'match' => 'E050 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (E015|0947|E1A8|E1A7|E199|E1B2|E1B1|0946|E202|E201|E1A3|E1C6|E1C5|0945|E200|E1FF|0948|E1AA|E1A9|E19A|E1B4|E1B3|0902|E00F|0901|0953|0954|0951) E015)',
    'replace' => 'E050 E015 \\1',
  ),
  335 => 
  array (
    'match' => 'E054 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (E015|0947|E1A8|E1A7|E199|E1B2|E1B1|0946|E202|E201|E1A3|E1C6|E1C5|0945|E200|E1FF|0948|E1AA|E1A9|E19A|E1B4|E1B3|0902|E00F|0901|0953|0954|0951) E015)',
    'replace' => 'E054 E015 \\1',
  ),
  336 => 
  array (
    'match' => 'E06B ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (E015|0947|E1A8|E1A7|E199|E1B2|E1B1|0946|E202|E201|E1A3|E1C6|E1C5|0945|E200|E1FF|0948|E1AA|E1A9|E19A|E1B4|E1B3|0902|E00F|0901|0953|0954|0951) E015)',
    'replace' => 'E06B E015 \\1',
  ),
  337 => 
  array (
    'match' => 'E071 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (E015|0947|E1A8|E1A7|E199|E1B2|E1B1|0946|E202|E201|E1A3|E1C6|E1C5|0945|E200|E1FF|0948|E1AA|E1A9|E19A|E1B4|E1B3|0902|E00F|0901|0953|0954|0951) E015)',
    'replace' => 'E071 E015 \\1',
  ),
  338 => 
  array (
    'match' => 'E072 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (E015|0947|E1A8|E1A7|E199|E1B2|E1B1|0946|E202|E201|E1A3|E1C6|E1C5|0945|E200|E1FF|0948|E1AA|E1A9|E19A|E1B4|E1B3|0902|E00F|0901|0953|0954|0951) E015)',
    'replace' => 'E072 E015 \\1',
  ),
  339 => 
  array (
    'match' => 'E073 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (E015|0947|E1A8|E1A7|E199|E1B2|E1B1|0946|E202|E201|E1A3|E1C6|E1C5|0945|E200|E1FF|0948|E1AA|E1A9|E19A|E1B4|E1B3|0902|E00F|0901|0953|0954|0951) E015)',
    'replace' => 'E073 E015 \\1',
  ),
  340 => 
  array (
    'match' => 'E074 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (E015|0947|E1A8|E1A7|E199|E1B2|E1B1|0946|E202|E201|E1A3|E1C6|E1C5|0945|E200|E1FF|0948|E1AA|E1A9|E19A|E1B4|E1B3|0902|E00F|0901|0953|0954|0951) E015)',
    'replace' => 'E074 E015 \\1',
  ),
  341 => 
  array (
    'match' => 'E078 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (E015|0947|E1A8|E1A7|E199|E1B2|E1B1|0946|E202|E201|E1A3|E1C6|E1C5|0945|E200|E1FF|0948|E1AA|E1A9|E19A|E1B4|E1B3|0902|E00F|0901|0953|0954|0951) E015)',
    'replace' => 'E078 E015 \\1',
  ),
  342 => 
  array (
    'match' => 'E0FB ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (E015|0947|E1A8|E1A7|E199|E1B2|E1B1|0946|E202|E201|E1A3|E1C6|E1C5|0945|E200|E1FF|0948|E1AA|E1A9|E19A|E1B4|E1B3|0902|E00F|0901|0953|0954|0951) E015)',
    'replace' => 'E0FB E015 \\1',
  ),
  343 => 
  array (
    'match' => 'E101 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (E015|0947|E1A8|E1A7|E199|E1B2|E1B1|0946|E202|E201|E1A3|E1C6|E1C5|0945|E200|E1FF|0948|E1AA|E1A9|E19A|E1B4|E1B3|0902|E00F|0901|0953|0954|0951) E015)',
    'replace' => 'E101 E015 \\1',
  ),
  344 => 
  array (
    'match' => 'E102 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (E015|0947|E1A8|E1A7|E199|E1B2|E1B1|0946|E202|E201|E1A3|E1C6|E1C5|0945|E200|E1FF|0948|E1AA|E1A9|E19A|E1B4|E1B3|0902|E00F|0901|0953|0954|0951) E015)',
    'replace' => 'E102 E015 \\1',
  ),
  345 => 
  array (
    'match' => 'E103 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (E015|0947|E1A8|E1A7|E199|E1B2|E1B1|0946|E202|E201|E1A3|E1C6|E1C5|0945|E200|E1FF|0948|E1AA|E1A9|E19A|E1B4|E1B3|0902|E00F|0901|0953|0954|0951) E015)',
    'replace' => 'E103 E015 \\1',
  ),
  346 => 
  array (
    'match' => 'E104 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (E015|0947|E1A8|E1A7|E199|E1B2|E1B1|0946|E202|E201|E1A3|E1C6|E1C5|0945|E200|E1FF|0948|E1AA|E1A9|E19A|E1B4|E1B3|0902|E00F|0901|0953|0954|0951) E015)',
    'replace' => 'E104 E015 \\1',
  ),
  347 => 
  array (
    'match' => 'E108 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (E015|0947|E1A8|E1A7|E199|E1B2|E1B1|0946|E202|E201|E1A3|E1C6|E1C5|0945|E200|E1FF|0948|E1AA|E1A9|E19A|E1B4|E1B3|0902|E00F|0901|0953|0954|0951) E015)',
    'replace' => 'E108 E015 \\1',
  ),
  348 => 
  array (
    'match' => 'E047 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (0941|0942|0943|0944|0952|E013) E015)',
    'replace' => 'E047 E015 \\1',
  ),
  349 => 
  array (
    'match' => 'E04D ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (0941|0942|0943|0944|0952|E013) E015)',
    'replace' => 'E04D E015 \\1',
  ),
  350 => 
  array (
    'match' => 'E04E ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (0941|0942|0943|0944|0952|E013) E015)',
    'replace' => 'E04E E015 \\1',
  ),
  351 => 
  array (
    'match' => 'E04F ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (0941|0942|0943|0944|0952|E013) E015)',
    'replace' => 'E04F E015 \\1',
  ),
  352 => 
  array (
    'match' => 'E050 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (0941|0942|0943|0944|0952|E013) E015)',
    'replace' => 'E050 E015 \\1',
  ),
  353 => 
  array (
    'match' => 'E054 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (0941|0942|0943|0944|0952|E013) E015)',
    'replace' => 'E054 E015 \\1',
  ),
  354 => 
  array (
    'match' => 'E06B ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (0941|0942|0943|0944|0952|E013) E015)',
    'replace' => 'E06B E015 \\1',
  ),
  355 => 
  array (
    'match' => 'E071 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (0941|0942|0943|0944|0952|E013) E015)',
    'replace' => 'E071 E015 \\1',
  ),
  356 => 
  array (
    'match' => 'E072 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (0941|0942|0943|0944|0952|E013) E015)',
    'replace' => 'E072 E015 \\1',
  ),
  357 => 
  array (
    'match' => 'E073 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (0941|0942|0943|0944|0952|E013) E015)',
    'replace' => 'E073 E015 \\1',
  ),
  358 => 
  array (
    'match' => 'E074 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (0941|0942|0943|0944|0952|E013) E015)',
    'replace' => 'E074 E015 \\1',
  ),
  359 => 
  array (
    'match' => 'E078 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (0941|0942|0943|0944|0952|E013) E015)',
    'replace' => 'E078 E015 \\1',
  ),
  360 => 
  array (
    'match' => 'E0FB ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (0941|0942|0943|0944|0952|E013) E015)',
    'replace' => 'E0FB E015 \\1',
  ),
  361 => 
  array (
    'match' => 'E101 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (0941|0942|0943|0944|0952|E013) E015)',
    'replace' => 'E101 E015 \\1',
  ),
  362 => 
  array (
    'match' => 'E102 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (0941|0942|0943|0944|0952|E013) E015)',
    'replace' => 'E102 E015 \\1',
  ),
  363 => 
  array (
    'match' => 'E103 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (0941|0942|0943|0944|0952|E013) E015)',
    'replace' => 'E103 E015 \\1',
  ),
  364 => 
  array (
    'match' => 'E104 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (0941|0942|0943|0944|0952|E013) E015)',
    'replace' => 'E104 E015 \\1',
  ),
  365 => 
  array (
    'match' => 'E108 ((0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029) (0941|0942|0943|0944|0952|E013) E015)',
    'replace' => 'E108 E015 \\1',
  ),
  366 => 
  array (
    'match' => '200D E00E',
    'replace' => '094D',
  ),
  367 => 
  array (
    'match' => 'E00C',
    'replace' => '094D',
  ),
  368 => 
  array (
    'match' => 'E00E',
    'replace' => '094D',
  ),
  369 => 
  array (
    'match' => 'E043 200D',
    'replace' => 'E043',
  ),
  370 => 
  array (
    'match' => 'E044 200D',
    'replace' => 'E044',
  ),
  371 => 
  array (
    'match' => 'E045 200D',
    'replace' => 'E045',
  ),
  372 => 
  array (
    'match' => 'E046 200D',
    'replace' => 'E046',
  ),
  373 => 
  array (
    'match' => 'E047 200D',
    'replace' => 'E047',
  ),
  374 => 
  array (
    'match' => 'E048 200D',
    'replace' => 'E048',
  ),
  375 => 
  array (
    'match' => 'E049 200D',
    'replace' => 'E049',
  ),
  376 => 
  array (
    'match' => 'E04A 200D',
    'replace' => 'E04A',
  ),
  377 => 
  array (
    'match' => 'E04B 200D',
    'replace' => 'E04B',
  ),
  378 => 
  array (
    'match' => 'E04C 200D',
    'replace' => 'E04C',
  ),
  379 => 
  array (
    'match' => 'E04D 200D',
    'replace' => 'E04D',
  ),
  380 => 
  array (
    'match' => 'E04E 200D',
    'replace' => 'E04E',
  ),
  381 => 
  array (
    'match' => 'E04F 200D',
    'replace' => 'E04F',
  ),
  382 => 
  array (
    'match' => 'E050 200D',
    'replace' => 'E050',
  ),
  383 => 
  array (
    'match' => 'E051 200D',
    'replace' => 'E051',
  ),
  384 => 
  array (
    'match' => 'E052 200D',
    'replace' => 'E052',
  ),
  385 => 
  array (
    'match' => 'E053 200D',
    'replace' => 'E053',
  ),
  386 => 
  array (
    'match' => 'E054 200D',
    'replace' => 'E054',
  ),
  387 => 
  array (
    'match' => 'E055 200D',
    'replace' => 'E055',
  ),
  388 => 
  array (
    'match' => 'E056 200D',
    'replace' => 'E056',
  ),
  389 => 
  array (
    'match' => 'E057 200D',
    'replace' => 'E057',
  ),
  390 => 
  array (
    'match' => 'E058 200D',
    'replace' => 'E058',
  ),
  391 => 
  array (
    'match' => 'E059 200D',
    'replace' => 'E059',
  ),
  392 => 
  array (
    'match' => 'E05A 200D',
    'replace' => 'E05A',
  ),
  393 => 
  array (
    'match' => 'E05B 200D',
    'replace' => 'E05B',
  ),
  394 => 
  array (
    'match' => 'E05C 200D',
    'replace' => 'E05C',
  ),
  395 => 
  array (
    'match' => 'E05D 200D',
    'replace' => 'E05D',
  ),
  396 => 
  array (
    'match' => 'E05E 200D',
    'replace' => 'E05E',
  ),
  397 => 
  array (
    'match' => 'E05F 200D',
    'replace' => 'E05F',
  ),
  398 => 
  array (
    'match' => 'E060 200D',
    'replace' => 'E060',
  ),
  399 => 
  array (
    'match' => 'E061 200D',
    'replace' => 'E061',
  ),
  400 => 
  array (
    'match' => 'E062 200D',
    'replace' => 'E062',
  ),
  401 => 
  array (
    'match' => 'E063 200D',
    'replace' => 'E063',
  ),
  402 => 
  array (
    'match' => 'E064 200D',
    'replace' => 'E064',
  ),
  403 => 
  array (
    'match' => 'E065 200D',
    'replace' => 'E065',
  ),
  404 => 
  array (
    'match' => 'E066 200D',
    'replace' => 'E066',
  ),
  405 => 
  array (
    'match' => 'E067 200D',
    'replace' => 'E067',
  ),
  406 => 
  array (
    'match' => 'E068 200D',
    'replace' => 'E068',
  ),
  407 => 
  array (
    'match' => 'E069 200D',
    'replace' => 'E069',
  ),
  408 => 
  array (
    'match' => 'E06A 200D',
    'replace' => 'E06A',
  ),
  409 => 
  array (
    'match' => 'E06B 200D',
    'replace' => 'E06B',
  ),
  410 => 
  array (
    'match' => 'E06C 200D',
    'replace' => 'E06C',
  ),
  411 => 
  array (
    'match' => 'E06D 200D',
    'replace' => 'E06D',
  ),
  412 => 
  array (
    'match' => 'E06E 200D',
    'replace' => 'E06E',
  ),
  413 => 
  array (
    'match' => 'E06F 200D',
    'replace' => 'E06F',
  ),
  414 => 
  array (
    'match' => 'E070 200D',
    'replace' => 'E070',
  ),
  415 => 
  array (
    'match' => 'E071 200D',
    'replace' => 'E071',
  ),
  416 => 
  array (
    'match' => 'E072 200D',
    'replace' => 'E072',
  ),
  417 => 
  array (
    'match' => 'E073 200D',
    'replace' => 'E073',
  ),
  418 => 
  array (
    'match' => 'E074 200D',
    'replace' => 'E074',
  ),
  419 => 
  array (
    'match' => 'E075 200D',
    'replace' => 'E075',
  ),
  420 => 
  array (
    'match' => 'E076 200D',
    'replace' => 'E076',
  ),
  421 => 
  array (
    'match' => 'E077 200D',
    'replace' => 'E077',
  ),
  422 => 
  array (
    'match' => 'E078 200D',
    'replace' => 'E078',
  ),
  423 => 
  array (
    'match' => 'E079 200D',
    'replace' => 'E079',
  ),
  424 => 
  array (
    'match' => 'E07A 200D',
    'replace' => 'E07A',
  ),
  425 => 
  array (
    'match' => 'E07B 200D',
    'replace' => 'E07B',
  ),
  426 => 
  array (
    'match' => 'E07C 200D',
    'replace' => 'E07C',
  ),
  427 => 
  array (
    'match' => 'E07D 200D',
    'replace' => 'E07D',
  ),
  428 => 
  array (
    'match' => 'E07E 200D',
    'replace' => 'E07E',
  ),
  429 => 
  array (
    'match' => 'E07F 200D',
    'replace' => 'E07F',
  ),
  430 => 
  array (
    'match' => 'E080 200D',
    'replace' => 'E080',
  ),
  431 => 
  array (
    'match' => 'E081 200D',
    'replace' => 'E081',
  ),
  432 => 
  array (
    'match' => 'E082 200D',
    'replace' => 'E082',
  ),
  433 => 
  array (
    'match' => 'E083 200D',
    'replace' => 'E083',
  ),
  434 => 
  array (
    'match' => 'E084 200D',
    'replace' => 'E084',
  ),
  435 => 
  array (
    'match' => 'E085 200D',
    'replace' => 'E085',
  ),
  436 => 
  array (
    'match' => 'E086 200D',
    'replace' => 'E086',
  ),
  437 => 
  array (
    'match' => 'E087 200D',
    'replace' => 'E087',
  ),
  438 => 
  array (
    'match' => 'E088 200D',
    'replace' => 'E088',
  ),
  439 => 
  array (
    'match' => 'E089 200D',
    'replace' => 'E089',
  ),
  440 => 
  array (
    'match' => 'E08A 200D',
    'replace' => 'E08A',
  ),
  441 => 
  array (
    'match' => 'E0D3 200D',
    'replace' => 'E0D3',
  ),
  442 => 
  array (
    'match' => 'E0D4 200D',
    'replace' => 'E0D4',
  ),
  443 => 
  array (
    'match' => 'E0D5 200D',
    'replace' => 'E0D5',
  ),
  444 => 
  array (
    'match' => 'E0D6 200D',
    'replace' => 'E0D6',
  ),
  445 => 
  array (
    'match' => 'E0D7 200D',
    'replace' => 'E0D7',
  ),
  446 => 
  array (
    'match' => 'E0D8 200D',
    'replace' => 'E0D8',
  ),
  447 => 
  array (
    'match' => 'E0D9 200D',
    'replace' => 'E0D9',
  ),
  448 => 
  array (
    'match' => 'E0DA 200D',
    'replace' => 'E0DA',
  ),
  449 => 
  array (
    'match' => 'E0DB 200D',
    'replace' => 'E0DB',
  ),
  450 => 
  array (
    'match' => 'E0DC 200D',
    'replace' => 'E0DC',
  ),
  451 => 
  array (
    'match' => 'E0DD 200D',
    'replace' => 'E0DD',
  ),
  452 => 
  array (
    'match' => 'E0DE 200D',
    'replace' => 'E0DE',
  ),
  453 => 
  array (
    'match' => 'E0DF 200D',
    'replace' => 'E0DF',
  ),
  454 => 
  array (
    'match' => 'E0E0 200D',
    'replace' => 'E0E0',
  ),
  455 => 
  array (
    'match' => 'E0E1 200D',
    'replace' => 'E0E1',
  ),
  456 => 
  array (
    'match' => 'E0E2 200D',
    'replace' => 'E0E2',
  ),
  457 => 
  array (
    'match' => 'E0E3 200D',
    'replace' => 'E0E3',
  ),
  458 => 
  array (
    'match' => 'E0E4 200D',
    'replace' => 'E0E4',
  ),
  459 => 
  array (
    'match' => 'E0E5 200D',
    'replace' => 'E0E5',
  ),
  460 => 
  array (
    'match' => 'E0E6 200D',
    'replace' => 'E0E6',
  ),
  461 => 
  array (
    'match' => 'E0E7 200D',
    'replace' => 'E0E7',
  ),
  462 => 
  array (
    'match' => 'E0E8 200D',
    'replace' => 'E0E8',
  ),
  463 => 
  array (
    'match' => 'E0E9 200D',
    'replace' => 'E0E9',
  ),
  464 => 
  array (
    'match' => 'E0EA 200D',
    'replace' => 'E0EA',
  ),
  465 => 
  array (
    'match' => 'E0EB 200D',
    'replace' => 'E0EB',
  ),
  466 => 
  array (
    'match' => 'E0EC 200D',
    'replace' => 'E0EC',
  ),
  467 => 
  array (
    'match' => 'E0ED 200D',
    'replace' => 'E0ED',
  ),
  468 => 
  array (
    'match' => 'E0EE 200D',
    'replace' => 'E0EE',
  ),
  469 => 
  array (
    'match' => 'E0EF 200D',
    'replace' => 'E0EF',
  ),
  470 => 
  array (
    'match' => 'E0F0 200D',
    'replace' => 'E0F0',
  ),
  471 => 
  array (
    'match' => 'E0F1 200D',
    'replace' => 'E0F1',
  ),
  472 => 
  array (
    'match' => 'E0F2 200D',
    'replace' => 'E0F2',
  ),
  473 => 
  array (
    'match' => 'E0F3 200D',
    'replace' => 'E0F3',
  ),
  474 => 
  array (
    'match' => 'E0F4 200D',
    'replace' => 'E0F4',
  ),
  475 => 
  array (
    'match' => 'E0F5 200D',
    'replace' => 'E0F5',
  ),
  476 => 
  array (
    'match' => 'E0F6 200D',
    'replace' => 'E0F6',
  ),
  477 => 
  array (
    'match' => 'E0F7 200D',
    'replace' => 'E0F7',
  ),
  478 => 
  array (
    'match' => 'E0F8 200D',
    'replace' => 'E0F8',
  ),
  479 => 
  array (
    'match' => 'E0F9 200D',
    'replace' => 'E0F9',
  ),
  480 => 
  array (
    'match' => 'E0FA 200D',
    'replace' => 'E0FA',
  ),
  481 => 
  array (
    'match' => 'E0FB 200D',
    'replace' => 'E0FB',
  ),
  482 => 
  array (
    'match' => 'E0FC 200D',
    'replace' => 'E0FC',
  ),
  483 => 
  array (
    'match' => 'E0FD 200D',
    'replace' => 'E0FD',
  ),
  484 => 
  array (
    'match' => 'E0FE 200D',
    'replace' => 'E0FE',
  ),
  485 => 
  array (
    'match' => 'E0FF 200D',
    'replace' => 'E0FF',
  ),
  486 => 
  array (
    'match' => 'E100 200D',
    'replace' => 'E100',
  ),
  487 => 
  array (
    'match' => 'E101 200D',
    'replace' => 'E101',
  ),
  488 => 
  array (
    'match' => 'E102 200D',
    'replace' => 'E102',
  ),
  489 => 
  array (
    'match' => 'E103 200D',
    'replace' => 'E103',
  ),
  490 => 
  array (
    'match' => 'E104 200D',
    'replace' => 'E104',
  ),
  491 => 
  array (
    'match' => 'E105 200D',
    'replace' => 'E105',
  ),
  492 => 
  array (
    'match' => 'E106 200D',
    'replace' => 'E106',
  ),
  493 => 
  array (
    'match' => 'E107 200D',
    'replace' => 'E107',
  ),
  494 => 
  array (
    'match' => 'E108 200D',
    'replace' => 'E108',
  ),
  495 => 
  array (
    'match' => 'E109 200D',
    'replace' => 'E109',
  ),
  496 => 
  array (
    'match' => 'E10A 200D',
    'replace' => 'E10A',
  ),
  497 => 
  array (
    'match' => 'E10B 200D',
    'replace' => 'E10B',
  ),
  498 => 
  array (
    'match' => 'E10C 200D',
    'replace' => 'E10C',
  ),
  499 => 
  array (
    'match' => 'E10D 200D',
    'replace' => 'E10D',
  ),
  500 => 
  array (
    'match' => 'E10E 200D',
    'replace' => 'E10E',
  ),
  501 => 
  array (
    'match' => 'E10F 200D',
    'replace' => 'E10F',
  ),
  502 => 
  array (
    'match' => 'E110 200D',
    'replace' => 'E110',
  ),
  503 => 
  array (
    'match' => 'E111 200D',
    'replace' => 'E111',
  ),
  504 => 
  array (
    'match' => 'E112 200D',
    'replace' => 'E112',
  ),
  505 => 
  array (
    'match' => 'E113 200D',
    'replace' => 'E113',
  ),
  506 => 
  array (
    'match' => 'E114 200D',
    'replace' => 'E114',
  ),
  507 => 
  array (
    'match' => 'E115 200D',
    'replace' => 'E115',
  ),
  508 => 
  array (
    'match' => 'E116 200D',
    'replace' => 'E116',
  ),
  509 => 
  array (
    'match' => 'E117 200D',
    'replace' => 'E117',
  ),
  510 => 
  array (
    'match' => 'E118 200D',
    'replace' => 'E118',
  ),
  511 => 
  array (
    'match' => 'E119 200D',
    'replace' => 'E119',
  ),
  512 => 
  array (
    'match' => 'E11A 200D',
    'replace' => 'E11A',
  ),
  513 => 
  array (
    'match' => 'E13D 200D',
    'replace' => 'E13D',
  ),
  514 => 
  array (
    'match' => 'E13E 200D',
    'replace' => 'E13E',
  ),
  515 => 
  array (
    'match' => 'E13F 200D',
    'replace' => 'E13F',
  ),
  516 => 
  array (
    'match' => 'E140 200D',
    'replace' => 'E140',
  ),
  517 => 
  array (
    'match' => 'E141 200D',
    'replace' => 'E141',
  ),
  518 => 
  array (
    'match' => 'E142 200D',
    'replace' => 'E142',
  ),
  519 => 
  array (
    'match' => 'E143 200D',
    'replace' => 'E143',
  ),
  520 => 
  array (
    'match' => 'E144 200D',
    'replace' => 'E144',
  ),
  521 => 
  array (
    'match' => 'E145 200D',
    'replace' => 'E145',
  ),
  522 => 
  array (
    'match' => '200D E013',
    'replace' => 'E013',
  ),
  523 => 
  array (
    'match' => '200D',
    'replace' => '200B',
  ),
  524 => 
  array (
    'match' => '200C',
    'replace' => '200B',
  ),
  525 => 
  array (
    'match' => '(093F) E047',
    'replace' => '\\1 E047 093F',
  ),
  526 => 
  array (
    'match' => '(093F) E04D',
    'replace' => '\\1 E04D 093F',
  ),
  527 => 
  array (
    'match' => '(093F) E04E',
    'replace' => '\\1 E04E 093F',
  ),
  528 => 
  array (
    'match' => '(093F) E04F',
    'replace' => '\\1 E04F 093F',
  ),
  529 => 
  array (
    'match' => '(093F) E050',
    'replace' => '\\1 E050 093F',
  ),
  530 => 
  array (
    'match' => '(093F) E054',
    'replace' => '\\1 E054 093F',
  ),
  531 => 
  array (
    'match' => '(093F) E06B',
    'replace' => '\\1 E06B 093F',
  ),
  532 => 
  array (
    'match' => '(093F) E071',
    'replace' => '\\1 E071 093F',
  ),
  533 => 
  array (
    'match' => '(093F) E072',
    'replace' => '\\1 E072 093F',
  ),
  534 => 
  array (
    'match' => '(093F) E073',
    'replace' => '\\1 E073 093F',
  ),
  535 => 
  array (
    'match' => '(093F) E074',
    'replace' => '\\1 E074 093F',
  ),
  536 => 
  array (
    'match' => '(093F) E078',
    'replace' => '\\1 E078 093F',
  ),
  537 => 
  array (
    'match' => '(093F) E0FB',
    'replace' => '\\1 E0FB 093F',
  ),
  538 => 
  array (
    'match' => '(093F) E101',
    'replace' => '\\1 E101 093F',
  ),
  539 => 
  array (
    'match' => '(093F) E102',
    'replace' => '\\1 E102 093F',
  ),
  540 => 
  array (
    'match' => '(093F) E103',
    'replace' => '\\1 E103 093F',
  ),
  541 => 
  array (
    'match' => '(093F) E104',
    'replace' => '\\1 E104 093F',
  ),
  542 => 
  array (
    'match' => '(093F) E108',
    'replace' => '\\1 E108 093F',
  ),
  543 => 
  array (
    'match' => '093F E047 (093F)',
    'replace' => 'E047 \\1',
  ),
  544 => 
  array (
    'match' => '093F E04D (093F)',
    'replace' => 'E04D \\1',
  ),
  545 => 
  array (
    'match' => '093F E04E (093F)',
    'replace' => 'E04E \\1',
  ),
  546 => 
  array (
    'match' => '093F E04F (093F)',
    'replace' => 'E04F \\1',
  ),
  547 => 
  array (
    'match' => '093F E050 (093F)',
    'replace' => 'E050 \\1',
  ),
  548 => 
  array (
    'match' => '093F E054 (093F)',
    'replace' => 'E054 \\1',
  ),
  549 => 
  array (
    'match' => '093F E06B (093F)',
    'replace' => 'E06B \\1',
  ),
  550 => 
  array (
    'match' => '093F E071 (093F)',
    'replace' => 'E071 \\1',
  ),
  551 => 
  array (
    'match' => '093F E072 (093F)',
    'replace' => 'E072 \\1',
  ),
  552 => 
  array (
    'match' => '093F E073 (093F)',
    'replace' => 'E073 \\1',
  ),
  553 => 
  array (
    'match' => '093F E074 (093F)',
    'replace' => 'E074 \\1',
  ),
  554 => 
  array (
    'match' => '093F E078 (093F)',
    'replace' => 'E078 \\1',
  ),
  555 => 
  array (
    'match' => '093F E0FB (093F)',
    'replace' => 'E0FB \\1',
  ),
  556 => 
  array (
    'match' => '093F E101 (093F)',
    'replace' => 'E101 \\1',
  ),
  557 => 
  array (
    'match' => '093F E102 (093F)',
    'replace' => 'E102 \\1',
  ),
  558 => 
  array (
    'match' => '093F E103 (093F)',
    'replace' => 'E103 \\1',
  ),
  559 => 
  array (
    'match' => '093F E104 (093F)',
    'replace' => 'E104 \\1',
  ),
  560 => 
  array (
    'match' => '093F E108 (093F)',
    'replace' => 'E108 \\1',
  ),
  561 => 
  array (
    'match' => '(E015) 0915 E015',
    'replace' => '\\1 0915',
  ),
  562 => 
  array (
    'match' => '(E015) 0916 E015',
    'replace' => '\\1 0916',
  ),
  563 => 
  array (
    'match' => '(E015) 0917 E015',
    'replace' => '\\1 0917',
  ),
  564 => 
  array (
    'match' => '(E015) 0918 E015',
    'replace' => '\\1 0918',
  ),
  565 => 
  array (
    'match' => '(E015) 0919 E015',
    'replace' => '\\1 0919',
  ),
  566 => 
  array (
    'match' => '(E015) 091A E015',
    'replace' => '\\1 091A',
  ),
  567 => 
  array (
    'match' => '(E015) 091B E015',
    'replace' => '\\1 091B',
  ),
  568 => 
  array (
    'match' => '(E015) 091C E015',
    'replace' => '\\1 091C',
  ),
  569 => 
  array (
    'match' => '(E015) 091D E015',
    'replace' => '\\1 091D',
  ),
  570 => 
  array (
    'match' => '(E015) 091E E015',
    'replace' => '\\1 091E',
  ),
  571 => 
  array (
    'match' => '(E015) 091F E015',
    'replace' => '\\1 091F',
  ),
  572 => 
  array (
    'match' => '(E015) 0920 E015',
    'replace' => '\\1 0920',
  ),
  573 => 
  array (
    'match' => '(E015) 0921 E015',
    'replace' => '\\1 0921',
  ),
  574 => 
  array (
    'match' => '(E015) 0922 E015',
    'replace' => '\\1 0922',
  ),
  575 => 
  array (
    'match' => '(E015) 0923 E015',
    'replace' => '\\1 0923',
  ),
  576 => 
  array (
    'match' => '(E015) 0924 E015',
    'replace' => '\\1 0924',
  ),
  577 => 
  array (
    'match' => '(E015) 0925 E015',
    'replace' => '\\1 0925',
  ),
  578 => 
  array (
    'match' => '(E015) 0926 E015',
    'replace' => '\\1 0926',
  ),
  579 => 
  array (
    'match' => '(E015) 0927 E015',
    'replace' => '\\1 0927',
  ),
  580 => 
  array (
    'match' => '(E015) 0928 E015',
    'replace' => '\\1 0928',
  ),
  581 => 
  array (
    'match' => '(E015) 092A E015',
    'replace' => '\\1 092A',
  ),
  582 => 
  array (
    'match' => '(E015) 092B E015',
    'replace' => '\\1 092B',
  ),
  583 => 
  array (
    'match' => '(E015) 092C E015',
    'replace' => '\\1 092C',
  ),
  584 => 
  array (
    'match' => '(E015) 092D E015',
    'replace' => '\\1 092D',
  ),
  585 => 
  array (
    'match' => '(E015) 092E E015',
    'replace' => '\\1 092E',
  ),
  586 => 
  array (
    'match' => '(E015) 092F E015',
    'replace' => '\\1 092F',
  ),
  587 => 
  array (
    'match' => '(E015) 0930 E015',
    'replace' => '\\1 0930',
  ),
  588 => 
  array (
    'match' => '(E015) 0932 E015',
    'replace' => '\\1 0932',
  ),
  589 => 
  array (
    'match' => '(E015) 0933 E015',
    'replace' => '\\1 0933',
  ),
  590 => 
  array (
    'match' => '(E015) 0935 E015',
    'replace' => '\\1 0935',
  ),
  591 => 
  array (
    'match' => '(E015) 0936 E015',
    'replace' => '\\1 0936',
  ),
  592 => 
  array (
    'match' => '(E015) 0937 E015',
    'replace' => '\\1 0937',
  ),
  593 => 
  array (
    'match' => '(E015) 0938 E015',
    'replace' => '\\1 0938',
  ),
  594 => 
  array (
    'match' => '(E015) 0939 E015',
    'replace' => '\\1 0939',
  ),
  595 => 
  array (
    'match' => '(E015) E028 E015',
    'replace' => '\\1 E028',
  ),
  596 => 
  array (
    'match' => '(E015) E029 E015',
    'replace' => '\\1 E029',
  ),
  597 => 
  array (
    'match' => '(E015) E015 E015',
    'replace' => '\\1 E015',
  ),
  598 => 
  array (
    'match' => '(E015) 0947 E015',
    'replace' => '\\1 0947',
  ),
  599 => 
  array (
    'match' => '(E015) E1A8 E015',
    'replace' => '\\1 E1A8',
  ),
  600 => 
  array (
    'match' => '(E015) E1A7 E015',
    'replace' => '\\1 E1A7',
  ),
  601 => 
  array (
    'match' => '(E015) E199 E015',
    'replace' => '\\1 E199',
  ),
  602 => 
  array (
    'match' => '(E015) E1B2 E015',
    'replace' => '\\1 E1B2',
  ),
  603 => 
  array (
    'match' => '(E015) E1B1 E015',
    'replace' => '\\1 E1B1',
  ),
  604 => 
  array (
    'match' => '(E015) 0946 E015',
    'replace' => '\\1 0946',
  ),
  605 => 
  array (
    'match' => '(E015) E202 E015',
    'replace' => '\\1 E202',
  ),
  606 => 
  array (
    'match' => '(E015) E201 E015',
    'replace' => '\\1 E201',
  ),
  607 => 
  array (
    'match' => '(E015) E1A3 E015',
    'replace' => '\\1 E1A3',
  ),
  608 => 
  array (
    'match' => '(E015) E1C6 E015',
    'replace' => '\\1 E1C6',
  ),
  609 => 
  array (
    'match' => '(E015) E1C5 E015',
    'replace' => '\\1 E1C5',
  ),
  610 => 
  array (
    'match' => '(E015) 0945 E015',
    'replace' => '\\1 0945',
  ),
  611 => 
  array (
    'match' => '(E015) E200 E015',
    'replace' => '\\1 E200',
  ),
  612 => 
  array (
    'match' => '(E015) E1FF E015',
    'replace' => '\\1 E1FF',
  ),
  613 => 
  array (
    'match' => '(E015) 0948 E015',
    'replace' => '\\1 0948',
  ),
  614 => 
  array (
    'match' => '(E015) E1AA E015',
    'replace' => '\\1 E1AA',
  ),
  615 => 
  array (
    'match' => '(E015) E1A9 E015',
    'replace' => '\\1 E1A9',
  ),
  616 => 
  array (
    'match' => '(E015) E19A E015',
    'replace' => '\\1 E19A',
  ),
  617 => 
  array (
    'match' => '(E015) E1B4 E015',
    'replace' => '\\1 E1B4',
  ),
  618 => 
  array (
    'match' => '(E015) E1B3 E015',
    'replace' => '\\1 E1B3',
  ),
  619 => 
  array (
    'match' => '(E015) 0902 E015',
    'replace' => '\\1 0902',
  ),
  620 => 
  array (
    'match' => '(E015) E00F E015',
    'replace' => '\\1 E00F',
  ),
  621 => 
  array (
    'match' => '(E015) 0901 E015',
    'replace' => '\\1 0901',
  ),
  622 => 
  array (
    'match' => '(E015) 0953 E015',
    'replace' => '\\1 0953',
  ),
  623 => 
  array (
    'match' => '(E015) 0954 E015',
    'replace' => '\\1 0954',
  ),
  624 => 
  array (
    'match' => '(E015) 0951 E015',
    'replace' => '\\1 0951',
  ),
  625 => 
  array (
    'match' => '(E015) 0941 E015',
    'replace' => '\\1 0941',
  ),
  626 => 
  array (
    'match' => '(E015) 0942 E015',
    'replace' => '\\1 0942',
  ),
  627 => 
  array (
    'match' => '(E015) 0943 E015',
    'replace' => '\\1 0943',
  ),
  628 => 
  array (
    'match' => '(E015) 0944 E015',
    'replace' => '\\1 0944',
  ),
  629 => 
  array (
    'match' => '(E015) 0952 E015',
    'replace' => '\\1 0952',
  ),
  630 => 
  array (
    'match' => '(E015) E013 E015',
    'replace' => '\\1 E013',
  ),
  631 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0915 E015',
    'replace' => '\\1 0915',
  ),
  632 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0916 E015',
    'replace' => '\\1 0916',
  ),
  633 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0917 E015',
    'replace' => '\\1 0917',
  ),
  634 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0918 E015',
    'replace' => '\\1 0918',
  ),
  635 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0919 E015',
    'replace' => '\\1 0919',
  ),
  636 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 091A E015',
    'replace' => '\\1 091A',
  ),
  637 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 091B E015',
    'replace' => '\\1 091B',
  ),
  638 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 091C E015',
    'replace' => '\\1 091C',
  ),
  639 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 091D E015',
    'replace' => '\\1 091D',
  ),
  640 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 091E E015',
    'replace' => '\\1 091E',
  ),
  641 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 091F E015',
    'replace' => '\\1 091F',
  ),
  642 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0920 E015',
    'replace' => '\\1 0920',
  ),
  643 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0921 E015',
    'replace' => '\\1 0921',
  ),
  644 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0922 E015',
    'replace' => '\\1 0922',
  ),
  645 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0923 E015',
    'replace' => '\\1 0923',
  ),
  646 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0924 E015',
    'replace' => '\\1 0924',
  ),
  647 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0925 E015',
    'replace' => '\\1 0925',
  ),
  648 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0926 E015',
    'replace' => '\\1 0926',
  ),
  649 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0927 E015',
    'replace' => '\\1 0927',
  ),
  650 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0928 E015',
    'replace' => '\\1 0928',
  ),
  651 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 092A E015',
    'replace' => '\\1 092A',
  ),
  652 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 092B E015',
    'replace' => '\\1 092B',
  ),
  653 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 092C E015',
    'replace' => '\\1 092C',
  ),
  654 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 092D E015',
    'replace' => '\\1 092D',
  ),
  655 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 092E E015',
    'replace' => '\\1 092E',
  ),
  656 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 092F E015',
    'replace' => '\\1 092F',
  ),
  657 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0930 E015',
    'replace' => '\\1 0930',
  ),
  658 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0932 E015',
    'replace' => '\\1 0932',
  ),
  659 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0933 E015',
    'replace' => '\\1 0933',
  ),
  660 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0935 E015',
    'replace' => '\\1 0935',
  ),
  661 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0936 E015',
    'replace' => '\\1 0936',
  ),
  662 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0937 E015',
    'replace' => '\\1 0937',
  ),
  663 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0938 E015',
    'replace' => '\\1 0938',
  ),
  664 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0939 E015',
    'replace' => '\\1 0939',
  ),
  665 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) E028 E015',
    'replace' => '\\1 E028',
  ),
  666 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) E029 E015',
    'replace' => '\\1 E029',
  ),
  667 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) E015 E015',
    'replace' => '\\1 E015',
  ),
  668 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0947 E015',
    'replace' => '\\1 0947',
  ),
  669 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) E1A8 E015',
    'replace' => '\\1 E1A8',
  ),
  670 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) E1A7 E015',
    'replace' => '\\1 E1A7',
  ),
  671 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) E199 E015',
    'replace' => '\\1 E199',
  ),
  672 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) E1B2 E015',
    'replace' => '\\1 E1B2',
  ),
  673 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) E1B1 E015',
    'replace' => '\\1 E1B1',
  ),
  674 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0946 E015',
    'replace' => '\\1 0946',
  ),
  675 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) E202 E015',
    'replace' => '\\1 E202',
  ),
  676 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) E201 E015',
    'replace' => '\\1 E201',
  ),
  677 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) E1A3 E015',
    'replace' => '\\1 E1A3',
  ),
  678 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) E1C6 E015',
    'replace' => '\\1 E1C6',
  ),
  679 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) E1C5 E015',
    'replace' => '\\1 E1C5',
  ),
  680 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0945 E015',
    'replace' => '\\1 0945',
  ),
  681 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) E200 E015',
    'replace' => '\\1 E200',
  ),
  682 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) E1FF E015',
    'replace' => '\\1 E1FF',
  ),
  683 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0948 E015',
    'replace' => '\\1 0948',
  ),
  684 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) E1AA E015',
    'replace' => '\\1 E1AA',
  ),
  685 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) E1A9 E015',
    'replace' => '\\1 E1A9',
  ),
  686 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) E19A E015',
    'replace' => '\\1 E19A',
  ),
  687 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) E1B4 E015',
    'replace' => '\\1 E1B4',
  ),
  688 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) E1B3 E015',
    'replace' => '\\1 E1B3',
  ),
  689 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0902 E015',
    'replace' => '\\1 0902',
  ),
  690 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) E00F E015',
    'replace' => '\\1 E00F',
  ),
  691 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0901 E015',
    'replace' => '\\1 0901',
  ),
  692 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0953 E015',
    'replace' => '\\1 0953',
  ),
  693 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0954 E015',
    'replace' => '\\1 0954',
  ),
  694 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0951 E015',
    'replace' => '\\1 0951',
  ),
  695 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0941 E015',
    'replace' => '\\1 0941',
  ),
  696 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0942 E015',
    'replace' => '\\1 0942',
  ),
  697 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0943 E015',
    'replace' => '\\1 0943',
  ),
  698 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0944 E015',
    'replace' => '\\1 0944',
  ),
  699 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) 0952 E015',
    'replace' => '\\1 0952',
  ),
  700 => 
  array (
    'match' => '(E015 (0915|0916|0917|0918|0919|091A|091B|091C|091D|091E|091F|0920|0921|0922|0923|0924|0925|0926|0927|0928|092A|092B|092C|092D|092E|092F|0930|0932|0933|0935|0936|0937|0938|0939|E028|E029)) E013 E015',
    'replace' => '\\1 E013',
  ),
  701 => 
  array (
    'match' => '(093F) E015',
    'replace' => '\\1 E015 093F',
  ),
  702 => 
  array (
    'match' => '093F E015',
    'replace' => 'E015',
  ),
  703 => 
  array (
    'match' => '0915 0962',
    'replace' => 'E153',
  ),
  704 => 
  array (
    'match' => '0915 0963',
    'replace' => 'E154',
  ),
  705 => 
  array (
    'match' => 'E08F 0941',
    'replace' => 'E155',
  ),
  706 => 
  array (
    'match' => 'E08F 0942',
    'replace' => 'E156',
  ),
  707 => 
  array (
    'match' => 'E08F 0943',
    'replace' => 'E157',
  ),
  708 => 
  array (
    'match' => 'E08F 0944',
    'replace' => 'E158',
  ),
  709 => 
  array (
    'match' => 'E091 0941',
    'replace' => 'E159',
  ),
  710 => 
  array (
    'match' => 'E091 0942',
    'replace' => 'E15A',
  ),
  711 => 
  array (
    'match' => 'E091 0943',
    'replace' => 'E15B',
  ),
  712 => 
  array (
    'match' => 'E091 0944',
    'replace' => 'E15C',
  ),
  713 => 
  array (
    'match' => 'E095 0941',
    'replace' => 'E15D',
  ),
  714 => 
  array (
    'match' => 'E095 0942',
    'replace' => 'E15E',
  ),
  715 => 
  array (
    'match' => 'E095 0943',
    'replace' => 'E15F',
  ),
  716 => 
  array (
    'match' => 'E095 0944',
    'replace' => 'E160',
  ),
  717 => 
  array (
    'match' => 'E096 0941',
    'replace' => 'E161',
  ),
  718 => 
  array (
    'match' => 'E096 0942',
    'replace' => 'E162',
  ),
  719 => 
  array (
    'match' => 'E096 0943',
    'replace' => 'E163',
  ),
  720 => 
  array (
    'match' => 'E096 0944',
    'replace' => 'E164',
  ),
  721 => 
  array (
    'match' => 'E097 0941',
    'replace' => 'E165',
  ),
  722 => 
  array (
    'match' => 'E097 0942',
    'replace' => 'E166',
  ),
  723 => 
  array (
    'match' => 'E097 0943',
    'replace' => 'E167',
  ),
  724 => 
  array (
    'match' => 'E097 0944',
    'replace' => 'E168',
  ),
  725 => 
  array (
    'match' => 'E098 0941',
    'replace' => 'E169',
  ),
  726 => 
  array (
    'match' => 'E098 0942',
    'replace' => 'E16A',
  ),
  727 => 
  array (
    'match' => 'E098 0943',
    'replace' => 'E16B',
  ),
  728 => 
  array (
    'match' => 'E098 0944',
    'replace' => 'E16C',
  ),
  729 => 
  array (
    'match' => '0926 0943',
    'replace' => 'E16D',
  ),
  730 => 
  array (
    'match' => '092A 0962',
    'replace' => 'E16E',
  ),
  731 => 
  array (
    'match' => '092A 0963',
    'replace' => 'E16F',
  ),
  732 => 
  array (
    'match' => '0939 0943',
    'replace' => 'E172',
  ),
  733 => 
  array (
    'match' => '0939 0944',
    'replace' => 'E173',
  ),
  734 => 
  array (
    'match' => 'E0B3 0941',
    'replace' => 'E174',
  ),
  735 => 
  array (
    'match' => 'E0B3 0942',
    'replace' => 'E175',
  ),
  736 => 
  array (
    'match' => 'E0B3 0943',
    'replace' => 'E176',
  ),
  737 => 
  array (
    'match' => 'E0B3 0944',
    'replace' => 'E177',
  ),
  738 => 
  array (
    'match' => 'E0B5 0941',
    'replace' => 'E178',
  ),
  739 => 
  array (
    'match' => 'E0B5 0942',
    'replace' => 'E179',
  ),
  740 => 
  array (
    'match' => 'E0B5 0943',
    'replace' => 'E17A',
  ),
  741 => 
  array (
    'match' => 'E0B5 0944',
    'replace' => 'E17B',
  ),
  742 => 
  array (
    'match' => 'E0B9 0941',
    'replace' => 'E17C',
  ),
  743 => 
  array (
    'match' => 'E0B9 0942',
    'replace' => 'E17D',
  ),
  744 => 
  array (
    'match' => 'E0B9 0943',
    'replace' => 'E17E',
  ),
  745 => 
  array (
    'match' => 'E0B9 0944',
    'replace' => 'E17F',
  ),
  746 => 
  array (
    'match' => 'E0BA 0941',
    'replace' => 'E180',
  ),
  747 => 
  array (
    'match' => 'E0BA 0942',
    'replace' => 'E181',
  ),
  748 => 
  array (
    'match' => 'E0BA 0943',
    'replace' => 'E182',
  ),
  749 => 
  array (
    'match' => 'E0BA 0944',
    'replace' => 'E183',
  ),
  750 => 
  array (
    'match' => 'E0BB 0941',
    'replace' => 'E184',
  ),
  751 => 
  array (
    'match' => 'E0BB 0942',
    'replace' => 'E185',
  ),
  752 => 
  array (
    'match' => 'E0BB 0943',
    'replace' => 'E186',
  ),
  753 => 
  array (
    'match' => 'E0BB 0944',
    'replace' => 'E187',
  ),
  754 => 
  array (
    'match' => 'E0BC 0941',
    'replace' => 'E188',
  ),
  755 => 
  array (
    'match' => 'E0BC 0942',
    'replace' => 'E189',
  ),
  756 => 
  array (
    'match' => 'E0BC 0943',
    'replace' => 'E18A',
  ),
  757 => 
  array (
    'match' => 'E0BC 0944',
    'replace' => 'E18B',
  ),
  758 => 
  array (
    'match' => 'E131 0941',
    'replace' => 'E18C',
  ),
  759 => 
  array (
    'match' => 'E131 0942',
    'replace' => 'E18D',
  ),
  760 => 
  array (
    'match' => 'E131 0943',
    'replace' => 'E18E',
  ),
  761 => 
  array (
    'match' => 'E131 0944',
    'replace' => 'E18F',
  ),
  762 => 
  array (
    'match' => 'E132 0941',
    'replace' => 'E190',
  ),
  763 => 
  array (
    'match' => 'E132 0942',
    'replace' => 'E191',
  ),
  764 => 
  array (
    'match' => 'E132 0943',
    'replace' => 'E192',
  ),
  765 => 
  array (
    'match' => 'E132 0944',
    'replace' => 'E193',
  ),
  766 => 
  array (
    'match' => '((0930|0931|E0A5|E0C9)) 0941',
    'replace' => '\\1 E170',
  ),
  767 => 
  array (
    'match' => '((0930|0931|E0A5|E0C9)) 0942',
    'replace' => '\\1 E171',
  ),
  768 => 
  array (
    'match' => '0947 E015',
    'replace' => 'E199',
  ),
  769 => 
  array (
    'match' => '0948 E015',
    'replace' => 'E19A',
  ),
  770 => 
  array (
    'match' => '0940 E015',
    'replace' => 'E19B',
  ),
  771 => 
  array (
    'match' => 'E194 E015',
    'replace' => 'E19C',
  ),
  772 => 
  array (
    'match' => 'E195 E015',
    'replace' => 'E19D',
  ),
  773 => 
  array (
    'match' => 'E196 E015',
    'replace' => 'E19E',
  ),
  774 => 
  array (
    'match' => 'E197 E015',
    'replace' => 'E19F',
  ),
  775 => 
  array (
    'match' => 'E198 E015',
    'replace' => 'E1A0',
  ),
  776 => 
  array (
    'match' => '094B E015',
    'replace' => 'E1A1',
  ),
  777 => 
  array (
    'match' => '094C E015',
    'replace' => 'E1A2',
  ),
  778 => 
  array (
    'match' => '0946 E015',
    'replace' => 'E1A3',
  ),
  779 => 
  array (
    'match' => '094A E015',
    'replace' => 'E1A4',
  ),
  780 => 
  array (
    'match' => '0908 0901',
    'replace' => 'E1A5',
  ),
  781 => 
  array (
    'match' => '0908 0902',
    'replace' => 'E1A6',
  ),
  782 => 
  array (
    'match' => 'E12F 0901',
    'replace' => 'E1C9',
  ),
  783 => 
  array (
    'match' => 'E130 0901',
    'replace' => 'E1CA',
  ),
  784 => 
  array (
    'match' => '0945 0901',
    'replace' => 'E200',
  ),
  785 => 
  array (
    'match' => '0946 0901',
    'replace' => 'E202',
  ),
  786 => 
  array (
    'match' => '0947 0901',
    'replace' => 'E1A7',
  ),
  787 => 
  array (
    'match' => '0948 0901',
    'replace' => 'E1A9',
  ),
  788 => 
  array (
    'match' => '094B 0901',
    'replace' => 'E1AB',
  ),
  789 => 
  array (
    'match' => '094C 0901',
    'replace' => 'E1AD',
  ),
  790 => 
  array (
    'match' => '0945 0902',
    'replace' => 'E1FF',
  ),
  791 => 
  array (
    'match' => '0946 0902',
    'replace' => 'E201',
  ),
  792 => 
  array (
    'match' => '0947 0902',
    'replace' => 'E1A8',
  ),
  793 => 
  array (
    'match' => '0948 0902',
    'replace' => 'E1AA',
  ),
  794 => 
  array (
    'match' => '094B 0902',
    'replace' => 'E1AC',
  ),
  795 => 
  array (
    'match' => '094C 0902',
    'replace' => 'E1AE',
  ),
  796 => 
  array (
    'match' => 'E015 0902 0901',
    'replace' => 'E1B0 E1AF',
  ),
  797 => 
  array (
    'match' => 'E199 0902 0901',
    'replace' => 'E1B2 E1B1',
  ),
  798 => 
  array (
    'match' => 'E19A 0902 0901',
    'replace' => 'E1B4 E1B3',
  ),
  799 => 
  array (
    'match' => 'E1A1 0902 0901',
    'replace' => 'E1C2 E1C1',
  ),
  800 => 
  array (
    'match' => 'E1A3 0902 0901',
    'replace' => 'E1C6 E1C5',
  ),
  801 => 
  array (
    'match' => 'E1A4 0902 0901',
    'replace' => 'E1C8 E1C7',
  ),
  802 => 
  array (
    'match' => 'E1A2 0902 0901',
    'replace' => 'E1C4 E1C3',
  ),
  803 => 
  array (
    'match' => 'E19B 0901',
    'replace' => 'E1B5',
  ),
  804 => 
  array (
    'match' => 'E19C 0901',
    'replace' => 'E1B6',
  ),
  805 => 
  array (
    'match' => 'E19D 0901',
    'replace' => 'E1B7',
  ),
  806 => 
  array (
    'match' => 'E19E 0901',
    'replace' => 'E1B8',
  ),
  807 => 
  array (
    'match' => 'E19F 0901',
    'replace' => 'E1B9',
  ),
  808 => 
  array (
    'match' => 'E1A0 0901',
    'replace' => 'E1BA',
  ),
  809 => 
  array (
    'match' => 'E19B 0902',
    'replace' => 'E1BB',
  ),
  810 => 
  array (
    'match' => 'E19C 0902',
    'replace' => 'E1BC',
  ),
  811 => 
  array (
    'match' => 'E19D 0902',
    'replace' => 'E1BD',
  ),
  812 => 
  array (
    'match' => 'E19E 0902',
    'replace' => 'E1BE',
  ),
  813 => 
  array (
    'match' => 'E19F 0902',
    'replace' => 'E1BF',
  ),
  814 => 
  array (
    'match' => 'E1A0 0902',
    'replace' => 'E1C0',
  ),
  815 => 
  array (
    'match' => '((0908|E01B|090D|090E|E01F|0910|E021|0914|E025|E016|E017|0940|E194|E195|E196|E197|E198|E19B|E19C|E19D|E19E|E19F|E1A0|0949|094A|E1A4|094B|E1A1|094C|E1A2|E015|0947|E1A8|E1A7|E199|E1B2|E1B1|0946|E202|E201|E1A3|E1C6|E1C5|0945|E200|E1FF|0948|E1AA|E1A9|E19A|E1B4|E1B3|0902|E00F|0901|0953|0954|0951)) 0901',
    'replace' => '\\1 E00F',
  ),
  816 => 
  array (
    'match' => '093F ((0930|0931))',
    'replace' => 'E14B \\1',
  ),
  817 => 
  array (
    'match' => '093F ((0915|0958|E08B|E0AF|0919|E02B|E08F|E0B3|091F|E030|E095|E0B9|0920|E031|E096|E0BA|0921|095C|E097|E0BB|0922|095D|E098|E0BC|0926|E035|E09C|E0C0|092B|095E|E0A0|E0C4|0939|E040|E0AC|E0D0|E120|E122|E124|E125|E129|E134|E139))',
    'replace' => 'E14C \\1',
  ),
  818 => 
  array (
    'match' => '093F ((0924|E033|E09A|E0BE|092A|E037|E09F|E0C3|092C|E038|E0A1|E0C5|0935|E03C|E0A8|E0CC|0937|E03E|E0AA|E0CE|E11B|E11E|E121|E123|E126|E12F|E130|E131|E132|E137|E138))',
    'replace' => 'E14D \\1',
  ),
  819 => 
  array (
    'match' => '093F ((0917|095A|E08D|E0B1|0918|E02A|E08E|E0B2|091A|E02C|E090|E0B4|091B|E02D|E091|E0B5|091E|E02F|E094|E0B8|0925|E034|0927|E036|E09D|E0C1|0928|0929|E09E|E0C2|092D|E039|E0A2|E0C6|092E|E03A|E0A3|E0C7|092F|095F|E0A4|E0C8|0932|E03B|E0A6|E0CA|0933|0934|E0A7|E0CB|0936|E03D|E0A9|E0CD|0938|E03F|E0AB|E0CF|E127|E128|E12A|E135|E136))',
    'replace' => 'E14E \\1',
  ),
  820 => 
  array (
    'match' => '((0940|094A|094B|094C|0946|0947|0948)) E015',
    'replace' => '\\1 E014',
  ),
  821 => 
  array (
    'match' => '((0919|E02B|E08F|E0B3|091F|E030|E095|E0B9|0922|095D|E098|E0BC|0926|E035|E09C|E0C0|0930|0931|0932|E03B|E0A6|E0CA|0939|E040|E0AC|E0D0)) 0940',
    'replace' => '\\1 E194',
  ),
  822 => 
  array (
    'match' => '((0919|E02B|E08F|E0B3|091F|E030|E095|E0B9|0922|095D|E098|E0BC|0926|E035|E09C|E0C0|0930|0931|0932|E03B|E0A6|E0CA|0939|E040|E0AC|E0D0)) E19B',
    'replace' => '\\1 E19C',
  ),
  823 => 
  array (
    'match' => '((0919|E02B|E08F|E0B3|091F|E030|E095|E0B9|0922|095D|E098|E0BC|0926|E035|E09C|E0C0|0930|0931|0932|E03B|E0A6|E0CA|0939|E040|E0AC|E0D0)) E1B5',
    'replace' => '\\1 E1B6',
  ),
  824 => 
  array (
    'match' => '((0919|E02B|E08F|E0B3|091F|E030|E095|E0B9|0922|095D|E098|E0BC|0926|E035|E09C|E0C0|0930|0931|0932|E03B|E0A6|E0CA|0939|E040|E0AC|E0D0)) E1BB',
    'replace' => '\\1 E1BC',
  ),
  825 => 
  array (
    'match' => '((091B|E02D|E091|E0B5|0920|E031|E096|E0BA)) 0940',
    'replace' => '\\1 E195',
  ),
  826 => 
  array (
    'match' => '((091B|E02D|E091|E0B5|0920|E031|E096|E0BA)) E19B',
    'replace' => '\\1 E19D',
  ),
  827 => 
  array (
    'match' => '((091B|E02D|E091|E0B5|0920|E031|E096|E0BA)) E1B5',
    'replace' => '\\1 E1B7',
  ),
  828 => 
  array (
    'match' => '((091B|E02D|E091|E0B5|0920|E031|E096|E0BA)) E1BB',
    'replace' => '\\1 E1BD',
  ),
  829 => 
  array (
    'match' => '((0915|0958|E08B|E0AF|092B|095E|E0A0|E0C4)) 0940',
    'replace' => '\\1 E198',
  ),
  830 => 
  array (
    'match' => '((0915|0958|E08B|E0AF|092B|095E|E0A0|E0C4)) E19B',
    'replace' => '\\1 E1A0',
  ),
  831 => 
  array (
    'match' => '((0915|0958|E08B|E0AF|092B|095E|E0A0|E0C4)) E1B5',
    'replace' => '\\1 E1BA',
  ),
  832 => 
  array (
    'match' => '((0915|0958|E08B|E0AF|092B|095E|E0A0|E0C4)) E1BB',
    'replace' => '\\1 E1C0',
  ),
);
?>
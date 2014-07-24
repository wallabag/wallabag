<?php
	$volt = array (
  0 => 
  array (
    'match' => '0995 09CD 09B7',
    'replace' => 'E002',
  ),
  1 => 
  array (
    'match' => '099C 09CD 099E',
    'replace' => 'E003',
  ),
  2 => 
  array (
    'match' => '09CD 200D',
    'replace' => '007E',
  ),
  3 => 
  array (
    'match' => '09CD 200C',
    'replace' => '200C',
  ),
  4 => 
  array (
    'match' => '200D 09CD',
    'replace' => '00D0',
  ),
  5 => 
  array (
    'match' => '((0995|0996|0997|0998|0999|099A|099B|099C|099D|099E|099F|09A0|09A1|09A2|09A3|09A4|09A5|09A6|09A7|09A8|09AA|09AB|09AC|09AD|09AE|09AF|09B0|09B2|09B6|09B7|09B8|09B9)) 09CD 09B0',
    'replace' => '\\1 E1CD',
  ),
  6 => 
  array (
    'match' => '((0995|0996|0997|0998|0999|099A|099B|099C|099D|099E|099F|09A0|09A1|09A2|09A3|09A4|09A5|09A6|09A7|09A8|09AA|09AB|09AC|09AD|09AE|09AF|09B0|09B2|09B6|09B7|09B8|09B9)) 09B0 09CD',
    'replace' => '\\1 E068',
  ),
  7 => 
  array (
    'match' => '((09BE|09C0|09C1|09C2|09C3|09C4|09CB|09CC|09D7|09BC)) 09CD 09B0',
    'replace' => '\\1 E1CD',
  ),
  8 => 
  array (
    'match' => '((09BE|09C0|09C1|09C2|09C3|09C4|09CB|09CC|09D7|09BC)) 09B0 09CD',
    'replace' => '\\1 E068',
  ),
  9 => 
  array (
    'match' => '(0020) 09CD 09B0',
    'replace' => '\\1 E1CD',
  ),
  10 => 
  array (
    'match' => '(0020) 09B0 09CD',
    'replace' => '\\1 E068',
  ),
  11 => 
  array (
    'match' => '(25CC) 09CD 09B0',
    'replace' => '\\1 E1CD',
  ),
  12 => 
  array (
    'match' => '(25CC) 09B0 09CD',
    'replace' => '\\1 E068',
  ),
  13 => 
  array (
    'match' => '((09B0|E042|E043|E044|E048|E049|E04E|E04F|E050|E051|E052|E053|E054|E056|E057|E058|E059|E05B|E05C|E05D|E05E|E062|E063|E064|E065|E0A8|E0BC|E0EF|E0FD|E101|E11C|E11E|E14F|E151|E152|E164|E17D|E18E|E190)) 09C1',
    'replace' => '\\1 E03C',
  ),
  14 => 
  array (
    'match' => '((09B0|E042|E043|E044|E048|E049|E04E|E04F|E050|E051|E052|E053|E054|E056|E057|E058|E059|E05B|E05C|E05D|E05E|E062|E063|E064|E065|E0A8|E0BC|E0EF|E0FD|E101|E11C|E11E|E14F|E151|E152|E164|E17D|E18E|E190)) 09C2',
    'replace' => '\\1 E03E',
  ),
  15 => 
  array (
    'match' => '((E045|E046|E047|E04A|E04B|E04C|E04D|E05F|E060|E061|E07C|E07D|E0B1|E0E8|E0E9|E11A|E11B|E163|E17B|E18D)) 09C1',
    'replace' => '\\1 E03D',
  ),
  16 => 
  array (
    'match' => 'E068 0981',
    'replace' => 'E069',
  ),
  17 => 
  array (
    'match' => '0995 09CD 0995',
    'replace' => 'E06A',
  ),
  18 => 
  array (
    'match' => '0995 09CD 0996',
    'replace' => 'E06B',
  ),
  19 => 
  array (
    'match' => '0995 09CD 099A',
    'replace' => 'E06C',
  ),
  20 => 
  array (
    'match' => '0995 09CD 099B',
    'replace' => 'E06D',
  ),
  21 => 
  array (
    'match' => '0995 09CD 099F',
    'replace' => 'E06E',
  ),
  22 => 
  array (
    'match' => '0995 09CD 09A0',
    'replace' => 'E06F',
  ),
  23 => 
  array (
    'match' => '0995 09CD 09A3',
    'replace' => 'E070',
  ),
  24 => 
  array (
    'match' => '0995 09CD 09A4',
    'replace' => 'E071',
  ),
  25 => 
  array (
    'match' => '0995 09CD 09A5',
    'replace' => 'E072',
  ),
  26 => 
  array (
    'match' => '0995 09CD 09A8',
    'replace' => 'E073',
  ),
  27 => 
  array (
    'match' => '0995 09CD 09AA',
    'replace' => 'E074',
  ),
  28 => 
  array (
    'match' => '0995 09CD 09AB',
    'replace' => 'E075',
  ),
  29 => 
  array (
    'match' => '0995 09CD 09AE',
    'replace' => 'E076',
  ),
  30 => 
  array (
    'match' => '0995 09CD 09B2',
    'replace' => 'E077',
  ),
  31 => 
  array (
    'match' => '0995 09CD 09AC',
    'replace' => 'E078',
  ),
  32 => 
  array (
    'match' => '0995 09CD 09B6',
    'replace' => 'E079',
  ),
  33 => 
  array (
    'match' => '0995 09CD 09B8',
    'replace' => 'E07A',
  ),
  34 => 
  array (
    'match' => '0996 09CD 0996',
    'replace' => 'E083',
  ),
  35 => 
  array (
    'match' => '0996 09CD 09A4',
    'replace' => 'E084',
  ),
  36 => 
  array (
    'match' => '0996 09CD 09A8',
    'replace' => 'E085',
  ),
  37 => 
  array (
    'match' => '0996 09CD 09AE',
    'replace' => 'E086',
  ),
  38 => 
  array (
    'match' => '0996 09CD 09AC',
    'replace' => 'E087',
  ),
  39 => 
  array (
    'match' => '0997 09CD 0997',
    'replace' => 'E088',
  ),
  40 => 
  array (
    'match' => '0997 09CD 0998',
    'replace' => 'E089',
  ),
  41 => 
  array (
    'match' => '0997 09CD 099C',
    'replace' => 'E08A',
  ),
  42 => 
  array (
    'match' => '0997 09CD 099D',
    'replace' => 'E08B',
  ),
  43 => 
  array (
    'match' => '0997 09CD 09A1',
    'replace' => 'E08C',
  ),
  44 => 
  array (
    'match' => '0997 09CD 09A2',
    'replace' => 'E08D',
  ),
  45 => 
  array (
    'match' => '0997 09CD 09A3',
    'replace' => 'E08E',
  ),
  46 => 
  array (
    'match' => '0997 09CD 09A6',
    'replace' => 'E08F',
  ),
  47 => 
  array (
    'match' => '0997 09CD 09A7',
    'replace' => 'E090',
  ),
  48 => 
  array (
    'match' => '0997 09CD 09A8',
    'replace' => 'E091',
  ),
  49 => 
  array (
    'match' => '0997 09CD 09AB',
    'replace' => 'E092',
  ),
  50 => 
  array (
    'match' => '0997 09CD 09AC',
    'replace' => 'E093',
  ),
  51 => 
  array (
    'match' => '0997 09CD 09AD',
    'replace' => 'E094',
  ),
  52 => 
  array (
    'match' => '0997 09CD 09AE',
    'replace' => 'E095',
  ),
  53 => 
  array (
    'match' => '0997 09CD 09B2',
    'replace' => 'E096',
  ),
  54 => 
  array (
    'match' => '0998 09CD 09A8',
    'replace' => 'E099',
  ),
  55 => 
  array (
    'match' => '0998 09CD 09AE',
    'replace' => 'E09A',
  ),
  56 => 
  array (
    'match' => '0998 09CD 09AC',
    'replace' => 'E09B',
  ),
  57 => 
  array (
    'match' => '0999 09CD 0995',
    'replace' => 'E09C',
  ),
  58 => 
  array (
    'match' => '0999 09CD 0996',
    'replace' => 'E09D',
  ),
  59 => 
  array (
    'match' => '0999 09CD 0997',
    'replace' => 'E09E',
  ),
  60 => 
  array (
    'match' => '0999 09CD 0998',
    'replace' => 'E09F',
  ),
  61 => 
  array (
    'match' => '0999 09CD 09A8',
    'replace' => 'E0A0',
  ),
  62 => 
  array (
    'match' => '0999 09CD 09AD',
    'replace' => 'E0A1',
  ),
  63 => 
  array (
    'match' => '0999 09CD 09AE',
    'replace' => 'E0A2',
  ),
  64 => 
  array (
    'match' => '0999 09CD 09AC',
    'replace' => 'E0A3',
  ),
  65 => 
  array (
    'match' => '0999 09CD 09B6',
    'replace' => 'E0A4',
  ),
  66 => 
  array (
    'match' => '0999 09CD 09B7',
    'replace' => 'E0A5',
  ),
  67 => 
  array (
    'match' => '0999 09CD 09B9',
    'replace' => 'E0A6',
  ),
  68 => 
  array (
    'match' => '099A 09CD 099A',
    'replace' => 'E0AC',
  ),
  69 => 
  array (
    'match' => '099A 09CD 099B',
    'replace' => 'E0AD',
  ),
  70 => 
  array (
    'match' => '099A 09CD 099E',
    'replace' => 'E0AE',
  ),
  71 => 
  array (
    'match' => '099A 09CD 09AE',
    'replace' => 'E0AF',
  ),
  72 => 
  array (
    'match' => '099A 09CD 09AC',
    'replace' => 'E0B0',
  ),
  73 => 
  array (
    'match' => '099B 09CD 099B',
    'replace' => 'E0B4',
  ),
  74 => 
  array (
    'match' => '099B 09CD 09B2',
    'replace' => 'E0B5',
  ),
  75 => 
  array (
    'match' => '099B 09CD 09AC',
    'replace' => 'E0B6',
  ),
  76 => 
  array (
    'match' => '099C 09CD 099C',
    'replace' => 'E0B7',
  ),
  77 => 
  array (
    'match' => '099C 09CD 099D',
    'replace' => 'E0B8',
  ),
  78 => 
  array (
    'match' => '099C 09CD 09A6',
    'replace' => 'E0B9',
  ),
  79 => 
  array (
    'match' => '099C 09CD 09AC',
    'replace' => 'E0BA',
  ),
  80 => 
  array (
    'match' => '099C 09CD 09AE',
    'replace' => 'E0BB',
  ),
  81 => 
  array (
    'match' => '099D 09CD 099D',
    'replace' => 'E0BE',
  ),
  82 => 
  array (
    'match' => '099D 09CD 09AE',
    'replace' => 'E0BF',
  ),
  83 => 
  array (
    'match' => '099D 09CD 09AC',
    'replace' => 'E0C0',
  ),
  84 => 
  array (
    'match' => '099E 09CD 099A',
    'replace' => 'E0C1',
  ),
  85 => 
  array (
    'match' => '099E 09CD 099B',
    'replace' => 'E0C2',
  ),
  86 => 
  array (
    'match' => '099E 09CD 099C',
    'replace' => 'E0C3',
  ),
  87 => 
  array (
    'match' => '099E 09CD 099D',
    'replace' => 'E0C4',
  ),
  88 => 
  array (
    'match' => '099E 09CD 09B6',
    'replace' => 'E0C5',
  ),
  89 => 
  array (
    'match' => '099F 09CD 0995',
    'replace' => 'E0C6',
  ),
  90 => 
  array (
    'match' => '099F 09CD 0996',
    'replace' => 'E0C7',
  ),
  91 => 
  array (
    'match' => '099F 09CD 099A',
    'replace' => 'E0C8',
  ),
  92 => 
  array (
    'match' => '099F 09CD 099B',
    'replace' => 'E0C9',
  ),
  93 => 
  array (
    'match' => '099F 09CD 099F',
    'replace' => 'E0CA',
  ),
  94 => 
  array (
    'match' => '099F 09CD 09A0',
    'replace' => 'E0CB',
  ),
  95 => 
  array (
    'match' => '099F 09CD 09A4',
    'replace' => 'E0CC',
  ),
  96 => 
  array (
    'match' => '099F 09CD 09A5',
    'replace' => 'E0CD',
  ),
  97 => 
  array (
    'match' => '099F 09CD 09AA',
    'replace' => 'E0CE',
  ),
  98 => 
  array (
    'match' => '099F 09CD 09AB',
    'replace' => 'E0CF',
  ),
  99 => 
  array (
    'match' => '099F 09CD 09AC',
    'replace' => 'E0D0',
  ),
  100 => 
  array (
    'match' => '099F 09CD 09AE',
    'replace' => 'E0D1',
  ),
  101 => 
  array (
    'match' => '099F 09CD 09B6',
    'replace' => 'E0D2',
  ),
  102 => 
  array (
    'match' => '099F 09CD 09B7',
    'replace' => 'E0D3',
  ),
  103 => 
  array (
    'match' => '099F 09CD 09B8',
    'replace' => 'E0D4',
  ),
  104 => 
  array (
    'match' => '09A0 09CD 09A0',
    'replace' => 'E0D5',
  ),
  105 => 
  array (
    'match' => '09A0 09CD 09A3',
    'replace' => 'E0D6',
  ),
  106 => 
  array (
    'match' => '09A0 09CD 09AC',
    'replace' => 'E0D7',
  ),
  107 => 
  array (
    'match' => '09A1 09CD 0997',
    'replace' => 'E0D8',
  ),
  108 => 
  array (
    'match' => '09A1 09CD 09A1',
    'replace' => 'E0D9',
  ),
  109 => 
  array (
    'match' => '09A1 09CD 09A2',
    'replace' => 'E0DA',
  ),
  110 => 
  array (
    'match' => '09A1 09CD 09AE',
    'replace' => 'E0DB',
  ),
  111 => 
  array (
    'match' => '09A1 09CD 09AC',
    'replace' => 'E0DC',
  ),
  112 => 
  array (
    'match' => '09A2 09CD 09A2',
    'replace' => 'E0DD',
  ),
  113 => 
  array (
    'match' => '09A2 09CD 09A3',
    'replace' => 'E0DE',
  ),
  114 => 
  array (
    'match' => '09A2 09CD 09AC',
    'replace' => 'E0DF',
  ),
  115 => 
  array (
    'match' => '09A3 09CD 099F',
    'replace' => 'E0E0',
  ),
  116 => 
  array (
    'match' => '09A3 09CD 09A0',
    'replace' => 'E0E1',
  ),
  117 => 
  array (
    'match' => '09A3 09CD 09A1',
    'replace' => 'E0E2',
  ),
  118 => 
  array (
    'match' => '09A3 09CD 09A2',
    'replace' => 'E0E3',
  ),
  119 => 
  array (
    'match' => '09A3 09CD 09A3',
    'replace' => 'E0E4',
  ),
  120 => 
  array (
    'match' => '09A3 09CD 09AE',
    'replace' => 'E0E5',
  ),
  121 => 
  array (
    'match' => '09A3 09CD 09AC',
    'replace' => 'E0E6',
  ),
  122 => 
  array (
    'match' => '09A3 09CD 09B8',
    'replace' => 'E0E7',
  ),
  123 => 
  array (
    'match' => '09A4 09CD 09A4',
    'replace' => 'E0EA',
  ),
  124 => 
  array (
    'match' => '09A4 09CD 09A5',
    'replace' => 'E0EB',
  ),
  125 => 
  array (
    'match' => '09A4 09CD 09A8',
    'replace' => 'E0EC',
  ),
  126 => 
  array (
    'match' => '09A4 09CD 09AE',
    'replace' => 'E0ED',
  ),
  127 => 
  array (
    'match' => '09A4 09CD 09AC',
    'replace' => 'E0EE',
  ),
  128 => 
  array (
    'match' => '09A5 09CD 09A5',
    'replace' => 'E0F1',
  ),
  129 => 
  array (
    'match' => '09A5 09CD 09A8',
    'replace' => 'E0F2',
  ),
  130 => 
  array (
    'match' => '09A5 09CD 09AC',
    'replace' => 'E0F3',
  ),
  131 => 
  array (
    'match' => '09A6 09CD 0997',
    'replace' => 'E0F4',
  ),
  132 => 
  array (
    'match' => '09A6 09CD 0998',
    'replace' => 'E0F5',
  ),
  133 => 
  array (
    'match' => '09A6 09CD 09A6',
    'replace' => 'E0F6',
  ),
  134 => 
  array (
    'match' => '09A6 09CD 09A7',
    'replace' => 'E0F7',
  ),
  135 => 
  array (
    'match' => '09A6 09CD 09A8',
    'replace' => 'E0F8',
  ),
  136 => 
  array (
    'match' => '09A6 09CD 09AC',
    'replace' => 'E0F9',
  ),
  137 => 
  array (
    'match' => '09A6 09CD 09AE',
    'replace' => 'E0FB',
  ),
  138 => 
  array (
    'match' => '09A6 09CD 09AF',
    'replace' => 'E0FC',
  ),
  139 => 
  array (
    'match' => '09A7 09CD 09A7',
    'replace' => 'E102',
  ),
  140 => 
  array (
    'match' => '09A7 09CD 09A8',
    'replace' => 'E103',
  ),
  141 => 
  array (
    'match' => '09A7 09CD 09AE',
    'replace' => 'E104',
  ),
  142 => 
  array (
    'match' => '09A7 09CD 09AC',
    'replace' => 'E105',
  ),
  143 => 
  array (
    'match' => '09A8 09CD 0995',
    'replace' => 'E106',
  ),
  144 => 
  array (
    'match' => '09A8 09CD 0997',
    'replace' => 'E107',
  ),
  145 => 
  array (
    'match' => '09A8 09CD 099A',
    'replace' => 'E108',
  ),
  146 => 
  array (
    'match' => '09A8 09CD 099C',
    'replace' => 'E109',
  ),
  147 => 
  array (
    'match' => '09A8 09CD 099F',
    'replace' => 'E10A',
  ),
  148 => 
  array (
    'match' => '09A8 09CD 09A0',
    'replace' => 'E10B',
  ),
  149 => 
  array (
    'match' => '09A8 09CD 09A1',
    'replace' => 'E10C',
  ),
  150 => 
  array (
    'match' => '09A8 09CD 09A4',
    'replace' => 'E10D',
  ),
  151 => 
  array (
    'match' => '09A8 09CD 09A5',
    'replace' => 'E10E',
  ),
  152 => 
  array (
    'match' => '09A8 09CD 09A6',
    'replace' => 'E10F',
  ),
  153 => 
  array (
    'match' => '09A8 09CD 09A7',
    'replace' => 'E110',
  ),
  154 => 
  array (
    'match' => '09A8 09CD 09A8',
    'replace' => 'E111',
  ),
  155 => 
  array (
    'match' => '09A8 09CD 09AB',
    'replace' => 'E112',
  ),
  156 => 
  array (
    'match' => '09A8 09CD 09AD',
    'replace' => 'E113',
  ),
  157 => 
  array (
    'match' => '09A8 09CD 09AE',
    'replace' => 'E114',
  ),
  158 => 
  array (
    'match' => '09A8 09CD 09AF',
    'replace' => 'E115',
  ),
  159 => 
  array (
    'match' => '09A8 09CD 09AC',
    'replace' => 'E116',
  ),
  160 => 
  array (
    'match' => '09A8 09CD 09B6',
    'replace' => 'E117',
  ),
  161 => 
  array (
    'match' => '09A8 09CD 09B7',
    'replace' => 'E118',
  ),
  162 => 
  array (
    'match' => '09A8 09CD 09B8',
    'replace' => 'E119',
  ),
  163 => 
  array (
    'match' => '09AA 09CD 0995',
    'replace' => 'E122',
  ),
  164 => 
  array (
    'match' => '09AA 09CD 0996',
    'replace' => 'E123',
  ),
  165 => 
  array (
    'match' => '09AA 09CD 099A',
    'replace' => 'E124',
  ),
  166 => 
  array (
    'match' => '09AA 09CD 099B',
    'replace' => 'E125',
  ),
  167 => 
  array (
    'match' => '09AA 09CD 099F',
    'replace' => 'E126',
  ),
  168 => 
  array (
    'match' => '09AA 09CD 09A0',
    'replace' => 'E127',
  ),
  169 => 
  array (
    'match' => '09AA 09CD 09A4',
    'replace' => 'E128',
  ),
  170 => 
  array (
    'match' => '09AA 09CD 09A8',
    'replace' => 'E129',
  ),
  171 => 
  array (
    'match' => '09AA 09CD 09AA',
    'replace' => 'E12A',
  ),
  172 => 
  array (
    'match' => '09AA 09CD 09AB',
    'replace' => 'E12B',
  ),
  173 => 
  array (
    'match' => '09AA 09CD 09AE',
    'replace' => 'E12C',
  ),
  174 => 
  array (
    'match' => '09AA 09CD 09B2',
    'replace' => 'E12D',
  ),
  175 => 
  array (
    'match' => '09AA 09CD 09AC',
    'replace' => 'E12E',
  ),
  176 => 
  array (
    'match' => '09AA 09CD 09B6',
    'replace' => 'E12F',
  ),
  177 => 
  array (
    'match' => '09AA 09CD 09B7',
    'replace' => 'E130',
  ),
  178 => 
  array (
    'match' => '09AA 09CD 09B8',
    'replace' => 'E131',
  ),
  179 => 
  array (
    'match' => '09AB 09CD 099F',
    'replace' => 'E132',
  ),
  180 => 
  array (
    'match' => '09AB 09CD 09A4',
    'replace' => 'E133',
  ),
  181 => 
  array (
    'match' => '09AB 09CD 09AA',
    'replace' => 'E134',
  ),
  182 => 
  array (
    'match' => '09AB 09CD 09AB',
    'replace' => 'E135',
  ),
  183 => 
  array (
    'match' => '09AB 09CD 09B2',
    'replace' => 'E136',
  ),
  184 => 
  array (
    'match' => '09AC 09CD 099C',
    'replace' => 'E137',
  ),
  185 => 
  array (
    'match' => '09AC 09CD 099D',
    'replace' => 'E138',
  ),
  186 => 
  array (
    'match' => '09AC 09CD 09A1',
    'replace' => 'E139',
  ),
  187 => 
  array (
    'match' => '09AC 09CD 09A2',
    'replace' => 'E13A',
  ),
  188 => 
  array (
    'match' => '09AC 09CD 09A6',
    'replace' => 'E13B',
  ),
  189 => 
  array (
    'match' => '09AC 09CD 09A7',
    'replace' => 'E13C',
  ),
  190 => 
  array (
    'match' => '09AC 09CD 09A8',
    'replace' => 'E13D',
  ),
  191 => 
  array (
    'match' => '09AC 09CD 09B2',
    'replace' => 'E13E',
  ),
  192 => 
  array (
    'match' => '09AC 09CD 09AC',
    'replace' => 'E13F',
  ),
  193 => 
  array (
    'match' => '09AD 09CD 09A3',
    'replace' => 'E141',
  ),
  194 => 
  array (
    'match' => '09AD 09CD 09A8',
    'replace' => 'E142',
  ),
  195 => 
  array (
    'match' => '09AD 09CD 09AD',
    'replace' => 'E143',
  ),
  196 => 
  array (
    'match' => '09AD 09CD 09AE',
    'replace' => 'E144',
  ),
  197 => 
  array (
    'match' => '09AD 09CD 09B2',
    'replace' => 'E145',
  ),
  198 => 
  array (
    'match' => '09AD 09CD 09AC',
    'replace' => 'E146',
  ),
  199 => 
  array (
    'match' => '09AE 09CD 09A3',
    'replace' => 'E147',
  ),
  200 => 
  array (
    'match' => '09AE 09CD 09A8',
    'replace' => 'E148',
  ),
  201 => 
  array (
    'match' => '09AE 09CD 09AA',
    'replace' => 'E149',
  ),
  202 => 
  array (
    'match' => '09AE 09CD 09AB',
    'replace' => 'E14A',
  ),
  203 => 
  array (
    'match' => '09AE 09CD 09AC',
    'replace' => 'E14B',
  ),
  204 => 
  array (
    'match' => '09AE 09CD 09AD',
    'replace' => 'E14C',
  ),
  205 => 
  array (
    'match' => '09AE 09CD 09AE',
    'replace' => 'E14D',
  ),
  206 => 
  array (
    'match' => '09AE 09CD 09B2',
    'replace' => 'E14E',
  ),
  207 => 
  array (
    'match' => '09B2 09CD 0995',
    'replace' => 'E153',
  ),
  208 => 
  array (
    'match' => '09B2 09CD 0996',
    'replace' => 'E154',
  ),
  209 => 
  array (
    'match' => '09B2 09CD 0997',
    'replace' => 'E155',
  ),
  210 => 
  array (
    'match' => '09B2 09CD 099A',
    'replace' => 'E156',
  ),
  211 => 
  array (
    'match' => '09B2 09CD 099C',
    'replace' => 'E157',
  ),
  212 => 
  array (
    'match' => '09B2 09CD 099F',
    'replace' => 'E158',
  ),
  213 => 
  array (
    'match' => '09B2 09CD 09A1',
    'replace' => 'E159',
  ),
  214 => 
  array (
    'match' => '09B2 09CD 09A6',
    'replace' => 'E15A',
  ),
  215 => 
  array (
    'match' => '09B2 09CD 09AA',
    'replace' => 'E15B',
  ),
  216 => 
  array (
    'match' => '09B2 09CD 09AB',
    'replace' => 'E15C',
  ),
  217 => 
  array (
    'match' => '09B2 09CD 09AC',
    'replace' => 'E15D',
  ),
  218 => 
  array (
    'match' => '09B2 09CD 09AE',
    'replace' => 'E15E',
  ),
  219 => 
  array (
    'match' => '09B2 09CD 09B2',
    'replace' => 'E15F',
  ),
  220 => 
  array (
    'match' => '09B2 09CD 09B6',
    'replace' => 'E160',
  ),
  221 => 
  array (
    'match' => '09B2 09CD 09B8',
    'replace' => 'E161',
  ),
  222 => 
  array (
    'match' => '09B2 09CD 09B9',
    'replace' => 'E162',
  ),
  223 => 
  array (
    'match' => '09B6 09CD 0995',
    'replace' => 'E166',
  ),
  224 => 
  array (
    'match' => '09B6 09CD 099A',
    'replace' => 'E167',
  ),
  225 => 
  array (
    'match' => '09B6 09CD 099B',
    'replace' => 'E168',
  ),
  226 => 
  array (
    'match' => '09B6 09CD 09A4',
    'replace' => 'E169',
  ),
  227 => 
  array (
    'match' => '09B6 09CD 09A8',
    'replace' => 'E16A',
  ),
  228 => 
  array (
    'match' => '09B6 09CD 09AA',
    'replace' => 'E16B',
  ),
  229 => 
  array (
    'match' => '09B6 09CD 09AE',
    'replace' => 'E16C',
  ),
  230 => 
  array (
    'match' => '09B6 09CD 09AF',
    'replace' => 'E16D',
  ),
  231 => 
  array (
    'match' => '09B6 09CD 09B2',
    'replace' => 'E16E',
  ),
  232 => 
  array (
    'match' => '09B6 09CD 09AC',
    'replace' => 'E16F',
  ),
  233 => 
  array (
    'match' => '09B6 09CD 09B6',
    'replace' => 'E170',
  ),
  234 => 
  array (
    'match' => '09B7 09CD 0995',
    'replace' => 'E171',
  ),
  235 => 
  array (
    'match' => '09B7 09CD 099F',
    'replace' => 'E172',
  ),
  236 => 
  array (
    'match' => '09B7 09CD 09A0',
    'replace' => 'E173',
  ),
  237 => 
  array (
    'match' => '09B7 09CD 09A3',
    'replace' => 'E174',
  ),
  238 => 
  array (
    'match' => '09B7 09CD 09AA',
    'replace' => 'E175',
  ),
  239 => 
  array (
    'match' => '09B7 09CD 09AB',
    'replace' => 'E176',
  ),
  240 => 
  array (
    'match' => '09B7 09CD 09AE',
    'replace' => 'E177',
  ),
  241 => 
  array (
    'match' => '09B7 09CD 09AF',
    'replace' => 'E178',
  ),
  242 => 
  array (
    'match' => '09B7 09CD 09AC',
    'replace' => 'E179',
  ),
  243 => 
  array (
    'match' => '09B8 09CD 0995',
    'replace' => 'E17E',
  ),
  244 => 
  array (
    'match' => '09B8 09CD 0996',
    'replace' => 'E17F',
  ),
  245 => 
  array (
    'match' => '09B8 09CD 099C',
    'replace' => 'E180',
  ),
  246 => 
  array (
    'match' => '09B8 09CD 099F',
    'replace' => 'E181',
  ),
  247 => 
  array (
    'match' => '09B8 09CD 09A4',
    'replace' => 'E182',
  ),
  248 => 
  array (
    'match' => '09B8 09CD 09A5',
    'replace' => 'E183',
  ),
  249 => 
  array (
    'match' => '09B8 09CD 09A8',
    'replace' => 'E184',
  ),
  250 => 
  array (
    'match' => '09B8 09CD 09AA',
    'replace' => 'E185',
  ),
  251 => 
  array (
    'match' => '09B8 09CD 09AB',
    'replace' => 'E186',
  ),
  252 => 
  array (
    'match' => '09B8 09CD 09AE',
    'replace' => 'E187',
  ),
  253 => 
  array (
    'match' => '09B8 09CD 09AF',
    'replace' => 'E188',
  ),
  254 => 
  array (
    'match' => '09B8 09CD 09B2',
    'replace' => 'E189',
  ),
  255 => 
  array (
    'match' => '09B8 09CD 09AC',
    'replace' => 'E18A',
  ),
  256 => 
  array (
    'match' => '09B8 09CD 09B8',
    'replace' => 'E18B',
  ),
  257 => 
  array (
    'match' => '09B9 09CD 09A3',
    'replace' => 'E192',
  ),
  258 => 
  array (
    'match' => '09B9 09CD 09A8',
    'replace' => 'E193',
  ),
  259 => 
  array (
    'match' => '09B9 09CD 09AE',
    'replace' => 'E194',
  ),
  260 => 
  array (
    'match' => '09B9 09CD 09AF',
    'replace' => 'E195',
  ),
  261 => 
  array (
    'match' => '09B9 09CD 09B2',
    'replace' => 'E196',
  ),
  262 => 
  array (
    'match' => '09B9 09CD 09AC',
    'replace' => 'E197',
  ),
  263 => 
  array (
    'match' => '09DC 09CD 0997',
    'replace' => 'E198',
  ),
  264 => 
  array (
    'match' => '09DC 09CD 099C',
    'replace' => 'E199',
  ),
  265 => 
  array (
    'match' => '09DC 09CD 09A7',
    'replace' => 'E19A',
  ),
  266 => 
  array (
    'match' => '0995 E1CD',
    'replace' => 'E041',
  ),
  267 => 
  array (
    'match' => '0996 E1CD',
    'replace' => 'E042',
  ),
  268 => 
  array (
    'match' => '0997 E1CD',
    'replace' => 'E043',
  ),
  269 => 
  array (
    'match' => '0998 E1CD',
    'replace' => 'E044',
  ),
  270 => 
  array (
    'match' => '0999 E1CD',
    'replace' => 'E045',
  ),
  271 => 
  array (
    'match' => '099A E1CD',
    'replace' => 'E046',
  ),
  272 => 
  array (
    'match' => '099B E1CD',
    'replace' => 'E047',
  ),
  273 => 
  array (
    'match' => '099C E1CD',
    'replace' => 'E048',
  ),
  274 => 
  array (
    'match' => '099D E1CD',
    'replace' => 'E049',
  ),
  275 => 
  array (
    'match' => '099F E1CD',
    'replace' => 'E04A',
  ),
  276 => 
  array (
    'match' => '09A0 E1CD',
    'replace' => 'E04B',
  ),
  277 => 
  array (
    'match' => '09A1 E1CD',
    'replace' => 'E04C',
  ),
  278 => 
  array (
    'match' => '09A2 E1CD',
    'replace' => 'E04D',
  ),
  279 => 
  array (
    'match' => '09A3 E1CD',
    'replace' => 'E04E',
  ),
  280 => 
  array (
    'match' => '09A4 E1CD',
    'replace' => 'E04F',
  ),
  281 => 
  array (
    'match' => '09A5 E1CD',
    'replace' => 'E050',
  ),
  282 => 
  array (
    'match' => '09A6 E1CD',
    'replace' => 'E051',
  ),
  283 => 
  array (
    'match' => '09A7 E1CD',
    'replace' => 'E052',
  ),
  284 => 
  array (
    'match' => '09A8 E1CD',
    'replace' => 'E053',
  ),
  285 => 
  array (
    'match' => '09AA E1CD',
    'replace' => 'E054',
  ),
  286 => 
  array (
    'match' => '09AB E1CD',
    'replace' => 'E055',
  ),
  287 => 
  array (
    'match' => '09AC E1CD',
    'replace' => 'E056',
  ),
  288 => 
  array (
    'match' => '09AD E1CD',
    'replace' => 'E057',
  ),
  289 => 
  array (
    'match' => '09AE E1CD',
    'replace' => 'E058',
  ),
  290 => 
  array (
    'match' => '09AF E1CD',
    'replace' => 'E059',
  ),
  291 => 
  array (
    'match' => '09B0 E1CD',
    'replace' => 'E05A',
  ),
  292 => 
  array (
    'match' => '09B2 E1CD',
    'replace' => 'E05B',
  ),
  293 => 
  array (
    'match' => '09B6 E1CD',
    'replace' => 'E05C',
  ),
  294 => 
  array (
    'match' => '09B7 E1CD',
    'replace' => 'E05D',
  ),
  295 => 
  array (
    'match' => '09B8 E1CD',
    'replace' => 'E05E',
  ),
  296 => 
  array (
    'match' => '09B9 E1CD',
    'replace' => 'E05F',
  ),
  297 => 
  array (
    'match' => '09DC E1CD',
    'replace' => 'E060',
  ),
  298 => 
  array (
    'match' => '09DD E1CD',
    'replace' => 'E061',
  ),
  299 => 
  array (
    'match' => '09DF E1CD',
    'replace' => 'E062',
  ),
  300 => 
  array (
    'match' => '00D0 09B0',
    'replace' => 'E1CD',
  ),
  301 => 
  array (
    'match' => 'E06A E1CD',
    'replace' => 'E07B',
  ),
  302 => 
  array (
    'match' => 'E06E E1CD',
    'replace' => 'E07C',
  ),
  303 => 
  array (
    'match' => 'E071 E1CD',
    'replace' => 'E07D',
  ),
  304 => 
  array (
    'match' => 'E071 09CD 09AC',
    'replace' => 'E07E',
  ),
  305 => 
  array (
    'match' => 'E002 09CD 09A3',
    'replace' => 'E07F',
  ),
  306 => 
  array (
    'match' => 'E002 09CD 09AE',
    'replace' => 'E080',
  ),
  307 => 
  array (
    'match' => 'E002 E1CD',
    'replace' => 'E081',
  ),
  308 => 
  array (
    'match' => 'E002 09CD 09AC',
    'replace' => 'E082',
  ),
  309 => 
  array (
    'match' => 'E090 E1CD',
    'replace' => 'E097',
  ),
  310 => 
  array (
    'match' => 'E090 09CD 09AC',
    'replace' => 'E098',
  ),
  311 => 
  array (
    'match' => 'E09C E1CD',
    'replace' => 'E0A7',
  ),
  312 => 
  array (
    'match' => 'E09F E1CD',
    'replace' => 'E0A8',
  ),
  313 => 
  array (
    'match' => '0999 09CD E002',
    'replace' => 'E0A9',
  ),
  314 => 
  array (
    'match' => 'E0AD E1CD',
    'replace' => 'E0B1',
  ),
  315 => 
  array (
    'match' => 'E0AD 09CD 09B2',
    'replace' => 'E0B2',
  ),
  316 => 
  array (
    'match' => 'E0AD 09CD 09AC',
    'replace' => 'E0B3',
  ),
  317 => 
  array (
    'match' => 'E0B7 E1CD',
    'replace' => 'E0BC',
  ),
  318 => 
  array (
    'match' => 'E0B7 09CD 09AC',
    'replace' => 'E0BD',
  ),
  319 => 
  array (
    'match' => 'E0E0 E1CD',
    'replace' => 'E0E8',
  ),
  320 => 
  array (
    'match' => 'E0E2 E1CD',
    'replace' => 'E0E9',
  ),
  321 => 
  array (
    'match' => 'E0EA E1CD',
    'replace' => 'E0EF',
  ),
  322 => 
  array (
    'match' => 'E0EA 09CD 09AC',
    'replace' => 'E0F0',
  ),
  323 => 
  array (
    'match' => 'E0F6 E1CD',
    'replace' => 'E0FD',
  ),
  324 => 
  array (
    'match' => 'E0F6 09CD 09AC',
    'replace' => 'E0FE',
  ),
  325 => 
  array (
    'match' => 'E0F7 E1CD',
    'replace' => 'E0FF',
  ),
  326 => 
  array (
    'match' => 'E0F7 09CD 09AC',
    'replace' => 'E100',
  ),
  327 => 
  array (
    'match' => 'E0FA E1CD',
    'replace' => 'E101',
  ),
  328 => 
  array (
    'match' => 'E10A E1CD',
    'replace' => 'E11A',
  ),
  329 => 
  array (
    'match' => 'E10C E1CD',
    'replace' => 'E11B',
  ),
  330 => 
  array (
    'match' => 'E10D E1CD',
    'replace' => 'E11C',
  ),
  331 => 
  array (
    'match' => 'E10D 09CD 09AC',
    'replace' => 'E11D',
  ),
  332 => 
  array (
    'match' => 'E10F E1CD',
    'replace' => 'E11E',
  ),
  333 => 
  array (
    'match' => 'E10F 09CD 09AC',
    'replace' => 'E11F',
  ),
  334 => 
  array (
    'match' => 'E110 E1CD',
    'replace' => 'E120',
  ),
  335 => 
  array (
    'match' => 'E110 09CD 09AC',
    'replace' => 'E121',
  ),
  336 => 
  array (
    'match' => 'E13C 09CD 09AC',
    'replace' => 'E140',
  ),
  337 => 
  array (
    'match' => 'E149 E1CD',
    'replace' => 'E14F',
  ),
  338 => 
  array (
    'match' => 'E14A E1CD',
    'replace' => 'E150',
  ),
  339 => 
  array (
    'match' => 'E14B E1CD',
    'replace' => 'E151',
  ),
  340 => 
  array (
    'match' => 'E14C E1CD',
    'replace' => 'E152',
  ),
  341 => 
  array (
    'match' => 'E158 E1CD',
    'replace' => 'E163',
  ),
  342 => 
  array (
    'match' => 'E15B E1CD',
    'replace' => 'E164',
  ),
  343 => 
  array (
    'match' => 'E161 09CD 099F',
    'replace' => 'E165',
  ),
  344 => 
  array (
    'match' => 'E171 E1CD',
    'replace' => 'E17A',
  ),
  345 => 
  array (
    'match' => 'E172 E1CD',
    'replace' => 'E17B',
  ),
  346 => 
  array (
    'match' => 'E172 09CD 09AC',
    'replace' => 'E17C',
  ),
  347 => 
  array (
    'match' => 'E175 E1CD',
    'replace' => 'E17D',
  ),
  348 => 
  array (
    'match' => 'E17E E1CD',
    'replace' => 'E18C',
  ),
  349 => 
  array (
    'match' => 'E181 E1CD',
    'replace' => 'E18D',
  ),
  350 => 
  array (
    'match' => 'E182 E1CD',
    'replace' => 'E18E',
  ),
  351 => 
  array (
    'match' => 'E182 09CD 09AC',
    'replace' => 'E18F',
  ),
  352 => 
  array (
    'match' => 'E185 E1CD',
    'replace' => 'E190',
  ),
  353 => 
  array (
    'match' => 'E185 09CD 09B2',
    'replace' => 'E191',
  ),
  354 => 
  array (
    'match' => '((0995|0996|0997|0998|0999|099A|099B|099C|099D|099F|09A0|09A1|09A2|09A3|09A4|09A5|09A6|09A7|09A8|09AA|09AB|09AC|09AD|09AE|09AF|09B0|09B2|09B6|09B7|09B8|09B9|09DC|09DD|09DF)) 09CD 09AF',
    'replace' => '\\1 E067',
  ),
  355 => 
  array (
    'match' => '((E041|E042|E043|E044|E045|E046|E047|E048|E049|E04A|E04B|E04C|E04D|E04E|E04F|E050|E051|E052|E053|E054|E055|E056|E057|E058|E059|E05A|E05B|E05C|E05D|E05E|E05F|E060|E061|E062)) 09CD 09AF',
    'replace' => '\\1 E067',
  ),
  356 => 
  array (
    'match' => '((E002|E003|E06B|E06C|E06D|E06E|E06F|E070|E071|E072|E073|E074|E075|E076|E079|E07A|E07C|E07D|E07E|E07F|E080|E081|E082|E083|E086|E089|E08A|E08B|E08C|E08D|E08F|E090|E092|E095|E097|E098|E09A|E09B|E09D|E09F|E0A2|E0A4|E0A5|E0A6|E0A7|E0A8|E0A9|E0AA|E0AB|E0AC|E0AD|E0AE|E0AF|E0B1|E0B2|E0B3|E0B4|E0B7|E0B8|E0B9|E0BB|E0BC|E0BD|E0BE|E0BF|E0C5|E0C6|E0C7|E0C8|E0C9|E0CB|E0CC|E0CD|E0CE|E0CF|E0D2|E0D3|E0D5|E0D6|E0D8|E0D9|E0DA|E0DB|E0E0|E0E1|E0E3|E0E5|E0E7|E0E8|E0ED|E0F1|E0F5|E0F6|E0FB|E0FC|E0FD|E0FE|E102|E104|E105|E106|E108|E109|E10A|E10B|E10C|E10F|E110|E112|E114|E115|E117|E118|E119|E11A|E11B|E11E|E11F|E120|E121|E122|E123|E124|E125|E126|E127|E12B|E12C|E130|E131|E132|E133|E134|E135|E136|E137|E139|E13A|E13B|E13F|E144|E149|E14A|E14D|E14F|E150|E153|E154|E156|E157|E158|E159|E15A|E15C|E161|E162|E163|E165|E166|E167|E168|E16C|E16D|E174|E175|E176|E177|E178|E17A|E17B|E17C|E17D|E17E|E17F|E180|E181|E185|E186|E187|E188|E18B|E18C|E18D|E190|E191|E193|E194|E195|E198|E199|E19A|0995|0999|099A|099B|099E|09A1|09A2|09A4|09AB|09AD|09B9|E002|E003|E06A|E073|E074|E077|E078|E07B|E07D|E07E|E07F|E081|E082|E084|E08A|E08C|E08D|E090|E092|E094|E097|E098|E09C|E0A0|E0A1|E0A6|E0A7|E0A9|E0AA|E0AB|E0AC|E0AD|E0AE|E0B1|E0B2|E0B3|E0B4|E0B5|E0B6|E0C1|E0C2|E0C3|E0C4|E0C6|E0C8|E0C9|E0CC|E0CF|E0D9|E0DA|E0DC|E0DD|E0DF|E0E2|E0E3|E0E9|E0EA|E0F0|E108|E109|E10C|E10D|E10E|E110|E112|E113|E11B|E120|E121|E122|E124|E125|E128|E12B|E133|E134|E135|E136|E137|E139|E13A|E13C|E140|E143|E14A|E14C|E153|E156|E157|E159|E15C|E162|E166|E167|E168|E169|E171|E174|E176|E17A|E17E|E180|E182|E183|E18C|E192|E193|E194|E195|E196|E197|09A6|E08F|E0B9|E0F4|E0F6|E0F7|E0F8|E0F9|E0FA|E0FD|E0DC|E0FF|E100|E101|E10F|E11E|E11F|E13B|E15A|099F|09A0|E06E|E06F|E07C|E0CA|E0CB|E0D0|E0D1|E0D4|E0D5|E0D7|E0E0|E0E1|E0E8|E10A|E10B|E11A|E126|E127|E132|E158|E163|E165|E172|E173|E17B|E17C|E181|E18D)) 09CD 09AF',
    'replace' => '\\1 E067',
  ),
  357 => 
  array (
    'match' => '(200C) 09CD 09AF',
    'replace' => '\\1 E067',
  ),
  358 => 
  array (
    'match' => 'E0A9 E1CD',
    'replace' => 'E0AA',
  ),
  359 => 
  array (
    'match' => 'E0A9 E1CD',
    'replace' => 'E0AB',
  ),
  360 => 
  array (
    'match' => '(09BF (0995|0996|0997|0998|0999|099A|099B|099C|099D|099F|09A0|09A1|09A2|09A3|09A4|09A5|09A6|09A7|09A8|09AA|09AB|09AC|09AD|09AE|09AF|09B0|09B2|09B6|09B7|09B8|09B9|09DC|09DD|09DF)) 09CD',
    'replace' => '\\1 09CD 09BF',
  ),
  361 => 
  array (
    'match' => '(09BF (0995|0996|0997|0998|0999|099A|099B|099C|099D|099F|09A0|09A1|09A2|09A3|09A4|09A5|09A6|09A7|09A8|09AA|09AB|09AC|09AD|09AE|09AF|09B0|09B2|09B6|09B7|09B8|09B9|09DC|09DD|09DF)) 007E',
    'replace' => '\\1 007E 09BF',
  ),
  362 => 
  array (
    'match' => '(09C7 (0995|0996|0997|0998|0999|099A|099B|099C|099D|099F|09A0|09A1|09A2|09A3|09A4|09A5|09A6|09A7|09A8|09AA|09AB|09AC|09AD|09AE|09AF|09B0|09B2|09B6|09B7|09B8|09B9|09DC|09DD|09DF)) 09CD',
    'replace' => '\\1 09CD 09C7',
  ),
  363 => 
  array (
    'match' => '(09C7 (0995|0996|0997|0998|0999|099A|099B|099C|099D|099F|09A0|09A1|09A2|09A3|09A4|09A5|09A6|09A7|09A8|09AA|09AB|09AC|09AD|09AE|09AF|09B0|09B2|09B6|09B7|09B8|09B9|09DC|09DD|09DF)) 007E',
    'replace' => '\\1 007E 09C7',
  ),
  364 => 
  array (
    'match' => '(09C8 (0995|0996|0997|0998|0999|099A|099B|099C|099D|099F|09A0|09A1|09A2|09A3|09A4|09A5|09A6|09A7|09A8|09AA|09AB|09AC|09AD|09AE|09AF|09B0|09B2|09B6|09B7|09B8|09B9|09DC|09DD|09DF)) 09CD',
    'replace' => '\\1 09CD 09C8',
  ),
  365 => 
  array (
    'match' => '(09C8 (0995|0996|0997|0998|0999|099A|099B|099C|099D|099F|09A0|09A1|09A2|09A3|09A4|09A5|09A6|09A7|09A8|09AA|09AB|09AC|09AD|09AE|09AF|09B0|09B2|09B6|09B7|09B8|09B9|09DC|09DD|09DF)) 007E',
    'replace' => '\\1 007E 09C8',
  ),
  366 => 
  array (
    'match' => '09BF 0995 (09CD (09BF|09C7|09C8))',
    'replace' => '0995 \\1',
  ),
  367 => 
  array (
    'match' => '09BF 0996 (09CD (09BF|09C7|09C8))',
    'replace' => '0996 \\1',
  ),
  368 => 
  array (
    'match' => '09BF 0997 (09CD (09BF|09C7|09C8))',
    'replace' => '0997 \\1',
  ),
  369 => 
  array (
    'match' => '09BF 0998 (09CD (09BF|09C7|09C8))',
    'replace' => '0998 \\1',
  ),
  370 => 
  array (
    'match' => '09BF 0999 (09CD (09BF|09C7|09C8))',
    'replace' => '0999 \\1',
  ),
  371 => 
  array (
    'match' => '09BF 099A (09CD (09BF|09C7|09C8))',
    'replace' => '099A \\1',
  ),
  372 => 
  array (
    'match' => '09BF 099B (09CD (09BF|09C7|09C8))',
    'replace' => '099B \\1',
  ),
  373 => 
  array (
    'match' => '09BF 099C (09CD (09BF|09C7|09C8))',
    'replace' => '099C \\1',
  ),
  374 => 
  array (
    'match' => '09BF 099D (09CD (09BF|09C7|09C8))',
    'replace' => '099D \\1',
  ),
  375 => 
  array (
    'match' => '09BF 099F (09CD (09BF|09C7|09C8))',
    'replace' => '099F \\1',
  ),
  376 => 
  array (
    'match' => '09BF 09A0 (09CD (09BF|09C7|09C8))',
    'replace' => '09A0 \\1',
  ),
  377 => 
  array (
    'match' => '09BF 09A1 (09CD (09BF|09C7|09C8))',
    'replace' => '09A1 \\1',
  ),
  378 => 
  array (
    'match' => '09BF 09A2 (09CD (09BF|09C7|09C8))',
    'replace' => '09A2 \\1',
  ),
  379 => 
  array (
    'match' => '09BF 09A3 (09CD (09BF|09C7|09C8))',
    'replace' => '09A3 \\1',
  ),
  380 => 
  array (
    'match' => '09BF 09A4 (09CD (09BF|09C7|09C8))',
    'replace' => '09A4 \\1',
  ),
  381 => 
  array (
    'match' => '09BF 09A5 (09CD (09BF|09C7|09C8))',
    'replace' => '09A5 \\1',
  ),
  382 => 
  array (
    'match' => '09BF 09A6 (09CD (09BF|09C7|09C8))',
    'replace' => '09A6 \\1',
  ),
  383 => 
  array (
    'match' => '09BF 09A7 (09CD (09BF|09C7|09C8))',
    'replace' => '09A7 \\1',
  ),
  384 => 
  array (
    'match' => '09BF 09A8 (09CD (09BF|09C7|09C8))',
    'replace' => '09A8 \\1',
  ),
  385 => 
  array (
    'match' => '09BF 09AA (09CD (09BF|09C7|09C8))',
    'replace' => '09AA \\1',
  ),
  386 => 
  array (
    'match' => '09BF 09AB (09CD (09BF|09C7|09C8))',
    'replace' => '09AB \\1',
  ),
  387 => 
  array (
    'match' => '09BF 09AC (09CD (09BF|09C7|09C8))',
    'replace' => '09AC \\1',
  ),
  388 => 
  array (
    'match' => '09BF 09AD (09CD (09BF|09C7|09C8))',
    'replace' => '09AD \\1',
  ),
  389 => 
  array (
    'match' => '09BF 09AE (09CD (09BF|09C7|09C8))',
    'replace' => '09AE \\1',
  ),
  390 => 
  array (
    'match' => '09BF 09AF (09CD (09BF|09C7|09C8))',
    'replace' => '09AF \\1',
  ),
  391 => 
  array (
    'match' => '09BF 09B0 (09CD (09BF|09C7|09C8))',
    'replace' => '09B0 \\1',
  ),
  392 => 
  array (
    'match' => '09BF 09B2 (09CD (09BF|09C7|09C8))',
    'replace' => '09B2 \\1',
  ),
  393 => 
  array (
    'match' => '09BF 09B6 (09CD (09BF|09C7|09C8))',
    'replace' => '09B6 \\1',
  ),
  394 => 
  array (
    'match' => '09BF 09B7 (09CD (09BF|09C7|09C8))',
    'replace' => '09B7 \\1',
  ),
  395 => 
  array (
    'match' => '09BF 09B8 (09CD (09BF|09C7|09C8))',
    'replace' => '09B8 \\1',
  ),
  396 => 
  array (
    'match' => '09BF 09B9 (09CD (09BF|09C7|09C8))',
    'replace' => '09B9 \\1',
  ),
  397 => 
  array (
    'match' => '09BF 09DC (09CD (09BF|09C7|09C8))',
    'replace' => '09DC \\1',
  ),
  398 => 
  array (
    'match' => '09BF 09DD (09CD (09BF|09C7|09C8))',
    'replace' => '09DD \\1',
  ),
  399 => 
  array (
    'match' => '09BF 09DF (09CD (09BF|09C7|09C8))',
    'replace' => '09DF \\1',
  ),
  400 => 
  array (
    'match' => '09C7 0995 (09CD (09BF|09C7|09C8))',
    'replace' => '0995 \\1',
  ),
  401 => 
  array (
    'match' => '09C7 0996 (09CD (09BF|09C7|09C8))',
    'replace' => '0996 \\1',
  ),
  402 => 
  array (
    'match' => '09C7 0997 (09CD (09BF|09C7|09C8))',
    'replace' => '0997 \\1',
  ),
  403 => 
  array (
    'match' => '09C7 0998 (09CD (09BF|09C7|09C8))',
    'replace' => '0998 \\1',
  ),
  404 => 
  array (
    'match' => '09C7 0999 (09CD (09BF|09C7|09C8))',
    'replace' => '0999 \\1',
  ),
  405 => 
  array (
    'match' => '09C7 099A (09CD (09BF|09C7|09C8))',
    'replace' => '099A \\1',
  ),
  406 => 
  array (
    'match' => '09C7 099B (09CD (09BF|09C7|09C8))',
    'replace' => '099B \\1',
  ),
  407 => 
  array (
    'match' => '09C7 099C (09CD (09BF|09C7|09C8))',
    'replace' => '099C \\1',
  ),
  408 => 
  array (
    'match' => '09C7 099D (09CD (09BF|09C7|09C8))',
    'replace' => '099D \\1',
  ),
  409 => 
  array (
    'match' => '09C7 099F (09CD (09BF|09C7|09C8))',
    'replace' => '099F \\1',
  ),
  410 => 
  array (
    'match' => '09C7 09A0 (09CD (09BF|09C7|09C8))',
    'replace' => '09A0 \\1',
  ),
  411 => 
  array (
    'match' => '09C7 09A1 (09CD (09BF|09C7|09C8))',
    'replace' => '09A1 \\1',
  ),
  412 => 
  array (
    'match' => '09C7 09A2 (09CD (09BF|09C7|09C8))',
    'replace' => '09A2 \\1',
  ),
  413 => 
  array (
    'match' => '09C7 09A3 (09CD (09BF|09C7|09C8))',
    'replace' => '09A3 \\1',
  ),
  414 => 
  array (
    'match' => '09C7 09A4 (09CD (09BF|09C7|09C8))',
    'replace' => '09A4 \\1',
  ),
  415 => 
  array (
    'match' => '09C7 09A5 (09CD (09BF|09C7|09C8))',
    'replace' => '09A5 \\1',
  ),
  416 => 
  array (
    'match' => '09C7 09A6 (09CD (09BF|09C7|09C8))',
    'replace' => '09A6 \\1',
  ),
  417 => 
  array (
    'match' => '09C7 09A7 (09CD (09BF|09C7|09C8))',
    'replace' => '09A7 \\1',
  ),
  418 => 
  array (
    'match' => '09C7 09A8 (09CD (09BF|09C7|09C8))',
    'replace' => '09A8 \\1',
  ),
  419 => 
  array (
    'match' => '09C7 09AA (09CD (09BF|09C7|09C8))',
    'replace' => '09AA \\1',
  ),
  420 => 
  array (
    'match' => '09C7 09AB (09CD (09BF|09C7|09C8))',
    'replace' => '09AB \\1',
  ),
  421 => 
  array (
    'match' => '09C7 09AC (09CD (09BF|09C7|09C8))',
    'replace' => '09AC \\1',
  ),
  422 => 
  array (
    'match' => '09C7 09AD (09CD (09BF|09C7|09C8))',
    'replace' => '09AD \\1',
  ),
  423 => 
  array (
    'match' => '09C7 09AE (09CD (09BF|09C7|09C8))',
    'replace' => '09AE \\1',
  ),
  424 => 
  array (
    'match' => '09C7 09AF (09CD (09BF|09C7|09C8))',
    'replace' => '09AF \\1',
  ),
  425 => 
  array (
    'match' => '09C7 09B0 (09CD (09BF|09C7|09C8))',
    'replace' => '09B0 \\1',
  ),
  426 => 
  array (
    'match' => '09C7 09B2 (09CD (09BF|09C7|09C8))',
    'replace' => '09B2 \\1',
  ),
  427 => 
  array (
    'match' => '09C7 09B6 (09CD (09BF|09C7|09C8))',
    'replace' => '09B6 \\1',
  ),
  428 => 
  array (
    'match' => '09C7 09B7 (09CD (09BF|09C7|09C8))',
    'replace' => '09B7 \\1',
  ),
  429 => 
  array (
    'match' => '09C7 09B8 (09CD (09BF|09C7|09C8))',
    'replace' => '09B8 \\1',
  ),
  430 => 
  array (
    'match' => '09C7 09B9 (09CD (09BF|09C7|09C8))',
    'replace' => '09B9 \\1',
  ),
  431 => 
  array (
    'match' => '09C7 09DC (09CD (09BF|09C7|09C8))',
    'replace' => '09DC \\1',
  ),
  432 => 
  array (
    'match' => '09C7 09DD (09CD (09BF|09C7|09C8))',
    'replace' => '09DD \\1',
  ),
  433 => 
  array (
    'match' => '09C7 09DF (09CD (09BF|09C7|09C8))',
    'replace' => '09DF \\1',
  ),
  434 => 
  array (
    'match' => '09C8 0995 (09CD (09BF|09C7|09C8))',
    'replace' => '0995 \\1',
  ),
  435 => 
  array (
    'match' => '09C8 0996 (09CD (09BF|09C7|09C8))',
    'replace' => '0996 \\1',
  ),
  436 => 
  array (
    'match' => '09C8 0997 (09CD (09BF|09C7|09C8))',
    'replace' => '0997 \\1',
  ),
  437 => 
  array (
    'match' => '09C8 0998 (09CD (09BF|09C7|09C8))',
    'replace' => '0998 \\1',
  ),
  438 => 
  array (
    'match' => '09C8 0999 (09CD (09BF|09C7|09C8))',
    'replace' => '0999 \\1',
  ),
  439 => 
  array (
    'match' => '09C8 099A (09CD (09BF|09C7|09C8))',
    'replace' => '099A \\1',
  ),
  440 => 
  array (
    'match' => '09C8 099B (09CD (09BF|09C7|09C8))',
    'replace' => '099B \\1',
  ),
  441 => 
  array (
    'match' => '09C8 099C (09CD (09BF|09C7|09C8))',
    'replace' => '099C \\1',
  ),
  442 => 
  array (
    'match' => '09C8 099D (09CD (09BF|09C7|09C8))',
    'replace' => '099D \\1',
  ),
  443 => 
  array (
    'match' => '09C8 099F (09CD (09BF|09C7|09C8))',
    'replace' => '099F \\1',
  ),
  444 => 
  array (
    'match' => '09C8 09A0 (09CD (09BF|09C7|09C8))',
    'replace' => '09A0 \\1',
  ),
  445 => 
  array (
    'match' => '09C8 09A1 (09CD (09BF|09C7|09C8))',
    'replace' => '09A1 \\1',
  ),
  446 => 
  array (
    'match' => '09C8 09A2 (09CD (09BF|09C7|09C8))',
    'replace' => '09A2 \\1',
  ),
  447 => 
  array (
    'match' => '09C8 09A3 (09CD (09BF|09C7|09C8))',
    'replace' => '09A3 \\1',
  ),
  448 => 
  array (
    'match' => '09C8 09A4 (09CD (09BF|09C7|09C8))',
    'replace' => '09A4 \\1',
  ),
  449 => 
  array (
    'match' => '09C8 09A5 (09CD (09BF|09C7|09C8))',
    'replace' => '09A5 \\1',
  ),
  450 => 
  array (
    'match' => '09C8 09A6 (09CD (09BF|09C7|09C8))',
    'replace' => '09A6 \\1',
  ),
  451 => 
  array (
    'match' => '09C8 09A7 (09CD (09BF|09C7|09C8))',
    'replace' => '09A7 \\1',
  ),
  452 => 
  array (
    'match' => '09C8 09A8 (09CD (09BF|09C7|09C8))',
    'replace' => '09A8 \\1',
  ),
  453 => 
  array (
    'match' => '09C8 09AA (09CD (09BF|09C7|09C8))',
    'replace' => '09AA \\1',
  ),
  454 => 
  array (
    'match' => '09C8 09AB (09CD (09BF|09C7|09C8))',
    'replace' => '09AB \\1',
  ),
  455 => 
  array (
    'match' => '09C8 09AC (09CD (09BF|09C7|09C8))',
    'replace' => '09AC \\1',
  ),
  456 => 
  array (
    'match' => '09C8 09AD (09CD (09BF|09C7|09C8))',
    'replace' => '09AD \\1',
  ),
  457 => 
  array (
    'match' => '09C8 09AE (09CD (09BF|09C7|09C8))',
    'replace' => '09AE \\1',
  ),
  458 => 
  array (
    'match' => '09C8 09AF (09CD (09BF|09C7|09C8))',
    'replace' => '09AF \\1',
  ),
  459 => 
  array (
    'match' => '09C8 09B0 (09CD (09BF|09C7|09C8))',
    'replace' => '09B0 \\1',
  ),
  460 => 
  array (
    'match' => '09C8 09B2 (09CD (09BF|09C7|09C8))',
    'replace' => '09B2 \\1',
  ),
  461 => 
  array (
    'match' => '09C8 09B6 (09CD (09BF|09C7|09C8))',
    'replace' => '09B6 \\1',
  ),
  462 => 
  array (
    'match' => '09C8 09B7 (09CD (09BF|09C7|09C8))',
    'replace' => '09B7 \\1',
  ),
  463 => 
  array (
    'match' => '09C8 09B8 (09CD (09BF|09C7|09C8))',
    'replace' => '09B8 \\1',
  ),
  464 => 
  array (
    'match' => '09C8 09B9 (09CD (09BF|09C7|09C8))',
    'replace' => '09B9 \\1',
  ),
  465 => 
  array (
    'match' => '09C8 09DC (09CD (09BF|09C7|09C8))',
    'replace' => '09DC \\1',
  ),
  466 => 
  array (
    'match' => '09C8 09DD (09CD (09BF|09C7|09C8))',
    'replace' => '09DD \\1',
  ),
  467 => 
  array (
    'match' => '09C8 09DF (09CD (09BF|09C7|09C8))',
    'replace' => '09DF \\1',
  ),
  468 => 
  array (
    'match' => '09BF 0995 (007E (09BF|09C7|09C8))',
    'replace' => '0995 \\1',
  ),
  469 => 
  array (
    'match' => '09BF 0996 (007E (09BF|09C7|09C8))',
    'replace' => '0996 \\1',
  ),
  470 => 
  array (
    'match' => '09BF 0997 (007E (09BF|09C7|09C8))',
    'replace' => '0997 \\1',
  ),
  471 => 
  array (
    'match' => '09BF 0998 (007E (09BF|09C7|09C8))',
    'replace' => '0998 \\1',
  ),
  472 => 
  array (
    'match' => '09BF 0999 (007E (09BF|09C7|09C8))',
    'replace' => '0999 \\1',
  ),
  473 => 
  array (
    'match' => '09BF 099A (007E (09BF|09C7|09C8))',
    'replace' => '099A \\1',
  ),
  474 => 
  array (
    'match' => '09BF 099B (007E (09BF|09C7|09C8))',
    'replace' => '099B \\1',
  ),
  475 => 
  array (
    'match' => '09BF 099C (007E (09BF|09C7|09C8))',
    'replace' => '099C \\1',
  ),
  476 => 
  array (
    'match' => '09BF 099D (007E (09BF|09C7|09C8))',
    'replace' => '099D \\1',
  ),
  477 => 
  array (
    'match' => '09BF 099F (007E (09BF|09C7|09C8))',
    'replace' => '099F \\1',
  ),
  478 => 
  array (
    'match' => '09BF 09A0 (007E (09BF|09C7|09C8))',
    'replace' => '09A0 \\1',
  ),
  479 => 
  array (
    'match' => '09BF 09A1 (007E (09BF|09C7|09C8))',
    'replace' => '09A1 \\1',
  ),
  480 => 
  array (
    'match' => '09BF 09A2 (007E (09BF|09C7|09C8))',
    'replace' => '09A2 \\1',
  ),
  481 => 
  array (
    'match' => '09BF 09A3 (007E (09BF|09C7|09C8))',
    'replace' => '09A3 \\1',
  ),
  482 => 
  array (
    'match' => '09BF 09A4 (007E (09BF|09C7|09C8))',
    'replace' => '09A4 \\1',
  ),
  483 => 
  array (
    'match' => '09BF 09A5 (007E (09BF|09C7|09C8))',
    'replace' => '09A5 \\1',
  ),
  484 => 
  array (
    'match' => '09BF 09A6 (007E (09BF|09C7|09C8))',
    'replace' => '09A6 \\1',
  ),
  485 => 
  array (
    'match' => '09BF 09A7 (007E (09BF|09C7|09C8))',
    'replace' => '09A7 \\1',
  ),
  486 => 
  array (
    'match' => '09BF 09A8 (007E (09BF|09C7|09C8))',
    'replace' => '09A8 \\1',
  ),
  487 => 
  array (
    'match' => '09BF 09AA (007E (09BF|09C7|09C8))',
    'replace' => '09AA \\1',
  ),
  488 => 
  array (
    'match' => '09BF 09AB (007E (09BF|09C7|09C8))',
    'replace' => '09AB \\1',
  ),
  489 => 
  array (
    'match' => '09BF 09AC (007E (09BF|09C7|09C8))',
    'replace' => '09AC \\1',
  ),
  490 => 
  array (
    'match' => '09BF 09AD (007E (09BF|09C7|09C8))',
    'replace' => '09AD \\1',
  ),
  491 => 
  array (
    'match' => '09BF 09AE (007E (09BF|09C7|09C8))',
    'replace' => '09AE \\1',
  ),
  492 => 
  array (
    'match' => '09BF 09AF (007E (09BF|09C7|09C8))',
    'replace' => '09AF \\1',
  ),
  493 => 
  array (
    'match' => '09BF 09B0 (007E (09BF|09C7|09C8))',
    'replace' => '09B0 \\1',
  ),
  494 => 
  array (
    'match' => '09BF 09B2 (007E (09BF|09C7|09C8))',
    'replace' => '09B2 \\1',
  ),
  495 => 
  array (
    'match' => '09BF 09B6 (007E (09BF|09C7|09C8))',
    'replace' => '09B6 \\1',
  ),
  496 => 
  array (
    'match' => '09BF 09B7 (007E (09BF|09C7|09C8))',
    'replace' => '09B7 \\1',
  ),
  497 => 
  array (
    'match' => '09BF 09B8 (007E (09BF|09C7|09C8))',
    'replace' => '09B8 \\1',
  ),
  498 => 
  array (
    'match' => '09BF 09B9 (007E (09BF|09C7|09C8))',
    'replace' => '09B9 \\1',
  ),
  499 => 
  array (
    'match' => '09BF 09DC (007E (09BF|09C7|09C8))',
    'replace' => '09DC \\1',
  ),
  500 => 
  array (
    'match' => '09BF 09DD (007E (09BF|09C7|09C8))',
    'replace' => '09DD \\1',
  ),
  501 => 
  array (
    'match' => '09BF 09DF (007E (09BF|09C7|09C8))',
    'replace' => '09DF \\1',
  ),
  502 => 
  array (
    'match' => '09C7 0995 (007E (09BF|09C7|09C8))',
    'replace' => '0995 \\1',
  ),
  503 => 
  array (
    'match' => '09C7 0996 (007E (09BF|09C7|09C8))',
    'replace' => '0996 \\1',
  ),
  504 => 
  array (
    'match' => '09C7 0997 (007E (09BF|09C7|09C8))',
    'replace' => '0997 \\1',
  ),
  505 => 
  array (
    'match' => '09C7 0998 (007E (09BF|09C7|09C8))',
    'replace' => '0998 \\1',
  ),
  506 => 
  array (
    'match' => '09C7 0999 (007E (09BF|09C7|09C8))',
    'replace' => '0999 \\1',
  ),
  507 => 
  array (
    'match' => '09C7 099A (007E (09BF|09C7|09C8))',
    'replace' => '099A \\1',
  ),
  508 => 
  array (
    'match' => '09C7 099B (007E (09BF|09C7|09C8))',
    'replace' => '099B \\1',
  ),
  509 => 
  array (
    'match' => '09C7 099C (007E (09BF|09C7|09C8))',
    'replace' => '099C \\1',
  ),
  510 => 
  array (
    'match' => '09C7 099D (007E (09BF|09C7|09C8))',
    'replace' => '099D \\1',
  ),
  511 => 
  array (
    'match' => '09C7 099F (007E (09BF|09C7|09C8))',
    'replace' => '099F \\1',
  ),
  512 => 
  array (
    'match' => '09C7 09A0 (007E (09BF|09C7|09C8))',
    'replace' => '09A0 \\1',
  ),
  513 => 
  array (
    'match' => '09C7 09A1 (007E (09BF|09C7|09C8))',
    'replace' => '09A1 \\1',
  ),
  514 => 
  array (
    'match' => '09C7 09A2 (007E (09BF|09C7|09C8))',
    'replace' => '09A2 \\1',
  ),
  515 => 
  array (
    'match' => '09C7 09A3 (007E (09BF|09C7|09C8))',
    'replace' => '09A3 \\1',
  ),
  516 => 
  array (
    'match' => '09C7 09A4 (007E (09BF|09C7|09C8))',
    'replace' => '09A4 \\1',
  ),
  517 => 
  array (
    'match' => '09C7 09A5 (007E (09BF|09C7|09C8))',
    'replace' => '09A5 \\1',
  ),
  518 => 
  array (
    'match' => '09C7 09A6 (007E (09BF|09C7|09C8))',
    'replace' => '09A6 \\1',
  ),
  519 => 
  array (
    'match' => '09C7 09A7 (007E (09BF|09C7|09C8))',
    'replace' => '09A7 \\1',
  ),
  520 => 
  array (
    'match' => '09C7 09A8 (007E (09BF|09C7|09C8))',
    'replace' => '09A8 \\1',
  ),
  521 => 
  array (
    'match' => '09C7 09AA (007E (09BF|09C7|09C8))',
    'replace' => '09AA \\1',
  ),
  522 => 
  array (
    'match' => '09C7 09AB (007E (09BF|09C7|09C8))',
    'replace' => '09AB \\1',
  ),
  523 => 
  array (
    'match' => '09C7 09AC (007E (09BF|09C7|09C8))',
    'replace' => '09AC \\1',
  ),
  524 => 
  array (
    'match' => '09C7 09AD (007E (09BF|09C7|09C8))',
    'replace' => '09AD \\1',
  ),
  525 => 
  array (
    'match' => '09C7 09AE (007E (09BF|09C7|09C8))',
    'replace' => '09AE \\1',
  ),
  526 => 
  array (
    'match' => '09C7 09AF (007E (09BF|09C7|09C8))',
    'replace' => '09AF \\1',
  ),
  527 => 
  array (
    'match' => '09C7 09B0 (007E (09BF|09C7|09C8))',
    'replace' => '09B0 \\1',
  ),
  528 => 
  array (
    'match' => '09C7 09B2 (007E (09BF|09C7|09C8))',
    'replace' => '09B2 \\1',
  ),
  529 => 
  array (
    'match' => '09C7 09B6 (007E (09BF|09C7|09C8))',
    'replace' => '09B6 \\1',
  ),
  530 => 
  array (
    'match' => '09C7 09B7 (007E (09BF|09C7|09C8))',
    'replace' => '09B7 \\1',
  ),
  531 => 
  array (
    'match' => '09C7 09B8 (007E (09BF|09C7|09C8))',
    'replace' => '09B8 \\1',
  ),
  532 => 
  array (
    'match' => '09C7 09B9 (007E (09BF|09C7|09C8))',
    'replace' => '09B9 \\1',
  ),
  533 => 
  array (
    'match' => '09C7 09DC (007E (09BF|09C7|09C8))',
    'replace' => '09DC \\1',
  ),
  534 => 
  array (
    'match' => '09C7 09DD (007E (09BF|09C7|09C8))',
    'replace' => '09DD \\1',
  ),
  535 => 
  array (
    'match' => '09C7 09DF (007E (09BF|09C7|09C8))',
    'replace' => '09DF \\1',
  ),
  536 => 
  array (
    'match' => '09C8 0995 (007E (09BF|09C7|09C8))',
    'replace' => '0995 \\1',
  ),
  537 => 
  array (
    'match' => '09C8 0996 (007E (09BF|09C7|09C8))',
    'replace' => '0996 \\1',
  ),
  538 => 
  array (
    'match' => '09C8 0997 (007E (09BF|09C7|09C8))',
    'replace' => '0997 \\1',
  ),
  539 => 
  array (
    'match' => '09C8 0998 (007E (09BF|09C7|09C8))',
    'replace' => '0998 \\1',
  ),
  540 => 
  array (
    'match' => '09C8 0999 (007E (09BF|09C7|09C8))',
    'replace' => '0999 \\1',
  ),
  541 => 
  array (
    'match' => '09C8 099A (007E (09BF|09C7|09C8))',
    'replace' => '099A \\1',
  ),
  542 => 
  array (
    'match' => '09C8 099B (007E (09BF|09C7|09C8))',
    'replace' => '099B \\1',
  ),
  543 => 
  array (
    'match' => '09C8 099C (007E (09BF|09C7|09C8))',
    'replace' => '099C \\1',
  ),
  544 => 
  array (
    'match' => '09C8 099D (007E (09BF|09C7|09C8))',
    'replace' => '099D \\1',
  ),
  545 => 
  array (
    'match' => '09C8 099F (007E (09BF|09C7|09C8))',
    'replace' => '099F \\1',
  ),
  546 => 
  array (
    'match' => '09C8 09A0 (007E (09BF|09C7|09C8))',
    'replace' => '09A0 \\1',
  ),
  547 => 
  array (
    'match' => '09C8 09A1 (007E (09BF|09C7|09C8))',
    'replace' => '09A1 \\1',
  ),
  548 => 
  array (
    'match' => '09C8 09A2 (007E (09BF|09C7|09C8))',
    'replace' => '09A2 \\1',
  ),
  549 => 
  array (
    'match' => '09C8 09A3 (007E (09BF|09C7|09C8))',
    'replace' => '09A3 \\1',
  ),
  550 => 
  array (
    'match' => '09C8 09A4 (007E (09BF|09C7|09C8))',
    'replace' => '09A4 \\1',
  ),
  551 => 
  array (
    'match' => '09C8 09A5 (007E (09BF|09C7|09C8))',
    'replace' => '09A5 \\1',
  ),
  552 => 
  array (
    'match' => '09C8 09A6 (007E (09BF|09C7|09C8))',
    'replace' => '09A6 \\1',
  ),
  553 => 
  array (
    'match' => '09C8 09A7 (007E (09BF|09C7|09C8))',
    'replace' => '09A7 \\1',
  ),
  554 => 
  array (
    'match' => '09C8 09A8 (007E (09BF|09C7|09C8))',
    'replace' => '09A8 \\1',
  ),
  555 => 
  array (
    'match' => '09C8 09AA (007E (09BF|09C7|09C8))',
    'replace' => '09AA \\1',
  ),
  556 => 
  array (
    'match' => '09C8 09AB (007E (09BF|09C7|09C8))',
    'replace' => '09AB \\1',
  ),
  557 => 
  array (
    'match' => '09C8 09AC (007E (09BF|09C7|09C8))',
    'replace' => '09AC \\1',
  ),
  558 => 
  array (
    'match' => '09C8 09AD (007E (09BF|09C7|09C8))',
    'replace' => '09AD \\1',
  ),
  559 => 
  array (
    'match' => '09C8 09AE (007E (09BF|09C7|09C8))',
    'replace' => '09AE \\1',
  ),
  560 => 
  array (
    'match' => '09C8 09AF (007E (09BF|09C7|09C8))',
    'replace' => '09AF \\1',
  ),
  561 => 
  array (
    'match' => '09C8 09B0 (007E (09BF|09C7|09C8))',
    'replace' => '09B0 \\1',
  ),
  562 => 
  array (
    'match' => '09C8 09B2 (007E (09BF|09C7|09C8))',
    'replace' => '09B2 \\1',
  ),
  563 => 
  array (
    'match' => '09C8 09B6 (007E (09BF|09C7|09C8))',
    'replace' => '09B6 \\1',
  ),
  564 => 
  array (
    'match' => '09C8 09B7 (007E (09BF|09C7|09C8))',
    'replace' => '09B7 \\1',
  ),
  565 => 
  array (
    'match' => '09C8 09B8 (007E (09BF|09C7|09C8))',
    'replace' => '09B8 \\1',
  ),
  566 => 
  array (
    'match' => '09C8 09B9 (007E (09BF|09C7|09C8))',
    'replace' => '09B9 \\1',
  ),
  567 => 
  array (
    'match' => '09C8 09DC (007E (09BF|09C7|09C8))',
    'replace' => '09DC \\1',
  ),
  568 => 
  array (
    'match' => '09C8 09DD (007E (09BF|09C7|09C8))',
    'replace' => '09DD \\1',
  ),
  569 => 
  array (
    'match' => '09C8 09DF (007E (09BF|09C7|09C8))',
    'replace' => '09DF \\1',
  ),
  570 => 
  array (
    'match' => '09A4 09CD',
    'replace' => 'E066',
  ),
  571 => 
  array (
    'match' => '09A4 007E',
    'replace' => 'E066',
  ),
  572 => 
  array (
    'match' => 'E066 200D',
    'replace' => 'E066',
  ),
  573 => 
  array (
    'match' => '09BF 200D',
    'replace' => '09BF',
  ),
  574 => 
  array (
    'match' => '09C7 200D',
    'replace' => '09C7',
  ),
  575 => 
  array (
    'match' => '09C8 200D',
    'replace' => '09C8',
  ),
  576 => 
  array (
    'match' => '007E',
    'replace' => '09CD',
  ),
  577 => 
  array (
    'match' => '200C',
    'replace' => '09CD',
  ),
  578 => 
  array (
    'match' => '00D0',
    'replace' => '09CD',
  ),
  579 => 
  array (
    'match' => '0997 09C1',
    'replace' => 'E00A',
  ),
  580 => 
  array (
    'match' => '09DC 09C1',
    'replace' => 'E012',
  ),
  581 => 
  array (
    'match' => '09DC 09C2',
    'replace' => 'E013',
  ),
  582 => 
  array (
    'match' => '09DC 09C3',
    'replace' => 'E014',
  ),
  583 => 
  array (
    'match' => '09DC 09C4',
    'replace' => 'E015',
  ),
  584 => 
  array (
    'match' => '09DD 09C1',
    'replace' => 'E016',
  ),
  585 => 
  array (
    'match' => '09DD 09C2',
    'replace' => 'E017',
  ),
  586 => 
  array (
    'match' => '09DD 09C3',
    'replace' => 'E018',
  ),
  587 => 
  array (
    'match' => '09DD 09C4',
    'replace' => 'E019',
  ),
  588 => 
  array (
    'match' => '09B6 09C1',
    'replace' => 'E00F',
  ),
  589 => 
  array (
    'match' => '09B9 09C1',
    'replace' => 'E010',
  ),
  590 => 
  array (
    'match' => '09B9 09C3',
    'replace' => 'E011',
  ),
  591 => 
  array (
    'match' => 'E084 09C1',
    'replace' => 'E19B',
  ),
  592 => 
  array (
    'match' => 'E0F4 09C1',
    'replace' => 'E19C',
  ),
  593 => 
  array (
    'match' => 'E10D 09C1',
    'replace' => 'E19D',
  ),
  594 => 
  array (
    'match' => 'E128 09C1',
    'replace' => 'E19E',
  ),
  595 => 
  array (
    'match' => 'E133 09C1',
    'replace' => 'E19F',
  ),
  596 => 
  array (
    'match' => 'E155 09C1',
    'replace' => 'E1A0',
  ),
  597 => 
  array (
    'match' => 'E169 09C1',
    'replace' => 'E1A1',
  ),
  598 => 
  array (
    'match' => 'E182 09C1',
    'replace' => 'E1A2',
  ),
  599 => 
  array (
    'match' => '09BF ((E002|E003|E06B|E06C|E06D|E06E|E06F|E070|E071|E072|E073|E074|E075|E076|E079|E07A|E07C|E07D|E07E|E07F|E080|E081|E082|E083|E086|E089|E08A|E08B|E08C|E08D|E08F|E090|E092|E095|E097|E098|E09A|E09B|E09D|E09F|E0A2|E0A4|E0A5|E0A6|E0A7|E0A8|E0A9|E0AA|E0AB|E0AC|E0AD|E0AE|E0AF|E0B1|E0B2|E0B3|E0B4|E0B7|E0B8|E0B9|E0BB|E0BC|E0BD|E0BE|E0BF|E0C5|E0C6|E0C7|E0C8|E0C9|E0CB|E0CC|E0CD|E0CE|E0CF|E0D2|E0D3|E0D5|E0D6|E0D8|E0D9|E0DA|E0DB|E0E0|E0E1|E0E3|E0E5|E0E7|E0E8|E0ED|E0F1|E0F5|E0F6|E0FB|E0FC|E0FD|E0FE|E102|E104|E105|E106|E108|E109|E10A|E10B|E10C|E10F|E110|E112|E114|E115|E117|E118|E119|E11A|E11B|E11E|E11F|E120|E121|E122|E123|E124|E125|E126|E127|E12B|E12C|E130|E131|E132|E133|E134|E135|E136|E137|E139|E13A|E13B|E13F|E144|E149|E14A|E14D|E14F|E150|E153|E154|E156|E157|E158|E159|E15A|E15C|E161|E162|E163|E165|E166|E167|E168|E16C|E16D|E174|E175|E176|E177|E178|E17A|E17B|E17C|E17D|E17E|E17F|E180|E181|E185|E186|E187|E188|E18B|E18C|E18D|E190|E191|E193|E194|E195|E198|E199|E19A))',
    'replace' => 'E01C \\1',
  ),
  600 => 
  array (
    'match' => '((0995|0999|099A|099B|099F|09A0|09A1|09A2|09A4|09AB|09AD|09B9)) 09BE',
    'replace' => '\\1 E01A',
  ),
  601 => 
  array (
    'match' => '((0995|0999|099A|099B|099F|09A0|09A1|09A2|09A4|09AB|09AD|09B9)) 09D7',
    'replace' => '\\1 E03F',
  ),
  602 => 
  array (
    'match' => '((09A6)) 09BE',
    'replace' => '\\1 E01B',
  ),
  603 => 
  array (
    'match' => '((09A6)) 09D7',
    'replace' => '\\1 E040',
  ),
  604 => 
  array (
    'match' => '09C0 0981',
    'replace' => 'E1B4',
  ),
  605 => 
  array (
    'match' => '09D7 0981',
    'replace' => 'E1B5',
  ),
  606 => 
  array (
    'match' => 'E01D 0981',
    'replace' => 'E1B6',
  ),
  607 => 
  array (
    'match' => 'E01E 0981',
    'replace' => 'E1B7',
  ),
  608 => 
  array (
    'match' => 'E01F 0981',
    'replace' => 'E1B8',
  ),
  609 => 
  array (
    'match' => 'E03F 0981',
    'replace' => 'E1B9',
  ),
  610 => 
  array (
    'match' => 'E040 0981',
    'replace' => 'E1BA',
  ),
  611 => 
  array (
    'match' => '09C0 E069',
    'replace' => 'E1BB',
  ),
  612 => 
  array (
    'match' => '09D7 E069',
    'replace' => 'E1BC',
  ),
  613 => 
  array (
    'match' => 'E01D E069',
    'replace' => 'E1BD',
  ),
  614 => 
  array (
    'match' => 'E01E E069',
    'replace' => 'E1BE',
  ),
  615 => 
  array (
    'match' => 'E01F E069',
    'replace' => 'E1BF',
  ),
  616 => 
  array (
    'match' => 'E03F E069',
    'replace' => 'E1C0',
  ),
  617 => 
  array (
    'match' => 'E040 E069',
    'replace' => 'E1C1',
  ),
  618 => 
  array (
    'match' => '0987 0981',
    'replace' => 'E1C2',
  ),
  619 => 
  array (
    'match' => '0988 0981',
    'replace' => 'E1C3',
  ),
  620 => 
  array (
    'match' => '098A 0981',
    'replace' => 'E1C4',
  ),
  621 => 
  array (
    'match' => '0990 0981',
    'replace' => 'E1C5',
  ),
  622 => 
  array (
    'match' => '0994 0981',
    'replace' => 'E1C6',
  ),
  623 => 
  array (
    'match' => '099F 0981',
    'replace' => 'E1C7',
  ),
  624 => 
  array (
    'match' => '09A0 0981',
    'replace' => 'E1C8',
  ),
  625 => 
  array (
    'match' => '099F E068',
    'replace' => 'E1C9',
  ),
  626 => 
  array (
    'match' => '09A0 E068',
    'replace' => 'E1CA',
  ),
  627 => 
  array (
    'match' => '099F E069',
    'replace' => 'E1CB',
  ),
  628 => 
  array (
    'match' => '09A0 E069',
    'replace' => 'E1CC',
  ),
  629 => 
  array (
    'match' => '((0995|0999|099A|099B|099E|09A1|09A2|09A4|09AB|09AD|09B9|E002|E003|E06A|E073|E074|E077|E078|E07B|E07D|E07E|E07F|E081|E082|E084|E08A|E08C|E08D|E090|E092|E094|E097|E098|E09C|E0A0|E0A1|E0A6|E0A7|E0A9|E0AA|E0AB|E0AC|E0AD|E0AE|E0B1|E0B2|E0B3|E0B4|E0B5|E0B6|E0C1|E0C2|E0C3|E0C4|E0C6|E0C8|E0C9|E0CC|E0CF|E0D9|E0DA|E0DC|E0DD|E0DF|E0E2|E0E3|E0E9|E0EA|E0F0|E108|E109|E10C|E10D|E10E|E110|E112|E113|E11B|E120|E121|E122|E124|E125|E128|E12B|E133|E134|E135|E136|E137|E139|E13A|E13C|E140|E143|E14A|E14C|E153|E156|E157|E159|E15C|E162|E166|E167|E168|E169|E171|E174|E176|E17A|E17E|E180|E182|E183|E18C|E192|E193|E194|E195|E196|E197)) 09C0',
    'replace' => '\\1 E01D',
  ),
  630 => 
  array (
    'match' => '((09A6|E08F|E0B9|E0F4|E0F6|E0F7|E0F8|E0F9|E0FA|E0FD|E0DC|E0FF|E100|E101|E10F|E11E|E11F|E13B|E15A)) 09C0',
    'replace' => '\\1 E01E',
  ),
  631 => 
  array (
    'match' => '((099F|09A0|E06E|E06F|E07C|E0CA|E0CB|E0D0|E0D1|E0D4|E0D5|E0D7|E0E0|E0E1|E0E8|E10A|E10B|E11A|E126|E127|E132|E158|E163|E165|E172|E173|E17B|E17C|E181|E18D)) 09C0',
    'replace' => '\\1 E01F',
  ),
);
?>
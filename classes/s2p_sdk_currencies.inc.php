<?php

namespace S2P_SDK;

class S2P_SDK_Currencies extends S2P_SDK_Language
{
    private static $CURRENCIES_ARR = array(
        'AED' => 'UAE Dirham',
        'AFN' => 'Afghanistan Afghanis',
        'ALL' => 'Lek',
        'AMD' => 'Dram (Russian Ruble [RUR] was formerly in use)',
        'ANG' => 'Netherlands Antilles Guilder (Florin)',
        'AOA' => 'Angola Kwanza',
        'AON' => 'New Kwanza (replacement for Kwanza)',
        'ARA' => 'Austral Neuvo Peso',
        'ARS' => 'Argenintinian Neuvo Peso',
        'AUD' => 'Australian Dollar',
        'AWG' => 'Aruban Guilder (Florin)',
        'AZM' => 'Azerbaijani Manat',
        'AZN' => 'Azerbaijan New Manats',
        'BAM' => 'Convertible Mark',
        'BBD' => 'Barbados Dollar',
        'BDT' => 'Taka',
        'BGN' => 'Bulgaria Leva',
        'BHD' => 'Bahraini Dinar',
        'BIF' => 'Burundi Franc',
        'BMD' => 'Bermudian Dollar',
        'BND' => 'Brunei Dollar',
        'BOB' => 'Boliviano Peso',
        'BOP' => 'Bolivian Peso',
        'BRL' => 'Brazilian real',
        'BRR' => 'Cruzeiro Real',
        'BSD' => 'Bahamian Dollar',
        'BTN' => 'Ngultrum (Indian Rupee also circulates)',
        'BWP' => 'Pula',
        'BYR' => 'Belarussian Rouble',
        'BZD' => 'Belize Dollar',
        'CAD' => 'Canadian Dollar',
        'CDF' => 'Congo/Kinshasa Francs',
        'CDZ' => 'New Zalre',
        'CHF' => 'Swiss Franc',
        'CLF' => 'Unidades de Fomento Peso',
        'CLP' => 'Chilean Peso',
        'CNY' => 'Yuan Renminbi',
        'COP' => 'Colombian Peso',
        'CRC' => 'Costa Rican Colón',
        'CSD' => 'Serbian Dinar',
        'CUC' => 'Cuba Convertible Pesos',
        'CUP' => 'Cuban Peso',
        'CVE' => 'Escudo Caboverdiano',
        'CYP' => 'Cypriot Pound',
        'CZK' => 'Czech Koruna',
        'DJF' => 'Djibouti Franc',
        'DKK' => 'Danish Krone',
        'DOP' => 'Dominican Republic Peso',
        'DZD' => 'Algerian Dinar',
        'EEK' => 'Kroon',
        'EGP' => 'Egytian Pound',
        'ERN' => 'Eritreian Nakfa, Ethiopian Birr',
        'ETB' => 'Eritreian Nakfa, Ethiopian Birr',
        'EUR' => 'Euro',
        'FJD' => 'Fiji Dollar',
        'FKP' => 'Falkland Pound',
        'GBP' => 'Pound Sterling',
        'GEL' => 'Lari',
        'GGP' => 'Guernsey Pounds',
        'GHC' => 'Cedi',
        'GHS' => 'Ghana Cedis',
        'GIP' => 'Gibraltar Pound',
        'GMD' => 'Dalasi',
        'GNF' => 'Guinea Francs',
        'GNS' => 'Guinea Syli (also known as Guinea Franc)',
        'GQE' => 'Franc de la Communauté financicre africaine and Ekwele',
        'GTQ' => 'Quetzal',
        'GWP' => 'Guinea-Bissau Peso and Franc de la Communauté financicre africaine',
        'GYD' => 'Guyana Dollar',
        'HKD' => 'Hong Kong Dollar',
        'HNL' => 'Lempira',
        'HRD' => 'Kuna and Croatian Dinar',
        'HRK' => 'Kuna and Croatian Dinar',
        'HTG' => 'Gourde',
        'HUF' => 'Forint',
        'IDR' => 'Rupiah',
        'ILS' => 'Shekel',
        'IMP' => 'Isle of Man Pounds',
        'INR' => 'Ngultrum or Indian Rupee',
        'IQD' => 'Iraqi Dinar',
        'IRR' => 'Iranian Rial',
        'ISK' => 'Icelandic Króna',
        'JEP' => 'Jersey Pounds',
        'JMD' => 'Jamaican Dollar',
        'JOD' => 'Jordanian Dinar',
        'JPY' => 'Yen',
        'KES' => 'Kenyan Shilling',
        'KGS' => 'Kyrgyzstani Som',
        'KHR' => 'Riel',
        'KMF' => 'Comorian Franc',
        'KPW' => 'North Korean Won',
        'KRW' => 'South Korean Won',
        'KWD' => 'Kuwaiti Dinar',
        'KYD' => 'Cayman Islands Dollar',
        'KZT' => 'Tenge (Russian Ruble [RUR] was formerly in use)',
        'LAK' => 'Kip',
        'LBP' => 'Lebanese Pound',
        'LKR' => 'Sri Lankan Rupee',
        'LRD' => 'Liberian Dollar',
        'LSL' => 'Loti and South African Rand',
        'LSM' => 'Maloti and South African Rand',
        'LTL' => 'Litas',
        'LVL' => 'Lats',
        'LYD' => 'Libyan Dinar',
        'MAD' => 'Moroccan Dirham and Mauritanian Ouguiya',
        'MDL' => 'Moldovian Leu',
        'MGA' => 'Madagascar Ariary',
        'MGF' => 'Malagasy Franc',
        'MKD' => 'Macedonian Dinar',
        'MMK' => 'Kyat',
        'MNT' => 'Tugrik',
        'MOP' => 'Pataca',
        'MRO' => 'Moroccan Dirham and Mauritanian Ouguiya',
        'MTL' => 'Maltese Lira (Maltese Pound formerly in use)',
        'MUR' => 'Mauritius Rupee',
        'MVR' => 'Rufiyaa',
        'MWK' => 'Malawian Kwacha',
        'MXN' => 'Mexican New Peso (replacement for Mexican Peso)',
        'MYR' => 'Ringgit (Malaysian Dollar)',
        'MZM' => 'Metical',
        'MZN' => 'Mozambique Meticais',
        'NAD' => 'Namibian Dollar and South African Rand',
        'NGN' => 'Naira',
        'NIC' => 'Córdoba',
        'NIO' => 'Nicaragua Cordobas',
        'NOK' => 'Norwegian Krone',
        'NPR' => 'Nepalese Rupee',
        'NZD' => 'New Zealand Dollar',
        'OMR' => 'Rial Omani',
        'PAB' => 'Balboa and US Dollar',
        'PEI' => 'Inti and New Sol (New Sol replaced Sol)',
        'PEN' => 'Inti and New Sol (New Sol replaced Sol)',
        'PGK' => 'Kina',
        'PHP' => 'Philippines Peso',
        'PKR' => 'Pakistani Rupee',
        'PLN' => 'New Zloty (replacement for Zloty)',
        'PYG' => 'Guarani',
        'QAR' => 'Qatari Riyal',
        'RON' => 'Romanian Leu',
        'RSD' => 'Serbia Dinars',
        'RUB' => 'Russian Federation Rouble',
        'RWF' => 'Rwanda Franc',
        'SAR' => 'Saudi Riyal',
        'SBD' => 'Solomon Islands Dollar',
        'SCR' => 'Pound Sterling (United Kingdom Pound), Seychelles Rupee',
        'SDD' => 'Sudanese Pound and Sudanese Dinar',
        'SDG' => 'Sudan Pounds',
        'SDP' => 'Sudanese Pound and Sudanese Dinar',
        'SEK' => 'Swedish Krona',
        'SGD' => 'Singapore Dollar',
        'SHP' => 'St Helena Pound',
        'SIT' => 'Tolar',
        'SKK' => 'Slovak Koruna',
        'SLL' => 'Leone',
        'SOS' => 'Somali Shilling',
        'SPL' => 'Seborga Luigini',
        'SRD' => 'Suriname Dollars',
        'SRG' => 'Surinam Guilder (also known as Florin)',
        'STD' => 'Dobra',
        'SUR' => 'USSR Rouble',
        'SVC' => 'El Salvadorian Colón',
        'SYP' => 'Syrian Pound',
        'SZL' => 'Lilangeni',
        'THB' => 'Baht',
        'TJR' => 'Tajik Rouble (Russian Ruble [RUR] was formerly in use)',
        'TJS' => 'Tajikistan Somoni',
        'TMM' => 'Turkmenistani Manat',
        'TMT' => 'Turkmenistan New Manats',
        'TND' => 'Tunisian Dinar',
        'TOP' => 'Pa\'anga',
        'TPE' => 'Timorian Escudo',
        'TRL' => 'Turkish Lira',
        'TRY' => 'Turkey New Lira',
        'TTD' => 'Trinidad and Tobago Dollar',
        'TVD' => 'Tuvalu Dollars',
        'TWD' => 'New Taiwan Dollar',
        'TZS' => 'Tanzanian Shilling',
        'UAH' => 'Hryvna and Karbovanet',
        'UAK' => 'Hryvna and Karbovanet',
        'UGS' => 'Ugandan Shilling',
        'UGX' => 'Uganda Shillings',
        'USD' => 'US Dollar',
        'UYU' => 'Uruguayan New Peso (replacement for Uruguayan Peso)',
        'UZS' => 'Uzbekistani Som (Russian Ruble [RUR] was formerly in use)',
        'VEB' => 'Bolivar',
        'VEF' => 'Venezuela Bolivares Fuertes',
        'VND' => 'Dong',
        'VUV' => 'Vatu',
        'WST' => 'Tala',
        'XAF' => 'Guinea-Bissau Peso and Franc de la Communauté financicre africaine',
        'XAG' => 'Silver Ounces',
        'XAU' => 'Gold Ounces',
        'XCD' => 'East Caribbean Dollar',
        'XDR' => 'International Monetary Fund Special Drawing Rights',
        'XOF' => 'West African Franc and Franc de la Communauté financicre africaine',
        'XPD' => 'Palladium Ounces',
        'XPF' => 'Franc des Comptoirs franeais du Pacifique',
        'XPT' => 'Platinum Ounces',
        'YER' => 'Riyal (Dinar was used in South Yemen)',
        'ZAR' => 'Loti, Namibian Dollar, Maloti and South African Rand',
        'ZMK' => 'Zambian Kwacha',
        'ZMW' => 'Zambia Kwacha',
        'ZWD' => 'Zimbabwe Dollar',
    );

    public static function get_currencies()
    {
        static $sorted = false;

        if( $sorted === false )
        {
            asort( self::$CURRENCIES_ARR );
            $sorted = true;
        }

        return self::$CURRENCIES_ARR;
    }

    public static function valid_currency_arr( $cur_arr )
    {
        if( empty( $cur_arr ) or !is_array( $cur_arr ) )
            return false;

        $all_currencies = self::get_currencies();
        $return_arr = array();
        foreach( $cur_arr as $currency_iso )
        {
            $currency_iso = strtoupper( trim( $currency_iso ) );
            if( empty( $all_currencies[$currency_iso] ) )
                continue;

            $return_arr[$currency_iso] = $all_currencies[$currency_iso];
        }

        return (empty( $return_arr )?false:$return_arr);
    }

    public static function valid_currency( $cur )
    {
        if( empty( $cur )
         or !($currencies_arr = self::get_currencies()) or empty( $currencies_arr[$cur] ) )
            return false;

        return $currencies_arr[$cur];
    }

    public static function guess_from_term( $term )
    {
        $all_terms_arr = self::get_currencies();

        $found_terms = array();
        foreach( $all_terms_arr as $key => $val )
        {
            if( stristr( $key, $term ) === false
             or stristr( $val, $term ) === false )
                continue;

            $found_terms[$key] = $val;
        }

        return $found_terms;
    }
}

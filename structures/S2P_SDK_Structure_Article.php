<?php

namespace S2P_SDK;

class S2P_SDK_Structure_Article extends S2P_SDK_Scope_Structure
{
    /**
     * Function should return array with full variable definition
     * @return array
     */
    public function get_definition()
    {
        return array(
            'name' => 'article',
            'external_name' => 'Article',
            'type' => S2P_SDK_VTYPE_BLOB,
            'structure' => $this->get_structure_definition(),
        );
    }

    /**
     * Function should return structure definition for blobs or array variables
     * @return array
     */
    public function get_structure_definition()
    {
        return array(
            array(
                'name' => 'merchantarticleid',
                'external_name' => 'MerchantArticleID',
                'display_name' => self::s2p_t( 'Merchant assigned article ID' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '0',
                'regexp' => '^\d{1,19}$',
            ),
            array(
                'name' => 'name',
                'external_name' => 'Name',
                'display_name' => self::s2p_t( 'Article name' ),
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'regexp' => '^.{1,250}$',
            ),
            array(
                'name' => 'quantity',
                'external_name' => 'Quantity',
                'display_name' => self::s2p_t( 'Article quantity' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'regexp' => '^\d{1,3}$',
            ),
            array(
                'name' => 'price',
                'external_name' => 'Price',
                'display_name' => self::s2p_t( 'Article price in centimes' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                // 'regexp' => '^\d{1,12}$', ???
            ),
            array(
                'name' => 'vat',
                'external_name' => 'VAT',
                'display_name' => self::s2p_t( 'VAT percent in centimes' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'regexp' => '^\d{1,4}$',
            ),
            array(
                'name' => 'discount',
                'external_name' => 'Discount',
                'display_name' => self::s2p_t( 'Discount percent in centimes' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'regexp' => '^\d{1,4}$',
            ),
            array(
                'name' => 'discountvalue',
                'external_name' => 'DiscountValue',
                'display_name' => self::s2p_t( 'Integer value e.g 10 GBP discount (value with two decimals for 10 GBP send 1000)' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'regexp' => '^\d{1,4}$',
            ),
            array(
                'name' => 'type',
                'external_name' => 'Type',
                'display_name' => self::s2p_t( 'Article type' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                // 1 - product, 2 - shipping, 3 - handling,
                // 4 - discount, 5 - physical, 6 - shipping fee, 7 -sales tax, 8 - digital, 9 - gift card, 10 - store credit, 11 - surcharge
                // 1 - 4 are used for Klarna Invoice
                // 4 - 11 are used for Klarna Payments
                // see Type field in Articles node in sample at: https://docs.smart2pay.com/specific-capture-status-codes-for-klarna-payments/
                'regexp' => '^(1|2|3|4|5|6|7|8|9|10|11)$',
                'value_source' => S2P_SDK_Values_Source::TYPE_ARTICLE_TYPE,
            ),
            array(
                'name' => 'taxtype',
                'external_name' => 'TaxType',
                'display_name' => self::s2p_t( 'Tax type' ),
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                // 1 - without VAT
                // 2 - VAT at the rate of 0%
                // 3 - VAT of the receipt at the rate of 10%
                // 4 - VAT of the receipt at the rate of 18%
                // 5 - VAT of the receipt at the applicable rate of 10/110
                // 6 - VAT of the receipt at the applicable rate of 18/118
                'regexp' => '^(1|2|3|4|5|6)$',
                'value_source' => S2P_SDK_Values_Source::TYPE_ARTICLE_TAX_TYPE,
            ),
        );
    }

}

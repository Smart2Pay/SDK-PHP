<?php

namespace S2P_SDK;

if( !defined( 'S2P_SDK_DIR_STRUCTURES' ) or !defined( 'S2P_SDK_DIR_CLASSES' ) )
    die( 'Something went wrong.' );

include_once( S2P_SDK_DIR_CLASSES.'s2p_sdk_values_source.inc.php' );

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
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '0',
                'regexp' => '^\d{1,19}$',
            ),
            array(
                'name' => 'name',
                'external_name' => 'Name',
                'type' => S2P_SDK_VTYPE_STRING,
                'default' => '',
                'regexp' => '^.{1,250}$',
            ),
            array(
                'name' => 'quantity',
                'external_name' => 'Quantity',
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                'regexp' => '^\d{1,3}$',
            ),
            array(
                'name' => 'price',
                'external_name' => 'Price',
                'type' => S2P_SDK_VTYPE_FLOAT,
                'default' => 0,
                // 'regexp' => '^\d{1,12}$', ???
            ),
            array(
                'name' => 'vat',
                'external_name' => 'VAT',
                'type' => S2P_SDK_VTYPE_FLOAT,
                'default' => 0,
                // 'regexp' => '^\d{1,2}$', ???
            ),
            array(
                'name' => 'discount',
                'external_name' => 'Discount',
                'type' => S2P_SDK_VTYPE_FLOAT,
                'default' => 0,
                // 'regexp' => '^\d{1,2}$', ???
            ),
            array(
                'name' => 'foobarparam2',
                'external_name' => 'foobarparam2',
                'type' => S2P_SDK_VTYPE_ARRAY,
                'array_type' => S2P_SDK_VTYPE_STRING,
                'array_numeric_keys' => true,
                'default' => null,
                'value_source' => S2P_SDK_Values_Source::TYPE_CURRENCY,
            ),
            array(
                'name' => 'foobarparam3',
                'external_name' => 'foobarparam3',
                'type' => S2P_SDK_VTYPE_ARRAY,
                'array_type' => S2P_SDK_VTYPE_STRING,
                'array_numeric_keys' => false,
                'default' => null,
                'value_source' => S2P_SDK_Values_Source::TYPE_COUNTRY,
            ),
            array(
                'name' => 'type',
                'external_name' => 'Type',
                'type' => S2P_SDK_VTYPE_INT,
                'default' => 0,
                // 1 - product, 2 - shipping, 3 - handling
                'regexp' => '^(1|2|3)$',
                'value_source' => S2P_SDK_Values_Source::TYPE_ARTICLE_TYPE,
            ),
        );
    }

}
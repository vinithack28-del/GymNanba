<?php

return [
    'title'       => 'सदस्यता योजनाएं',
    'create_plan' => '+ योजना बनाएं',
    'edit_plan'   => 'योजना संपादित करें',

    'filters' => [
        'search_placeholder' => 'योजनाएं खोजें…',
    ],

    'card' => [
        'per_month'   => '/माह',
        'gst'         => 'GST',
        'included'    => 'सहित',
        'edit'        => 'संपादित करें',
        'duplicate'   => 'डुप्लीकेट करें',
        'archive'     => 'संग्रहीत करें',
        'all_branches'=> 'सभी शाखाएं',
        'branches'    => ':count शाखा|:count शाखाएं',
        'duration_months' => ':count महीना|:count महीने',
        'duration_days'   => ':count दिन',
    ],

    'status' => [
        'active'   => 'सक्रिय',
        'inactive' => 'निष्क्रिय',
        'archived' => 'संग्रहीत',
    ],

    'empty' => [
        'no_plans'   => 'अभी तक कोई योजना नहीं।',
        'get_started'=> 'शुरू करने के लिए अपनी पहली सदस्यता योजना बनाएं।',
    ],

    'form' => [
        'create_title'   => 'सदस्यता योजना बनाएं',
        'edit_title'     => 'सदस्यता योजना संपादित करें',
        'basic_info'     => 'योजना विवरण',
        'pricing'        => 'मूल्य निर्धारण',
        'branches_section'=> 'उपलब्ध शाखाएं',
        'advanced'       => 'उन्नत विकल्प',

        'name'           => 'योजना का नाम',
        'description'    => 'विवरण',
        'duration_type'  => 'अवधि प्रकार',
        'duration_months'=> 'अवधि (महीने)',
        'duration_days'  => 'अवधि (दिन)',
        'price'          => 'मूल्य (₹)',
        'gst_enabled'    => 'GST लागू करें',
        'gst_rate'       => 'GST दर (%)',
        'freeze_enabled' => 'फ्रीज अनुमति दें',
        'max_freeze_days'=> 'अधिकतम फ्रीज दिन',
        'status'         => 'स्थिति',

        'duration_types' => [
            'months' => 'महीने',
            'days'   => 'दिन',
        ],

        'statuses' => [
            'active'   => 'सक्रिय',
            'inactive' => 'निष्क्रिय',
        ],

        'all_branches'   => 'सभी शाखाएं',
        'select_branches'=> 'विशिष्ट शाखाएं चुनें',

        'price_preview'  => 'मूल्य पूर्वावलोकन',
        'base_price'     => 'आधार मूल्य',
        'gst_amount'     => 'GST (:rate%)',
        'total_price'    => 'कुल',

        'btn_create'     => 'योजना बनाएं',
        'btn_update'     => 'बदलाव सहेजें',
        'btn_cancel'     => 'रद्द करें',
    ],

    'flash' => [
        'created'    => 'योजना सफलतापूर्वक बनाई गई।',
        'updated'    => 'योजना सफलतापूर्वक अपडेट की गई।',
        'duplicated' => 'योजना डुप्लीकेट की गई।',
        'archived'   => 'योजना संग्रहीत की गई।',
    ],
];

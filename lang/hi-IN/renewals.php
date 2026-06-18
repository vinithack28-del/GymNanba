<?php

return [
    'title'  => 'नवीनीकरण बकाया',
    'export' => 'CSV निर्यात करें',

    'stats' => [
        'expired'    => 'समाप्त',
        'today'      => 'आज समाप्त हो रहे',
        'seven_days' => '7 दिनों में बकाया',
        'thirty_days'=> '30 दिनों में बकाया',
    ],

    'tabs' => [
        'all'    => 'सभी',
        'expired'=> 'समाप्त',
        'today'  => 'आज',
        '3days'  => 'अगले 3 दिन',
        '7days'  => 'अगले 7 दिन',
        '30days' => 'अगले 30 दिन',
        'custom' => 'कस्टम',
    ],

    'filters' => [
        'all_plans' => 'सभी योजनाएं',
        'from_date' => 'से',
        'to_date'   => 'तक',
    ],

    'table' => [
        'member'     => 'सदस्य',
        'member_id'  => 'सदस्य आईडी',
        'phone'      => 'फोन',
        'plan'       => 'योजना',
        'expiry'     => 'समाप्ति तिथि',
        'days_status'=> 'दिन',
        'balance'    => 'शेष राशि',
        'actions'    => 'कार्रवाई',
    ],

    'status' => [
        'days_overdue' => ':days दिन बकाया',
        'today'        => 'आज समाप्त होगा',
        'days_left'    => ':days दिन शेष',
    ],

    'actions' => [
        'renew'         => 'अभी नवीनीकरण करें',
        'view_profile'  => 'प्रोफ़ाइल देखें',
        'send_reminder' => 'रिमाइंडर भेजें',
    ],

    'drawer' => [
        'title'          => 'सदस्यता नवीनीकरण',
        'member_label'   => 'सदस्य',
        'plan'           => 'योजना',
        'start_date'     => 'नई प्रारंभ तिथि',
        'expiry_preview' => 'नई समाप्ति पूर्वावलोकन',
        'payment_amount' => 'भुगतान राशि',
        'payment_method' => 'भुगतान विधि',
        'notes'          => 'नोट्स',
        'btn_renew'      => 'नवीनीकरण की पुष्टि करें',
        'btn_cancel'     => 'रद्द करें',
        'calculating'    => 'गणना हो रही है…',
        'select_plan'    => 'योजना चुनें',
        'select_method'  => 'विधि चुनें',
        'methods' => [
            'cash'   => 'नकद',
            'upi'    => 'UPI',
            'card'   => 'कार्ड',
            'bank'   => 'बैंक ट्रांसफर',
            'cheque' => 'चेक',
        ],
    ],

    'empty' => [
        'no_renewals' => 'कोई नवीनीकरण बकाया नहीं।',
        'description' => 'चयनित फ़िल्टर से कोई सदस्य नहीं मिला।',
    ],

    'flash' => [
        'renewed' => ':name की सदस्यता नवीनीकृत हो गई। नई समाप्ति: :expiry।',
    ],
];

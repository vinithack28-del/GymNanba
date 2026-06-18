<?php

return [
    'title'          => 'सदस्य',
    'add_member'     => 'सदस्य जोड़ें',
    'edit_member'    => 'सदस्य संपादित करें',
    'showing_branch' => 'केवल इस शाखा के सदस्य दिखाए जा रहे हैं',

    'stats' => [
        'total'    => 'कुल सदस्य',
        'active'   => 'सक्रिय',
        'inactive' => 'निष्क्रिय',
        'expired'  => 'समाप्त',
    ],

    'filters' => [
        'search_placeholder' => 'नाम, फोन, ईमेल, आईडी खोजें…',
        'all_statuses'       => 'सभी स्थिति',
        'all_genders'        => 'सभी लिंग',
        'clear_filters'      => 'फ़िल्टर हटाएं',
        'export'             => 'निर्यात करें',
    ],

    'statuses' => [
        'active'   => 'सक्रिय',
        'inactive' => 'निष्क्रिय',
        'expired'  => 'समाप्त',
        'frozen'   => 'स्थगित',
    ],

    'genders' => [
        'male'   => 'पुरुष',
        'female' => 'महिला',
        'other'  => 'अन्य',
    ],

    'table' => [
        'id'      => 'आईडी',
        'member'  => 'सदस्य',
        'phone'   => 'फोन',
        'plan'    => 'योजना',
        'joined'  => 'जुड़ने की तिथि',
        'expires' => 'समाप्ति',
        'status'  => 'स्थिति',
        'balance' => 'शेष राशि',
        'showing' => ':first–:last दिखाया जा रहा है, कुल :total सदस्य',
    ],

    'actions' => [
        'view_profile'   => 'प्रोफ़ाइल देखें',
        'edit'           => 'संपादित करें',
        'collect_fee'    => 'शुल्क लें',
        'delete'         => 'हटाएं',
        'delete_confirm' => ':name को हटाएं? यह पूर्ववत नहीं किया जा सकता।',
        'toggle_status'  => 'स्थिति बदलने के लिए क्लिक करें',
    ],

    'empty' => [
        'no_match'      => 'आपकी खोज से कोई सदस्य नहीं मिला।',
        'try_adjusting' => 'अपने फ़िल्टर समायोजित करने का प्रयास करें।',
        'clear_all'     => 'सभी फ़िल्टर हटाएं',
        'no_members'    => 'अभी तक कोई सदस्य नहीं।',
        'get_started'   => 'शुरू करने के लिए अपना पहला सदस्य जोड़ें।',
    ],

    'form' => [
        'create_title'  => 'नया सदस्य जोड़ें',
        'edit_title'    => 'सदस्य संपादित करें',
        'personal_info' => 'व्यक्तिगत जानकारी',
        'membership'    => 'सदस्यता विवरण',
        'payment'       => 'प्रारंभिक भुगतान',
        'notes_section' => 'नोट्स',
        'member_info'   => 'सदस्य जानकारी',

        'name'          => 'पूरा नाम',
        'phone'         => 'फोन नंबर',
        'email'         => 'ईमेल पता',
        'dob'           => 'जन्म तिथि',
        'gender'        => 'लिंग',
        'address'       => 'पता',
        'branch'        => 'शाखा',
        'plan'          => 'सदस्यता योजना',
        'start_date'    => 'प्रारंभ तिथि',
        'expiry_preview'=> 'समाप्ति पूर्वावलोकन',
        'status'        => 'स्थिति',
        'notes'         => 'नोट्स',
        'payment_amount'=> 'भुगतान राशि',
        'payment_method'=> 'भुगतान विधि',
        'payment_ref'   => 'संदर्भ / रसीद नं.',

        'select_plan'   => 'योजना चुनें',
        'select_branch' => 'शाखा चुनें',
        'select_gender' => 'लिंग चुनें',
        'select_method' => 'विधि चुनें',

        'methods' => [
            'cash'   => 'नकद',
            'upi'    => 'UPI',
            'card'   => 'कार्ड',
            'bank'   => 'बैंक ट्रांसफर',
            'cheque' => 'चेक',
        ],

        'statuses' => [
            'active'   => 'सक्रिय',
            'inactive' => 'निष्क्रिय',
            'frozen'   => 'स्थगित',
        ],

        'genders' => [
            'male'   => 'पुरुष',
            'female' => 'महिला',
            'other'  => 'अन्य',
        ],

        'member_id'    => 'सदस्य आईडी',
        'joined_on'    => 'जुड़ने की तिथि',
        'expiry_date'  => 'समाप्ति तिथि',
        'last_updated' => 'अंतिम अपडेट',
        'never'        => 'कभी नहीं',
        'calculating'  => 'गणना हो रही है…',

        'btn_create'   => 'सदस्य जोड़ें',
        'btn_update'   => 'बदलाव सहेजें',
        'btn_cancel'   => 'रद्द करें',
        'required'     => 'आवश्यक',
    ],

    'flash' => [
        'created' => 'सदस्य सफलतापूर्वक जोड़ा गया।',
        'updated' => 'सदस्य सफलतापूर्वक अपडेट किया गया।',
        'deleted' => 'सदस्य हटा दिया गया।',
        'toggled' => 'सदस्य की स्थिति अपडेट की गई।',
    ],
];

<?php

return [
    // Admin panel keys
    'eyebrow'    => 'मॉड्यूल 1 और 5',
    'heading'    => 'सेटिंग्स और भाषाएँ',
    'subheading' => 'सुरक्षा स्थिति और प्लेटफ़ॉर्म-स्तरीय भाषा प्रबंधन का सारांश।',

    // Tenant settings — Module 14
    'title'    => 'सेटिंग्स',
    'subtitle' => 'अपने जिम प्रोफ़ाइल, खाते और प्राथमिकताएं प्रबंधित करें',

    'nav' => [
        'profile'      => 'जिम प्रोफ़ाइल',
        'account'      => 'मेरा खाता',
        'integrations' => 'इंटीग्रेशन',
        'language'     => 'भाषा',
        'subscription' => 'बिलिंग और सदस्यता',
        'data'         => 'डेटा और गोपनीयता',
    ],

    'profile' => [
        'saved' => 'जिम प्रोफ़ाइल सफलतापूर्वक सहेजी गई।',
        'section' => [
            'media'   => 'लोगो और कवर फोटो',
            'basic'   => 'बुनियादी जानकारी',
            'address' => 'पता',
            'legal'   => 'कानूनी और कर',
            'social'  => 'सोशल लिंक',
            'hours'   => 'कार्य घंटे',
        ],
        'field' => [
            'gym_name'      => 'जिम का नाम',
            'business_type' => 'व्यवसाय प्रकार',
            'logo'          => 'लोगो',
            'cover'         => 'कवर फोटो',
            'phone'         => 'संपर्क फोन',
            'email'         => 'संपर्क ईमेल',
            'website'       => 'वेबसाइट',
            'about'         => 'परिचय / विवरण',
            'address1'      => 'पता पंक्ति 1',
            'address2'      => 'पता पंक्ति 2',
            'city'          => 'शहर',
            'state'         => 'राज्य',
            'pin'           => 'पिन कोड',
            'gstin'         => 'GSTIN',
            'pan'           => 'PAN',
            'reg_number'    => 'व्यवसाय पंजीकरण संख्या',
        ],
        'business_types' => [
            'gym'          => 'जिम',
            'yoga_studio'  => 'योगा स्टूडियो',
            'crossfit'     => 'CrossFit',
            'martial_arts' => 'मार्शल आर्ट्स',
            'dance'        => 'डांस स्टूडियो',
            'sports_club'  => 'स्पोर्ट्स क्लब',
            'other'        => 'अन्य',
        ],
        'hours' => ['closed' => 'बंद'],
    ],

    'account' => [
        'saved'                     => 'खाता सफलतापूर्वक अपडेट किया गया।',
        'password_changed'          => 'पासवर्ड बदल दिया गया। सभी अन्य डिवाइस साइन आउट हो गए हैं।',
        'session_terminated'        => 'सत्र समाप्त किया गया।',
        'other_sessions_terminated' => 'सभी अन्य सत्र साइन आउट हो गए हैं।',
        'email_readonly'            => 'ईमेल यहाँ नहीं बदली जा सकती। अपडेट के लिए सहायता से संपर्क करें।',
        'password_rules'            => 'न्यूनतम 12 अक्षर · अपरकेस · लोअरकेस · संख्या · प्रतीक',
        'change_password'           => 'पासवर्ड बदलें',
        'section' => [
            'profile'  => 'प्रोफ़ाइल जानकारी',
            'password' => 'पासवर्ड बदलें',
            'sessions' => 'सक्रिय सत्र',
        ],
        'field' => [
            'name'             => 'पूरा नाम',
            'email'            => 'ईमेल',
            'phone'            => 'फोन',
            'avatar'           => 'प्रोफ़ाइल फोटो',
            'current_password' => 'वर्तमान पासवर्ड',
            'new_password'     => 'नया पासवर्ड',
            'confirm_password' => 'नया पासवर्ड की पुष्टि',
        ],
        'sessions' => [
            'current'         => 'वर्तमान',
            'terminate'       => 'समाप्त करें',
            'sign_out_others' => 'सभी अन्य डिवाइस साइन आउट करें',
        ],
    ],

    'integrations' => [
        'saved'         => ':name इंटीग्रेशन सहेजा गया।',
        'disconnected'  => ':name डिस्कनेक्ट किया गया।',
        'connected'     => 'जुड़ा हुआ',
        'not_connected' => 'नहीं जुड़ा',
        'connect'       => 'जोड़ें',
        'update'        => 'अपडेट करें',
        'disconnect'    => 'डिस्कनेक्ट करें',
        'test'          => 'कनेक्शन जांचें',
        'whatsapp' => [
            'desc'           => 'WhatsApp Business API के माध्यम से स्वचालित संदेश भेजें',
            'phone_number_id'=> 'बिजनेस फोन नंबर ID',
            'api_token'      => 'API एक्सेस टोकन',
            'verify_token'   => 'वेरिफाई टोकन',
            'webhook_url'    => 'Webhook URL',
        ],
        'razorpay' => [
            'desc'           => 'Razorpay के माध्यम से ऑनलाइन भुगतान स्वीकार करें',
            'key_id'         => 'Key ID',
            'key_secret'     => 'Key Secret',
            'webhook_secret' => 'Webhook Secret',
            'webhook_url'    => 'Webhook URL',
            'test_mode'      => 'सैंडबॉक्स / टेस्ट मोड',
        ],
        'biometric' => [
            'name'          => 'बायोमेट्रिक डिवाइस',
            'desc'          => 'ZKTeco, eSSL या Mantra से उपस्थिति डेटा सिंक करें',
            'device_type'   => 'डिवाइस प्रकार',
            'ip'            => 'IP पता',
            'port'          => 'पोर्ट',
            'serial'        => 'डिवाइस सीरियल (वैकल्पिक)',
            'sync'          => 'सिंक अनुसूची',
            'sync_realtime' => 'वास्तविक समय',
            'sync_5'        => 'हर 5 मिनट',
            'sync_15'       => 'हर 15 मिनट',
        ],
        'tally' => [
            'name'      => 'Tally / लेखा',
            'desc'      => 'भुगतान और खर्च डेटा Tally में निर्यात करें',
            'format'    => 'निर्यात प्रारूप',
            'auto_sync' => 'रोज रात 2:00 बजे IST पर स्वचालित सिंक',
        ],
    ],

    'language' => [
        'saved' => 'भाषा प्राथमिकता अपडेट की गई।',
        'apply' => 'भाषा लागू करें',
        'note'  => 'यह केवल आपके पोर्टल की भाषा बदलता है।',
        'section' => [
            'title' => 'पोर्टल भाषा',
            'desc'  => 'अपने जिम ओनर पोर्टल के लिए भाषा चुनें।',
        ],
    ],

    'subscription' => [
        'no_subscription' => 'कोई सक्रिय सदस्यता नहीं मिली।',
        'contact_note'    => 'अपनी योजना बदलने के लिए GymOS सहायता से संपर्क करें:',
        'section' => [
            'current'  => 'वर्तमान योजना',
            'invoices' => 'चालान इतिहास',
        ],
        'field' => [
            'plan'           => 'योजना',
            'renewal'        => 'नवीनीकरण तिथि',
            'amount'         => 'मासिक राशि',
            'branches'       => 'शाखाएं',
            'branches_used'  => 'शाखाएं उपयोग में',
            'members'        => 'सदस्य',
            'members_active' => 'सक्रिय सदस्य',
        ],
        'col' => [
            'invoice' => 'चालान #',
            'date'    => 'तारीख',
            'amount'  => 'राशि',
            'status'  => 'स्थिति',
        ],
    ],

    'data' => [
        'export_queued'      => 'निर्यात शुरू हुआ। डाउनलोड लिंक के साथ ईमेल भेजी जाएगी।',
        'deletion_requested' => 'खाता हटाने का अनुरोध सबमिट किया गया।',
        'export' => [
            'title'      => 'सभी डेटा निर्यात करें',
            'desc'       => 'CSV फ़ाइलों के रूप में अपने सभी जिम डेटा का ZIP डाउनलोड करें।',
            'button'     => 'मेरा जिम डेटा निर्यात करें',
            'email_note' => 'डाउनलोड लिंक ईमेल किया जाएगा (48 घंटे के लिए वैध)।',
        ],
        'delete' => [
            'title'           => 'खाता हटाने का अनुरोध',
            'desc'            => 'अपना GymOS खाता और सभी डेटा हटाने का अनुरोध करें।',
            'button'          => 'खाता हटाने का अनुरोध करें',
            'confirm_title'   => 'क्या आप सुनिश्चित हैं?',
            'confirm_desc'    => 'आपका डेटा 90 दिनों के बाद हटा दिया जाएगा। यह क्रिया वापस नहीं की जा सकती।',
            'confirm_checkbox'=> 'मैं समझता/समझती हूं कि मेरा डेटा 90 दिनों के बाद स्थायी रूप से हटा दिया जाएगा।',
            'submit'          => 'हटाने का अनुरोध सबमिट करें',
        ],
    ],
];

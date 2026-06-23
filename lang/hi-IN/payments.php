<?php

return [
    'nav' => [
        'payments'  => 'भुगतान',
        'collect'   => 'शुल्क एकत्र करें',
        'history'   => 'भुगतान इतिहास',
        'dues'      => 'बकाया राशि',
    ],

    'collect' => [
        'subtitle'            => 'सदस्य से भुगतान दर्ज करें',
        'member'              => 'सदस्य',
        'search_placeholder'  => 'नाम, फोन या सदस्य कोड से खोजें…',
        'no_results'          => 'कोई सदस्य नहीं मिला',
        'change_member'       => 'सदस्य बदलें',
        'select_member_first' => 'कृपया पहले एक सदस्य चुनें',
        'payment_details'     => 'भुगतान विवरण',
        'plan'                => 'सदस्यता योजना',
        'no_plan'             => 'कोई योजना नहीं',
        'amount'              => 'राशि',
        'payment_date'        => 'भुगतान तिथि',
        'apply_gst'           => 'GST लागू करें',
        'method'              => 'भुगतान विधि',
        'reference'           => 'संदर्भ / लेनदेन ID',
        'reference_placeholder' => 'UPI रेफरेंस, चेक नंबर…',
        'notes'               => 'नोट्स',
        'submit'              => 'भुगतान एकत्र करें',
        'summary'             => 'सारांश',
        'base_amount'         => 'मूल राशि',
        'gst'                 => 'GST',
        'total'               => 'कुल',
        'receipt_note'        => 'एकत्र करने के बाद रसीद बनाई जाएगी।',
    ],

    'history' => [
        'subtitle'           => 'सभी सदस्य भुगतान रिकॉर्ड',
        'search_placeholder' => 'रसीद, नाम या फोन से खोजें…',
        'method'             => 'विधि',
        'status'             => 'स्थिति',
        'date_from'          => 'से',
        'date_to'            => 'तक',
        'today_count'        => 'आज के भुगतान',
        'today_total'        => 'आज की आय',
        'receipt'            => 'रसीद',
        'member'             => 'सदस्य',
        'plan'               => 'योजना',
        'amount'             => 'राशि',
        'date'               => 'तिथि',
        'empty'              => 'कोई भुगतान नहीं मिला',
        'receipt_btn'        => 'रसीद',
        'void_btn'           => 'रद्द करें',
    ],

    'dues' => [
        'subtitle'           => 'बकाया शेष वाले सदस्य',
        'search_placeholder' => 'नाम या फोन से खोजें…',
        'total_due'          => 'कुल बकाया राशि',
        'members_due'        => 'बकाया वाले सदस्य',
        'member'             => 'सदस्य',
        'amount_due'         => 'बकाया राशि',
        'empty'              => 'कोई बकाया नहीं',
        'collect_btn'        => 'एकत्र करें',
    ],

    'void' => [
        'title'         => 'भुगतान रद्द करें',
        'desc_prefix'   => 'क्या आप रसीद रद्द करना चाहते हैं',
        'reason_label'  => 'रद्द करने का कारण',
        'select_reason' => 'कारण चुनें',
        'confirm'       => 'भुगतान रद्द करें',
    ],

    'void_reasons' => [
        'data_entry_error'  => 'डेटा प्रविष्टि त्रुटि',
        'duplicate_payment' => 'डुप्लिकेट भुगतान',
        'refund'            => 'वापसी',
        'other'             => 'अन्य',
    ],

    'methods' => [
        'cash'   => 'नकद',
        'upi'    => 'UPI',
        'card'   => 'कार्ड',
        'bank'   => 'बैंक ट्रांसफर',
        'cheque' => 'चेक',
        'split'  => 'विभाजित',
    ],

    'status' => [
        'active' => 'सक्रिय',
        'voided' => 'रद्द',
    ],

    'receipt' => [
        'title'        => 'भुगतान रसीद',
        'member'       => 'सदस्य',
        'plan'         => 'योजना',
        'amount'       => 'राशि',
        'gst'          => 'GST',
        'total'        => 'कुल',
        'method'       => 'विधि',
        'reference'    => 'संदर्भ',
        'date'         => 'तिथि',
        'collected_by' => 'एकत्र किया',
        'notes'        => 'नोट्स',
        'print'        => 'रसीद प्रिंट करें',
        'footer'       => 'भुगतान के लिए धन्यवाद!',
    ],

    'flash' => [
        'collected'      => 'भुगतान एकत्र हुआ। रसीद: :receipt',
        'voided'         => 'भुगतान :receipt रद्द कर दिया गया।',
        'already_voided' => 'यह भुगतान पहले से रद्द है।',
        'void_too_old'   => '90 दिन से पुराने भुगतान रद्द नहीं किए जा सकते।',
    ],
];

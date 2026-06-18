<?php

return [
    'nav' => [
        'invoices' => 'चालान',
        'create'   => 'चालान बनाएं',
    ],

    'index' => [
        'subtitle'           => 'सभी सदस्य भुगतानों के लिए GST-अनुरूप चालान',
        'search_placeholder' => 'चालान #, नाम या फोन से खोजें…',
        'status'             => 'स्थिति',
        'date_from'          => 'से',
        'date_to'            => 'तक',
        'empty'              => 'कोई चालान नहीं मिला',
        'empty_title'        => 'अभी तक कोई चालान नहीं',
        'empty_desc'         => 'जब भुगतान एकत्र किया जाता है तो चालान स्वचालित रूप से बनाए जाते हैं। आप कस्टम शुल्क के लिए मैन्युअल चालान भी बना सकते हैं।',
    ],

    'table' => [
        'number'      => 'चालान #',
        'date'        => 'तिथि',
        'member'      => 'सदस्य',
        'description' => 'विवरण',
        'subtotal'    => 'उप-कुल',
        'gst'         => 'GST',
        'total'       => 'कुल',
        'status'      => 'स्थिति',
        'view_btn'    => 'देखें',
        'void_btn'    => 'रद्द करें',
    ],

    'create' => [
        'subtitle'            => 'गैर-मानक शुल्क के लिए मैन्युअल चालान बनाएं',
        'member'              => 'बिल प्रेषित',
        'search_placeholder'  => 'नाम, फोन या सदस्य कोड से खोजें…',
        'no_results'          => 'कोई सदस्य नहीं मिला',
        'change_member'       => 'सदस्य बदलें',
        'select_member_first' => 'कृपया पहले एक सदस्य चुनें',
        'add_line_first'      => 'कृपया कम से कम एक पंक्ति जोड़ें',
        'details'             => 'चालान विवरण',
        'invoice_date'        => 'चालान तिथि',
        'due_date'            => 'देय तिथि',
        'no_branch'           => 'कोई शाखा नहीं',
        'line_items'          => 'पंक्ति आइटम',
        'add_line'            => 'पंक्ति जोड़ें',
        'col_desc'            => 'विवरण',
        'col_qty'             => 'मात्रा',
        'col_rate'            => 'दर (पैसे)',
        'col_gst'             => 'GST %',
        'col_amount'          => 'राशि',
        'notes'               => 'नोट्स',
        'submit'              => 'चालान बनाएं',
        'summary'             => 'सारांश',
        'subtotal'            => 'उप-कुल',
        'gst'                 => 'GST',
        'total'               => 'कुल',
    ],

    'show' => [
        'title'           => 'चालान',
        'print'           => 'प्रिंट',
        'bill_to'         => 'बिल प्रेषित',
        'date'            => 'चालान तिथि',
        'due_date'        => 'देय तिथि',
        'col_desc'        => 'विवरण',
        'col_qty'         => 'मात्रा',
        'col_rate'        => 'दर',
        'col_amount'      => 'राशि',
        'subtotal'        => 'उप-कुल',
        'total'           => 'कुल',
        'amount_words'    => 'राशि शब्दों में',
        'payment_method'  => 'भुगतान विधि',
        'place_of_supply' => 'आपूर्ति का स्थान',
        'footer'          => 'आपके व्यवसाय के लिए धन्यवाद!',
    ],

    'void' => [
        'title'         => 'चालान रद्द करें',
        'desc_prefix'   => 'क्या आप चालान रद्द करना चाहते हैं',
        'reason_label'  => 'रद्द करने का कारण',
        'select_reason' => 'कारण चुनें',
        'confirm'       => 'चालान रद्द करें',
    ],

    'void_reasons' => [
        'data_entry_error'  => 'डेटा प्रविष्टि त्रुटि',
        'duplicate_invoice' => 'डुप्लिकेट चालान',
        'cancelled_service' => 'सेवा रद्द',
        'other'             => 'अन्य',
    ],

    'status' => [
        'paid'    => 'भुगतान हुआ',
        'unpaid'  => 'अभी बकाया',
        'partial' => 'आंशिक',
        'void'    => 'रद्द',
    ],

    'flash' => [
        'created'        => 'चालान :number बनाया गया।',
        'voided'         => 'चालान :number रद्द कर दिया गया।',
        'already_voided' => 'यह चालान पहले से रद्द है।',
    ],
];

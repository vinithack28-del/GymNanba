<?php

return [
    'nav' => [
        'expenses' => 'खर्च',
        'add'      => 'खर्च जोड़ें',
        'edit'     => 'खर्च संपादित करें',
    ],

    'index' => [
        'subtitle'           => 'सभी जिम परिचालन खर्चों को ट्रैक करें',
        'search_placeholder' => 'विवरण, विक्रेता, संदर्भ खोजें…',
        'empty_title'        => 'कोई खर्च दर्ज नहीं',
        'empty_desc'         => 'लागत ट्रैक करने और P&L रिपोर्ट तैयार करने के लिए अपने जिम के खर्चों को रिकॉर्ड करें।',
    ],

    'create' => [
        'subtitle' => 'नया परिचालन खर्च दर्ज करें',
        'submit'   => 'खर्च सहेजें',
    ],

    'summary' => [
        'this_month'    => 'इस महीने',
        'vs_last_month' => 'पिछले महीने की तुलना में',
        'no_data'       => 'इस महीने कोई खर्च दर्ज नहीं।',
    ],

    'table' => [
        'date'        => 'तिथि',
        'category'    => 'श्रेणी',
        'description' => 'विवरण',
        'amount'      => 'राशि',
        'method'      => 'विधि',
        'branch'      => 'शाखा',
        'status'      => 'स्थिति',
    ],

    'form' => [
        'date'              => 'तिथि',
        'category'          => 'श्रेणी',
        'sub_category'      => 'उप-श्रेणी',
        'description'       => 'विवरण',
        'amount'            => 'राशि',
        'gst'               => 'GST भुगतान',
        'method'            => 'भुगतान विधि',
        'vendor'            => 'भुगतान प्रति (विक्रेता)',
        'reference'         => 'संदर्भ / बिल नंबर',
        'reference_placeholder' => 'बिल नंबर, चालान संदर्भ…',
        'receipt_url'       => 'रसीद URL',
        'notes'             => 'नोट्स',
        'select_branch'     => 'शाखा चुनें',
        'select_category'   => 'श्रेणी चुनें',
        'select_sub'        => 'उप-श्रेणी चुनें',
        'salary_section'    => 'वेतन विवरण',
        'staff_member'      => 'कर्मचारी',
        'select_staff'      => 'कर्मचारी चुनें',
        'salary_month'      => 'वेतन माह',
        'is_recurring'      => 'यह एक आवर्ती खर्च है',
        'frequency'         => 'आवृत्ति',
        'recurrence_end'    => 'समाप्ति तिथि (वैकल्पिक)',
    ],

    'categories' => [
        'rent'          => 'किराया',
        'utilities'     => 'उपयोगिताएं',
        'salaries'      => 'वेतन',
        'equipment'     => 'उपकरण',
        'marketing'     => 'विपणन',
        'supplies'      => 'आपूर्ति',
        'insurance'     => 'बीमा',
        'software'      => 'सॉफ्टवेयर',
        'miscellaneous' => 'विविध',
    ],

    'sub_categories' => [
        'rent'      => ['main_hall' => 'मुख्य हॉल', 'studio' => 'स्टूडियो', 'storage' => 'भंडारण', 'office' => 'कार्यालय'],
        'utilities' => ['electricity' => 'बिजली', 'water' => 'पानी', 'internet' => 'इंटरनेट', 'phone' => 'फोन'],
        'salaries'  => ['full_time' => 'पूर्णकालिक', 'part_time' => 'अंशकालिक', 'contract' => 'अनुबंध', 'bonus' => 'बोनस'],
        'equipment' => ['purchase' => 'खरीद', 'repair' => 'मरम्मत', 'maintenance' => 'रखरखाव', 'replacement' => 'प्रतिस्थापन'],
        'marketing' => ['social_media' => 'सोशल मीडिया', 'flyers' => 'फ्लायर', 'events' => 'इवेंट', 'promotions' => 'प्रचार'],
        'supplies'  => ['cleaning' => 'सफाई', 'consumables' => 'उपभोग्य', 'stationery' => 'स्टेशनरी', 'toiletries' => 'शौचालय सामग्री'],
        'insurance' => ['liability' => 'देनदारी', 'equipment' => 'उपकरण', 'health' => 'स्वास्थ्य'],
        'software'  => ['gymos_subscription' => 'GymOS सदस्यता', 'other' => 'अन्य'],
    ],

    'methods' => [
        'cash'   => 'नकद',
        'upi'    => 'UPI',
        'bank'   => 'बैंक ट्रांसफर',
        'cheque' => 'चेक',
        'card'   => 'कार्ड',
    ],

    'status' => [
        'pending'  => 'लंबित',
        'approved' => 'स्वीकृत',
        'rejected' => 'अस्वीकृत',
    ],

    'recurrence' => [
        'daily'   => 'दैनिक',
        'weekly'  => 'साप्ताहिक',
        'monthly' => 'मासिक',
        'annual'  => 'वार्षिक',
    ],

    'reject' => [
        'title'              => 'खर्च अस्वीकार करें',
        'reason_placeholder' => 'अस्वीकृति का कारण…',
        'confirm'            => 'अस्वीकार करें',
    ],

    'confirm_delete' => 'क्या आप इस खर्च को हटाना चाहते हैं?',

    'flash' => [
        'stored'         => 'खर्च सफलतापूर्वक दर्ज हुआ।',
        'stored_pending' => 'खर्च अनुमोदन के लिए सबमिट किया गया।',
        'updated'        => 'खर्च अपडेट हुआ।',
        'deleted'        => 'खर्च हटाया गया।',
        'approved'       => 'खर्च स्वीकृत हुआ।',
        'rejected'       => 'खर्च अस्वीकृत हुआ।',
    ],
];

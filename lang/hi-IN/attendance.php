<?php

return [

    'nav' => [
        'checkins' => 'चेक-इन',
        'walkins'  => 'वॉक-इन',
    ],

    'checkins' => [
        'title'       => 'सदस्य चेक-इन',
        'subtitle'    => 'आज कौन आया इसे ट्रैक करें',
        'date_label'  => 'तारीख',
        'branch_label'=> 'शाखा',
        'method_label'=> 'तरीका',
        'all_branches'=> 'सभी शाखाएं',
        'all_methods' => 'सभी तरीके',
        'search_ph'   => 'सदस्य खोजें…',
        'export_csv'  => 'CSV डाउनलोड',
        'checkin_btn' => 'चेक इन करें',
    ],

    'stats' => [
        'total'     => 'कुल चेक-इन',
        'unique'    => 'अद्वितीय सदस्य',
        'peak_hour' => 'पीक समय',
    ],

    'table' => [
        'member'     => 'सदस्य',
        'plan'       => 'योजना',
        'branch'     => 'शाखा',
        'method'     => 'तरीका',
        'time_in'    => 'समय आगमन',
        'time_out'   => 'समय प्रस्थान',
        'duration'   => 'अवधि',
        'logged_by'  => 'दर्ज किया',
        'actions'    => 'कार्रवाई',
    ],

    'methods' => [
        'manual'    => 'मैन्युअल',
        'qr'        => 'QR',
        'biometric' => 'बायोमेट्रिक',
    ],

    'empty' => [
        'title'        => 'अभी कोई चेक-इन नहीं',
        'subtitle'     => 'आज चेक-इन करने वाले सदस्य यहां दिखेंगे।',
        'no_match'     => 'फ़िल्टर से कोई चेक-इन नहीं मिला',
        'try_adjusting'=> 'फ़िल्टर बदलें या हटाएं।',
        'clear_all'    => 'फ़िल्टर साफ़ करें',
        'checkin_now'  => 'सदस्य चेक-इन करें',
    ],

    'checkin_drawer' => [
        'title'       => 'नया चेक-इन',
        'search_ph'   => 'नाम, फोन या ID से खोजें…',
        'no_results'  => 'कोई सदस्य नहीं मिला',
        'plan_expires'=> 'समाप्ति',
        'reason'      => 'कारण (वैकल्पिक)',
        'reason_ph'   => 'जैसे सुबह की कसरत',
        'method'      => 'तरीका',
        'confirm'     => 'चेक-इन पुष्टि करें',
        'cancel'      => 'रद्द करें',
    ],

    'walkins' => [
        'title'       => 'वॉक-इन',
        'subtitle'    => 'विज़िटर, डे-पास और ट्रायल दर्ज करें',
        'add_walkin'  => 'वॉक-इन जोड़ें',
        'date_label'  => 'तारीख',
        'branch_label'=> 'शाखा',
        'all_branches'=> 'सभी शाखाएं',
    ],

    'walkin_stats' => [
        'total'   => 'आज वॉक-इन',
        'revenue' => 'संग्रहित राशि',
    ],

    'walkin_form' => [
        'title'          => 'वॉक-इन दर्ज करें',
        'name'           => 'विज़िटर का नाम',
        'name_ph'        => 'पूरा नाम',
        'phone'          => 'फोन',
        'phone_ph'       => '10 अंकों का मोबाइल नंबर',
        'purpose'        => 'उद्देश्य',
        'fee'            => 'शुल्क (₹)',
        'fee_ph'         => 'मुफ़्त हो तो 0',
        'payment_method' => 'भुगतान का तरीका',
        'reference'      => 'संदर्भ / रसीद नं.',
        'reference_ph'   => 'वैकल्पिक',
        'notes'          => 'नोट्स',
        'notes_ph'       => 'अतिरिक्त जानकारी',
        'guest_of'       => 'सदस्य के अतिथि',
        'guest_of_ph'    => 'सदस्य चुनें (वैकल्पिक)',
        'branch'         => 'शाखा',
        'submit'         => 'वॉक-इन दर्ज करें',
        'cancel'         => 'रद्द करें',
    ],

    'purposes' => [
        'day_pass'   => 'डे पास',
        'free_trial' => 'मुफ़्त ट्रायल',
        'inquiry'    => 'जानकारी',
        'guest'      => 'अतिथि',
    ],

    'payment_methods' => [
        'cash' => 'नकद',
        'upi'  => 'UPI',
        'card' => 'कार्ड',
    ],

    'walkin_table' => [
        'visitor'  => 'विज़िटर',
        'purpose'  => 'उद्देश्य',
        'fee'      => 'शुल्क',
        'payment'  => 'भुगतान',
        'guest_of' => 'अतिथि',
        'branch'   => 'शाखा',
        'time'     => 'समय',
    ],

    'walkin_empty' => [
        'title'    => 'आज कोई वॉक-इन नहीं',
        'subtitle' => 'विज़िटर, डे-पास या ट्रायल दर्ज करें।',
        'add_now'  => 'वॉक-इन जोड़ें',
    ],

    'flash' => [
        'checked_in'    => 'सदस्य सफलतापूर्वक चेक-इन हुआ।',
        'checked_out'   => 'चेक-आउट दर्ज किया गया।',
        'deleted'       => 'चेक-इन रिकॉर्ड हटाया गया।',
        'walkin_logged' => 'वॉक-इन सफलतापूर्वक दर्ज किया गया।',
    ],

];

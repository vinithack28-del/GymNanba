<?php

return [
    'nav' => [
        'expenses' => 'వ్యయాలు',
        'add'      => 'వ్యయం జోడించు',
        'edit'     => 'వ్యయం సవరించు',
    ],

    'index' => [
        'subtitle'           => 'అన్ని జిమ్ కార్యాచరణ వ్యయాలను ట్రాక్ చేయండి',
        'search_placeholder' => 'వివరణ, విక్రేత, రెఫరెన్స్ శోధించండి…',
        'empty_title'        => 'వ్యయాలు ఏవీ నమోదు కాలేదు',
        'empty_desc'         => 'ఖర్చులు ట్రాక్ చేయడానికి మరియు P&L నివేదికలు రూపొందించడానికి మీ జిమ్ వ్యయాలను నమోదు చేయండి.',
    ],

    'create' => [
        'subtitle' => 'కొత్త కార్యాచరణ వ్యయాన్ని నమోదు చేయండి',
        'submit'   => 'వ్యయం సేవ్ చేయి',
    ],

    'summary' => [
        'this_month'    => 'ఈ నెల',
        'vs_last_month' => 'గత నెలతో పోలిస్తే',
        'no_data'       => 'ఈ నెల వ్యయాలు ఏవీ నమోదు కాలేదు.',
    ],

    'table' => [
        'date'        => 'తేదీ',
        'category'    => 'వర్గం',
        'description' => 'వివరణ',
        'amount'      => 'మొత్తం',
        'method'      => 'పద్ధతి',
        'branch'      => 'శాఖ',
        'status'      => 'స్థితి',
    ],

    'form' => [
        'date'              => 'తేదీ',
        'category'          => 'వర్గం',
        'sub_category'      => 'ఉప-వర్గం',
        'description'       => 'వివరణ',
        'amount'            => 'మొత్తం',
        'gst'               => 'GST చెల్లించింది',
        'method'            => 'చెల్లింపు పద్ధతి',
        'vendor'            => 'ఎవరికి చెల్లించారు',
        'reference'         => 'రెఫరెన్స్ / బిల్ నం.',
        'reference_placeholder' => 'బిల్ నం., ఇన్వాయిస్ రెఫ్…',
        'receipt_url'       => 'రసీదు URL',
        'notes'             => 'గమనికలు',
        'select_branch'     => 'శాఖ ఎంచుకోండి',
        'select_category'   => 'వర్గం ఎంచుకోండి',
        'select_sub'        => 'ఉప-వర్గం ఎంచుకోండి',
        'salary_section'    => 'జీతం వివరాలు',
        'staff_member'      => 'సిబ్బంది',
        'select_staff'      => 'సిబ్బంది ఎంచుకోండి',
        'salary_month'      => 'జీతం నెల',
        'is_recurring'      => 'ఇది పునరావృత వ్యయం',
        'frequency'         => 'పౌనఃపున్యం',
        'recurrence_end'    => 'ముగింపు తేదీ (ఐచ్ఛికం)',
    ],

    'categories' => [
        'rent'          => 'అద్దె',
        'utilities'     => 'సేవలు',
        'salaries'      => 'జీతాలు',
        'equipment'     => 'పరికరాలు',
        'marketing'     => 'మార్కెటింగ్',
        'supplies'      => 'సరఫరాలు',
        'insurance'     => 'భీమా',
        'software'      => 'సాఫ్ట్‌వేర్',
        'miscellaneous' => 'ఇతరాలు',
    ],

    'sub_categories' => [
        'rent'      => ['main_hall' => 'ప్రధాన హాల్', 'studio' => 'స్టూడియో', 'storage' => 'నిల్వ', 'office' => 'కార్యాలయం'],
        'utilities' => ['electricity' => 'విద్యుత్', 'water' => 'నీరు', 'internet' => 'ఇంటర్నెట్', 'phone' => 'ఫోన్'],
        'salaries'  => ['full_time' => 'పూర్తి-సమయం', 'part_time' => 'పాక్షిక-సమయం', 'contract' => 'కాంట్రాక్ట్', 'bonus' => 'బోనస్'],
        'equipment' => ['purchase' => 'కొనుగోలు', 'repair' => 'మరమ్మత్తు', 'maintenance' => 'నిర్వహణ', 'replacement' => 'భర్తీ'],
        'marketing' => ['social_media' => 'సోషల్ మీడియా', 'flyers' => 'ఫ్లైయర్లు', 'events' => 'ఈవెంట్లు', 'promotions' => 'ప్రచారాలు'],
        'supplies'  => ['cleaning' => 'శుభ్రపరచడం', 'consumables' => 'వినియోగ వస్తువులు', 'stationery' => 'స్టేషనరీ', 'toiletries' => 'శౌచాలయ వస్తువులు'],
        'insurance' => ['liability' => 'బాధ్యత', 'equipment' => 'పరికరాలు', 'health' => 'ఆరోగ్యం'],
        'software'  => ['gymos_subscription' => 'GymOS సభ్యత్వం', 'other' => 'ఇతర'],
    ],

    'methods' => [
        'cash'   => 'నగదు',
        'upi'    => 'UPI',
        'bank'   => 'బ్యాంక్ బదిలీ',
        'cheque' => 'చెక్',
        'card'   => 'కార్డు',
    ],

    'status' => [
        'pending'  => 'పెండింగ్',
        'approved' => 'ఆమోదించబడింది',
        'rejected' => 'తిరస్కరించబడింది',
    ],

    'recurrence' => [
        'daily'   => 'రోజువారీ',
        'weekly'  => 'వారానికొకసారి',
        'monthly' => 'నెలవారీ',
        'annual'  => 'వార్షిక',
    ],

    'reject' => [
        'title'              => 'వ్యయాన్ని తిరస్కరించు',
        'reason_placeholder' => 'తిరస్కరణ కారణం…',
        'confirm'            => 'తిరస్కరించు',
    ],

    'confirm_delete' => 'ఈ వ్యయాన్ని తొలగించాలనుకుంటున్నారా?',

    'flash' => [
        'stored'         => 'వ్యయం విజయవంతంగా నమోదు చేయబడింది.',
        'stored_pending' => 'వ్యయం ఆమోదం కోసం సమర్పించబడింది.',
        'updated'        => 'వ్యయం నవీకరించబడింది.',
        'deleted'        => 'వ్యయం తొలగించబడింది.',
        'approved'       => 'వ్యయం ఆమోదించబడింది.',
        'rejected'       => 'వ్యయం తిరస్కరించబడింది.',
    ],
];

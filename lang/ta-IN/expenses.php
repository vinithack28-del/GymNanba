<?php

return [
    'nav' => [
        'expenses' => 'செலவுகள்',
        'add'      => 'செலவு சேர்',
        'edit'     => 'செலவு திருத்து',
    ],

    'index' => [
        'subtitle'           => 'அனைத்து ஜிம் செயல்பாட்டு செலவுகளை கண்காணிக்கவும்',
        'search_placeholder' => 'விளக்கம், விற்பனையாளர், குறிப்பு தேடுங்கள்…',
        'empty_title'        => 'செலவுகள் எதுவும் பதிவு இல்லை',
        'empty_desc'         => 'செலவுகளை கண்காணிக்கவும் P&L அறிக்கைகள் உருவாக்கவும் உங்கள் ஜிம் செலவுகளை பதிவு செய்யுங்கள்.',
    ],

    'create' => [
        'subtitle' => 'புதிய செயல்பாட்டு செலவை பதிவு செய்யுங்கள்',
        'submit'   => 'செலவு சேமி',
    ],

    'summary' => [
        'this_month'    => 'இந்த மாதம்',
        'vs_last_month' => 'கடந்த மாதத்துடன் ஒப்பிடும்போது',
        'no_data'       => 'இந்த மாதம் செலவுகள் எதுவும் இல்லை.',
    ],

    'table' => [
        'date'        => 'தேதி',
        'category'    => 'வகை',
        'description' => 'விளக்கம்',
        'amount'      => 'தொகை',
        'method'      => 'முறை',
        'branch'      => 'கிளை',
        'status'      => 'நிலை',
    ],

    'form' => [
        'date'              => 'தேதி',
        'category'          => 'வகை',
        'sub_category'      => 'உப-வகை',
        'description'       => 'விளக்கம்',
        'amount'            => 'தொகை',
        'gst'               => 'GST செலுத்தியது',
        'method'            => 'கட்டண முறை',
        'vendor'            => 'யாருக்கு செலுத்தியது',
        'reference'         => 'குறிப்பு / பில் எண்',
        'reference_placeholder' => 'பில் எண், இன்வாய்ஸ் குறிப்பு…',
        'receipt_url'       => 'ரசீது URL',
        'notes'             => 'குறிப்புகள்',
        'select_branch'     => 'கிளை தேர்ந்தெடுக்கவும்',
        'select_category'   => 'வகை தேர்ந்தெடுக்கவும்',
        'select_sub'        => 'உப-வகை தேர்ந்தெடுக்கவும்',
        'salary_section'    => 'சம்பள விவரங்கள்',
        'staff_member'      => 'ஊழியர்',
        'select_staff'      => 'ஊழியரை தேர்ந்தெடுக்கவும்',
        'salary_month'      => 'சம்பள மாதம்',
        'is_recurring'      => 'இது மீண்டும் மீண்டும் வரும் செலவு',
        'frequency'         => 'அதிர்வெண்',
        'recurrence_end'    => 'முடிவு தேதி (விருப்பத்தேர்வு)',
    ],

    'categories' => [
        'rent'          => 'வாடகை',
        'utilities'     => 'பயன்பாடுகள்',
        'salaries'      => 'சம்பளங்கள்',
        'equipment'     => 'உபகரணங்கள்',
        'marketing'     => 'சந்தைப்படுத்தல்',
        'supplies'      => 'பொருட்கள்',
        'insurance'     => 'காப்பீடு',
        'software'      => 'மென்பொருள்',
        'miscellaneous' => 'இதர',
    ],

    'sub_categories' => [
        'rent'      => ['main_hall' => 'பிரதான அரங்கம்', 'studio' => 'ஸ்டுடியோ', 'storage' => 'சேமிப்பு', 'office' => 'அலுவலகம்'],
        'utilities' => ['electricity' => 'மின்சாரம்', 'water' => 'தண்ணீர்', 'internet' => 'இணையம்', 'phone' => 'தொலைபேசி'],
        'salaries'  => ['full_time' => 'முழுநேர', 'part_time' => 'பகுதிநேர', 'contract' => 'ஒப்பந்தம்', 'bonus' => 'போனஸ்'],
        'equipment' => ['purchase' => 'கொள்முதல்', 'repair' => 'பழுதுபார்ப்பு', 'maintenance' => 'பராமரிப்பு', 'replacement' => 'மாற்றீடு'],
        'marketing' => ['social_media' => 'சமூக ஊடகம்', 'flyers' => 'துண்டுப்பிரசுரம்', 'events' => 'நிகழ்வுகள்', 'promotions' => 'விளம்பரங்கள்'],
        'supplies'  => ['cleaning' => 'சுத்தம்', 'consumables' => 'நுகர்பொருட்கள்', 'stationery' => 'எழுதுபொருட்கள்', 'toiletries' => 'சுகாதார பொருட்கள்'],
        'insurance' => ['liability' => 'பொறுப்பு', 'equipment' => 'உபகரணம்', 'health' => 'சுகாதாரம்'],
        'software'  => ['gymos_subscription' => 'GymOS சந்தா', 'other' => 'மற்றவை'],
    ],

    'methods' => [
        'cash'   => 'பணம்',
        'upi'    => 'UPI',
        'bank'   => 'வங்கி பரிமாற்றம்',
        'cheque' => 'காசோலை',
        'card'   => 'கார்டு',
    ],

    'status' => [
        'pending'  => 'நிலுவையில்',
        'approved' => 'அங்கீகரிக்கப்பட்டது',
        'rejected' => 'நிராகரிக்கப்பட்டது',
    ],

    'recurrence' => [
        'daily'   => 'தினசரி',
        'weekly'  => 'வாராந்திர',
        'monthly' => 'மாதாந்திர',
        'annual'  => 'வார்ஷிக',
    ],

    'reject' => [
        'title'              => 'செலவை நிராகரி',
        'reason_placeholder' => 'நிராகரிப்பு காரணம்…',
        'confirm'            => 'நிராகரி',
    ],

    'confirm_delete' => 'இந்த செலவை நீக்க விரும்புகிறீர்களா?',

    'flash' => [
        'stored'         => 'செலவு வெற்றிகரமாக பதிவு செய்யப்பட்டது.',
        'stored_pending' => 'செலவு அங்கீகாரத்திற்கு சமர்ப்பிக்கப்பட்டது.',
        'updated'        => 'செலவு புதுப்பிக்கப்பட்டது.',
        'deleted'        => 'செலவு நீக்கப்பட்டது.',
        'approved'       => 'செலவு அங்கீகரிக்கப்பட்டது.',
        'rejected'       => 'செலவு நிராகரிக்கப்பட்டது.',
    ],
];

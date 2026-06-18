<?php

return [
    'title'     => 'Renewals Due',
    'export'    => 'Export CSV',

    'stats' => [
        'expired'    => 'Expired',
        'today'      => 'Expiring today',
        'seven_days' => 'Due in 7 days',
        'thirty_days'=> 'Due in 30 days',
    ],

    'tabs' => [
        'all'    => 'All',
        'expired'=> 'Expired',
        'today'  => 'Today',
        '3days'  => 'Next 3 days',
        '7days'  => 'Next 7 days',
        '30days' => 'Next 30 days',
        'custom' => 'Custom',
    ],

    'filters' => [
        'all_plans'  => 'All plans',
        'from_date'  => 'From',
        'to_date'    => 'To',
    ],

    'table' => [
        'member'     => 'Member',
        'member_id'  => 'Member ID',
        'phone'      => 'Phone',
        'plan'       => 'Plan',
        'expiry'     => 'Expiry date',
        'days_status'=> 'Days',
        'balance'    => 'Balance',
        'actions'    => 'Actions',
    ],

    'status' => [
        'days_overdue' => ':days days overdue',
        'today'        => 'Expires today',
        'days_left'    => ':days days left',
    ],

    'actions' => [
        'renew'        => 'Renew now',
        'view_profile' => 'View profile',
        'send_reminder'=> 'Send reminder',
    ],

    'drawer' => [
        'title'          => 'Renew Membership',
        'member_label'   => 'Member',
        'plan'           => 'Plan',
        'start_date'     => 'New start date',
        'expiry_preview' => 'New expiry preview',
        'payment_amount' => 'Amount paid',
        'payment_method' => 'Payment method',
        'notes'          => 'Notes',
        'btn_renew'      => 'Confirm renewal',
        'btn_cancel'     => 'Cancel',
        'calculating'    => 'Calculating…',
        'select_plan'    => 'Select plan',
        'select_method'  => 'Select method',
        'methods' => [
            'cash'   => 'Cash',
            'upi'    => 'UPI',
            'card'   => 'Card',
            'bank'   => 'Bank transfer',
            'cheque' => 'Cheque',
        ],
    ],

    'empty' => [
        'no_renewals'  => 'No renewals due.',
        'description'  => 'No members match the selected filter.',
    ],

    'flash' => [
        'renewed' => 'Membership renewed for :name. New expiry: :expiry.',
    ],
];

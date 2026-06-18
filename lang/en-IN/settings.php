<?php

return [
    // Admin panel (super admin) keys
    'eyebrow'    => 'Modules 1 & 5',
    'heading'    => 'Settings & Languages',
    'subheading' => 'Starter security posture overview plus platform-wide language management based on the Phase 1 specification.',

    // Tenant settings (gym owner portal — Module 14)
    'title'    => 'Settings',
    'subtitle' => 'Manage your gym profile, account, and preferences',

    'nav' => [
        'profile'      => 'Gym Profile',
        'account'      => 'My Account',
        'integrations' => 'Integrations',
        'language'     => 'Language',
        'subscription' => 'Billing & Subscription',
        'data'         => 'Data & Privacy',
    ],

    'profile' => [
        'saved' => 'Gym profile saved successfully.',
        'section' => [
            'media'   => 'Logo & Cover Photo',
            'basic'   => 'Basic Information',
            'address' => 'Address',
            'legal'   => 'Legal & Tax',
            'social'  => 'Social Links',
            'hours'   => 'Operating Hours',
        ],
        'field' => [
            'gym_name'      => 'Gym Name',
            'business_type' => 'Business Type',
            'logo'          => 'Logo',
            'cover'         => 'Cover Photo',
            'phone'         => 'Contact Phone',
            'email'         => 'Contact Email',
            'website'       => 'Website',
            'about'         => 'About / Description',
            'address1'      => 'Address Line 1',
            'address2'      => 'Address Line 2',
            'city'          => 'City',
            'state'         => 'State',
            'pin'           => 'PIN Code',
            'gstin'         => 'GSTIN',
            'pan'           => 'PAN',
            'reg_number'    => 'Business Registration No.',
        ],
        'business_types' => [
            'gym'          => 'Gym',
            'yoga_studio'  => 'Yoga Studio',
            'crossfit'     => 'CrossFit',
            'martial_arts' => 'Martial Arts',
            'dance'        => 'Dance Studio',
            'sports_club'  => 'Sports Club',
            'other'        => 'Other',
        ],
        'hours' => ['closed' => 'Closed'],
    ],

    'account' => [
        'saved'                     => 'Account updated successfully.',
        'password_changed'          => 'Password changed. All other devices have been signed out.',
        'session_terminated'        => 'Session terminated.',
        'other_sessions_terminated' => 'All other sessions have been signed out.',
        'email_readonly'            => 'Email cannot be changed here. Contact support to update.',
        'password_rules'            => 'Min. 12 chars · uppercase · lowercase · number · symbol',
        'change_password'           => 'Change Password',
        'section' => [
            'profile'  => 'Profile Information',
            'password' => 'Change Password',
            'sessions' => 'Active Sessions',
        ],
        'field' => [
            'name'             => 'Full Name',
            'email'            => 'Email',
            'phone'            => 'Phone',
            'avatar'           => 'Profile Photo',
            'current_password' => 'Current Password',
            'new_password'     => 'New Password',
            'confirm_password' => 'Confirm New Password',
        ],
        'sessions' => [
            'current'         => 'Current',
            'terminate'       => 'Terminate',
            'sign_out_others' => 'Sign out all other devices',
        ],
    ],

    'integrations' => [
        'saved'         => ':name integration saved.',
        'disconnected'  => ':name disconnected.',
        'connected'     => 'Connected',
        'not_connected' => 'Not connected',
        'connect'       => 'Connect',
        'update'        => 'Update',
        'disconnect'    => 'Disconnect',
        'test'          => 'Test Connection',
        'whatsapp' => [
            'desc'           => 'Send automated messages to members via WhatsApp Business API',
            'phone_number_id'=> 'Business Phone Number ID',
            'api_token'      => 'API Access Token',
            'verify_token'   => 'Verify Token',
            'webhook_url'    => 'Webhook URL',
        ],
        'razorpay' => [
            'desc'           => 'Accept online payments and manage subscriptions via Razorpay',
            'key_id'         => 'Key ID',
            'key_secret'     => 'Key Secret',
            'webhook_secret' => 'Webhook Secret',
            'webhook_url'    => 'Webhook URL',
            'test_mode'      => 'Sandbox / Test Mode',
        ],
        'biometric' => [
            'name'          => 'Biometric Device',
            'desc'          => 'Sync attendance data from ZKTeco, eSSL, or Mantra biometric devices',
            'device_type'   => 'Device Type',
            'ip'            => 'IP Address',
            'port'          => 'Port',
            'serial'        => 'Device Serial (optional)',
            'sync'          => 'Sync Schedule',
            'sync_realtime' => 'Real-time',
            'sync_5'        => 'Every 5 minutes',
            'sync_15'       => 'Every 15 minutes',
        ],
        'tally' => [
            'name'      => 'Tally / Accounting',
            'desc'      => 'Export payment and expense data to Tally or any accounting software',
            'format'    => 'Export Format',
            'auto_sync' => 'Auto-sync daily at 2:00 AM IST',
        ],
    ],

    'language' => [
        'saved' => 'Language preference updated.',
        'apply' => 'Apply Language',
        'note'  => 'This changes the language for your portal only. Staff and members have their own preferences.',
        'section' => [
            'title' => 'Portal Language',
            'desc'  => 'Choose the language for your gym owner portal.',
        ],
    ],

    'subscription' => [
        'no_subscription' => 'No active subscription found.',
        'contact_note'    => 'To change your plan or billing details, contact GymOS support at',
        'section' => [
            'current'  => 'Current Plan',
            'invoices' => 'Invoice History',
        ],
        'field' => [
            'plan'           => 'Plan',
            'renewal'        => 'Renewal Date',
            'amount'         => 'Monthly Amount',
            'branches'       => 'Branches',
            'branches_used'  => 'branches used',
            'members'        => 'Members',
            'members_active' => 'active members',
        ],
        'col' => [
            'invoice' => 'Invoice #',
            'date'    => 'Date',
            'amount'  => 'Amount',
            'status'  => 'Status',
        ],
    ],

    'data' => [
        'export_queued'      => 'Export started. You will receive an email with the download link.',
        'deletion_requested' => 'Account deletion request submitted. You will receive a confirmation email.',
        'export' => [
            'title'      => 'Export All Data',
            'desc'       => 'Download a ZIP file containing all your gym data as CSV files.',
            'button'     => 'Export My Gym Data',
            'email_note' => 'For large gyms this may take a few minutes. A download link will be emailed to you (valid 48 hours).',
        ],
        'delete' => [
            'title'           => 'Account Deletion Request',
            'desc'            => 'Request deletion of your GymOS account and all associated data.',
            'button'          => 'Request Account Deletion',
            'confirm_title'   => 'Are you sure?',
            'confirm_desc'    => 'Your data will be scheduled for deletion after 90 days. Active subscriptions will end. This action cannot be reversed without contacting support.',
            'confirm_checkbox'=> 'I understand that my data will be permanently deleted after 90 days.',
            'submit'          => 'Submit Deletion Request',
        ],
    ],
];

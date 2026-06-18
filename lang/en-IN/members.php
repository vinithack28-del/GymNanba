<?php

return [
    'title'          => 'Members',
    'add_member'     => 'Add member',
    'edit_member'    => 'Edit member',
    'showing_branch' => 'Showing members for this branch only',

    'stats' => [
        'total'    => 'Total members',
        'active'   => 'Active',
        'inactive' => 'Inactive',
        'expired'  => 'Expired',
    ],

    'filters' => [
        'search_placeholder' => 'Search name, phone, email, ID…',
        'all_statuses'       => 'All statuses',
        'all_genders'        => 'All genders',
        'clear_filters'      => 'Clear filters',
        'export'             => 'Export',
    ],

    'statuses' => [
        'active'   => 'Active',
        'inactive' => 'Inactive',
        'expired'  => 'Expired',
        'frozen'   => 'Frozen',
    ],

    'genders' => [
        'male'   => 'Male',
        'female' => 'Female',
        'other'  => 'Other',
    ],

    'table' => [
        'id'      => 'ID',
        'member'  => 'Member',
        'phone'   => 'Phone',
        'plan'    => 'Plan',
        'joined'  => 'Joined',
        'expires' => 'Expires',
        'status'  => 'Status',
        'balance' => 'Balance',
        'showing' => 'Showing :first–:last of :total members',
    ],

    'actions' => [
        'view_profile'  => 'View profile',
        'edit'          => 'Edit',
        'collect_fee'   => 'Collect fee',
        'delete'        => 'Delete',
        'delete_confirm'=> 'Delete :name? This cannot be undone.',
        'toggle_status' => 'Click to toggle status',
    ],

    'empty' => [
        'no_match'       => 'No members match your search.',
        'try_adjusting'  => 'Try adjusting your filters.',
        'clear_all'      => 'Clear all filters',
        'no_members'     => 'No members yet.',
        'get_started'    => 'Add your first member to get started.',
    ],

    'form' => [
        'create_title'  => 'Add New Member',
        'edit_title'    => 'Edit Member',
        'personal_info' => 'Personal Information',
        'membership'    => 'Membership Details',
        'payment'       => 'Initial Payment',
        'notes_section' => 'Notes',
        'member_info'   => 'Member Info',

        'name'          => 'Full name',
        'phone'         => 'Phone number',
        'email'         => 'Email address',
        'dob'           => 'Date of birth',
        'gender'        => 'Gender',
        'address'       => 'Address',
        'branch'        => 'Branch',
        'plan'          => 'Membership plan',
        'start_date'    => 'Start date',
        'expiry_preview'=> 'Expiry preview',
        'status'        => 'Status',
        'notes'         => 'Notes',
        'payment_amount'=> 'Amount paid',
        'payment_method'=> 'Payment method',
        'payment_ref'   => 'Reference / receipt no.',

        'select_plan'   => 'Select a plan',
        'select_branch' => 'Select branch',
        'select_gender' => 'Select gender',
        'select_method' => 'Select method',

        'methods' => [
            'cash'   => 'Cash',
            'upi'    => 'UPI',
            'card'   => 'Card',
            'bank'   => 'Bank transfer',
            'cheque' => 'Cheque',
        ],

        'statuses' => [
            'active'   => 'Active',
            'inactive' => 'Inactive',
            'frozen'   => 'Frozen',
        ],

        'genders' => [
            'male'   => 'Male',
            'female' => 'Female',
            'other'  => 'Other',
        ],

        'member_id'    => 'Member ID',
        'joined_on'    => 'Joined on',
        'expiry_date'  => 'Expiry date',
        'last_updated' => 'Last updated',
        'never'        => 'Never',
        'calculating'  => 'Calculating…',

        'btn_create'   => 'Add member',
        'btn_update'   => 'Save changes',
        'btn_cancel'   => 'Cancel',
        'required'     => 'Required',
    ],

    'flash' => [
        'created' => 'Member added successfully.',
        'updated' => 'Member updated successfully.',
        'deleted' => 'Member deleted.',
        'toggled' => 'Member status updated.',
    ],
];

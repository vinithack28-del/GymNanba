<?php

return [
    'title'       => 'Membership Plans',
    'create_plan' => 'Create plan',
    'edit_plan'   => 'Edit plan',

    'filters' => [
        'search_placeholder' => 'Search plans…',
    ],

    'card' => [
        'per_month'   => '/mo',
        'gst'         => 'GST',
        'included'    => 'incl.',
        'edit'        => 'Edit',
        'duplicate'   => 'Duplicate',
        'archive'     => 'Archive',
        'all_branches'=> 'All branches',
        'branches'    => ':count branch|:count branches',
        'duration_months' => ':count month|:count months',
        'duration_days'   => ':count day|:count days',
    ],

    'status' => [
        'active'   => 'Active',
        'inactive' => 'Inactive',
        'archived' => 'Archived',
    ],

    'empty' => [
        'no_plans'   => 'No plans yet.',
        'get_started'=> 'Create your first membership plan to get started.',
    ],

    'form' => [
        'create_title'  => 'Create Membership Plan',
        'edit_title'    => 'Edit Membership Plan',
        'basic_info'    => 'Plan Details',
        'pricing'       => 'Pricing',
        'branches_section' => 'Available Branches',
        'advanced'      => 'Advanced Options',

        'name'          => 'Plan name',
        'description'   => 'Description',
        'duration_type' => 'Duration type',
        'duration_months'=> 'Duration (months)',
        'duration_days' => 'Duration (days)',
        'price'         => 'Price (₹)',
        'gst_enabled'   => 'Apply GST',
        'gst_rate'      => 'GST rate (%)',
        'freeze_enabled'=> 'Allow freeze',
        'max_freeze_days'=> 'Max freeze days',
        'status'        => 'Status',

        'duration_types' => [
            'months' => 'Months',
            'days'   => 'Days',
        ],

        'statuses' => [
            'active'   => 'Active',
            'inactive' => 'Inactive',
        ],

        'all_branches'  => 'All branches',
        'select_branches'=> 'Select specific branches',

        'price_preview' => 'Price preview',
        'base_price'    => 'Base price',
        'gst_amount'    => 'GST (:rate%)',
        'total_price'   => 'Total',

        'btn_create'    => 'Create plan',
        'btn_update'    => 'Save changes',
        'btn_cancel'    => 'Cancel',
    ],

    'flash' => [
        'created'    => 'Plan created successfully.',
        'updated'    => 'Plan updated successfully.',
        'duplicated' => 'Plan duplicated.',
        'archived'   => 'Plan archived.',
    ],
];

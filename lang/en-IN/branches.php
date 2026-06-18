<?php

return [
    'title'      => 'Branches',
    'add_branch' => 'Add branch',
    'edit_branch'=> 'Edit branch',

    'toolbar' => [
        'of_limit'  => ':active of :limit branches used',
        'unlimited' => ':active branches used',
        'inactive'  => ':count inactive',
        'limit_tip' => 'Plan limit reached. Upgrade to add more.',
    ],

    'limit_banner' => "You've used all :limit branches on your :plan plan. Upgrade your plan to add more branches.",

    'card' => [
        'primary'        => 'Primary',
        'active'         => 'Active',
        'inactive'       => 'Inactive',
        'no_manager'     => 'No manager assigned',
        'members'        => 'Members',
        'active_members' => 'Active',
        'checkins_today' => 'Check-ins today',
        'revenue_mo'     => 'Revenue / mo',
        'edit'           => 'Edit',
        'members_link'   => 'Members',
        'deactivate'     => 'Deactivate',
        'reactivate'     => 'Reactivate',
    ],

    'credentials' => [
        'title'       => 'Branch admin login created',
        'description' => 'Share these credentials with the branch admin. The password can be changed after first login.',
        'email'       => 'Email',
        'password'    => 'Password',
        'copy'        => 'Copy',
        'copied'      => 'Copied!',
        'dismiss'     => 'Dismiss',
    ],

    'deactivate_modal' => [
        'title'        => 'Deactivate branch',
        'message'      => 'Deactivate ":name"? Members will be blocked from checking in at this branch.',
        'reassign_to'  => 'Reassign members to',
        'leave_unassigned' => 'Leave members unassigned',
        'confirm'      => 'Deactivate',
        'cancel'       => 'Cancel',
    ],

    'empty' => [
        'no_branches'  => 'No branches yet',
        'get_started'  => "Add your gym's first branch to get started.",
    ],

    'form' => [
        'create_title'  => 'Add New Branch',
        'edit_title'    => 'Edit Branch',
        'basic_info'    => 'Basic Information',
        'location'      => 'Location',
        'contact'       => 'Contact & Manager',
        'hours'         => 'Operating Hours',
        'amenities'     => 'Amenities',
        'settings'      => 'Settings',

        'name'          => 'Branch name',
        'address1'      => 'Address line 1',
        'address2'      => 'Address line 2',
        'city'          => 'City',
        'state'         => 'State',
        'pin'           => 'PIN code',
        'phone'         => 'Phone',
        'email'         => 'Email',
        'manager_name'  => 'Manager name',
        'is_primary'    => 'Set as primary branch',
        'status'        => 'Status',

        'btn_create'    => 'Add branch',
        'btn_update'    => 'Save changes',
        'btn_cancel'    => 'Cancel',
    ],

    'flash' => [
        'created'     => 'Branch created successfully.',
        'updated'     => 'Branch updated successfully.',
        'deactivated' => 'Branch deactivated.',
        'reactivated' => 'Branch reactivated.',
        'limit'       => 'Branch limit reached for your plan.',
    ],
];

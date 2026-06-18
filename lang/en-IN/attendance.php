<?php

return [

    'nav' => [
        'checkins' => 'Check-ins',
        'walkins'  => 'Walk-ins',
    ],

    'checkins' => [
        'title'       => 'Member Check-ins',
        'subtitle'    => 'Track who checked in today',
        'date_label'  => 'Date',
        'branch_label'=> 'Branch',
        'method_label'=> 'Method',
        'all_branches'=> 'All branches',
        'all_methods' => 'All methods',
        'search_ph'   => 'Search member…',
        'export_csv'  => 'Export CSV',
        'checkin_btn' => 'Check in',
    ],

    'stats' => [
        'total'     => 'Total check-ins',
        'unique'    => 'Unique members',
        'peak_hour' => 'Peak hour',
    ],

    'table' => [
        'member'     => 'Member',
        'plan'       => 'Plan',
        'branch'     => 'Branch',
        'method'     => 'Method',
        'time_in'    => 'Time in',
        'time_out'   => 'Time out',
        'duration'   => 'Duration',
        'logged_by'  => 'Logged by',
        'actions'    => 'Actions',
    ],

    'methods' => [
        'manual'    => 'Manual',
        'qr'        => 'QR',
        'biometric' => 'Biometric',
    ],

    'empty' => [
        'title'        => 'No check-ins yet',
        'subtitle'     => 'Members who check in today will appear here.',
        'no_match'     => 'No check-ins match your filters',
        'try_adjusting'=> 'Try adjusting the filters or clear them.',
        'clear_all'    => 'Clear filters',
        'checkin_now'  => 'Check in a member',
    ],

    'checkin_drawer' => [
        'title'       => 'New Check-in',
        'search_ph'   => 'Search by name, phone or ID…',
        'no_results'  => 'No members found',
        'plan_expires'=> 'Expires',
        'reason'      => 'Reason (optional)',
        'reason_ph'   => 'e.g. Morning workout',
        'method'      => 'Method',
        'confirm'     => 'Confirm check-in',
        'cancel'      => 'Cancel',
    ],

    // ── Walk-ins ──────────────────────────────────────────────────────────────

    'walkins' => [
        'title'       => 'Walk-ins',
        'subtitle'    => 'Log visitors, day-passes and trials',
        'add_walkin'  => 'Log walk-in',
        'date_label'  => 'Date',
        'branch_label'=> 'Branch',
        'all_branches'=> 'All branches',
    ],

    'walkin_stats' => [
        'total'   => 'Walk-ins today',
        'revenue' => 'Revenue collected',
    ],

    'walkin_form' => [
        'title'          => 'Log a walk-in',
        'name'           => 'Visitor name',
        'name_ph'        => 'Full name',
        'phone'          => 'Phone',
        'phone_ph'       => '10-digit mobile number',
        'purpose'        => 'Purpose',
        'fee'            => 'Fee (₹)',
        'fee_ph'         => '0 if free',
        'payment_method' => 'Payment method',
        'reference'      => 'Reference / receipt no.',
        'reference_ph'   => 'Optional',
        'notes'          => 'Notes',
        'notes_ph'       => 'Any additional info',
        'guest_of'       => 'Guest of member',
        'guest_of_ph'    => 'Select member (optional)',
        'branch'         => 'Branch',
        'submit'         => 'Log walk-in',
        'cancel'         => 'Cancel',
    ],

    'purposes' => [
        'day_pass'   => 'Day pass',
        'free_trial' => 'Free trial',
        'inquiry'    => 'Inquiry',
        'guest'      => 'Guest',
    ],

    'payment_methods' => [
        'cash' => 'Cash',
        'upi'  => 'UPI',
        'card' => 'Card',
    ],

    'walkin_table' => [
        'visitor'  => 'Visitor',
        'purpose'  => 'Purpose',
        'fee'      => 'Fee',
        'payment'  => 'Payment',
        'guest_of' => 'Guest of',
        'branch'   => 'Branch',
        'time'     => 'Time',
    ],

    'walkin_empty' => [
        'title'    => 'No walk-ins today',
        'subtitle' => 'Log a visitor, day-pass or trial session.',
        'add_now'  => 'Log walk-in',
    ],

    // ── Shared flash ──────────────────────────────────────────────────────────

    'flash' => [
        'checked_in'    => 'Member checked in successfully.',
        'checked_out'   => 'Check-out recorded.',
        'deleted'       => 'Check-in record deleted.',
        'walkin_logged' => 'Walk-in logged successfully.',
    ],

];

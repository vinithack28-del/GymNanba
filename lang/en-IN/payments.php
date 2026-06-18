<?php

return [
    'nav' => [
        'payments'  => 'Payments',
        'collect'   => 'Collect Fee',
        'history'   => 'Payment History',
        'dues'      => 'Pending Dues',
    ],

    'collect' => [
        'subtitle'           => 'Record a payment from a member',
        'member'             => 'Member',
        'search_placeholder' => 'Search by name, phone or member code…',
        'no_results'         => 'No members found',
        'change_member'      => 'Change member',
        'select_member_first' => 'Please select a member first',
        'payment_details'    => 'Payment Details',
        'plan'               => 'Membership Plan',
        'no_plan'            => 'No plan',
        'amount'             => 'Amount',
        'payment_date'       => 'Payment Date',
        'apply_gst'          => 'Apply GST',
        'method'             => 'Payment Method',
        'reference'          => 'Reference / Transaction ID',
        'reference_placeholder' => 'UPI ref, cheque no…',
        'notes'              => 'Notes',
        'submit'             => 'Collect Payment',
        'summary'            => 'Summary',
        'base_amount'        => 'Base Amount',
        'gst'                => 'GST',
        'total'              => 'Total',
        'receipt_note'       => 'A receipt will be generated after collection.',
    ],

    'history' => [
        'subtitle'          => 'All member payment records',
        'search_placeholder' => 'Search by receipt, name or phone…',
        'method'            => 'Method',
        'status'            => 'Status',
        'date_from'         => 'From',
        'date_to'           => 'To',
        'today_count'       => 'Payments Today',
        'today_total'       => 'Revenue Today',
        'receipt'           => 'Receipt',
        'member'            => 'Member',
        'plan'              => 'Plan',
        'amount'            => 'Amount',
        'date'              => 'Date',
        'empty'             => 'No payments found',
        'receipt_btn'       => 'Receipt',
        'void_btn'          => 'Void',
    ],

    'dues' => [
        'subtitle'         => 'Members with outstanding balances',
        'search_placeholder' => 'Search by name or phone…',
        'total_due'        => 'Total Outstanding Dues',
        'members_due'      => 'members with dues',
        'member'           => 'Member',
        'amount_due'       => 'Amount Due',
        'empty'            => 'No members with pending dues',
        'collect_btn'      => 'Collect',
    ],

    'void' => [
        'title'         => 'Void Payment',
        'desc_prefix'   => 'Are you sure you want to void receipt',
        'reason_label'  => 'Reason for voiding',
        'select_reason' => 'Select a reason',
        'confirm'       => 'Void Payment',
    ],

    'void_reasons' => [
        'data_entry_error' => 'Data Entry Error',
        'duplicate_payment' => 'Duplicate Payment',
        'refund'           => 'Refund',
        'other'            => 'Other',
    ],

    'methods' => [
        'cash'  => 'Cash',
        'upi'   => 'UPI',
        'card'  => 'Card',
        'bank'  => 'Bank Transfer',
        'cheque' => 'Cheque',
    ],

    'status' => [
        'active' => 'Active',
        'voided' => 'Voided',
    ],

    'receipt' => [
        'title'        => 'Payment Receipt',
        'member'       => 'Member',
        'plan'         => 'Plan',
        'amount'       => 'Amount',
        'gst'          => 'GST',
        'total'        => 'Total',
        'method'       => 'Method',
        'reference'    => 'Reference',
        'date'         => 'Date',
        'collected_by' => 'Collected By',
        'notes'        => 'Notes',
        'print'        => 'Print Receipt',
        'footer'       => 'Thank you for your payment!',
    ],

    'flash' => [
        'collected'     => 'Payment collected. Receipt: :receipt',
        'voided'        => 'Payment :receipt has been voided.',
        'already_voided' => 'This payment is already voided.',
        'void_too_old'  => 'Payments older than 90 days cannot be voided.',
    ],
];

<?php

return [
    'nav' => [
        'invoices' => 'Invoices',
        'create'   => 'Create Invoice',
    ],

    'index' => [
        'subtitle'         => 'GST-compliant invoices for all member payments',
        'search_placeholder' => 'Search by invoice #, name or phone…',
        'status'           => 'Status',
        'date_from'        => 'From',
        'date_to'          => 'To',
        'empty'            => 'No invoices found',
        'empty_title'      => 'No invoices yet',
        'empty_desc'       => 'Invoices are auto-generated when a payment is collected. You can also create a manual invoice for any custom charge.',
    ],

    'table' => [
        'number'      => 'Invoice #',
        'date'        => 'Date',
        'member'      => 'Member',
        'description' => 'Description',
        'subtotal'    => 'Subtotal',
        'gst'         => 'GST',
        'total'       => 'Total',
        'status'      => 'Status',
        'view_btn'    => 'View',
        'void_btn'    => 'Void',
    ],

    'create' => [
        'subtitle'          => 'Create a manual invoice for non-standard charges',
        'member'            => 'Bill To',
        'search_placeholder' => 'Search by name, phone or member code…',
        'no_results'        => 'No members found',
        'change_member'     => 'Change member',
        'select_member_first' => 'Please select a member first',
        'add_line_first'    => 'Please add at least one line item',
        'details'           => 'Invoice Details',
        'invoice_date'      => 'Invoice Date',
        'due_date'          => 'Due Date',
        'no_branch'         => 'No branch',
        'line_items'        => 'Line Items',
        'add_line'          => 'Add line',
        'col_desc'          => 'Description',
        'col_qty'           => 'Qty',
        'col_rate'          => 'Rate (paise)',
        'col_gst'           => 'GST %',
        'col_amount'        => 'Amount',
        'notes'             => 'Notes',
        'submit'            => 'Create Invoice',
        'summary'           => 'Summary',
        'subtotal'          => 'Subtotal',
        'gst'               => 'GST',
        'total'             => 'Total',
    ],

    'show' => [
        'title'          => 'Invoice',
        'print'          => 'Print',
        'bill_to'        => 'Bill To',
        'date'           => 'Invoice Date',
        'due_date'       => 'Due Date',
        'col_desc'       => 'Description',
        'col_qty'        => 'Qty',
        'col_rate'       => 'Rate',
        'col_amount'     => 'Amount',
        'subtotal'       => 'Subtotal',
        'total'          => 'Total',
        'amount_words'   => 'Amount in words',
        'payment_method' => 'Payment Method',
        'place_of_supply' => 'Place of Supply',
        'footer'         => 'Thank you for your business!',
    ],

    'void' => [
        'title'         => 'Void Invoice',
        'desc_prefix'   => 'Are you sure you want to void invoice',
        'reason_label'  => 'Reason for voiding',
        'select_reason' => 'Select a reason',
        'confirm'       => 'Void Invoice',
    ],

    'void_reasons' => [
        'data_entry_error'   => 'Data Entry Error',
        'duplicate_invoice'  => 'Duplicate Invoice',
        'cancelled_service'  => 'Cancelled Service',
        'other'              => 'Other',
    ],

    'status' => [
        'paid'    => 'Paid',
        'unpaid'  => 'Unpaid',
        'partial' => 'Partial',
        'void'    => 'Void',
    ],

    'flash' => [
        'created'       => 'Invoice :number created.',
        'voided'        => 'Invoice :number has been voided.',
        'already_voided' => 'This invoice is already voided.',
    ],
];

<?php

return [
    'nav' => [
        'expenses' => 'Expenses',
        'add'      => 'Add Expense',
        'edit'     => 'Edit Expense',
    ],

    'index' => [
        'subtitle'         => 'Track all gym operational expenses',
        'search_placeholder' => 'Search description, vendor, reference…',
        'empty_title'      => 'No expenses recorded',
        'empty_desc'       => 'Start recording your gym\'s operational expenses to track costs and generate P&L reports.',
    ],

    'create' => [
        'subtitle' => 'Record a new operational expense',
        'submit'   => 'Save Expense',
    ],

    'summary' => [
        'this_month'    => 'This Month',
        'vs_last_month' => 'vs last month',
        'no_data'       => 'No expenses recorded this month.',
    ],

    'table' => [
        'date'        => 'Date',
        'category'    => 'Category',
        'description' => 'Description',
        'amount'      => 'Amount',
        'method'      => 'Method',
        'branch'      => 'Branch',
        'status'      => 'Status',
    ],

    'form' => [
        'date'              => 'Date',
        'category'          => 'Category',
        'sub_category'      => 'Sub-category',
        'description'       => 'Description',
        'amount'            => 'Amount',
        'gst'               => 'GST Paid',
        'method'            => 'Payment Method',
        'vendor'            => 'Paid To (Vendor)',
        'reference'         => 'Reference / Bill No.',
        'reference_placeholder' => 'Bill no., invoice ref…',
        'receipt_url'       => 'Receipt URL',
        'notes'             => 'Notes',
        'select_branch'     => 'Select branch',
        'select_category'   => 'Select category',
        'select_sub'        => 'Select sub-category',
        'salary_section'    => 'Salary Details',
        'staff_member'      => 'Staff Member',
        'select_staff'      => 'Select staff',
        'salary_month'      => 'Salary Month',
        'is_recurring'      => 'This is a recurring expense',
        'frequency'         => 'Frequency',
        'recurrence_end'    => 'End Date (optional)',
    ],

    'categories' => [
        'rent'          => 'Rent',
        'utilities'     => 'Utilities',
        'salaries'      => 'Salaries',
        'equipment'     => 'Equipment',
        'marketing'     => 'Marketing',
        'supplies'      => 'Supplies',
        'insurance'     => 'Insurance',
        'software'      => 'Software',
        'miscellaneous' => 'Miscellaneous',
    ],

    'sub_categories' => [
        'rent'      => ['main_hall' => 'Main Hall', 'studio' => 'Studio', 'storage' => 'Storage', 'office' => 'Office'],
        'utilities' => ['electricity' => 'Electricity', 'water' => 'Water', 'internet' => 'Internet', 'phone' => 'Phone'],
        'salaries'  => ['full_time' => 'Full-time', 'part_time' => 'Part-time', 'contract' => 'Contract', 'bonus' => 'Bonus'],
        'equipment' => ['purchase' => 'Purchase', 'repair' => 'Repair', 'maintenance' => 'Maintenance', 'replacement' => 'Replacement'],
        'marketing' => ['social_media' => 'Social Media', 'flyers' => 'Flyers', 'events' => 'Events', 'promotions' => 'Promotions'],
        'supplies'  => ['cleaning' => 'Cleaning', 'consumables' => 'Consumables', 'stationery' => 'Stationery', 'toiletries' => 'Toiletries'],
        'insurance' => ['liability' => 'Liability', 'equipment' => 'Equipment', 'health' => 'Health'],
        'software'  => ['gymos_subscription' => 'GymOS Subscription', 'other' => 'Other'],
    ],

    'methods' => [
        'cash'   => 'Cash',
        'upi'    => 'UPI',
        'bank'   => 'Bank Transfer',
        'cheque' => 'Cheque',
        'card'   => 'Card',
    ],

    'status' => [
        'pending'  => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ],

    'recurrence' => [
        'daily'   => 'Daily',
        'weekly'  => 'Weekly',
        'monthly' => 'Monthly',
        'annual'  => 'Annual',
    ],

    'reject' => [
        'title'              => 'Reject Expense',
        'reason_placeholder' => 'Reason for rejection…',
        'confirm'            => 'Reject',
    ],

    'confirm_delete' => 'Are you sure you want to delete this expense?',

    'flash' => [
        'stored'         => 'Expense recorded successfully.',
        'stored_pending' => 'Expense submitted for approval.',
        'updated'        => 'Expense updated.',
        'deleted'        => 'Expense deleted.',
        'approved'       => 'Expense approved.',
        'rejected'       => 'Expense rejected.',
    ],
];

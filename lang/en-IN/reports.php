<?php

return [
    'nav' => [
        'reports'    => 'Reports',
        'revenue'    => 'Revenue',
        'members'    => 'Members',
        'attendance' => 'Attendance',
        'staff'      => 'Staff',
    ],

    'index' => [
        'subtitle'       => 'Analytics and insights for your gym',
        'open'           => 'Open Report',
        'no_access'      => 'Access restricted',
        'revenue_desc'   => 'Payment collections, GST, dues, and top-paying members',
        'members_desc'   => 'New joins, churn, retention, and growth trends',
        'attendance_desc'=> 'Check-in patterns, heatmap, and class attendance',
        'staff_desc'     => 'Staff attendance, trainer performance, and collections',
    ],

    'filters' => [
        'period' => 'Period',
        'plan'   => 'Plan',
        'presets' => [
            'today'          => 'Today',
            'this_week'      => 'This Week',
            'this_month'     => 'This Month',
            'last_month'     => 'Last Month',
            'last_3_months'  => 'Last 3 Months',
            'last_6_months'  => 'Last 6 Months',
            'last_year'      => 'Last Year',
            'q1'             => 'Q1 (Jan–Mar)',
            'q2'             => 'Q2 (Apr–Jun)',
            'q3'             => 'Q3 (Jul–Sep)',
            'q4'             => 'Q4 (Oct–Dec)',
            'custom'         => 'Custom Range',
        ],
    ],

    'revenue' => [
        'subtitle'        => 'Payment collections, GST, pending dues, and member rankings',
        'kpi' => [
            'total'       => 'Total Revenue',
            'count'       => 'Payments',
            'transactions'=> 'transactions',
            'avg'         => 'Avg. per Payment',
            'per_txn'     => 'per transaction',
            'gst'         => 'GST Collected',
            'gst_collected'=> 'included in total',
            'dues'        => 'Pending Dues',
            'outstanding' => 'outstanding',
        ],
        'chart' => [
            'trend'      => 'Revenue Trend',
            'by_method'  => 'By Payment Method',
            'by_plan'    => 'By Membership Plan',
            'by_branch'  => 'By Branch',
        ],
        'top_members'    => 'Top 10 Paying Members',
        'daily_breakdown'=> 'Daily Breakdown',
        'col' => [
            'date'     => 'Date',
            'member'   => 'Member',
            'plan'     => 'Plan',
            'payments' => 'Payments',
            'subtotal' => 'Subtotal',
            'gst'      => 'GST',
            'total'    => 'Total',
        ],
    ],

    'members' => [
        'subtitle'   => 'Membership growth, churn, retention, and demographic breakdown',
        'kpi' => [
            'new'        => 'New Members',
            'churned'    => 'Churned',
            'churn_rate' => 'churn rate',
            'retention'  => 'Retention Rate',
            'net_growth' => 'Net Growth',
        ],
        'chart' => [
            'trend'    => 'New Member Trend',
            'gender'   => 'By Gender',
            'by_plan'  => 'By Membership Plan',
            'age_group'=> 'By Age Group',
        ],
        'by_branch'          => 'Members by Branch',
        'monthly_comparison' => 'Month-on-Month Comparison',
        'col' => [
            'month'   => 'Month',
            'count'   => 'Members',
            'new'     => 'New',
            'churned' => 'Churned',
            'net'     => 'Net',
        ],
    ],

    'attendance' => [
        'subtitle' => 'Check-in volumes, peak hours heatmap, and class attendance summary',
        'kpi' => [
            'total'          => 'Total Check-ins',
            'unique'         => 'Unique Members',
            'walkins'        => 'Walk-ins',
            'avg_per_member' => 'Avg. Visits / Member',
            'visits'         => 'per member',
        ],
        'chart' => [
            'trend'          => 'Daily Check-in Trend',
            'by_method'      => 'By Check-in Method',
            'daily_breakdown'=> 'Members vs Walk-ins',
        ],
        'heatmap' => [
            'less' => 'Less',
            'more' => 'More',
        ],
        'class_summary' => 'Class Attendance Summary',
        'col' => [
            'class'    => 'Class',
            'sessions' => 'Sessions',
            'attendees'=> 'Total Attendees',
            'avg_fill' => 'Avg / Session',
        ],
    ],

    'staff' => [
        'subtitle' => 'Staff attendance, trainer activity, fee collections, and POS sales',
        'section' => [
            'attendance' => 'Staff Attendance',
            'classes'    => 'Classes by Trainer',
            'fees'       => 'Fees Collected by Staff',
            'pos'        => 'POS Sales by Staff',
        ],
        'empty' => 'No data for the selected period.',
        'col' => [
            'name'            => 'Name',
            'role'            => 'Role',
            'days_present'    => 'Days Present',
            'total_hours'     => 'Total Hours',
            'trainer'         => 'Trainer',
            'scheduled'       => 'Scheduled',
            'held'            => 'Held',
            'cancelled'       => 'Cancelled',
            'pct_held'        => '% Held',
            'payment_count'   => 'Payments',
            'total_collected' => 'Total Collected',
            'bill_count'      => 'Bills',
            'total_sales'     => 'Total Sales',
        ],
    ],
];

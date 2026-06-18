<?php

return [

    'title'    => 'Classes & Schedules',
    'subtitle' => 'Manage group fitness classes and bookings',

    // ── Timetable ──────────────────────────────────────────────────────────────

    'timetable' => [
        'title'       => 'Timetable',
        'week_of'     => 'Week of',
        'prev_week'   => 'Previous week',
        'next_week'   => 'Next week',
        'today'       => 'Today',
        'all_branches'=> 'All branches',
        'calendar_view'=> 'Calendar',
        'list_view'   => 'List',
        'create_class'=> 'Create class',
        'no_classes'  => 'No classes scheduled this week',
        'no_classes_sub'=> 'Create a class to see it on the timetable.',
    ],

    'days' => [
        1 => 'Mon', 2 => 'Tue', 3 => 'Wed',
        4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun',
    ],

    'days_full' => [
        1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
        4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday',
    ],

    // ── Class types ───────────────────────────────────────────────────────────

    'types' => [
        'yoga'     => 'Yoga',
        'hiit'     => 'HIIT',
        'zumba'    => 'Zumba',
        'strength' => 'Strength',
        'pilates'  => 'Pilates',
        'crossfit' => 'CrossFit',
        'aerobics' => 'Aerobics',
        'custom'   => 'Custom',
    ],

    // ── Status ────────────────────────────────────────────────────────────────

    'statuses' => [
        'scheduled' => 'Scheduled',
        'cancelled' => 'Cancelled',
        'completed' => 'Completed',
        'full'      => 'Full',
    ],

    // ── Create / Edit form ────────────────────────────────────────────────────

    'form' => [
        'create_title'   => 'Create class',
        'edit_title'     => 'Edit class',
        'name'           => 'Class name',
        'name_ph'        => 'e.g. Morning Yoga',
        'type'           => 'Class type',
        'branch'         => 'Branch',
        'room'           => 'Room / area',
        'room_ph'        => 'e.g. Studio A',
        'trainer'        => 'Trainer',
        'no_trainer'     => 'No trainer assigned',
        'start_time'     => 'Start time',
        'end_time'       => 'End time',
        'repeat'         => 'Repeat',
        'repeat_none'    => 'Does not repeat',
        'repeat_daily'   => 'Daily',
        'repeat_weekly'  => 'Weekly on selected days',
        'days_of_week'   => 'Days of week',
        'start_date'     => 'Start date',
        'end_date'       => 'End date',
        'max_capacity'   => 'Max capacity',
        'allow_waitlist' => 'Allow waitlist',
        'visible'        => 'Visible to members',
        'description'    => 'Description',
        'description_ph' => 'Shown to members when booking',
        'scope'          => 'Apply changes to',
        'scope_this'     => 'This class only',
        'scope_future'   => 'This and all future classes',
        'create_btn'     => 'Create class',
        'update_btn'     => 'Save changes',
        'cancel_btn'     => 'Cancel',
    ],

    // ── Cancel class ──────────────────────────────────────────────────────────

    'cancel_modal' => [
        'title'    => 'Cancel class',
        'reason'   => 'Reason for cancellation',
        'reasons'  => [
            'trainer_unavailable' => 'Trainer unavailable',
            'facility'            => 'Facility maintenance',
            'holiday'             => 'Holiday',
            'other'               => 'Other',
        ],
        'scope_label'  => 'Cancel',
        'scope_this'   => 'This class only',
        'scope_future' => 'This and all future classes',
        'confirm'      => 'Cancel class',
        'back'         => 'Go back',
        'warning'      => 'All members with bookings will be notified and their bookings cancelled.',
    ],

    // ── Show / detail ─────────────────────────────────────────────────────────

    'show' => [
        'booked'      => 'Booked',
        'waitlisted'  => 'Waitlisted',
        'available'   => 'Available spots',
        'trainer'     => 'Trainer',
        'branch'      => 'Branch',
        'room'        => 'Room',
        'date'        => 'Date',
        'time'        => 'Time',
        'duration'    => 'Duration',
        'description' => 'Description',
        'book_member' => 'Book a member',
        'member_ph'   => 'Search by name, phone or ID…',
        'no_booked'   => 'No bookings yet',
        'no_waitlist' => 'Waitlist is empty',
        'cancel_booking' => 'Cancel booking',
        'mark_attendance'=> 'Mark attendance',
        'edit_class'  => 'Edit class',
        'cancel_class'=> 'Cancel class',
    ],

    // ── Book page ─────────────────────────────────────────────────────────────

    'book' => [
        'title'       => 'Book a Class',
        'subtitle'    => 'Find and book upcoming classes for members',
        'all_branches'=> 'All branches',
        'spots_left'  => ':n spots left',
        'full'        => 'Full',
        'waitlist'    => 'Waitlist',
        'book_btn'    => 'Book',
        'no_classes'  => 'No upcoming classes',
        'no_classes_sub'=> 'Check back later or create a new class.',
        'select_member'=> 'Select member',
        'member_ph'   => 'Search by name, phone or ID…',
        'confirm'     => 'Confirm booking',
    ],

    // ── Trainers page ─────────────────────────────────────────────────────────

    'trainers' => [
        'title'        => 'Trainers',
        'subtitle'     => 'Staff with the trainer role',
        'table_name'   => 'Trainer',
        'table_spec'   => 'Specialisation',
        'table_phone'  => 'Phone',
        'table_classes'=> 'Classes this week',
        'table_status' => 'Status',
        'table_actions'=> 'Actions',
        'view_schedule'=> 'View schedule',
        'add_trainer'  => 'Add trainer',
        'no_trainers'  => 'No trainers found',
        'no_trainers_sub'=> 'Add a staff member with the Trainer role.',
    ],

    // ── Attendance ────────────────────────────────────────────────────────────

    'attendance' => [
        'title'    => 'Mark Attendance',
        'subtitle' => 'Mark present / absent for this class',
        'present'  => 'Present',
        'absent'   => 'Absent',
        'late_cancel'=> 'Late cancel',
        'no_members' => 'No members booked for this class',
        'submit'   => 'Save attendance',
        'warning'  => 'Once saved, attendance cannot be reopened.',
    ],

    // ── Flash ─────────────────────────────────────────────────────────────────

    'flash' => [
        'created'           => 'Class created successfully.',
        'updated'           => 'Class updated.',
        'cancelled'         => 'Class cancelled.',
        'booked'            => 'Booking confirmed.',
        'waitlisted'        => 'Member added to waitlist.',
        'booking_cancelled' => 'Booking cancelled.',
        'attendance_saved'  => 'Attendance saved.',
    ],

];

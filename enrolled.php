<?php
    // This file is part of Moodle - http://moodle.org/
    //
    // Moodle is free software: you can redistribute it and/or modify
    // it under the terms of the GNU General Public License as published by
    // the Free Software Foundation, either version 3 of the License, or
    // (at your option) any later version.
    //
    // Moodle is distributed in the hope that it will be useful,
    // but WITHOUT ANY WARRANTY; without even the implied warranty of
    // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    // GNU General Public License for more details.
    //
    // You should have received a copy of the GNU General Public License
    // along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

    /**
     * Allows course enrolment via a simple text code.
     *
     * @package   enrol_bulk_enrollment
     * @copyright 2021 World University of Bangladesh (CIS)
     * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */

    require_once(dirname(__FILE__) . '/../../config.php');
    require_once($CFG->dirroot . '/enrol/bulk_enrollment/lib.php');
    require_once($CFG->dirroot . '/enrol/bulk_enrollment/classes/enrolhelper.php');

    if(!is_siteadmin()){
        redirect('/');
        exit();
    }

    require_login();

    global $DB;
    $enrol_helper = new enrolhelper();

    $PAGE->set_url(new moodle_url('/enrol/bulk_enrollment/enrolled.php'));
    $PAGE->set_context(\context_system::instance());
    $PAGE->set_title("Bulk Enrollment");
    $PAGE->set_pagelayout('standard');
    $PAGE->navbar->add(get_string("pluginname","enrol_bulk_enrollment"),"/enrol/bulk_enrollment/index.php");
    $PAGE->navbar->add(get_string("enrolled_navbar","enrol_bulk_enrollment"),"/enrol/bulk_enrollment/enrolled.php");
    $PAGE->set_heading(get_string('pluginname','enrol_bulk_enrollment'));

    $courses = get_courses();
    $res = [];
    foreach ($courses as $k => $course){
        $res[] = (array) $course;
    }

    $student_role = $DB->get_record("role",["shortname"=> "student"]);
    $role_assignments = $DB->get_records("role_assignments", ["roleid" => $student_role->id]);
    $all_users = $DB->get_records("user", ['deleted' => '0', 'suspended' => 0]);
    $students = $emails = [];
    foreach ($all_users as $user){
        if(!is_siteadmin($user->id) && !isguestuser($user->id)){
            $emails[] = $user->email;
            $students[] = (array) $user;
        }
    }

    // get students information ums database
    //$output = $enrol_helper->ums_std($emails);
    $output = [];

    $context_data= (object)[
        "courses" => $res,
        "students" => $students,
        "api_std" => json_encode($output),
    ];

    print_r($OUTPUT->header());
    print_r($OUTPUT->heading("Bulk Enrollment"));
    print_r($OUTPUT->render_from_template('enrol_bulk_enrollment/enrolled',$context_data));
    print_r($OUTPUT->footer());
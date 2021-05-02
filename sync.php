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

    $PAGE->set_url(new moodle_url('/enrol/bulk_enrollment/sync.php'));
    $PAGE->set_context(\context_system::instance());
    $PAGE->set_title("Synchronization Data");
    $PAGE->set_pagelayout('standard');
    $PAGE->navbar->add(get_string("pluginname","enrol_bulk_enrollment"),"/enrol/bulk_enrollment/index.php");
    $PAGE->navbar->add(get_string("enrolled_sync","enrol_bulk_enrollment"),"/enrol/bulk_enrollment/sync.php");
    $PAGE->set_heading(get_string('enrolled_sync','enrol_bulk_enrollment'));

    $programs = $enrol_helper->get_program();
    /*$programs = [
        [
            'code' => '01',
            'title' => "Computer Science & Engineering",
        ],[
            'code' => '02',
            'title' => "Business Administrative",
        ],[
            'code' => '03',
            'title' => "Electrical & Electronics Engineering",
        ],
    ];*/
    $context_data = (object)[
        "programs" => $programs,
    ];

    print_r($OUTPUT->header());
    print_r($OUTPUT->heading("Synchronization Data"));
    print_r($OUTPUT->render_from_template('enrol_bulk_enrollment/sync',$context_data));
    print_r($OUTPUT->footer());
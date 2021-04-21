<?php
// This file is part of Moodle - https://moodle.org/
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
 * Adds admin settings for the plugin.
 *
 *
 * @package    enrol_bulk_enrollment
 * @category    admin
 * @copyright  2021 World University of Bangladesh (CIS)
 * @license    https://license.elearning.com.bd/ GNU GPL v3 or later
 * @author     CIS (https://cis.com/)
 * @author     Farjan Hasan <farjan@wub.edu.bd>
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading("enrol_bulk_enrollment_name","",get_string("pluginname_desc","enrol_bulk_enrollment")));

    // WUB UMS API SETUP SETTING
    $settings->add(new admin_setting_heading('enrol_bulk_enrolment_api',
        get_string('api_heading','enrol_bulk_enrollment'),
        get_string('api_description','enrol_bulk_enrollment'),
    ));
    $settings->add(new admin_setting_configtext(
       'enrol_bulk_enrollment/api_url',
        get_string('api_url','enrol_bulk_enrollment'),
        get_string('api_url_desc','enrol_bulk_enrollment'),
        "https://api.e-dhrubo.com/students/multiple_username_wise_std_details"
    ));
    $settings->add(new admin_setting_configtext(
        'enrol_bulk_enrollment/api_username',
        get_string('api_username','enrol_bulk_enrollment'),
        get_string('api_username_desc','enrol_bulk_enrollment'),
        "admin"
    ));
    $settings->add(new admin_setting_configtext(
        'enrol_bulk_enrollment/api_password',
        get_string('api_password','enrol_bulk_enrollment'),
        get_string('api_password_desc','enrol_bulk_enrollment'),
        "1234"
    ));
    $settings->add(new admin_setting_configtext(
        'enrol_bulk_enrollment/api_x_api_key',
        get_string('api_x_api_key','enrol_bulk_enrollment'),
        get_string('api_x_api_key_desc','enrol_bulk_enrollment'),
        "9e50f38559e4b248d3f19cbfa9f43def7f5121393f3f2ec06f3c5c0d57f0caa4"
    ));
}

if ($hassiteconfig) {
    $ADMIN->add('courses', new admin_externalpage('enrol_bulk_enrollment',
        get_string('pluginname', 'enrol_bulk_enrollment'),
        new moodle_url('/enrol/bulk_enrollment/index.php')));
}
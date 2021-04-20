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
 * bulk_enrollment enrolment plugin.
 *
 * This plugin lets the user specify a "flatfile" (CSV) containing enrolment information.
 * On a regular cron cycle, the specified file is parsed and then deleted.
 *
 * @package    enrol_bulk_enrollment
 * @copyright  2021 World University of Bangladesh (CIS)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * bulk_enrollment enrolment plugin implementation.
 *
 * Comma separated file assumed to have four or six fields per line:
 *   operation, role, idnumber(user), idnumber(course) [, starttime [, endtime]]
 * where:
 *   operation        = add | del
 *   role             = student | teacher | teacheredit
 *   idnumber(user)   = idnumber in the user table NB not id
 *   idnumber(course) = idnumber in the course table NB not id
 *   starttime        = start time (in seconds since epoch) - optional
 *   endtime          = end time (in seconds since epoch) - optional
 *
 * @author  Eugene Venter - based on code by Petr Skoda, Martin Dougiamas, Martin Langhoff and others
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


class enrol_bulk_enrollment extends enrol_plugin {






}

function enrol_bulk_enrollment_extend_navigation(global_navigation $navigation){
    if (!has_capability('moodle/site:config', context_system::instance())){
        return;
    }

    $main_node = $navigation->add(get_string('pluginname','enrol_bulk_enrollment'),'/enrol/bulk_enrolment/');
    $main_node->nodetype = 1;
    $main_node->collapse = false;
    $main_node->forceopen = true;
    $main_node->isexpandable = false;
    $main_node->showinflatnavigation = true;
    $navigation->add_node($main_node);
}
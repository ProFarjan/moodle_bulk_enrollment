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
 * @package     enrol_bulk_enrollment
 * @category    admin
 * @copyright   2021 World University of Bangladesh (CIS)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/*if ($hassiteconfig) {
    $ADMIN->add('enrolplugins', new admin_category('enrol_bulk_enrollment', new lang_string('pluginname', 'enrol_bulk_enrollment')));
    $settingspage = new admin_settingpage('managelocalhelloworld', new lang_string('manage', 'local_helloworld'));

    if ($ADMIN->fulltree) {
        $settingspage->add(new admin_setting_configcheckbox(
            'enrol_bulk_enrollment/bulk_enrollment',
            new lang_string('showinnavigation', 'enrol_bulk_enrollment'),
            new lang_string('showinnavigation_desc', 'enrol_bulk_enrollment'),
            1
        ));
    }

    $ADMIN->add('enrolplugins', $settingspage);
}*/
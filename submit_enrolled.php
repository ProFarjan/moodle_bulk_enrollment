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
    require_once($CFG->dirroot . '/enrol/bulk_enrollment/classes/enrolhelper.php');

    if(!is_siteadmin()){
        redirect('/');
        exit();
    }

    $PAGE->set_url(new moodle_url('/enrol/bulk_enrollment/submit_enrolled.php'));

    $enrol_helper = new enrolhelper();
    $output = [];
    if (isset($_POST)){
        $output = $enrol_helper->save_enrolled($_POST);
    }
?>


<?php if (count($output) > 0): ?>
    <?php foreach ($output as $course_id => $data):?>
        <table class="table table-sm table-striped">
            <thead>
            <tr>
                <th colspan="5" style="text-align: center;font-size: 22px;background: lightgreen;border-top: 3px solid lightgreen;">
                    <?=($data[0]['course']->fullname);?>
                </th>
            </tr>
            <tr>
                <th width="3%">SL</th>
                <th>Name</th>
                <th>Email</th>
                <th width="14%" style="text-align: right;">Status</th>
            </tr>
            </thead>
            <tbody>
            <?php $i = 0;?>
            <?php foreach ($data as $k => $res):?>
                <tr>
                    <td><?=++$i;?></td>
                    <td><?=$res['user']->firstname. " " . $res['user']->lastname;?></td>
                    <td><?=$res['user']->email;?></td>
                    <td style="text-align: right;"><?=$res['status'];?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>

<?php endif ?>
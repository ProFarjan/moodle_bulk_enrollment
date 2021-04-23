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

    $PAGE->set_url(new moodle_url('/enrol/bulk_enrollment/request_enrolled.php'));

    $enrol_helper = new enrolhelper();
    $output = $ums = [];
    if (isset($_POST)){
        $output = $enrol_helper->verify_enrollment($_POST);
        $ums = $output['ums'];
    }
?>


<?php if (count($output['moodle']) > 0): ?>
<?php foreach ($output['moodle'] as $course_id => $data):?>
<table class="table table-sm">
    <thead>
        <tr>
            <th colspan="5" style="text-align: center;font-size: 22px;background: #eee;border-top: 3px solid #ddd;">
                <?=($data[0]['course']->fullname);?>
            </th>
        </tr>
        <tr>
            <th width="3%">
                <input type="checkbox" onclick="sAll(this,'#course_<?=$course_id;?> input:checkbox');" checked/>
            </th>
            <th>Name</th>
            <th>Email</th>
            <th>Is Active</th>
            <th width="14%" style="text-align: right;">Status</th>
        </tr>
    </thead>
    <tbody id="course_<?=$course_id;?>">
        <?php $i = 0;?>
        <?php foreach ($data as $k => $res):?>
            <?php
                $student = [];
                $email = $res['user']->email;
                if (array_key_exists($email,$ums)){
                    $student = $ums[$email];
                }
            ?>
            <?php
                $ums_status = 'Undefined';
                if ($student){
                    if ($student->is_active){
                        $ums_status = 'Active';
                    }else{
                        $ums_status = 'Deactivate';
                    }
                }
            ?>
            <tr class="<?= ($ums_status == 'Deactivate') ? 'bg-danger' : '' ;?>">
                <td>
                    <input type="checkbox" name="student[<?=$res['course']->id;?>][<?=$res['user']->id;?>]"
                        <?php if(($res['status'] == 'already exist') || $ums_status == 'Deactivate'): ?>
                            disabled
                        <?php elseif($ums_status == 'Undefined'):?>

                        <?php elseif($res['status'] == 'enrollable'):?>
                            checked
                        <?php endif;?>
                    />
                </td>
                <td><?=$res['user']->firstname. " " . $res['user']->lastname;?></td>
                <td><?=$res['user']->email;?></td>
                <td>
                    <?=$ums_status;?>
                </td>
                <td style="text-align: right;"><?=$res['status'];?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endforeach; ?>
<script>
    function sAll(elemEnt,eachAll='input:checkbox'){
        var status = $(elemEnt).is(":checked");
        $(eachAll).each(function(){
            const d = $(this).attr('disabled');
            if (d != 'disabled'){
                $(this).prop('checked',status);
            }
        });
    }
</script>
<?php endif ?>
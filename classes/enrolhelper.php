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

class enrolhelper {

    /**
     * @param array $emails
     * @return array
     */
    public function ums_std(array $emails){
        $output = [];
        $api = "https://api.e-dhrubo.com/students/multiple_username_wise_std_details";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $api);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query([
            "X-API-KEY" => "9e50f38559e4b248d3f19cbfa9f43def7f5121393f3f2ec06f3c5c0d57f0caa4",
            "email" => implode(",",$emails)
        ]));
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, FALSE);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 45);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
        curl_setopt($curl, CURLOPT_USERPWD, "admin:1234");

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($curl);
        $student_details_data = json_decode($result);
        curl_close($curl);
        if($student_details_data->status == 'success') {
            $output = $student_details_data->message->StudentDetails;
        }
        return $output;
    }

    /**
     * @param $course_id
     * @param $userid
     * @param $roleid
     * @param string $enrolmethod
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    public function check_enrol($course_id, $userid, $roleid, $enrolmethod = 'manual') {
        global $DB;
        $response = [];
        $user = $DB->get_record('user', array('id' => $userid, 'deleted' => 0), '*', MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $course_id), '*', MUST_EXIST);
        $context = context_course::instance($course->id);
        if (!is_enrolled($context, $user)) {
            $enrol = enrol_get_plugin($enrolmethod);
            if ($enrol === null) {
                return $response = [
                    "status" => "not enrolled",
                    "user" => $user,
                    "course" => $course,
                ];
            }
            $instances = enrol_get_instances($course->id, true);
            $manualinstance = null;
            foreach ($instances as $instance) {
                if ($instance->name == $enrolmethod) {
                    $manualinstance = $instance;
                    break;
                }
            }
            if ($manualinstance !== null) {
                $instanceid = $enrol->add_default_instance($course);
                if ($instanceid === null) {
                    $instanceid = $enrol->add_instance($course);
                }
                $instance = $DB->get_record('enrol', array('id' => $instanceid));
            }
            $enrol->enrol_user($instance, $userid, $roleid);
            $response = [
                "status" => "enrolled",
                "user" => $user,
                "course" => $course,
            ];
        }else{
            $response = [
                "status" => "already exist",
                "user" => $user,
                "course" => $course,
            ];
        }
        return $response;
    }

    public function save_enrolled($data){
        if(isset($_POST) && isset($_POST['courses']) && isset($_POST['users'])){
            global $DB;
            $courses = $_POST['courses'];
            $users = $_POST['users'];
            $courses = explode(',', $courses);
            $users = explode(',', $users);

            $res = [];
            foreach ($courses as $course_id){
                $plugin_instance = $DB->get_record("enrol", array('courseid'=> $course_id, 'enrol'=>'manual'));
                foreach ($users as $user_id){
                    $res[$course_id][] = $this->check_enrol($course_id,$user_id,$plugin_instance->roleid);
                }
            }
            return $res;
        }
    }

    public function pre($data){
        print_r("<pre>");
        print_r($data);
        print_r("</pre>");
    }

    public function dd($data){
        print_r("<pre>");
        print_r($data);
        print_r("</pre>");
        die();
    }


}
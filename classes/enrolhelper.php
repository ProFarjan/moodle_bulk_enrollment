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
        $api_url = get_config('enrol_bulk_enrollment','api_url');
        $api_username = get_config('enrol_bulk_enrollment','api_username');
        $api_password = get_config('enrol_bulk_enrollment','api_password');
        $api_x_api_key = get_config('enrol_bulk_enrollment','api_x_api_key');

        $output = [];
        if ($api_url && $api_x_api_key){
            $api = $api_url;
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $api);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query([
                "X-API-KEY" => $api_x_api_key,
                "email" => implode(",",$this->short_email($emails))
            ]));
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, FALSE);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 45);
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
            curl_setopt($curl, CURLOPT_USERPWD, "$api_username:$api_password");

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($curl);
            $student_details_data = json_decode($result);
            curl_close($curl);
            if($student_details_data->status == 'success') {
                $output = $student_details_data->message->StudentDetails;
            }
        }
        return $output;
    }

    /**
     * @param array $emails
     * @return array
     */
    private function short_email(array $emails){
        $short_email = [];
        foreach ($emails as $email){
            $sm = explode($email);
            $short_email[] = $sm[0];
        }
        return $short_email;
    }

    /**
     * @param $course_id
     * @param $userid
     * @param $roleid
     * @param bool $check_enrollment
     * @param string $enrolmethod
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    public function check_enrol($course, $user, $roleid, $check_enrollment = true, $enrolmethod = 'manual') {
        global $DB;
        $response = [];
        //$user = $DB->get_record('user', array('id' => $userid, 'deleted' => 0), '*', MUST_EXIST);
        //$course = $DB->get_record('course', array('id' => $course_id), '*', MUST_EXIST);
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
            $status_value = "enrollable";
            if ($check_enrollment){
                $enrol->enrol_user($instance, $user->id, $roleid);
                $status_value = "enrolled";
            }
            $response = [
                "status" => $status_value,
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

            $courses = $this->setID($DB->get_records_sql("SELECT * FROM {course} WHERE id IN ($courses)"));
            $users = $this->setID($DB->get_records_sql("SELECT * FROM {user} WHERE deleted = 0 and id IN ($users)"));

            $res = [];
            foreach ($courses as $course){
                $plugin_instance = $DB->get_record("enrol", array('courseid'=> $course->id, 'enrol'=>'manual'));
                foreach ($users as $user){
                    $res[$course->id][] = $this->check_enrol($course,$user,$plugin_instance->roleid);
                }
            }
            return $res;
        }
    }

    public function verify_enrollment($data){
        if(isset($_POST) && isset($_POST['courses']) && isset($_POST['users'])){
            global $DB;
            $courses = $_POST['courses'];
            $users = $_POST['users'];

            $courses = $this->setID($DB->get_records_sql("SELECT * FROM {course} WHERE id IN ($courses)"));
            $users = $this->setID($DB->get_records_sql("SELECT * FROM {user} WHERE deleted = 0 and id IN ($users)"));

            $emails = $this->get_emails($users);
            $api_data = $this->ums_std($emails);

            $res = [];
            foreach ($courses as $course){
                $plugin_instance = $DB->get_record("enrol", array('courseid'=> $course->id, 'enrol'=>'manual'));
                foreach ($users as $user){
                    $res[$course->id][] = $this->check_enrol($course,$user,$plugin_instance->roleid,false);
                }
            }
            return $res;
        }
    }

    /**
     * @param array $users
     * @return array
     */
    private function get_emails(array $users){
        $emails = [];
        foreach ($users as $user){
            $emails[] = $user->email;
        }
        return $emails;
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

    /**
     * @param $data
     * @param $col
     * @return array
     */
    private function setID($data,$col="id"){
        $res = [];
        if(count($data) == 0){
            return $res;
        }
        foreach ($data as $k => $val){
            $val = (array) $val;
            $res[$val[$col]] = (object) $val;
        }
        return $res;
    }


}
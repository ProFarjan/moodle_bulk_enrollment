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
 * @license   https://opensource.org/licenses/MIT GNU GPL v3 or later
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

            $email_all = $this->short_email($emails);
            $api = $api_url;

            $curl = curl_init();

            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, "X-API-KEY=$api_x_api_key&email=" . implode(',', $email_all));

            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, FALSE);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 45);
            // Optional Authentication:
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
            curl_setopt($curl, CURLOPT_USERPWD, "$api_username:$api_password");
            curl_setopt($curl, CURLOPT_URL, $api);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($curl);
            $student_details_data = json_decode($result);
            curl_close($curl);

            //$this->dd($student_details_data);
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
            $sm = explode("@",$email);
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

    /**
     * @param $data
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    public function save_enrolled($data){
        if(isset($_POST) && isset($_POST['student']) && isset($_POST['users'])){
            global $DB;
            $students = $_POST['student'];

            $cu = $this->get_student_output($students);
            $courses = implode(',',$cu['courses']);
            $users = implode(',',$cu['students']);

            $courses = $this->setID($DB->get_records_sql("SELECT * FROM {course} WHERE id IN ($courses)"));
            $users = $this->setID($DB->get_records_sql("SELECT * FROM {user} WHERE deleted = 0 and id IN ($users)"));

            $res = [];
            foreach ($students as $course_id => $student){
                $plugin_instance = $DB->get_record("enrol", array('courseid'=> $course_id, 'enrol'=>'manual'));
                $course = $courses[$course_id];
                foreach ($student as $id => $status){
                    $user = $users[$id];
                    $res[$course_id][] = $this->check_enrol($course,$user,$plugin_instance->roleid);
                }
            }
            return $res;
        }
    }

    /**
     * @param array $students
     * @return array
     */
    private function get_student_output(array $students): array
    {
        $res = [
            'courses' => [],
            'students' => [],
        ];
        foreach ($students as $course => $student){
            $res['courses'][$course] = $course;
            foreach ($student as $id => $status){
                $res['students'][$id] = $id;
            }
        }
        return $res;
    }

    /**
     * @param $data
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    public function verify_enrollment($data){
        if(isset($_POST) && isset($_POST['courses']) && isset($_POST['users'])){
            global $DB;
            $courses = $_POST['courses'];
            $users = $_POST['users'];

            $courses = $this->setID($DB->get_records_sql("SELECT * FROM {course} WHERE id IN ($courses)"));
            $users = $this->setID($DB->get_records_sql("SELECT * FROM {user} WHERE deleted = 0 and id IN ($users)"));

            $emails = $this->get_emails($users);
            $api_data = $this->setID($this->ums_std($emails),"username");

            $res = [];
            $res['ums'] = $api_data;
            foreach ($courses as $course){
                $plugin_instance = $DB->get_record("enrol", array('courseid'=> $course->id, 'enrol'=>'manual'));
                foreach ($users as $user){
                    $res['moodle'][$course->id][] = $this->check_enrol($course,$user,$plugin_instance->roleid,false);
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

    /**
     * @param $obj_2d
     * @return array
     */
    public function convert_arr($obj_2d){
        $res = [];
        foreach ($obj_2d as $data){
            $res[] = (array) $data;
        }
        return $res;
    }

    /**
     * @param $data
     */
    public function pre($data){
        print_r("<pre>");
        print_r($data);
        print_r("</pre>");
    }

    /**
     * @param $data
     */
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

    public function get_courses($data){
        if (isset($_POST)){
            global $DB;
            $category_id = $_POST['category_id'];
            return $DB->get_records('course',["category"=>$category_id]);
        }
    }

    public function get_program(){
        $res = [];
        $programs = $_SESSION['programs'];
        if (!$programs){
            $api_url_programs = get_config('enrol_bulk_enrollment','api_url_programs');
            $programs = $this->ums($api_url_programs);
            $_SESSION['programs'] = $programs;
        }
        return $programs;
    }

    /**
     * @param $program_id
     * @return array
     * @throws dml_exception
     */
    public function get_batches($program_id){
        $api_url_batch = get_config('enrol_bulk_enrollment','api_url_batch');
        if (substr($api_url_batch,-1) != '/'){
            $api_url_batch .= '/';
        }
        $api_url_batch .= $program_id;
        return $this->ums($api_url_batch);
    }

    public function get_students($data){
        $program = $data['program'];
        $batch = $data['batch'];
        $res = [];
        if ($program){
            global $DB;

            $get_programs = $_SESSION['programs'];
            if (!$get_programs){
                $get_programs = [];
            }
            $get_programs = $this->setID($get_programs);

            $wh_sql = '1';
            if ($program != 'all'){
                $wh_sql .= " and ums.program_id = '$program'";
            }
            if (!empty($batch)){
                $wh_sql .= " and ums.batch_id = '$batch'";
            }

            $sql = "SELECT u.*,ums.program_id,ums.batch_id FROM {user} u LEFT JOIN {enrol_ums_user} ums ON u.id = ums.user_id WHERE $wh_sql ORDER BY u.firstname";

            $students = $DB->get_records_sql($sql);
            foreach ($students as $student){
                if (array_key_exists($student->program_id,$get_programs)){
                    $student->program_id = $get_programs[$student->program_id]->title;
                }
                $res[] = $student;
            }
        }
        return $res;
    }

    /**
     * @param $api
     * @return array
     * @throws dml_exception
     */
    private function ums($api){
        $api_x_api_key = get_config('enrol_bulk_enrollment','api_x_api_key');
        $api_username = get_config('enrol_bulk_enrollment','api_username');
        $api_password = get_config('enrol_bulk_enrollment','api_password');
        $api_x_api_key = "X-API-KEY=".$api_x_api_key;
        $api .= "?".$api_x_api_key;
        $ch = curl_init($api);
        if ($ch === false) {
            throw new Exception('failed to initialize');
        }
        curl_setopt($ch,CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
        curl_setopt($ch, CURLOPT_USERPWD, "$api_username:$api_password");
        $d = curl_exec($ch);
        if ($d === false) {
            throw new Exception(curl_error($ch), curl_errno($ch));
        }
        curl_close($ch);
        $output = [];
        if ($d){
            $d = json_decode($d);
            if ($d->status == 'success'){
                $output = $d->message;
            }
        }
        return $output;
    }

    /**
     * @param array $data
     * @return array
     * @throws dml_exception
     */
    public function get_program_wise_students(array $data): array {
        $program_id = $data['sync_program'];
        $res = [];
        if ($program_id){
            global $DB;
            $sql = "SELECT u.*,ums.user_id as sync FROM {user} u left JOIN {enrol_ums_user} ums ON u.id = ums.user_id WHERE u.username like '$program_id%' ORDER BY u.firstname";
            $res = $DB->get_records_sql($sql);
        }
        return $res;
    }

    /**
     * @param array $data
     * @return array
     */
    public function get_synchronization_data(array $data): array{
        global $DB;
        $output = [];
        $users = $data['user'];
        if (is_array($users) && count($users) > 0){
            $sql = "SELECT * FROM {user} WHERE id IN (".implode(',',array_keys($users)).")";
            $users = $this->setID($DB->get_records_sql($sql),'id');
            if ($users){
                $users_id = array_keys($users);
                $ums_data = $this->get_ums_sync_data($users_id);
//                $DB->insert_records("enrol_ums_user",$ums_data);
//                $output['sync'] = $ums_data;
                $output['users'] = $users;
            }
        }
        return $output;
    }

    private function get_ums_sync_data($users_id){
        $output = [];

        $api = get_config('enrol_bulk_enrollment','api_ums_sync');
        $api_username = get_config('enrol_bulk_enrollment','api_username');
        $api_password = get_config('enrol_bulk_enrollment','api_password');
        $api_x_api_key = get_config('enrol_bulk_enrollment','api_x_api_key');

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, "X-API-KEY=$api_x_api_key&users=" . implode(',', $users_id));

        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, FALSE);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 45);
        // Optional Authentication:
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
        curl_setopt($curl, CURLOPT_USERPWD, "$api_username:$api_password");
        curl_setopt($curl, CURLOPT_URL, $api);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($curl);
        $student_details_data = json_decode($result);
        curl_close($curl);

        //$this->dd($student_details_data);
        if($student_details_data->status == 'success') {
            $output = $student_details_data->StudentDetails;
        }
        return $output;
    }

}
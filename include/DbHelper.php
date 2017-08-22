<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DbHelper
 *
 * @author abert
 */
class DbHelper {

    //put your code here
    private $con;

    function __construct() {
        require_once dirname(__FILE__) . '/dbConnect.php';

        $db = new DbConnect();
        $this->con = $db->connect();
    }

    //machine timestamp
    function getDatetimeNow() {
        $tz_object = new DateTimeZone('EAT');
        $datetime = new DateTime();
        $datetime->setTimezone($tz_object);
        return $datetime->format('Y\-m\-d');
    }

//check if the user exist
    public function studentLogin($reg_number, $password) {
        // $password = md5($pass);
        $stmt = $this->con->prepare("SELECT * FROM student WHERE registration_number=? and password=? && status=0");
        $stmt->bind_param("ss", $reg_number, $password);
        $stmt->execute();
        $stmt->store_result();
        //Getting the result
        $num_rows = $stmt->num_rows;
        //closing the statment
        $stmt->close();
        return $num_rows > 0;
    }

    //if exists fetch the user details
    public function user_details($registration_number) {
        $stmt = $this->con->prepare("SELECT student_id,firstName,lastName,registration_number,universities_university_id FROM student where registration_number=?");
        $stmt->bind_param('s', $registration_number);
        $stmt->execute();
        $stmt->bind_result($student_id, $firstName, $lastName, $registration_number, $universities_university_id);
        $feed = array();
        while ($stmt->fetch()) {
            $data['student_id'] = $student_id;
            $data['firstName'] = $firstName;
            $data['lastName'] = $lastName;
            $data['registration_number'] = $registration_number;
            $data['university_id'] = $universities_university_id;
            $data['status'] = true;
            $data['message'] = "Login successfull";
            array_push($feed, $data);
        }
        $stmt->close();
        return $feed;
    }

    //match student old password
    function checkPassword($student_id, $old_password) {
        $stmt = $this->con->prepare("SELECT * FROM student WHERE student_id=? and password=?");
        $stmt->bind_param("ss", $student_id, $old_password);
        $stmt->execute();
        $stmt->store_result();
        //Getting the result
        $num_rows = $stmt->num_rows;
        //closing the statment
        $stmt->close();
        return $num_rows > 0;
    }

    //change password
    function change_password($student_id, $new_password) {
        $stmt = $this->con->prepare('update student set password=? where student_id=?');
        $stmt->bind_param('ss', $new_password, $student_id);
        $stmt->execute();
        $stmt->close();
    }

    //registered academic years
    public function studentAcademicYears($student_id) {
        $stmt = $this->con->prepare('select registered_academic_years_id,academic_year,semester,academic_years_years_id,'
                . 'semester_semester_id,ra._when_added as time_stamp from registered_academic_years ra LEFT JOIN academic_years ac ON(ra.academic_years_years_id=ac.academic_year_id)'
                . ' LEFT JOIN semesters s ON(ra.semester_semester_id=s.semester_id) where student_student_id=?');
        $stmt->bind_param('s', $student_id);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($data[0], $data[1], $data[2], $data[3], $data[4], $data[5]);
        $response = array();
        $response['success'] = true;
        if ($stmt->num_rows > 0) {
            $response['data'] = array();
            while ($stmt->fetch()) {
                $data['id'] = $data[0];
                $data['academic_year'] = $data[1];
                $data['semester'] = $data[2];
                $data['academic_years_years_id'] = $data[3];
                $data['semester_semester_id'] = $data[4];
                $data['time_stamp'] = date("F Y", strtotime($data[5]));
                //date("jS F, Y", strtotime("11/12/10"))
                array_push($response['data'], $data);
            }
        } else {
            $response['success'] = false;
        }

        $stmt->close();
        return $response;
    }

    //first student details
    public function student_details($student_id) {
        $stmt = $this->con->prepare('select universities_university_id,courses_course_id from student where student_id=?');
        $stmt->bind_param('s', $student_id);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $data;
    }

    public function fetch_student_course($student_id) {
        $stmt = $this->con->prepare('select courses_course_id from student where student_id=?');
        $stmt->bind_param('s', $student_id);
        $stmt->execute();
        return $stmt;
    }

    //all university academic years
    public function university_academic_years($university_id) {
        //university  academic years
        $stmt = $this->con->prepare('select academic_year_id,academic_year from academic_years where university_university_id=? && academic_years.status=0');
        $stmt->bind_param('s', $university_id);
        $stmt->execute();
        return $stmt;
    }

    public function semesters() {
        $response = array();
        $sql = "select semester_id,semester from semesters";
        $stmt = $this->con->prepare($sql);
        $stmt->execute();
        $stmt->bind_result($semester_id, $semester);
        while ($stmt->fetch()) {
            $data['id'] = $semester_id;
            $data['semester'] = $semester;
            array_push($response, $data);
        }
        return $response;
    }

    //check if the student already registered

    public function isAlreadyRegistered($student_id, $academic_year, $semester) {
        $stmt = $this->con->prepare('select * from registered_academic_years where student_student_id=? && academic_years_years_id=? && semester_semester_id=?');
        $stmt->bind_param('sss', $student_id, $academic_year, $semester);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    //student regiatration/academic year
    public function studentRegistration($student_id, $academic_year, $semester) {
        $time_stamp = $this->getDatetimeNow();
        $stmt = $this->con->prepare('INSERT INTO registered_academic_years(student_student_id,academic_years_years_id,semester_semester_id,_when_added)'
                . 'VALUES(?,?,?,?)');
        $stmt->bind_param('ssss', $student_id, $academic_year, $semester, $time_stamp);
        $stmt->execute();
        $lastInsertId = $this->con->insert_id;
        $stmt->close();
        return $lastInsertId;
    }

    public function register_units($student_id, $academic_year, $semester, $registered_academic_years_id) {
        //fetch un  
        $course = $this->student_Course($student_id);
        $stmt = $this->con->prepare('select assigned_units.course_unit_unit_id from assigned_units where academic_academic_year_id=? && semester_semester_id=?'
                . ' && 	course_course_id=?');
        $stmt->bind_param('sss', $academic_year, $semester, $course);
        $stmt->execute();
        $stmt->bind_result($data[0]);

        $insert_stmt = $this->con->prepare('INSERT INTO registered_course_units(course_units_units_id,student_student_id,registered_academic_years_id)'
                . ' values(?,?,?)');
        while ($stmt->fetch()) {
            $insert_stmt->bind_param('sss', $data[0], $student_id, $registered_academic_years_id);
            $insert_stmt->execute();

            print_r($data[0], $student_id, $registered_academic_years_id);
        }
//        $insert_stmt->close();
        $stmt->close();
    }

    public function student_Course($student_id) {
        $stmt = $this->con->prepare('select courses_course_id,lastName from student where student_id=?');
        $stmt->bind_param('s', $student_id);
        $stmt->execute();
        $stmt->bind_result($data[0], $data[1]);
        $stmt->fetch();
        return $data[0];
    }

    //course units
    public function course_units($student_id, $register_year_id) {
        $stmt = $this->con->prepare('select course_unit_code,course_unit from registered_course_units rc left join course_units c ON(rc.course_units_units_id=c.course_unit_id) where student_student_id=? and registered_academic_years_id=?');
        $stmt->bind_param('ss', $student_id, $register_year_id);
        $stmt->execute();
        return $stmt;
    }

//all course units
    public function fetch_course_units($course_id) {
        $stmt = $this->con->prepare('select * from course_units where courses_course_id=?');
        $stmt->bind_param('s', $course_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function academic_year_units($year, $semester, $course_id) {
        $stmt = $this->con->prepare('select course_unit_code,course_unit from assigned_units au '
                . 'left join course_units c ON(au.course_unit_unit_id=c.course_unit_id) '
                . 'where au.academic_academic_year_id=? and au.semester_semester_id=? && course_course_id=?');
        $stmt->bind_param('sss', $year, $semester, $course_id);
        $stmt->execute();
        return $stmt;
    }

    //fetch all the non registered course units 
    public function fetch_non_registered_units($registerd_year_id, $student_id) {
        $course_id = $this->student_Course($student_id);
        $stmt_year_fetch = $this->registered_year($registerd_year_id);
        $stmt_year_fetch->bind_result($data[0], $data[1]);
        $stmt_year_fetch->fetch();
        $stmt_year_fetch->close();
        $stmt = $this->con->prepare('select course_unit_id,course_unit_code,course_unit from assigned_units au left join'
                . ' course_units c on(au.course_unit_unit_id=c.course_unit_id) '
                . 'where au.course_course_id=? && au.academic_academic_year_id=? && au.semester_semester_id=?'
                . ' && au.course_unit_unit_id NOT IN (select course_units_units_id '
                . 'from registered_course_units rc where registered_academic_years_id=? && student_student_id=?)');
        $stmt->bind_param('sssss', $course_id, $data[0], $data[1], $registerd_year_id, $student_id);
        $stmt->execute();
        return $stmt;
    }

//    fetch student  registered year details
    private function registered_year($registerd_year_id) {
        $stmt = $this->con->prepare('select academic_years_years_id,semester_semester_id from registered_academic_years where registered_academic_years_id=?');
        $stmt->bind_param('s', $registerd_year_id);
        $stmt->execute();
        return $stmt;
    }

    //register units

    public function registerUnits($unit_id, $year_id, $student_id) {
        $stmt = $this->con->prepare('insert into registered_course_units(course_units_units_id,registered_academic_years_id,student_student_id)'
                . 'values(?,?,?)');
        $stmt->bind_param('sss', $unit_id, $year_id, $student_id);
        $stmt->execute();
        $stmt->close();
    }

    //check if course unit already registered


    public function UnitExists($unit_id, $student_id) {
        $stmt = $this->con->prepare('select * from registered_course_units where course_units_units_id=? && student_student_id=?');
        $stmt->bind_param('ss', $unit_id, $student_id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    //get marks academic years and semesters


    public function fetch_marks($student_reg, $university_id, $year_id, $semester_id) {
        $stmt = $this->con->prepare('select course_unit,mark from marks where reg_number=? && university_university_id=? && academic_years_year_id=? && semesters_semester_id=?');
        $stmt->bind_param('ssss', $student_reg, $university_id, $year_id, $semester_id);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($course_unit, $mark);

        $response = array();
        $response['success'] = false;
        $university = $university_id;
        if ($stmt->num_rows > 0) {
            $response['array'] = array();
            $response['success'] = true;
            $grading = $this->fetch_grading($university);
            $grading->bind_result($grade1, $from, $to, $value, $progress);
            $array_grades = array();
            while ($grading->fetch()) {
                $array_grades[] = array("from" => $from, "to" => $to, "grade_value" => $grade1, "value" => $value, "progress" => $progress);
            }
            $array_data = array();
            $data_row = array();
            while ($stmt->fetch()) {
                foreach ($array_grades as $grade) {
                    if ($mark >= $grade["from"] && $mark <= $grade["to"]) {

                        if ($grade["progress"] == 1) {
                            $data_row[] = array(
                                "course_unit" => $course_unit,
                                'mark' => $mark,
                                "grade_value" => $grade["grade_value"],
                                "value" => $grade["value"],
                                "progress" => "Retake"
                            );
                        } else {
                            $data_row[] = array(
                                "course_unit" => $course_unit,
                                'mark' => $mark,
                                "grade_value" => $grade["grade_value"],
                                "value" => $grade["value"],
                                "progress" => "Normal Progress"
                            );
                        }
                    }
                }
            }
            return $data_row;
        }
    }

    public function fetch_retakes($student_reg, $university_id) {
        $stmt = $this->con->prepare('select course_unit,mark from marks where reg_number=?'
                . '&& university_university_id=?');
        $stmt->bind_param('ss', $student_reg, $university_id);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($course_unit, $mark);

        $response = array();
        $response['success'] = false;
        $university = $university_id;
        if ($stmt->num_rows > 0) {
            $response['array'] = array();
            $response['success'] = true;
            $grading = $this->fetch_grading($university);
            $grading->bind_result($grade1, $from, $to, $value, $progress);
            $array_grades = array();
            while ($grading->fetch()) {
                $array_grades[] = array("from" => $from, "to" => $to, "grade_value" => $grade1, "value" => $value, "progress" => $progress);
            }
            $array_data = array();
            $data_row = array();
            while ($stmt->fetch()) {
                foreach ($array_grades as $grade) {
                    if ($mark >= $grade["from"] && $mark <= $grade["to"]) {

                        if ($grade["progress"] == 1) {
                            $data_row[] = array(
                                "course_unit" => $course_unit
                            );
                        }
                    }
                }
            }
            return $data_row;
        }
    }

    public function fetch_grading($university_id) {
        $stmt = $this->con->prepare('select grade,value_frm,value_to,grade_value,progress from grading where university_id=?');
        $stmt->bind_param('s', $university_id);
        $stmt->execute();
        return $stmt;
    }

    //submit student complaints
    public function student_complaint($student_id, $description, $complaint_category, $year, $semester) {
        $time_stamp = $this->getDatetimeNow();
        $stmt = $this->con->prepare('INSERT INTO complaints(complaint,student_student_id,type_id,_when_added,year,semester) values(?,?,?,?,?,?)');
        $stmt->bind_param('ssssss', $description, $student_id, $complaint_category, $time_stamp, $year, $semester);
        $stmt->execute();
        $stmt->close();
    }

    public function fetch_my_complaints($student_id) {
        $stmt = $this->con->prepare('select c.complaint_id,c.complaint,t.type,c._status from complaints c left join '
                . 'complaint_types t on(c.type_id=t.complaint_types_id) where c.student_student_id=?');
        $stmt->bind_param('s', $student_id);
        $stmt->execute();
        return $stmt;
    }

    //chat
    //Function to store gcm registration token in database
    public function storeGCMToken($student_id, $token) {
        $stmt = $this->con->prepare("INSERT INTO tokens(token,student_student_id) VALUES(?,?)");
        $stmt->bind_param("ss", $token, $student_id);
        if ($stmt->execute())
            return true;
        return false;
    }

    //fetch chat groups


    public function fetch_chat_groups($student_id) {
        $stmt = $this->con->prepare('select * from chatgroups g left join group_members m on(g.group_id=m.group_group_id) where student_student_id=?');
        $stmt->bind_param('s', $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function fetch_other_groups($student_id, $university_id) {
        $stmt = $this->con->prepare('select g.group_id,g.group_name from chatgroups g where g.status=0 && g.university_id=? && g.group_id not in(select m.group_group_id from group_members m where student_student_id=?)');
        $stmt->bind_param('ss', $university_id, $student_id);
        $stmt->execute();
        return $stmt;
    }

    public function insert_request($user, $student_id, $group_id) {
        $stmt = $this->con->prepare('insert into group_members(role,student_student_id,group_group_id) '
                . 'values(?,?,?)');
        $stmt->bind_param('sss', $user, $student_id, $group_id);
        $stmt->execute();
        $stmt->close();
    }

    //create group
    public function create_group($group_name) {
        $time_stamp = $this->getDatetimeNow();
        $stmt = $this->con->prepare('insert into chatgroups(group_name,_when_added)values(?,?)');
        $stmt->bind_param('ss', $group_name, $time_stamp);
        $stmt->execute();
        $group_id = $this->con->insert_id;
        $stmt->close();
        return $group_id;
    }

//register group members

    public function register_members($student_id, $role, $group_id) {
        $time_stamp = $this->getDatetimeNow();
        $stmt = $this->con->prepare('insert into group_members(role,student_student_id,group_group_id,_when_added)values(?,?,?,?)');
        $stmt->bind_param('ssss', $role, $student_id, $group_id, $time_stamp);
        $stmt->execute();
        $stmt->close();
    }

    //search student 
    public function search_student($university_id, $student_reg) {
        $stmt = $this->con->prepare('select * from student where universities_university_id=? && registration_number=?');
        $stmt->bind_param('ss', $university_id, $student_reg);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result;
    }

    //sending message to other devices
    public function getRegistrationTokens($student_id) {
        $stmt = $this->con->prepare("SELECT token FROM tokens WHERE NOT student_student_id = ?;");
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $tokens = array();
        while ($row = $result->fetch_assoc()) {
            array_push($tokens, $row['token']);
        }
        return $tokens;
    }

    //group devices
    public function getGroupTokens($student_id, $group_id) {
        $stmt = $this->con->prepare("SELECT token FROM tokens t left join student s on(t.student_student_id=s.student_id)"
                . " left join group_members g on(s.student_id=g.student_student_id) WHERE NOT t.student_student_id = ? && group_group_id=?;");
        $stmt->bind_param("ss", $student_id, $group_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $tokens = array();
        while ($row = $result->fetch_assoc()) {
            array_push($tokens, $row['token']);
        }
        return $tokens;
    }

    //fetch group chat messges
    public function group_messages($student_id, $group_id) {
        $stmt = $this->con->prepare("SELECT group_message_id,message,m.student_student_id,firstName,lastName,m._when_added as time_stamp FROM group_messages m inner join  student s on(m.student_student_id = s.student_id)inner join chatgroups g  on( m.group_group_id=g.group_id) inner join group_members gm on(g.group_id=gm.group_group_id)"
                . " where gm.student_student_id=? and m.group_group_id=?  ORDER BY group_message_id ASC;");
        $stmt->bind_param('ss', $student_id, $group_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result;
    }

    //send group chat message
    public function sendGroupMessage($student_id, $group_id, $message) {
        $time_stamp = $this->getDatetimeNow();
        $stmt = $this->con->prepare("INSERT INTO group_messages(message,group_group_id,student_student_id,_when_added) VALUES (?,?,?,?)");
        $stmt->bind_param("ssss", $message, $group_id, $student_id, $time_stamp);
        if ($stmt->execute())
            return true;
        return false;
    }

    //fetch group memembers
    public function groupMembers($group_id, $student_id) {
        $stmt = $this->con->prepare("select member_id,firstName,lastName from group_members m left join student s on(m.student_student_id=s.student_id)where group_group_id=? && not student_student_id=?");
        $stmt->bind_param('ss', $group_id, $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    //fetch member group role
    public function user_group_role($student_id, $group_id) {
        $stmt = $this->con->prepare('select role from group_members where student_student_id=? && group_group_id=?');
        $stmt->bind_param('ss', $student_id, $group_id);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $data;
    }

    //Function to add message to the database
    public function addMessage($student_id, $message) {
//        $time_stamp = $this->getDatetimeNow();
        $stmt = $this->con->prepare("INSERT INTO messages (message,student_student_id) VALUES (?,?)");
        $stmt->bind_param("ss", $message, $student_id);
        if ($stmt->execute())
            return true;
        return false;
    }

    //Function to get messages from the database 
    public function getMessages() {
        $stmt = $this->con->prepare("SELECT message_id,message,student_student_id,firstName,m._when_added as time_stamp FROM messages m, student s WHERE m.student_student_id = s.student_id ORDER BY message_id ASC;");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result;
    }

//latest news
    public function fetch_latestNews($university_id) {
        $stmt = $this->con->prepare('select news_id,title,news_detail,_when_added,image from news'
                . ' where university_university_id=? order by news_id desc');
        $stmt->bind_param('s', $university_id);
        $stmt->execute();
        return $stmt;
    }

    //add my favourite news
    public function submitMyNews($news_id, $student_id) {
        $time_stamp = $this->getDatetimeNow();
        $stmt = $this->con->prepare('insert into my_news(news_news_id,student_student_id,time_stamp) values(?,?,?)');
        $stmt->bind_param('sss', $news_id, $student_id, $time_stamp);
        $stmt->execute();
        $stmt->close();
    }

    //check if favourte news already exists
    public function newsExists($news_id, $student_id) {
        $stmt = $this->con->prepare('select * from my_news where news_news_id=? && student_student_id=?');
        $stmt->bind_param('ss', $news_id, $student_id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    //show my news
    public function fetch_myNews($student_id) {
        $stmt = $this->con->prepare('select * from news n left join news_categories c on(n.category_category_id=c.category_id) Left JOIN my_news m on(n.news_id=m.news_news_id) where m.student_student_id=? order by my_news_id desc');
        $stmt->bind_param('s', $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    //fetch news views
    public function newsViews($news_id) {
        $stmt = $this->con->prepare('select views_count from news where news_id=?');
        $stmt->bind_param('s', $news_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    //update the number of views
    function updateNews($news_id, $updateCount) {
        $stmt = $this->con->prepare('update news set views_count=? where news_id=?');
        $stmt->bind_param('ss', $updateCount, $news_id);
        $stmt->execute();
        $stmt->close();
    }

    function fetch_most_viewd_News($university_id) {
        $stmt = $this->con->prepare('select * from news n left join news_categories c on(n.category_category_id=c.category_id) where n.university_university_id=? && views_count>12 limit 12 ');
        $stmt->bind_param('s', $university_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    function fetch_events($university_id) {
        $stmt = $this->con->prepare('select event_id,title,event_detail,time_stamp '
                . 'from campus_events where university_university_id=?');
        $stmt->bind_param('s', $university_id);
        $stmt->execute();
        return $stmt;
    }

    function fetch_complait_categories($university_id) {
        $stmt = $this->con->prepare('select complaint_types_id,type from complaint_types where university_university_id=?');
        $stmt->bind_param('s', $university_id);
        $stmt->execute();
        return $stmt;
    }

    public function fetch_suggestions($university_id) {
        $stmt = $this->con->prepare('select suggestion_box_id,suggestion,sent_time from suggestion_box where university_university_id=? order by suggestion_box_id desc');
        $stmt->bind_param('s', $university_id);
        $stmt->execute();
        return $stmt;
    }

    public function insert_suggestion($suggestion, $university_id, $student_id) {
        $stmt = $this->con->prepare('insert into suggestion_box(suggestion,student_student_id,university_university_id)'
                . 'values(?,?,?)');
        $stmt->bind_param('sss', $suggestion, $student_id, $university_id);
        $stmt->execute();
        $stmt->close();
    }

    public function fetch_counsellors($university_id) {
        $stmt = $this->con->prepare('select u.firstName,u.lastName,TIMESTAMPDIFF(MINUTE, u.last_login, NOW()) as last_login,u.user_id from counsellors c left join users u on(c.user_id=u.user_id)'
                . ' where university_id=?');
        $stmt->bind_param('s', $university_id);
        $stmt->execute();
        return $stmt;
    }

}

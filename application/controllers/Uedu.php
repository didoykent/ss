<?php
require APPPATH . '/libraries/ImplementJwt.php';

require_once APPPATH . 'libraries/codeigniter-predis/src/Redis.php';

use \Firebase\JWT\JWT;

class Uedu extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->objOfJwt = new ImplementJwt();
            $this->redis = new \CI_Predis\Redis();

        header('Content-Type: application/json');

          $this->load->model('tutor_model', 'tutor');
          $this->load->model('regularclass_model', 'regularclass');
          $this->load->model('UeduMember_model', 'UeduMember');

          $this->load->library('session');

    }

    function connid_key() {
            $this->load->library('uuid');
            $id = $this->uuid->v4();
            return $id;
    }


    /////////// Generating Token and put user data into  token ///////////

    public function LoginToken()
    {
            $tokenData['uniqueId'] = '55555';
            $tokenData['role'] = 'admin';
            $tokenData['timeStamp'] = Date('Y-m-d h:i:s');
            $jwtToken = $this->objOfJwt->GenerateToken($tokenData);
            echo json_encode(array('Token'=>$jwtToken));
         }


         public function zoomToken(){
           $tokenData['iss'] =  'OYfYnfotR7CY75ve-OpVXw';
           $tokenData['exp'] = Date('Y-m-d h:i:s');

           $jwtToken = $this->objOfJwt->GenerateToken($tokenData);

           echo json_encode($jwtToken);

         }


         public function getAuthRole(){



   $received_Token = $this->input->request_headers('Authorization');



         echo json_encode($received_Token);



         }


         public function getToken(){


           $received_Token = $this->input->request_headers('Authorization');


           $token = str_replace("Bearer", "", $received_Token['Authorization']);

           $token = str_replace(" ", "", $token);




           try
               {
               $jwtData = $this->objOfJwt->DecodeToken($token);

               return $jwtData;
               }
               catch (Exception $e)
               {
               http_response_code('401');
               echo json_encode(array( "status" => false, "message" => $e->getMessage()));exit;
               }



         }





         public function signIn(){



           $config = array(


array(
'field' => 'id',
'label' => 'Id',
'rules' => 'trim|required'

),

array(
'field' => 'pw',
'label' => 'Password',
'rules' => 'trim|required'

)

           );



           $this->form_validation->set_rules($config);

           if($this->form_validation->run() === FALSE){

             $result['error'] = true;
             $result['msg'] = array(

             'id' => form_error('id'),
             'pw' => form_error('pw')

           );

           }

           else{

             $data = array(
               'id' => $this->input->post('id'),
               'pw' => $this->input->post('pw')


             );

            $result  = $this->tutor->signIn($data);
            $result2 = $this->regularclass->signIn($data);

            if($result === false && $result2 === false){



                           $result['error'] = true;
                           $result['msg'] = 'Credentials failed';
            }

            else{

              if($result2){

                $result = $result2;
              }

              $jwtToken = $this->objOfJwt->GenerateToken($result);

               $user = $this->objOfJwt->DecodeToken($jwtToken)[0];


                if($user && $jwtToken){



                           echo json_encode( array('token'=> $jwtToken, 'user' => $user));



                                     $this->redis->publish('notif', json_encode($user));



                           return;

              }
            }




           }

           echo json_encode($result);

     }

    //////// get data from token ////////////

    public function GetTokenData()
    {
    $received_Token = $this->input->request_headers('Authorization');
        try
            {
            $jwtData = $this->objOfJwt->DecodeToken($received_Token['token']);
            echo json_encode($jwtData);
            }
            catch (Exception $e)
            {
            http_response_code('401');
            echo json_encode(array( "status" => false, "message" => $e->getMessage()));exit;
            }
    }


    public function create_tutor_meeting(){



      $this->load->library('codeigniter-guzzle/guzzle');
      $client = new GuzzleHttp\Client(['base_uri' => 'https://api.zoom.us']);

      $key = "w0XvAh4gzAsaQWqPi0dURDloRqn55iAGP0Dk";
      $payload = array(
          "iss" => "OYfYnfotR7CY75ve-OpVXw",
          "exp" => time()+36000, // expire in 10 hours
      );

      $jwt = JWT::encode($payload, $key, 'HS256'); // use the secret to sign the payload with a specific hashing algorithim



         try {
             $response = $client->request('POST', '/v2/users/me/meetings', [
                 "headers" => [
                     "Authorization" => "Bearer $jwt"
                 ],
                 'json' => [
                     "topic" => "test",
                     "type" => 2,
                     "start_time" => "2020-06-16T20:30:00",
                     "duration" => "30", // 30 mins
                     "password" => "123456"
                 ],
             ]);

             $data = json_decode($response->getBody());
             echo "Join URL: ". $data->join_url;
             echo "<br>";
             echo "Meeting Password: ". $data->password;

         } catch(Exception $e) {

                 echo $e->getMessage();
             }







}


function generate_signature ( $api_key, $api_sercet, $meeting_number, $role){

	$time = time() * 1000; //time in milliseconds (or close enough)

	$data = base64_encode($api_key . $meeting_number . $time . $role);

	$hash = hash_hmac('sha256', $data, $api_sercet, true);

	$_sig = $api_key . "." . $meeting_number . "." . $time . "." . $role . "." . base64_encode($hash);

	//return signature, url safe base64 encoded
	return rtrim(strtr(base64_encode($_sig), '+/', '-_'), '=');
}
public function generate_student_signature(){




  $classId = $this->input->post('classData');
  $tutorConn = $this->input->post('tutorData');

  $meetingId = '';
  $meetingPw = '';
  $userName = '';


  if($classId && $tutorConn){

      $tutor = $this->tutor->get_tutor_by_conn($tutorConn) ? $this->tutor->get_tutor_by_conn($tutorConn)[0] : '';

      if($tutor){

        $schedules = json_decode($tutor->class_schedule);

      if($schedules){


        foreach ($schedules as $key => $value) {


        if($value->schedule->classid === $classId && $value->active === true){

            $meetingId  = property_exists($value->schedule, 'meetingId') ? $value->schedule->meetingId : '';
            $meetingPw  = property_exists($value->schedule, 'meetingPw') ? $value->schedule->meetingPw : '';
            $userName = $value->schedule->name;
        }
        }


        if($meetingId && $meetingPw) {



          $key = "OYfYnfotR7CY75ve-OpVXw";

          $payload = array(
              "meetingNumber" => (int)$meetingId,
              "apiKey" => "OYfYnfotR7CY75ve-OpVXw",
              'apiSecret' => "w0XvAh4gzAsaQWqPi0dURDloRqn55iAGP0Dk",
              'role' => (int)0
);
          $jwt = $this->generate_signature("OYfYnfotR7CY75ve-OpVXw", "w0XvAh4gzAsaQWqPi0dURDloRqn55iAGP0Dk", (int)$meetingId, (int)0);


          $roomData = array(

            "meetingNumber" => (int)$meetingId,
            "signature" => $jwt,
            'apiKey' =>  "OYfYnfotR7CY75ve-OpVXw",
            'password' => $meetingPw,
            'userName' => $userName
          );

  echo json_encode($roomData);
        }


}
      }
}


}



public function migrate_tutor_data(){



        $this->load->library('codeigniter-guzzle/guzzle');
        $client = new GuzzleHttp\Client(['base_uri' => 'http://phone.megatalking.com']);

        $key = 'super_hero_ueducation';
        $token = base64_encode($key);

        $tutorsArray =array();


           try {
               $response = $client->request('POST', '/origin/api/get_tutors_json.php', [

                   'form_params' => [
                       "token" => $token,

                   ],
               ]);

               $tutors_data = $response->getBody();
               $array = json_decode($tutors_data, true);

               $tutors = json_encode($array, JSON_UNESCAPED_UNICODE);
               $tutorsArray = json_decode($tutors, true);



               foreach ($tutorsArray as $key => $value) {

                  $tutor = $this->tutor->get_tutor_by_tutor_id($value['id']) ? $this->tutor->get_tutor_by_tutor_id($value['id'])[0] : '' ;

                  if(!$tutor){
                        $newArray = array(

                          'id' => $value['id'],
                          'pw' => $value['pw'],
                          'first_name' => $value['first_name'],
                          'mega_name' => $value['mega_name'],
                          'sex' => $value['sex'],
                          'date_of_birth' => $value['date_of_birth'],
                          'address' => $value['address'],
                          'major' => $value['major'],
                          'school' => $value['school'],
                          'workexp' => json_encode($value['workexp']),
                          'introduction' => $value['introduction'],
                          'mobile_number' => $value['mobile_number'],
                          'email' => $value['email'],
                          'affiliation' => $value['affiliation'],
                          'language' => $value['language'],
                          'country' => $value['country'],
                          'starting_date' => $value['starting_date'],
                          'am' => $value['am'],
                          'pm' => $value['pm'],
                          'start_am' => $value['start_am'],
                          'end_am' => $value['end_am'],
                          'start_pm' => $value['start_pm'],
                          'end_pm' => $value['end_pm'],
                          'academies' => json_encode($value['academies']),
                          'position' => 'Tutor',
                          'conn_id' => $this->connid_key()

                        );
                        $this->tutor->addTutor($newArray);
                       }

}

           } catch(Exception $e) {

                   echo $e->getMessage();
               }
}


public function migrate_student_data(){
  $this->load->library('codeigniter-guzzle/guzzle');
  $client = new GuzzleHttp\Client(['base_uri' => 'http://phone.megatalking.com']);

  $key = 'super_hero_ueducation';
  $token = base64_encode($key);

  $tutorsArray =array();


     try {
         $response = $client->request('POST', '/origin/api/get_members_json.php', [

             'form_params' => [
                 "token" => $token,

             ],
         ]);

         $students_data = $response->getBody();
         $array = json_decode($students_data, true);

         $students = json_encode($array, JSON_UNESCAPED_UNICODE);
         $studentsArray = json_decode($students, true);


         foreach ($studentsArray as $key => $value) {

            $student =  $this->regularclass->get_student_by_id($value['id']) ?   $this->regularclass->get_student_by_id($value['id'])[0] : '';
            if(!$student){
                  $newArray = array(

                    'id' => $value['id'],
                    'pw' => $value['pw'],
                    'kr_name' => $value['kr_name'],
                    'name' => $value['en_name'],
                    'skypeid' => $value['skypeid'],
                    'email' => $value['email'],
                    'phone_number' => $value['phone_number'],
                    'conn_id' => $this->connid_key()


                  );
                  $this->regularclass->addRegularClass($newArray);

}
                 }




     } catch(Exception $e) {

             echo $e->getMessage();
         }


}


public function update_conn_id(){

  $classes = $this->UeduMember->showAll();

  $testArray = array();



    $account =  $this->regularclass->get_student_by_id($classes[0]->member_id) ?   $this->regularclass->get_student_by_id($classes[0]->member_id)[0] : '';


echo json_encode($classes[0]);

}

public function migrate_student_classes(){




  $this->load->library('codeigniter-guzzle/guzzle');
  $client = new GuzzleHttp\Client(['base_uri' => 'http://phone.megatalking.com']);

  $key = 'super_hero_ueducation';
  $token = base64_encode($key);
  try {
      $response = $client->request('POST', '/origin/api/get_classes_json.php', [

        'form_params' => [
            "token" => $token,

        ],


      ]);

      $students_data = $response->getBody();
      $array = json_decode($students_data, true);

      $students = json_encode($array, JSON_UNESCAPED_UNICODE);
      $studentsArray = json_decode($students, true);



      foreach ($studentsArray as $key => $value) {
        $student = $this->UeduMember->getUedu_classes($value['member_id']) ? $this->UeduMember->getUedu_classes($value['member_id'])[0] : '';

        $member =  $this->regularclass->get_student_by_id($value['member_id']) ?   $this->regularclass->get_student_by_id($value['member_id'])[0] : '';

        $conn_id = '';
        if($member){

          $conn_id =  $member->conn_id;

        }
if($student){



  if($student->status !== $value['type'] || $student->conn_id !== $conn_id) {


$newArray = array(

  'status' => $value['type'],
  'conn_id' => $conn_id
);
    $this->UeduMember->update_Uedu_class_by_class_id($value['s_id'], $newArray);
  }
}

        else{




                         $applicableDays = array(

                          $value['d1'] ? 'M' : '',
                          $value['d2'] ? 'T' : '',
                          $value['d3'] ? 'W' : '',
                          $value['d4'] ? 'TH' : '',
                          $value['d5'] ? 'F' : '',
                         );

                         $class = '';

                      switch($value['device']){

                        case 'T':

                        $class  = 'Phone';

                        break;

                        case 'M':

                        $class  = 'Phone';

                        break;


                        case 'VZ':

                        $class  = 'Video Zoom';

                        break;


                        case 'VS':

                        $class  = 'Video Skype';

                        break;


                        case 'VSC':

                        $class  = 'Video Schoolnet';

                        break;







                      }
               $newArray = array(









                 'status' => $value['type'],
                 'class_type' => $class,
                 's_id' => $value['s_id'],
                 'member_id' => $value['member_id'],
                 'telephohne' =>  $value['telephohne'],
                 'mobile' => $value['mobile'],
                 'parent_telephone' => $value['parent_telephone'],
                 'book_name' => $value['book_name'],
                 'book_link' => $value['book_link'],
                 'start_class' => $value['start_day'],
                 'end_class' => $value['end_day'],
                 'hour' => $value['start_hour'],
                 'minute'   => $value['strat_min'],
                 'duration'  => $value['duration_time'],
                 'en_name' => $value['name_en'],
                 'kr_name' => $value['name_kr'],
                 'academy' => $value['academy_id'],
                 'applicable_days' => json_encode($applicableDays),
                 'days' => $value['weeks'],
                 'period' => $value['month'],
                 'class_id' => $value['s_id'],
                 'tutor_id'  => $value['tutor_id'],
                 'conn_id' => $conn_id

               );
               $this->UeduMember->addUedu_classes($newArray);

}
              }






  } catch(Exception $e) {

          echo $e->getMessage();
      }

}

public function student_add_class(){


$classes = $this->UeduMember->showAll();



echo json_encode($classes);

}




  public function update_tutor_class(){


    $classes = json_decode(file_get_contents("php://input"), true)? json_decode(json_decode(file_get_contents("php://input"), true)['Classes']) : '';









      if($classes){

        $classes = json_encode($classes, JSON_UNESCAPED_UNICODE);

        $classes = json_decode($classes, true);


      foreach ($classes as $key => $value) {






        $tutor = $this->tutor->get_tutor_by_tutor_id($value['tutor_id']) ?  $this->tutor->get_tutor_by_tutor_id($value['tutor_id'])[0] : '';

        if($tutor){




          $class_schedule =  $tutor->class_schedule ? json_decode($tutor->class_schedule) : '';

          if($class_schedule){



          foreach (  $class_schedule as $key2 => $schedule) {


            if($schedule->schedule->classid === $value['s_id']){


                $class_schedule[$key2]->cancelled_class = $value['cancelled_class'] ? explode(" ", $value['cancelled_class']): [];
                $class_schedule[$key2]->start_class = json_encode($value['start_class']);
                $class_schedule[$key2]->end_class = json_encode($value['end_class']);
                $class_schedule[$key2]->hour = (int)$value['hour'];
                $class_schedule[$key2]->duration = (int)$value['duration'];
                $class_schedule[$key2]->minute = (int)$value['minute'];

                $class_schedule[$key2]->conn_id =  $value['conn_id'] ?   $value['conn_id'] : '';



                $newArray  = array(

                  'class_schedule' =>   json_encode($class_schedule)
                );

                if($key2 === count($class_schedule)-1){
                $this->tutor->update_tutor_by_tutor_id($value['tutor_id'], $newArray);
                break;

              }

            }

          }



}

      }



  }



      }



  }






public function create_tutor_class(){


      $classes = json_decode(file_get_contents("php://input"), true)? json_decode(json_decode(file_get_contents("php://input"), true)['Classes']) : '';



    if($classes){


   $classes = json_encode($classes, JSON_UNESCAPED_UNICODE);

   $classes = json_decode($classes, true);



    foreach ($classes as $key => $value) {



      $data = array(


          'name' => $value['en_name'],
          'kr_name' => $value['kr_name'],
          'academy' => $value['academy'],
          'class_type' => $value['class_type'],
          'date' => date($value['date']),
          'days' => $value['days'],
          'applicable_days' => json_encode($value['applicable_days']),
          'hour' => (int)$value['hour'],
          'minute' => (int)$value['minute'],
          'duration' => (int)$value['duration'],
          'period' => (int)$value['period'],
          'class_start' => json_encode($value['start_schedule']),
          'class_end' => json_encode($value['end_schedule']),
          'phone_number' => $value['mobile'],
          'category' => 'regularclass',
          'classid' => $value['s_id'],
          'tutor_id' => $value['tutor_id'],
          'conn_id' => $value['conn_id']



    );


      $tutor = $this->tutor->get_tutor_by_tutor_id($value['tutor_id']) ?  $this->tutor->get_tutor_by_tutor_id($value['tutor_id'])[0] : '';

      if($tutor){

        $class_schedule =  $tutor->class_schedule ? json_decode($tutor->class_schedule) : '';

        if($class_schedule){


          $count = 0;
        foreach (  $class_schedule as $key2 => $schedule) {



              $equal = false;
          if($schedule->schedule->classid !== $value['s_id']){


            $equal = false;


          }

          else{

            $equal = true;
          }


          if($equal === true &&  $count === count($class_schedule)-1){


                $this->tutor->add_class_by_tutor_id($value['tutor_id'], $data);
                  $count++;
                break;
          }










    }



}
else{

          $this->tutor->add_class_by_tutor_id($value['tutor_id'], $data);

}



    }



}


}
}

public function get_and_update_makeup_class(){



  $this->load->library('codeigniter-guzzle/guzzle');
  $client = new GuzzleHttp\Client(['base_uri' => 'http://phone.megatalking.com']);

  $key = 'super_hero_ueducation';
  $token = base64_encode($key);
  try {
      $response = $client->request('POST', '/origin/api/get_classes_json.php', [

        'form_params' => [
            "token" => $token,

        ],


      ]);

      $students_data = $response->getBody();
      $array = json_decode($students_data, true);

      $students = json_encode($array, JSON_UNESCAPED_UNICODE);
      $studentsArray = json_decode($students, true);



      foreach ($studentsArray as $key => $value) {

        $member =  $this->UeduMember->getUedu_classes($value['member_id']) ? $this->UeduMember->getUedu_classes($value['member_id'])[0] : '';

        if($member){

            if($member->status !== $value['type']){

              $newData  = array(

                'status' => $value['type']
              );

              $this->UeduMember->updateUedu_classes($member->member_id, $newData);

            }


        }

        else{

          $member =  $this->regularclass->get_student_by_id($value['member_id']) ?   $this->regularclass->get_student_by_id($value['member_id'])[0] : '';
          $conn_id = '';
          if($member){

            $conn_id = $member->conn_id;
          }




                           $applicableDays = array(

                            $value['d1'] ? 'M' : '',
                            $value['d2'] ? 'T' : '',
                            $value['d3'] ? 'W' : '',
                            $value['d4'] ? 'TH' : '',
                            $value['d5'] ? 'F' : '',
                           );

                           $class = '';

                        switch($value['device']){

                          case 'T':

                          $class  = 'Phone';

                          break;

                          case 'M':

                          $class  = 'Phone';

                          break;


                          case 'VZ':

                          $class  = 'Video Zoom';

                          break;


                          case 'VS':

                          $class  = 'Video Skype';

                          break;


                          case 'VSC':

                          $class  = 'Video Schoolnet';

                          break;







                        }
                 $newArray = array(









                   'status' => $value['type'],
                   'class_type' => $class,
                   's_id' => $value['s_id'],
                   'member_id' => $value['member_id'],
                   'telephohne' =>  $value['telephohne'],
                   'mobile' => $value['mobile'],
                   'parent_telephone' => $value['parent_telephone'],
                   'book_name' => $value['book_name'],
                   'book_link' => $value['book_link'],
                   'start_class' => $value['start_day'],
                   'end_class' => $value['end_day'],
                   'hour' => $value['start_hour'],
                   'minute'   => $value['strat_min'],
                   'duration'  => $value['duration_time'],
                   'en_name' => $value['name_en'],
                   'kr_name' => $value['name_kr'],
                   'academy' => $value['academy_id'],
                   'applicable_days' => json_encode($applicableDays),
                   'days' => $value['weeks'],
                   'period' => $value['month'],
                   'class_id' => $value['s_id'],
                   'tutor_id'  => $value['tutor_id'],
                   'conn_id' => $conn_id

                 );
                 $this->UeduMember->addUedu_classes($newArray);


                }


        }









  } catch(Exception $e) {

          echo $e->getMessage();
      }

}


public function get_and_update_cancelled_class(){

  $this->load->library('codeigniter-guzzle/guzzle');
  $client = new GuzzleHttp\Client(['base_uri' => 'http://phone.megatalking.com']);

  $key = 'super_hero_ueducation';
  $token = base64_encode($key);
  try {
      $response = $client->request('POST', '/origin/api/get_cancel_classes_json.php', [

        'form_params' => [
            "token" => $token,

        ],


      ]);


      $students_data = $response->getBody();
      $array = json_decode($students_data, true);

      $students = json_encode($array, JSON_UNESCAPED_UNICODE);
      $studentsArray = json_decode($students, true);

        foreach ($studentsArray as $key => $value) {


          $schedule = $this->UeduMember->get_Uedu_class_by_class_id($key) ? $this->UeduMember->get_Uedu_class_by_class_id($key)[0] : '';

          if($schedule){


                    $member_schedule_array = array();

            foreach ($value as $new => $set) {

              $toDate = new DateTime("@$new", new DateTimeZone('Asia/Seoul'));

            $date =   date_format($toDate, "Y-m-d");

              $member_schedule_array[] =   $date;

            }





                $my_cancelled_class =  $schedule->cancelled_class ? $schedule->cancelled_class : '';

                if($my_cancelled_class){

                  $member_cancelled_class = implode(" ",   $member_schedule_array);

                  if($my_cancelled_class !==  $member_cancelled_class){


                    $my_cancelled_class = $member_cancelled_class;

                    $newArray = array(
                      'cancelled_class' => $my_cancelled_class
                    );

                    $this->UeduMember->update_Uedu_class_by_class_id($key, $newArray);

                  }
                }

                else{

                  $newArray = array(

                    'cancelled_class' => implode(" ",   $member_schedule_array)
                  );



                    $this->UeduMember->update_Uedu_class_by_class_id($key, $newArray);
                }



        }

        }










}catch(Exception $e) {

        echo $e->getMessage();
    }


}


public function update_tutor_cancelled_class($class_id, $data){





}

}

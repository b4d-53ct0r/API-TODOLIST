<?php

require_once './config/config.php';
require_once './functions/functions.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT , DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

$request_data = file_get_contents('php://input');
$request_object = json_decode($request_data);
$url = $_GET['url'];
if ( $url == "Register" && $_SERVER['REQUEST_METHOD'] == 'POST'){
  if (!isset($request_object->username) || !isset($request_object->password) || !isset($request_object->email)  ) {
    http_response_code(400);
    echo json_encode(array('message' => 'Missing Parameters'));
    exit();
  }
        $username = $request_object->username;
        $email = $request_object->email;
        $password = $request_object->password;
        if (register($username, $email, $password)) {
        http_response_code(201);
        echo json_encode(array('message' => 'Registration successful'));
        } else {
        http_response_code(400);
        echo json_encode(array('message' => 'Registration failed'));
        }
        exit();
        
    }
    elseif ( $url=="Login" && $_SERVER['REQUEST_METHOD'] == 'POST'){
      if (!isset($request_object->username) || !isset($request_object->password)  ) {
        http_response_code(400);
        echo json_encode(array('message' => 'Missing Parameters'));
        exit();
      }
      session_start();
        $username = $request_object->username;
        $password = $request_object->password;
        $token = login($username, $password);
        if (count($token) > 0) {
          http_response_code(200);
          echo json_encode($token);
        } else {
          http_response_code(401);
          echo json_encode(array('message' => 'Login failed'));
        }
        exit();
        
    }

    elseif ( $url=='AddTask' && $_SERVER['REQUEST_METHOD'] == 'POST'){
      if (!isset($request_object->Title) || !isset($request_object->Deadline) || !isset($request_object->Priority) ) {
        http_response_code(400);
        echo json_encode(array('message' => 'Missing Parameters'));
        exit();
      }
        $title = $request_object->Title;
        $deadline = $request_object->Deadline;
        $priority = $request_object->Priority;
        checkAuth();
        if(addTask($_SESSION['user_id'], $title,$deadline,$priority)){
          http_response_code(201);
          echo json_encode(array('message' => 'Task created'));
          exit();
        }else{
          http_response_code(500);
          echo json_encode(array('message' => 'Internal Server Error'));
          
        }
      
        exit();
    }
    elseif ( $url=='GetTask' && $_SERVER['REQUEST_METHOD'] == 'POST'){

      if (!isset($request_object->TaskID) ) {
        http_response_code(400);
        echo json_encode(array('message' => 'Missing Parameters'));
        exit();
      }
      $task_id = $request_object->TaskID;
      checkAuth();
        $tasks = getTaskUser($_SESSION['user_id'],$task_id);
        http_response_code(200);
        echo json_encode($tasks);
        exit();
        http_response_code(502);
        echo json_encode(array('message' => 'Internal Server Error'));
        exit();
      
    }
    elseif ( $url=='GetTasks' && $_SERVER['REQUEST_METHOD'] == 'GET'){
      checkAuth();
      $tasks = getTasksForUser($_SESSION['user_id']);
      http_response_code(200);
      echo json_encode($tasks);
      exit();
      http_response_code(502);
      echo json_encode(array('message' => 'Internal Server Error'));
      exit();

  }
    elseif ( $url=='UpdateTaskStatus' && $_SERVER['REQUEST_METHOD'] == 'PUT'){

    if (!isset($request_object->TaskID) || !isset($request_object->Status)) {
      http_response_code(400);
      echo json_encode(array('message' => 'Missing Parameters'));
      exit();
    }
      checkAuth();
      $task_id = $request_object->TaskID;
      $completed = $request_object->Status;
        updateTaskStatus($task_id, $completed, $_SESSION['user_id']);
        exit();

    }
    elseif ( $url=='UpdateTaskPriority' && $_SERVER['REQUEST_METHOD'] == 'PUT'){
      if (!isset($request_object->TaskID) || !isset($request_object->Priority)) {
          http_response_code(400);
          echo json_encode(array('message' => 'Missing Parameters'));
          exit();
        }
      checkAuth();
        $task_id = $request_object->TaskID;
        $Priority = $request_object->Priority;
        updateTaskPriority($task_id, $Priority, $_SESSION['user_id']);

 
        exit();
    }
    elseif ( $url=='UpdateTaskTitle' && $_SERVER['REQUEST_METHOD'] == 'PUT'){
      if (!isset($request_object->TaskID) || !isset($request_object->Title)) {
        http_response_code(400);
        echo json_encode(array('message' => 'Missing Parameters'));
        exit();
      }
        $task_id = $request_object->TaskID;
        $Title = $request_object->Title;

      checkAuth();
        updateTaskTitle($task_id, $Title, $_SESSION['user_id']);
        exit();
    }
    elseif ( $url=='UpdateTaskDeadline' && $_SERVER['REQUEST_METHOD'] == 'PUT'){

      if (!isset($request_object->TaskID) || !isset($request_object->Deadline)) {
        http_response_code(400);
        echo json_encode(array('message' => 'Missing Parameters'));
        exit();
      }
      checkAuth();
        $task_id = $request_object->TaskID;
        $Deadline = $request_object->Deadline;
        updateTaskDeadline($task_id, $Deadline, $_SESSION['user_id']);

        exit();
    }
    elseif ( $url=='DeleteTask' && $_SERVER['REQUEST_METHOD'] == 'DELETE'){
      if (!isset($request_object->TaskID)  ) {
        http_response_code(400);
        echo json_encode(array('message' => 'Missing Parameters'));
        exit();
      }
      $task_id = $request_object->TaskID;  
      checkAuth();
        $task_id = $request_object->TaskID;
        deleteTask($task_id, $_SESSION['user_id']);

        exit();
    }

    elseif ( $url=='UpdateTask' && $_SERVER['REQUEST_METHOD'] == 'PUT'){
      if (!isset($request_object->TaskID) || !isset($request_object->Title) || !isset($request_object->Deadline) || !isset($request_object->Priority) || !isset($request_object->Status)  ) {
        http_response_code(400);
        echo json_encode(array('message' => 'Missing Parameters'));
        exit();
      }
      checkAuth();
      
        $task_id = $request_object->TaskID;
        $title = $request_object->Title;
        $deadline = $request_object->Deadline;
        $priority = $request_object->Priority;
        $Status = $request_object->Status;
      

        updateTask($task_id, $title, $_SESSION['user_id'],$deadline,$priority,$Status);

        exit();
        
    }
    elseif ( $url=='Logout' && $_SERVER['REQUEST_METHOD'] == 'GET'){
      checkAuth();
        logout();
        http_response_code(200);
        echo json_encode(array('message' => 'Logout successful'));
        exit();
        
    }
    else{
        http_response_code(404);
        echo json_encode(array('message' => 'Endpoint not found'));
    }


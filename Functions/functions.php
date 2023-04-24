<?php
function Login(string $username, string $password): array
{
    global $mysqli;
    $query = "SELECT id, password FROM users WHERE name = ? OR email = ?";
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param('ss', $username, $username);
        $stmt->execute();
        $stmt->bind_result($id, $ppassword);
        $password = md5($password);
        if ($stmt->fetch() && ($password == $ppassword)) {
            $_SESSION['user_id'] = $id;
            return array("Token" => session_id());
        }
    }
    return array("message" => "Wrong password or Username");
}

function Logout(): void
{
    session_unset();
    session_destroy();
}

function CheckAuth(): void
{
    $headers = getallheaders();
    if (isset($headers['authorization'])) {
        $authHeader = $headers['authorization'];
        $token = str_replace('Bearer ', '', $authHeader);
        session_id($token);
        session_start();
        header_remove('Set-Cookie');
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(array('message' => 'Invalid Token'));
            exit();
        }
    } else {
        http_response_code(401);
        echo json_encode(array('message' => 'Unauthorized access'));
        exit();
    }
}

function GetTasksForUser(int $user_id): array
{
    global $mysqli;
    $query = "SELECT id, title, completed,deadline, priority,created_at,updated_at FROM todos WHERE user_id = ?";
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->bind_result($id, $title, $completed, $deadline, $priority, $created_at, $updated_at);
        $tasks = array();
        while ($stmt->fetch()) {

            $tasks[] = array('id' => $id, 'title' => $title, 'completed' => $completed, 'deadline' => $deadline, 'priority' => $priority, 'created_at' => $created_at, 'updated_at' => $updated_at);
        }
        return $tasks;
    }
    return array();
}
function GetTaskUser(int $user_id, int $TaskID): array
{
    global $mysqli;
    $query = "SELECT id, title, completed,deadline, priority ,created_at,updated_at FROM todos WHERE user_id = ? And id = ?";
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param('ii', $user_id, $TaskID);
        $stmt->execute();
        $stmt->bind_result($id, $title, $completed, $deadline, $priority, $created_at, $updated_at);
        $tasks = array();
        while ($stmt->fetch()) {
            $tasks[] = array('id' => $id, 'title' => $title, 'completed' => $completed, 'deadline' => $deadline, 'priority' => $priority, 'created_at' => $created_at, 'updated_at' => $updated_at);
        }
        if (count($tasks) == 0) {
            return array('message' => 'Unauthorized access Or Id not found');
        } else {
            return $tasks;
        }
    }
}

function AddTask(int $user_id, string $title, string $deadline, int $priority): bool
{
    global $mysqli;

    $query = "INSERT INTO todos (user_id, title, deadline, priority) VALUES (?, ?, ?, ?)";
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param('issi', $user_id, $title, $deadline, $priority);
        $stmt->execute();
        $stmt->close();
        return true;
    } else {
        return false;
    }
}
function UpdateTaskStatus(int $task_id, int $completed, int $user_id): void
{
    global $mysqli;
    $query = "UPDATE todos SET completed = ? WHERE id = ? AND user_id = ?";
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param('iii', $completed, $task_id, $user_id);
        $stmt->execute();
        http_response_code(200);
        echo json_encode(array('message' => 'Task Updated Successfully'));
    } else {
        http_response_code(301);
        echo json_encode(array('message' => 'Internal Server Error'));
    }
}
function UpdateTaskPriority(int $task_id, bool $priority, int $user_id): void
{
    global $mysqli;
    $query = "UPDATE todos SET priority = ? WHERE id = ? AND user_id = ?";
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param('iii', $priority, $task_id, $user_id);
        $stmt->execute();
        http_response_code(200);
        echo json_encode(array('message' => 'Task Updated Successfully'));
    } else {
        http_response_code(301);
        echo json_encode(array('message' => 'Internal Server Error'));
    }
}
function UpdateTaskTitle(int $task_id, string $title, int $user_id): void
{
    global $mysqli;
    $query = "UPDATE todos SET title = ? WHERE id = ? AND user_id = ?";
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param('sii', $title, $task_id, $user_id);
        $stmt->execute();
        http_response_code(200);
        echo json_encode(array('message' => 'Task Updated Successfully'));
    } else {
        http_response_code(301);
        echo json_encode(array('message' => 'Internal Server Error'));
    }
}
function UpdateTaskDeadline(int $task_id, string $deadline, int $user_id): void
{
    global $mysqli;
    $query = "UPDATE todos SET deadline = ? WHERE id = ? AND user_id = ?";
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param('sii', $deadline, $task_id, $user_id);
        $stmt->execute();
        http_response_code(200);
        echo json_encode(array('message' => 'Task Updated Successfully'));
    } else {
        http_response_code(301);
        echo json_encode(array('message' => 'Internal Server Error'));
    }
}

function UpdateTask(int $task_id, string $title, int $user_id, string $deadline, int $priority, int $completed): void
{
    global $mysqli;

    $query = "UPDATE todos SET title = ?, deadline = ? , priority = ?,completed = ? WHERE id = ? AND user_id = ?";
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param('ssisii', $title, $deadline, $priority, $completed, $task_id, $user_id);
        $stmt->execute();
        if ($stmt->affected_rows == 1) {
            http_response_code(200);
            echo json_encode(array('message' => 'Task Updated Successfully'));
        }
    } else {
        http_response_code(301);
        echo json_encode(array('message' => 'Internal Server Error'));
    }
}

function deleteTask(int $task_id, int $user_id): void
{
    global $mysqli;
    $query = "DELETE FROM todos WHERE id = ? AND user_id = ?";
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param('ii', $task_id, $user_id);
        $stmt->execute();
        if ($stmt->affected_rows == 1) {
            http_response_code(204);
            echo json_encode(array('message' => 'Task deleted'));    }
    } else {
        http_response_code(301);
        echo json_encode(array('message' => 'Internal Server Error'));
    }
}

function register(string $username, string $email, string $password): bool
{
    global $mysqli;
    $hashed_password = md5($password);
    $query = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param('sss', $username, $email, $hashed_password);
        if ($stmt->execute()) {
            return true;
        }
    }
    return false;
}

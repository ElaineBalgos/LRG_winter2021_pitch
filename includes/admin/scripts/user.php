<?php

function generateRandomString($n) {
    $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $randomString = "";
  
    for ($i = 0; $i < $n; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }
  
    return $randomString;
}

function createUserByAdmin($user_data) {

    // auto generate password
    $password = generateRandomString(8);

    if (isUsernameExists($user_data["username"])) {
        return "Create User Failed: Username is invalid. Do not repeat.";
    }

    // Run the proper SQL query to insert user
    $pdo = Database::getInstance()->getConnection();
    $create_user_query = "INSERT INTO tbl_user(user_name, user_pass, user_fname, user_lname, user_email)";
    $create_user_query .= " VALUES(:username, :password, :fname, :lname, :email)";

    $create_user_set = $pdo->prepare($create_user_query);
    $create_user_result = $create_user_set->execute(
        array (
            ":username" => $user_data["username"],
            ":password" => $password,
            ":fname" => $user_data["fname"],
            ":lname" => $user_data["lname"],
            ":email" => $user_data["email"]
        )
    );


    // If create user successfully, send user a email with the auto generated password
    // Otherwise, showing the error message

    if ($create_user_result) {

        // Sending a confirmation email to new user
        $email_to = $user_data["email"];
        $email_from = "lrg@londonrefereesgroup.com";
        $email_subject = "Register Confirmation Email";
        $email_message = sprintf("<html> <h2>Registration Confirmation</h2><p>Your username: %s </p><p>Password: %s</p><p>Registered Email: %s</p>",  $user_data["username"], $password, $user_data["email"]);
        $email_message .= "</html>";

        $email_headers = array(
            "Content-type" => "text/html; charset=iso-8859-1",
            "From" => $email_from,
            "Reply-To" => $email_to

        );
            
        $email_result = mail($email_to, $email_subject, $email_message, $email_headers);

        if (!($email_result)) { 
            return "Password COULD NOT be sent to REGISTERED email.";
        } else {
            return "Password is SUCCESSFULLY sent to the REGISTERED email. New user can edit after login.";
        }
    } else {
        return "The user did not go through!!! Try fill the form again";
    }
}

function createUserByVisitor($user_data) {

    // auto generate username
    $username = generateRandomString(6);
    // auto generate password
    $password = generateRandomString(8);

    while (isUsernameExists($user_name)) {
        $username = generateRandomString(7);
    }

    // Run the proper SQL query to insert user
    $pdo = Database::getInstance()->getConnection();
    $create_user_query = "INSERT INTO tbl_user(user_name, user_pass, user_fname, user_lname, user_email)";
    $create_user_query .= " VALUES(:username, :password, :fname, :lname, :email)";

    $create_user_set = $pdo->prepare($create_user_query);
    $create_user_result = $create_user_set->execute(
        array (
            ":username" => $username,
            ":password" => $password,
            ":fname" => $user_data["fname"],
            ":lname" => $user_data["lname"],
            ":email" => $user_data["email"]
        )
    );


    // If create user successfully, send user a email with the auto generated username and password
    // Otherwise, showing the error message

    if ($create_user_result) {

        // Sending a confirmation email to new user
        $email_to = $user_data["email"];
        $email_from = "lrg@londonrefereesgroup.com";
        $email_subject = "Register Confirmation Email";
        $email_message = sprintf("<html> <h2>Registration Confirmation</h2><p>Your username: %s </p><p>Password: %s</p><p>Registered Email: %s</p>", $username, $password, $user_data["email"]);
        $email_message .= "</html>";

        $email_headers = array(
            "Content-type" => "text/html; charset=iso-8859-1",
            "From" => $email_from,
            "Reply-To" => $email_to

        );
            
        $email_result = mail($email_to, $email_subject, $email_message, $email_headers);

        if (!($email_result)) { 
            return "Username and password COULD NOT be sent to your email.";
        } else {
            return "Username and password are SUCCESSFULLY sent to your email. You can edit them after login.";
        }
    } else {
        return "The user did not go through!!! Try fill the form again";
    }
}

function getSingleUser($user_id) {

    $pdo = Database::getInstance()->getConnection();

    $get_user_query = "SELECT * FROM tbl_user WHERE user_id = :id";
    $get_user_set = $pdo->prepare($get_user_query);
    $result = $get_user_set->execute(
        array(
            ":id" => $user_id
        )
    );

    if($result && $get_user_set->rowCount()) {
        return $get_user_set;
    } else {
        return false;
    }
}

function getUserById($user_id) {

    $pdo = Database::getInstance()->getConnection();

    $get_user_query = "SELECT * FROM tbl_user WHERE user_id = :id";
    $get_user_set = $pdo->prepare($get_user_query);
    $result = $get_user_set->execute(
        array(
            ":id" => $user_id
        )
    );

    if ($result && $get_user_set->rowCount()){
        while ($row = $get_user_set->fetch(PDO::FETCH_ASSOC)) {
            $user = array();
            $user["user_id"] = $row["user_id"];
            $user["user_name"] = $row["user_name"];
            $user["user_fname"] = $row["user_fname"];
            $user["user_lname"] = $row["user_lname"];
            $user["user_email"] = $row["user_email"];
            $user["user_gender"] = $row["user_gender"];
            return $user;
        }
    } else {
        return "user does not exist";
    }
    return "Sth wrong when grab your information. Refresh...";

}

function editUserByMember($user_data) {
    if(empty($user_data["username"]) || isUsernameExistsExceptSelf($user_data["username"], $user_data["id"])) {
        return "Username is invalid!";
    }

    $pdo = Database::getInstance()->getConnection();

    $update_user_query = "";
    $input = array(
        ":username" => $user_data["username"],
        ":email" => $user_data["email"],
        ":gender" => $user_data["gender"],
        ":id" => $user_data["id"]
    );
    if (array_key_exists("password", $user_data)) {
        $update_user_query = "UPDATE tbl_user SET user_name=:username, user_pass=:password, user_email = :email, user_gender = :gender WHERE user_id = :id";
        $input[":password"] = $user_data["password"];
    } else {
        $update_user_query = "UPDATE tbl_user SET user_name=:username, user_email = :email, user_gender = :gender WHERE user_id = :id";
    }
 
    $update_user_set = $pdo->prepare($update_user_query);
    $update_user_result = $update_user_set->execute(
        $input
    );

    if($update_user_result) {
        $user = array(
            "user_id" => $user_data["id"],
            "user_name" => $user_data["username"]
        );

        return $user;

    }else {
        return "Failed to edit user in database. Try again.";
    }
}

function editUserByAdmin($user_data) {

    if(empty($user_data["username"]) || isUsernameExistsExceptSelf($user_data["username"], $user_data["id"])) {
        return "Username is invalid!";
    }
    
    $pdo = Database::getInstance()->getConnection();

    $update_user_query = "UPDATE tbl_user SET user_name=:username, user_fname=:fname, user_lname=:lname, user_email = :email, user_level = :ulevel WHERE user_id = :id";
 
    $update_user_set = $pdo->prepare($update_user_query);
    $update_user_result = $update_user_set->execute(
        array(
            ":username" => $user_data["username"],
            ":fname" => $user_data["fname"],
            ":lname" => $user_data["lname"],
            ":email" => $user_data["email"],
            ":ulevel" => $user_data["level"],
            ":id" => $user_data["id"]
        )
    );


    if($update_user_result) {
        $user = array(
            "user_id" => $user_data["id"],
            "user_name" => $user_data["username"],
        );

        return $user;

    }else {
        return "Failed to edit user in database. Try again.";
    }
}

// called when create user
function isUsernameExists($username) {
    $pdo = DATABASE::getInstance()->getConnection();

    $user_exists_query = 'SELECT COUNT(*) FROM tbl_user WHERE user_name = :username';
    $user_exists_set = $pdo->prepare($user_exists_query);
    $user_exists_result = $user_exists_set->execute(
        array(
            ':username'=>$username
        )
    );
    return !$user_exists_result || $user_exists_set->fetchColumn() > 0;
}

// called when edit user
function isUsernameExistsExceptSelf($username, $self_userid) {
    $pdo = Database::getInstance()->getConnection();

    $user_exists_query = "SELECT COUNT(*) FROM tbl_user WHERE user_name = :username AND user_id != :selfid";
    $user_exists_set = $pdo->prepare($user_exists_query);
    $user_exists_result = $user_exists_set->execute(
        array (
            ":username"=>$username,
            ":selfid"=>$self_userid
        )
    );

    return !$user_exists_result || $user_exists_set->fetchColumn() > 0;
}

function getAllUsers(){

    $pdo = Database::getInstance()->getConnection();

    $queryAll = "SELECT * FROM tbl_user";
    $runAll = $pdo->query($queryAll);
    $users = $runAll->fetchAll(PDO::FETCH_ASSOC);

    if ($users){
        return $users;
    } else {
        return "Sth wrong when grab users information. Refresh...";
    }
}

function deleteUserById($user_id) {
    $pdo = Database::getInstance()->getConnection();

    $delete_user_query = "DELETE FROM tbl_user WHERE user_id = :id";
    $delete_user_set = $pdo->prepare($delete_user_query);
    $result = $delete_user_set->execute(
        array(
            ":id" => $user_id
        )
    );

    if ($result) {
        $data = array(
            "user_id" => $user_id
        );
        return $data;
    } else {
        return "Sth wrong when delete user. Refresh...";
    }
}

<?php

/*
    This is a stand alone autherizeation and login exstention
    simpy require this file at the start of the program and make sure 
    required mysql tables are suplied also it requires a class called database
    with a function called query  

    Author Owen Rempel Mar 4 2020
*/


//var decliration

$cstrong= True;

$token = bin2hex(openssl_random_pseudo_bytes(56, $cstrong));


// HTML for login

function login(){

    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Funds Admin</title>
        <style>

            .error{
                width: 20vw;
                /* margin: auto; */
                text-align: center;
                background-color: #F44336;
                padding: 0px 40px;
                position: absolute;
                top: 10px;
                color: white;
                font-family: sans-serif;
                font-weight: 100;
            }

            .login {
                width: 300px;
                margin: auto;
                text-align: center;
                margin-top: 17vh;
                background-color: #eef3fb00;
                padding: 80px 20px;
                box-shadow: 50px 0px 30px -56px black, -54px 0px 30px -60px black;
            }

            .logologin {
                width: 40%;
                margin: auto;
                opacity: .5;
            }

            @media screen and (max-width: 1000px) {
                .login {
                    width: calc(90% - 20px);
                    height: 548px;
                    padding: 20px;
                    margin: 10px auto;
                }
                .error{
                    width: 80vw;
                    margin: auto;
                    text-align: center;
                    background-color: #F44336;
                    padding: 1px 40px;
                    position: relative;
                    color: white;
                    font-family: sans-serif;
                    font-weight: 100;
            }
            }

            .input-field {
                position: relative;
                margin-top: 1rem;
                margin-bottom: 1rem;
            }

            .input-field input {
                border: none;
                border-bottom: 1px solid #9e9e9e;
                border-radius: 0;
                outline: none;
                height: 3rem;
                width: 100%;
                font-size: 16px;
                margin: 0 0 8px 0;
                padding: 0;
            }

            .input-field>label {
                color: #9e9e9e;
                position: absolute;
                top: -7px;
                left: 0;
                font-size: 14px;
                font-family: sans-serif;
                cursor: text;
            }

            .btn {
                text-decoration: none;
                color: #fff;
                background-color: #26a69a;
                text-align: center;
                letter-spacing: .5px;
                -webkit-transition: background-color .2s ease-out;
                transition: background-color .2s ease-out;
                cursor: pointer;
                border: none;
                border-radius: 2px;
                display: inline-block;
                height: 36px;
                line-height: 36px;
                padding: 0 16px;
                text-transform: uppercase;
                vertical-align: middle;
                -webkit-tap-highlight-color: transparent;
            }

            .blue {
                background-color: #2196F3 !important;
            }

        </style>
    </head>
    <body>
        <div class="login">
            <img class="logologin" src="../img/logo-textoffwhite.png">
            <form action="" method="post" class="log">
                <div class="input-field">
                <input type="text" name="user">
                    <label for="user" class="active">Username</label>
                </div>
                <div class="input-field">
                <input type="password" name="pass">
                    <label for="pass" class="active">Password</label>
                </div>
                <input type="submit" value="Login" name="auth_pass_send" class="btn blue">
            </form>
        </div>
    </body>
    </html>

    <?php

}

// loop through and delete any entrys that have expired

$get_entrys = Database::query('SELECT ID, expire from auth');

foreach($get_entrys as $row){
    if($row['expire'] < time()){
        Database::query('DELETE from auth where ID = :id', array('id'=>$row['ID']));
    }
}

// SQL for app

/*
CREATE TABLE `funds`.`auth` 
 ( `token` TEXT NOT NULL , 
    `expire` TEXT NOT NULL , 
    `user` TEXT NULL , 
    `adate` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , 
    `ID` INT NOT NULL AUTO_INCREMENT , PRIMARY KEY (`ID`)) ENGINE = InnoDB;

CREATE TABLE `funds`.`users` 
  ( `user` TEXT NOT NULL , 
    `pass` TEXT NOT NULL , 
    `adate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , 
    `ID` INT NOT NULL AUTO_INCREMENT , PRIMARY KEY (`ID`)) ENGINE = InnoDB;
*/

// Check for logout parm

if(isset($_GET['auth_logout'])){
    if(isset($_COOKIE['CID'])){
        Database::query('DELETE FROM auth WHERE token=:token', array(':token'=>$_COOKIE['CID']));
    }
    setcookie('CID', 1, time() - 36000, '/', NULL, NULL, TRUE);
    header('location:./');
}


//check if cookies exist

if(isset($_COOKIE['CID']) and !isset($_POST['auth_pass_send'])){

    $data = Database::query("SELECT ID from auth WHERE token=:token", array('token'=>$_COOKIE['CID']));
    if(!$data){
        echo login();
        exit();
    }

}else{

    // post check

    if(isset($_POST['auth_pass_send']) and isset($_POST['user']) and isset($_POST['pass'])){
        $data = Database::query("SELECT user, pass, ID FROM users WHERE user = :user LIMIT 1", array('user'=>$_POST['user']));
        if(isset($data[0])){
            $pass = $_POST['pass'];
            $ver = $data[0]['pass'];
            if(password_verify($pass, $ver)){
                Database::query('INSERT INTO auth (token, expire, user) values ( "'.$token.'", '.(time() + 60 * 60 * 24 * 2 ).', :user )', array('user'=>$_POST['user']));
                setcookie('CID', $token, time() + 60 * 60 * 24 * 2, '/', NULL, NULL, TRUE);
                sleep(1);
                header('location:./');
            }else{
                echo '<div class="error"><h4>Incorect Password</h4></div>';
            }
        }else{
            echo '<div class="error"><h4>Incorect Username</h4></div>';
        }
    }

    // login form 
    
    echo login();

    // Exit to stop any other output

    exit();
}

?>



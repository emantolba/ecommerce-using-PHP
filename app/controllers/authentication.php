<?php
include_once __DIR__. "/../requests/authenticationRequest.php";
include_once "controller.php";
include_once __DIR__. "/../models/User.php";
include_once __DIR__."/../mails/verification.php";
class authentication extends controller {

    public function register_post($request)
    {
        $validation = new authenticationRequest;
        $validation->setEmail($request['email']);
        $emailValidationResult = $validation->emailValidation();
        if(!empty($emailValidationResult)){
            // save data in sesssion
            $_SESSION['message']['errors'] = $emailValidationResult;
            $_SESSION['request'] = $request;
            // header('location:../views/register.php');die;
        }

        $validation->setPassword($request['password']);
        $validation->setConfirmPassword($request['confirm-password']);
        $passwordValidationResult  = $validation->passwordValidation();
        if(!empty($passwordValidationResult)){
            $_SESSION['message']['errors'] = $passwordValidationResult;
            $_SESSION['request'] = $request;
            // header('location:../views/register.php');die;
        }

        if(!empty($passwordValidationResult) || !empty($emailValidationResult)){
            header('location:../views/register.php');die;
        }


       // insert data
        $user = new User;
        $user->setName($request['name']);
        $user->setPassword($request['password']);
        $user->setEmail($request['email']);
        $user->setPhone($request['phone']);
        $user->setGender($request['gender']);
        // generate code
        $code = rand(10000,99999);
        $user->setCode( $code );
        // check if email exists in db
        $emailCheckResult = $user->emailCheckDB();
        if(empty($emailCheckResult)){
            $result = $user->insertData();
            if($result){
                //send mail
                $mail = new verification;
                $subject = "Verification Code";
                $body = "<div> Your Verificaiton Code Is:<b>$code</b> <div>";
                $mailResult = $mail->sendMail($request['email'],$subject,$body);
                if($mailResult){
                    header('location:web.php?email='.$request['email']);die;
                }else{
                    header('location:../views/errors/500.php');die;
                }
            }else{
                // return error
            $_SESSION['message']['errors'] = ['something'=>"<div class='alert alert-danger'> SomeThing Went Wrong </div>"];
            $_SESSION['request'] = $request;
            header('location:../views/register.php');die;
            }
        }else{
            // return error
            $_SESSION['message']['errors'] = ['email-exists'=>"<div class='alert alert-danger'> Email Already Exists </div>"];
            $_SESSION['request'] = $request;
            header('location:../views/register.php');die;
        }

    }

    public function verifyCode_post($request)
    {
        // echo __DIR__;die;
        $emailValidation = new authenticationRequest;
        $emailValidationResult = $emailValidation->urlEmailValidation($request);
        if($emailValidationResult){
            if($emailValidationResult->code == $request['code']){
                // header index with user data
                $user = new user;
                $user->setStatus(1);
                $user->setEmail($request['email']);
                $updateStatusResult = $user->updateStatus();
                if($updateStatusResult){
                    $emailValidationResult->status = 1;
                    $_SESSION['user'] = $emailValidationResult;
                    header('location:../views/index.php');
                }else{
                    $_SESSION['message']['errors'] = ['something'=>"<div class='alert alert-danger'> SomeThing Went wrong </div>"];
                    $_SESSION['request'] = $request;
                    header("location:../views/verify-code.php?email={$request['email']}");die;
                }
                

            }else{
                // header verify code with Error
                $_SESSION['message']['errors'] = ['wrong-code'=>"<div class='alert alert-danger'> Wrong Code </div>"];
                $_SESSION['request'] = $request;
                header("location:../views/verify-code.php?email={$request['email']}");die;
            }
        }else{
            header("location:../../views/errors/404.php");die;
        }
        // validate on code
        // check on db
        // change status if code is true & header index
        // else return error
    }

    public function verifyCode_get($request)
    {
        $emailValidation = new authenticationRequest;
        $emailValidationResult = $emailValidation->urlEmailValidation($request);
        if($emailValidationResult){
            header("location:../views/verify-code.php?email={$request['email']}");die;
        }else{
            header("location:../../views/errors/404.php");die;
        }
       
    }

    public function logout()
    {
        session_unset();
        session_destroy();
        header('location:../views/login.php');
    }
    public function login_post($request)
    {
        # code...
    }
    public function login_get()
    {
        header('location:../views/login.php');die;
    }
    public function register_get()
    {
        header('location:../views/register.php');die;
    }

    public function profile_get()
    {
        header('location:../views/profile.php');die;
    }
}

?>
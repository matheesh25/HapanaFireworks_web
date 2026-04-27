<?php
session_start();
include("config.php");

if($_SERVER["REQUEST_METHOD"]=="POST"){

    $email = mysqli_real_escape_string($conn,$_POST['email']);
    $password = $_POST['password'];

    // ADMIN
    if($email=="admin@hapana.com" && $password=="admin123"){
        $_SESSION["role"]="admin";
        $_SESSION["user_name"]="Admin";
        header("Location: admin/dashboard.php");
        exit();
    }

    // USER
    $sql = "SELECT * FROM users WHERE email='$email' AND status='active'";
    $result = mysqli_query($conn,$sql);

    if(mysqli_num_rows($result)==1){

        $row = mysqli_fetch_assoc($result);

        if(password_verify($password, $row['password'])){

            $_SESSION["user_id"] = $row["id"];
            $_SESSION["user_name"] = $row["name"];
            $_SESSION["role"] = $row["role"];

            header("Location: profile.php");
            exit();

        } else {
            echo "<script>alert('Wrong Password');window.location='login.html';</script>";
            exit();
        }

    } else {
        echo "<script>alert('User Not Found');window.location='login.html';</script>";
        exit();
    }

}else{
    header("Location: login.html");
    exit();
}
?>
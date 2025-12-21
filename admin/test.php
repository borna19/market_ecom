<?php
$error="";
$username="";
$phonenumber="";

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $phonenumber = $_POST['phonenumber'];

    if(empty($username)){
        $error ="user name cannot be empty";
    }elseif(strlen($phonenumber)!= 10){
        $error="phone number must be 10 digits";
    }else{
        echo "username: ".$username."<br>";
        echo "phone number: ".$phonenumber;
        exit();
    }
}

?> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h2>user info</h2>
    <form method="POST" action="">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>"><br><br>

        <label for="phonenumber">Phone Number:</label>
        <input type="text" id="phonenumber" name="phonenumber" value="<?php echo htmlspecialchars($phonenumber); ?>"><br><br>

        <input type="submit" name="submit" value="Submit">
    </form>
</body>
</html>

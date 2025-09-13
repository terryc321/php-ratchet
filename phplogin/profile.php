<?php
// We need to use sessions, so you should always initialize sessions using the below function
session_start();
// If the user is not logged in, redirect to the login page
if (!isset($_SESSION['account_loggedin'])) {
    header('Location: index.php');
    exit;
}
// Change the below variables to reflect your MySQL database details
$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'phplogin';
// Try and connect using the info above
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
// Ensure there are no connection errors
if (mysqli_connect_errno()) {
    exit('Failed to connect to MySQL!');
}
// We don't have the email or registered info stored in sessions so instead we can get the results from the database
$stmt = $con->prepare('SELECT email, registered FROM accounts WHERE id = ?');
// In this case, we can use the account ID to get the account info
$stmt->bind_param('i', $_SESSION['account_id']);
$stmt->execute();
$stmt->bind_result($email, $registered);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,minimum-scale=1">
        <title>Home</title>
        <link href="style.css" rel="stylesheet" type="text/css">
    </head>
    <body>

        <header class="header">

            <div class="wrapper">

                <h1>Website Title</h1>
                
                <nav class="menu">
                    <a href="home.php">Home</a>
                    <a href="profile.php">Profile</a>
                    <a href="logout.php">
                        <svg width="12" height="12" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M377.9 105.9L500.7 228.7c7.2 7.2 11.3 17.1 11.3 27.3s-4.1 20.1-11.3 27.3L377.9 406.1c-6.4 6.4-15 9.9-24 9.9c-18.7 0-33.9-15.2-33.9-33.9l0-62.1-128 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l128 0 0-62.1c0-18.7 15.2-33.9 33.9-33.9c9 0 17.6 3.6 24 9.9zM160 96L96 96c-17.7 0-32 14.3-32 32l0 256c0 17.7 14.3 32 32 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-64 0c-53 0-96-43-96-96L0 128C0 75 43 32 96 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32z"/></svg>
                        Logout
                    </a>
                </nav>

            </div>

        </header>

        <div class="content">

            <div class="page-title">
                <div class="wrap">
                    <h2>Profile</h2>
                    <p>View your profile details below.</p>
                </div>
            </div>

            <div class="block">

                <div class="profile-detail">
                    <strong>Username</strong>
                    <?=htmlspecialchars($_SESSION['account_name'])?>
                </div>

                <div class="profile-detail">
                    <strong>Email</strong>
                    <?=htmlspecialchars($email)?>
                </div>

                <div class="profile-detail">
                    <strong>Registered</strong>
                    <?=htmlspecialchars($registered)?>
                </div>

            </div>

        </div>

    </body>
</html>


     

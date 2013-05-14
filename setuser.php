<?php

if(isset($_GET['user'])) {
    
    $user = $_GET['user'];
    $dir = str_replace("_", "\\", $user);
    
    $session_id = explode('_', $user)[3];
    session_id($session_id);

    session_start();
    
    $_SESSION['user'] = $user;    
    $_SESSION['dir'] = $dir;    

?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title>Isolated spoken digit recognition - Saved sessions</title>
    </head>
    <body>
        <center>
        <h4>Isolated spoken digit recognition - Saved sessions</h4>
        <p>Welcome!</p>
        <p>Save this page to your favourites so you can come back anytime<br /> to continue your session using the link below</p>
        <br />
        <p><a href="index.php">Click here to continue your session</a></p>
        <br />
        <p>Your unique session id is</p>
        <p><?php print $_SESSION['user']; ?></p>
        </center>
    </body>
    </html>

<?php

}

?>

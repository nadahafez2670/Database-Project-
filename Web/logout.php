<?php
session_start();

$_SESSION = [];

session_destroy();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Logging Out...</title>
</head>
<body>
    <script type="text/javascript">
        window.location.replace("index.php");
    </script>
    
    <noscript>
        <meta http-equiv="refresh" content="0;url=index.php">
    </noscript>
</body>
</html>
<?php
function redirect($url, $permanent = false) {
    // Check if headers have already been sent
    if (headers_sent()) {
        // If headers are already sent, use JavaScript for redirection
        echo "<script>window.location.href='" . $url . "';</script>";
    } else {
        // If headers are not sent, use HTTP header for redirection
        if ($permanent) {
            // Send a 301 Moved Permanently header
            header('HTTP/1.1 301 Moved Permanently');
        }
        header('Location: ' . $url);      
    }
    die();
}
?>

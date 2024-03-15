<?php
session_start();
require_once("./core/global.php");

$uact = $_REQUEST['uact'] ?? '';
switch ($uact) {
   case 'login':
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        redirect("./login?error=missingcredentials");
    }

    $stmt = $conn->prepare("SELECT * FROM players WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Assuming you're transitioning to password_hash, use password_verify for new hashes
        // For MD5 (not recommended), direct comparison can be made as below
        if (md5($password) === $user['password']) { // Consider migrating to password_verify()
            $csession = md5(uniqid() . time()); // Unique session ID
            $_SESSION['csession'] = $csession;
         //   setcookie("csession", $csession, time() + 84600, "/", "", true, true); // Secure and HttpOnly flags
            
            // Use prepared statements to prevent SQL Injection
            $updateStmt = $conn->prepare("UPDATE players SET csession = ?, last_seen = NOW() WHERE email = ?");
            $updateStmt->bind_param("ss", $csession, $email);
            $updateStmt->execute();
          
          $stmt = $conn->prepare("INSERT INTO user_sessions (user_id, csession, ip_address) VALUES (?, ?, ?)");
          $stmt->bind_param("iss", $user['id'], $csession, $hostaddr); // Assuming $userId, $csession, and $ipAddress are already defined
          $stmt->execute();
          
            redirect("./dashboard?login={$csession}");
        } else {  
            redirect("./login?error=incorrectpassword");
        }
    } else {
        redirect("./login?error=usernotfound");
    }
    break;

case 'logout':
    // Check if the session or cookie exists
    if (isset($_SESSION['csession']) || isset($_COOKIE['csession'])) {
        // Clear the session cookie by setting its expiration to a past time
        if (isset($_COOKIE['csession'])) {
            unset($_COOKIE['csession']);
            setcookie('csession', '', time() - 42000, '/'); // Ensure path and domain match the cookie's original parameters
        }
        // Clear all session variables
        $_SESSION = array();

        // If sessions are used, destroy the session
        if (session_id()) {
            session_destroy();
        }
        // Redirect the user to the login page with a success message
        redirect("./login?logout=success");
    } else {
        // If there's no user session or cookie, redirect to the login page with an error message
        redirect("./login?error=nosession");
    }
    break;

    case 'register':
        $email = clean($_POST['email'] ?? '');
        $username = clean($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (!$email || !$password) {
            redirect("./register.php?error=missingfields");
        }

        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            redirect("./register.php?error=emailtaken");
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashedPassword);
            if ($stmt->execute()) {
                redirect("./login.php?success=registration");
            } else {
                redirect("./register.php?error=sqlerror");
            }
        }
        break;

    case 'reset-password':
        $email = clean($_POST['email'] ?? '');
        if (!$email) {
            redirect("./forgot-password.php?error=missingemail");
        }
        
        $token = bin2hex(random_bytes(32));
        $expires = new DateTime('now + 1 hour');
        
        $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
        $stmt->bind_param("sss", $token, $expires->format('Y-m-d H:i:s'), $email);
        $stmt->execute();
        
        $resetLink = "https://yourdomain.com/password-reset.php?token=$token";
        sendEmail($email, "Password Reset", "Please click on the following link to reset your password: $resetLink");
        
        redirect("./login.php?reset=initiated");
        break;

    case 'process-reset-password':
        $token = $_POST['token'] ?? '';
        $newPassword = $_POST['password'] ?? '';
        if (!$token || !$newPassword) {
            redirect("./password-reset.php?error=missingdata");
        }
        
        $stmt = $conn->prepare("SELECT email FROM users WHERE reset_token = ? AND reset_expires > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $email = $result->fetch_assoc()['email'];
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE email = ?");
            $stmt->bind_param("ss", $hashedPassword, $email);
            $stmt->execute();
            
            redirect("./login.php?reset=success");
        } else {
            redirect("./password-reset.php?error=invalidtoken");
        }
        break;

    case 'verify-email':
        $token = $_GET['token'] ?? '';
        if (!$token) {
            redirect("./email-verification.php?error=missingtoken");
        }
        
        $stmt = $conn->prepare("UPDATE users SET is_email_verified = 1 WHERE verification_token = ? AND is_email_verified = 0");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        if ($stmt->affected_rows === 1) {
            redirect("./login.php?email=verified");
        } else {
            redirect("./email-verification.php?error=invalidtoken");
        }
        break;

    default:
        redirect("./index.php?error=invalidaction");
        break;
}

?>

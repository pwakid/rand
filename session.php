<?php
// Check if the "token" cookie exists
if (isset($_SESSION['csession'])) {
    // Retrieve the token from the cookie
    $token = clean($_SESSION['csession']);

    // Prepare a SQL statement to fetch the user data based on the session token
    $sql = "SELECT * FROM players WHERE csession = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if a matching user is found
    if ($result->num_rows > 0) {
        // User is authenticated, return user data
        $user = $result->fetch_assoc();
      
      
      
      
      
     
        // echo json_encode($user); // Output user data as JSON
    } else {
        // Token does not match, user is not authenticated
        redirect("./login?e=1");
    }
} else {
    // Cookie does not exist, user is not authenticated
    redirect("./login?e=2");
}
?>

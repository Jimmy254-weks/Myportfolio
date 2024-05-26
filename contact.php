<?php
// Check if the form is submitted
$error = "";
$success = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $name = $_POST["name"] ?? "";
    $email = $_POST["email"] ?? "";
    $project = $_POST["project"] ?? "";
    $message = $_POST["message"] ?? "";

    // Store data in the database
    $servername = "localhost";
    $username = "root";
    $password = ""; // Update with your database password
    $dbname = "portfolio"; // Corrected database name

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and execute SQL query
    $sql = "INSERT INTO contact_messages (name, email, project, message) VALUES ('$name', '$email', '$project', '$message')";
    if ($conn->query($sql) === TRUE) {
        $success = "Message sent successfully!";
    } else {
        $error = "Error: " . $sql . "<br>" . $conn->error;
    }

    // Close connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Form</title>
</head>
<body>
    <?php if (!empty($success)): ?>
        <p id="success-msg"><?php echo $success; ?></p>
        <p>You will be redirected shortly...</p>
        <script>
            // Display success message using JavaScript
            document.addEventListener("DOMContentLoaded", function() {
                var successMsg = document.getElementById("success-msg");
                successMsg.style.display = "block";
                
                // Redirect after 3 seconds
                setTimeout(function(){
                    window.location.href = "index.html";
                }, 3000);
            });
        </script>
    <?php else: ?>
        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <input type="text" name="name" placeholder="Name"><br><br>
            <input type="email" name="email" placeholder="Email"><br><br>
            <input type="text" name="project" placeholder="Project"><br><br>
            <textarea name="message" placeholder="Message"></textarea><br><br>
            <button type="submit">Submit</button>
        </form>
    <?php endif; ?>
</body>
</html>

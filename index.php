<?php
//comments are mentioned for better walk-through of the project

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "contactform";

// connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Checking the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// variables Initialization
$full_name = $phone_number = $email = $subject = $message = $ip_address = $success_message = $error_message = "";

// Function to sanitize and validate input data
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

//Server-side validation begins from here

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate Full Name
    if (empty($_POST["full_name"])) {
        $error_message = "Full Name is required";
    } else {
        $full_name = test_input($_POST["full_name"]);
        // Check if name only contains letters and whitespace
        if (!preg_match("/^[a-zA-Z ]*$/", $full_name)) {
            $error_message = "Only letters and white space allowed for Full Name";
        }
    }

    // Validate Phone Number
    if (empty($_POST["phone_number"])) {
        $error_message = "Phone Number is required";
    } else {
        $phone_number = test_input($_POST["phone_number"]);
        // Check if phone number is valid
        if (!preg_match("/^[0-9]*$/", $phone_number)) {
            $error_message = "Invalid Phone Number";
        }
    }

    // Validate Email
    if (empty($_POST["email"])) {
        $error_message = "Email is required";
    } else {
        $email = test_input($_POST["email"]);
        // Check if email address is well-formed
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Invalid Email format";
        }
    }

    // Validate Subject
    if (empty($_POST["subject"])) {
        $error_message = "Subject is required";
    } else {
        $subject = test_input($_POST["subject"]);
    }

    // Validate Message
    if (empty($_POST["message"])) {
        $error_message = "Message is required";
    } else {
        $message = test_input($_POST["message"]);
    }

    // If no errors, check for duplicate record
    if (empty($error_message)) {
        $ip_address = $_SERVER['REMOTE_ADDR'];

        // Check if the same email or phone number already exists in the database
        $check_sql = "SELECT * FROM contacts_form WHERE email = '$email' OR phone_number = '$phone_number'";
        $result = $conn->query($check_sql);

        if ($result->num_rows > 0) {
            $error_message = "Duplicate entry: Email or Phone Number already exists";
        } else {
            // If no duplicate record found, proceed to insert
            $insert_sql = "INSERT INTO contacts_form (full_name, phone_number, email, subject, message, submission_ip)
                    VALUES ('$full_name', '$phone_number', '$email', '$subject', '$message', '$ip_address')";

            if ($conn->query($insert_sql) === TRUE) {
                $success_message = "Form submitted successfully";
                
                // Clear form fields, we can uncomment the below code snipet to clear the fields of the input box
               // $full_name = $phone_number = $email = $subject = $message = "";
            } else {
                $error_message = "Error: " . $insert_sql . "<br>" . $conn->error;
            }
        }
    }
}

// Close the database connection
$conn->close();
?>

<!-- HTML code for front end begins from here-->

<!DOCTYPE html>
<html>
<head>
    <title>Contact Form</title>
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
</head>
<body>
    
    <div class="error-message"><?php echo isset($error_message) ? $error_message : ''; ?></div>

    <div class="container mt-4">
        <div class="row justify-content-center ">
            <div class="col-md-6">
                <div class="card card-primary">
                    <div class="card-header text-center">
                        <h2>Contact Us</h2>
                    </div>

                    <div class="card-body">
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <div>
            Full Name: <input type="text" name="full_name" value="<?php echo $full_name; ?>">
        </div>
        <div>
            Phone Number: <input type="text" name="phone_number" value="<?php echo $phone_number; ?>">
        </div>
        <div>
            Email: <input type="text" name="email" value="<?php echo $email; ?>">
        </div>
        <div>
            Subject: <input type="text" name="subject" value="<?php echo $subject; ?>">
        </div>
        <div>
            Message: <textarea name="message"><?php echo $message; ?></textarea>
        </div>
        <div>
            <input type="submit" name="submit" value="Submit">
        </div>
    </form>
    </div>

<div class="card-footer" style="text-align: right;">
    &copy; Jayashree Das(Owner)
</div>

</div>
</div>
</div>
</div>

    <span class="success"><?php echo $success_message; ?></span>
</body>
</html>

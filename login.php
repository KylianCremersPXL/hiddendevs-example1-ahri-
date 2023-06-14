<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Database connection parameters
$host = 'localhost';
$dbName = 'your_database_name';
$username = 'your_username';
$password = 'your_password';

// Connect to the database
try {
    $db = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Database connection error: ' . $e->getMessage());
}

// Function to check if a user is logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Function to authenticate a user
function authenticateUser($username, $password)
{
    global $db;

    // Prepare the SQL statement
    $stmt = $db->prepare('SELECT id, username, password FROM users WHERE username = :username');
    $stmt->execute(['username' => $username]);

    // Fetch the user from the database
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Password is correct, set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            return true;
        }
    }

    return false;
}

// Function to log out the current user
function logout()
{
    session_unset();
    session_destroy();
}

// Usage example

// Check if the user is already logged in
if (isLoggedIn()) {
    echo 'You are already logged in as ' . $_SESSION['username'];
    echo '<br><a href="logout.php">Log out</a>';
    exit;
}

// Process the login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (authenticateUser($username, $password)) {
        echo 'Login successful!';
        echo '<br><a href="logout.php">Log out</a>';
        exit;
    } else {
        echo 'Invalid username or password.';
    }
}
?>

<!-- HTML login form -->
<form method="POST" action="">
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required><br>

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required><br>

    <input type="submit" value="Log in">
</form>
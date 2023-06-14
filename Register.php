<?php
error_reporting(E_ALL); // Enable error reporting
ini_set('display_errors', 1); // Display errors on the screen

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

// Function to register a new user
function registerUser($username, $password)
{
    global $db;

    // Check if the username is already taken
    $stmt = $db->prepare('SELECT id FROM users WHERE username = :username');
    $stmt->execute(['username' => $username]);

    if ($stmt->rowCount() > 0) {
        return 'Username is already taken.';
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert the new user into the database
    $stmt = $db->prepare('INSERT INTO users (username, password) VALUES (:username, :password)');
    $stmt->execute(['username' => $username, 'password' => $hashedPassword]);

    return true;
}

// Process the registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $registrationResult = registerUser($username, $password);

    if ($registrationResult === true) {
        echo 'Registration successful!';
        exit;
    } else {
        $error = $registrationResult;
    }
}
?>

<!-- HTML registration form -->
<form method="POST" action="">
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required><br>

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required><br>

    <input type="submit" value="Register">
</form>

<?php if (isset($error)) : ?>
    <p><?php echo $error; ?></p>
<?php endif; ?>

<?php
require_once('vendor/autoload.php'); // Include the Stripe PHP library

// Set your Stripe API keys
$stripeSecretKey = 'your_stripe_secret_key';
$stripePublicKey = 'your_stripe_public_key';

\Stripe\Stripe::setApiKey($stripeSecretKey); // Set the Stripe API key

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

// Fetch products from the database
function getProducts()
{
    global $db;

    $stmt = $db->query('SELECT * FROM products');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get a product by ID
function getProductByID($productID)
{
    $products = getProducts();

    foreach ($products as $product) {
        if ($product['id'] === $productID) {
            return $product;
        }
    }

    return null;
}

// Process payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['stripeToken'];
    $productID = $_POST['product'];
    $product = getProductByID($productID);

    if ($product) {
        try {
            // Create a new charge
            $charge = \Stripe\Charge::create([
                'amount' => $product['price'] * 100, // Stripe requires the amount in cents
                'currency' => $product['currency'],
                'source' => $token,
            ]);

            // Payment successful
            echo 'Payment successful! You purchased: ' . $product['name'] . ' (Price: ' . $product['price'] . ' ' . $product['currency'] . ')';
        } catch (\Stripe\Exception\CardException $e) {
            // Payment failed
            echo 'Payment failed. Error: ' . $e->getMessage();
        }
    } else {
        // Invalid product selected
        echo 'Invalid product selected.';
    }
}
?>

<!-- HTML payment form -->
<form method="POST" action="">
    <script src="https://js.stripe.com/v3/"></script>
    <select name="product">
        <?php foreach (getProducts() as $product) : ?>
            <option value="<?php echo $product['id']; ?>">
                <?php echo $product['name']; ?> (Price: <?php echo $product['price']; ?> <?php echo $product['currency']; ?>)
            </option>
        <?php endforeach; ?>
    </select>
    <div id="card-element"></div>
    <button type="submit">Pay</button>
</form>

<script>
    var stripe = Stripe('<?php echo $stripePublicKey; ?>');
    var elements = stripe.elements();
    var cardElement = elements.create('card');
    cardElement.mount('#card-element');
</script>
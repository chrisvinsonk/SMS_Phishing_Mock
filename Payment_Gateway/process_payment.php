<?php
$servername = "127.0.0.1";
$username = "root";
$password = "test";
$database = "payment_details";
$port = "3306";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function sanitize_input($input)
{
    global $conn;
    return mysqli_real_escape_string($conn, trim($input));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $paymentMethod = sanitize_input($_POST["paymentMethod"]);

    switch ($paymentMethod) {
        case "card":
            $cardNumber = sanitize_input($_POST["cardNumber"]);
            $expiryDate = sanitize_input($_POST["expiryDate"]);
            $cvv = sanitize_input($_POST["cvv"]);
            $sql = "INSERT INTO payments (payment_method, card_number, expiry_date, cvv) VALUES (?, ?, ?, ?)";
            break;

        case "upi":
            $upiProvider = sanitize_input($_POST["upiProvider"]);
            $upiID = sanitize_input($_POST["upiID"]);
            $upiPIN = sanitize_input($_POST["upiPIN"]);
            $sql = "INSERT INTO payments (payment_method, upi_provider, upi_id, upi_pin) VALUES (?, ?, ?, ?)";
            break;

        case "wallet":
            $phoneNumber = sanitize_input($_POST["phoneNumber"]);
            $walletPasscode = sanitize_input($_POST["walletPasscode"]);
            $sql = "INSERT INTO payments (payment_method, phone_number, wallet_passcode) VALUES (?, ?, ?)";
            break;

        case "additional":
            $additionalOptionField = sanitize_input($_POST["additionalOptionField"]);
            $sql = "INSERT INTO payments (payment_method, additional_field) VALUES (?, ?)";
            break;

        default:
            die("Unsupported payment method");
    }

    // Use prepared statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $paymentMethod, $cardNumber, $expiryDate, $cvv);

    if ($stmt->execute()) {
        echo "Payment processed successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

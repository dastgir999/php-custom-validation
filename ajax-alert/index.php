<?php
// Database connection
$host = 'localhost';
$dbname = 'testing';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Initialize variables
$name = $email = '';
$message = '';
$action = 'add'; // Default action
$id = ''; // For update operation

// Create (Insert) or Update
if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];

    if (!empty($name) && !empty($email)) {
        if ($_POST['action'] === 'add') {
            // Insert new record
            $stmt = $conn->prepare("INSERT INTO users (name, email) VALUES (:name, :email)");
            $stmt->execute([':name' => $name, ':email' => $email]);
            $message = "User added successfully!";
        } elseif ($_POST['action'] === 'update') {
            // Update existing record
            $id = $_POST['id'];
            $stmt = $conn->prepare("UPDATE users SET name = :name, email = :email WHERE id = :id");
            $stmt->execute([':name' => $name, ':email' => $email, ':id' => $id]);
            $message = "User updated successfully!";
        }
        // Redirect to index.php with message as query parameter
        header("Location: index.php?message=" . urlencode($message));
        exit();
    } else {
        $message = "Please fill all fields!";
    }
}

// Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $message = "User deleted successfully!";
        // Redirect to index.php with message as query parameter
        header("Location: index.php?message=" . urlencode($message));
        exit();
    } catch (PDOException $e) {
        $message = "Error deleting user: " . $e->getMessage();
    }
}

// Fetch data for update
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $name = $user['name'];
        $email = $user['email'];
        $action = 'update';
    }
}

// Read (Fetch all users)
$stmt = $conn->prepare("SELECT * FROM users");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get message from query parameter
if (isset($_GET['message'])) {
    $message = urldecode($_GET['message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP CRUD with Custom Alert</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        input[type="text"], input[type="email"] {
            padding: 10px;
            margin: 5px 0;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border: none;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
        }

        button:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .alert {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .alert-content {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            text-align: center;
            width: 300px;
        }

        .close-btn {
            float: right;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
        }

        .close-btn:hover {
            color: #ff0000;
        }

        .alert-buttons {
            margin-top: 20px;
        }

        .alert-buttons button {
            margin: 0 10px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
        }

        .alert-buttons button.yes {
            background-color: #28a745;
            color: white;
        }

        .alert-buttons button.yes:hover {
            background-color: #218838;
        }

        .alert-buttons button.no {
            background-color: #dc3545;
            color: white;
        }

        .alert-buttons button.no:hover {
            background-color: #c82333;
        }

        h1{
            color:red;font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>PHP CRUD with Custom Alert</h1>

    <!-- Form for adding/updating users -->
    <form method="POST" action="index.php">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <input type="hidden" name="action" value="<?php echo $action; ?>">
        <input type="text" name="name" placeholder="Name" value="<?php echo $name; ?>" required>
        <input type="email" name="email" placeholder="Email" value="<?php echo $email; ?>" required>
        <button type="submit" name="submit">
            <?php echo $action === 'add' ? 'Add User' : 'Update User'; ?>
        </button>
    </form>

    <!-- Display users -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo $user['name']; ?></td>
                    <td><?php echo $user['email']; ?></td>
                    <td>
                        <a href="index.php?edit=<?php echo $user['id']; ?>">Edit</a>
                        <a href="#" onclick="confirmDelete(<?php echo $user['id']; ?>)">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Success/Error Alert -->
    <div id="customAlert" class="alert">
        <div class="alert-content">
            <span class="close-btn" onclick="closeAlert()">&times;</span>
            <h2>Alert!</h2>
            <p id="alertMessage"><?php echo $message; ?></p>
            <button id="okBtn" onclick="closeAlert()">OK</button>
        </div>
    </div>

    <!-- Delete Confirmation Alert -->
    <div id="deleteAlert" class="alert">
        <div class="alert-content">
            <span class="close-btn" onclick="closeDeleteAlert()">&times;</span>
            <h2>Confirm Delete</h2>
            <p>Are you sure you want to delete this user?</p>
            <div class="alert-buttons">
                <button class="yes" onclick="deleteUser()">Yes</button>
                <button class="no" onclick="closeDeleteAlert()">No</button>
            </div>
        </div>
    </div>

    <script>
        // Show success/error alert if there's a message from PHP
        const urlParams = new URLSearchParams(window.location.search);
        const alertMessage = urlParams.get('message');

        if (alertMessage) {
            document.getElementById('customAlert').style.display = 'flex';
            document.getElementById('alertMessage').textContent = alertMessage;
        }

        // Close success/error alert
        function closeAlert() {
            document.getElementById('customAlert').style.display = 'none';
        }

        // Delete confirmation
        let userIdToDelete = null;

        function confirmDelete(userId) {
            userIdToDelete = userId;
            document.getElementById('deleteAlert').style.display = 'flex';
        }

        function closeDeleteAlert() {
            document.getElementById('deleteAlert').style.display = 'none';
        }

        function deleteUser() {
            if (userIdToDelete) {
                window.location.href = `index.php?delete=${userIdToDelete}`;
            }
        }
    </script>
</body>
</html>
<?php
// Include database connection
include_once 'config.php'; // Ensure this file connects to your database

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = $_SESSION["user"]["ID"] ?? null; // Get the user ID from the session
    $amount = $_POST['amount'] ?? 0; // Get the amount from the form
    $description = $_POST['description'] ?? ''; // Get the description from the form
    $category = $_POST['category'] ?? ''; // Get the category from the form
    $date = date("Y-m-d H:i:s"); // Get the current date

    // Check if a category is selected
    if (empty($category)) {
        $_SESSION['error'] = 'Please select a category.';
        header("Location: dashboard.php");
        exit();
    }

    // Prepare SQL INSERT statement
    $insertSql = "INSERT INTO expense (User_Id, Description, Cost, Category, Date) VALUES (?, ?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertSql);

    if (!$insertStmt) {
        $_SESSION['error'] = "SQL preparation failed: " . $conn->error;
        header("Location: dashboard.php");
        exit();
    }

    // Bind parameters and execute
    $insertStmt->bind_param("issss", $userId, $description, $amount, $category, $date);

    if ($insertStmt->execute()) {
        $_SESSION['message'] = 'Expense added successfully!';

        // Deduct the amount from the budget
        $updateBudgetSql = "UPDATE budget SET Budget = Budget - ? WHERE User_Id = ?";
        $updateBudgetStmt = $conn->prepare($updateBudgetSql);
        $updateBudgetStmt->bind_param("di", $amount, $userId); // 'd' for double, 'i' for integer

        if (!$updateBudgetStmt->execute()) {
            $_SESSION['error'] = 'Error updating budget: ' . $updateBudgetStmt->error;
        }

        $updateBudgetStmt->close();
    } else {
        $_SESSION['error'] = 'Error: ' . $insertStmt->error;
    }

    $insertStmt->close();
    header("Location: dashboard.php"); // Redirect after processing
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Expense</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="css/add_expense.css">
    
</head>
<body>
    <div class="background-content">
        <?php include 'dashboard.php'; ?>
    </div>

    <div class="modal-overlay" onclick="redirectToDashboard()"></div>
    <div class="modal">
        <h2>DAILY EXPENSE</h2>
        <form method="post" action="" onsubmit="return validateForm()">
            <input name="amount" placeholder="AMOUNT" type="number" required/>
            <input name="description" placeholder="DESCRIPTION" type="text"/>
            <input type="hidden" name="category" id="category" value=""/> <!-- Hidden input for category -->
            <input type="hidden" name="dateTime" value="<?php echo date('Y-m-d H:i:s'); ?>"/> <!-- Hidden input for current date and time -->
            <div class="buttons">
                <button type="button" onclick="setCategory('FOOD', this)">
                    <i class="fas fa-utensils"></i>
                    FOOD
                </button>
                <button type="button" onclick="setCategory('MATERIAL', this)">
                    <i class="fas fa-box"></i>
                    MATERIAL
                </button>
                <button type="button" onclick="setCategory('ENTERTAINMENT', this)">
                    <i class="fas fa-tv"></i>
                    ENTERTAINMENT
                </button>
                <button type="button" onclick="setCategory('MISCELLANEOUS', this)">
                    <i class="fas fa-ellipsis-h"></i>
                    MISCELLANEOUS
                </button>
                <button type="button" onclick="setCategory('TRANSPORTATION', this)">
                    <i class="fas fa-bicycle"></i>
                    TRANSPORTATION
                </button>
            </div>
            <button type="submit" class="add-expense">ADD EXPENSE</button>
        </form>
    </div>

    <script>
        function redirectToDashboard() {
            window.location.href = 'dashboard.php'; // Change this to your actual dashboard URL
        }

        function setCategory(category, button) {
            // Set the selected category in the hidden input
            document.getElementById('category').value = category;

            // Remove 'selected' class from all buttons
            const buttons = document.querySelectorAll('.modal .buttons button');
            buttons.forEach(btn => btn.classList.remove('selected'));

            // Add 'selected' class to the clicked button
            button.classList.add('selected');
        }

        function validateForm() {
            // Add any form validation logic here if needed
            return true; // Allow form submission
        }
    </script>
</body>
</html>
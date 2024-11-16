<?php
include_once 'config.php'; // Start the session

// Redirect to login if user is not logged in
if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit();
}

// Check for messages
$message = '';
$error = '';

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Clear the message after displaying
}

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']); // Clear the error after displaying
}

// Fetch user details and budget for the logged-in user
$user_id = $_SESSION["user"]["ID"];
$sql = "SELECT Username, Email, Photo, Budget FROM user LEFT JOIN budget ON user.UserId = budget.User_Id WHERE user.UserId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$user = null; // Initialize user variable
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc(); // Get user details
}

$budget = $user ? $user['Budget'] : 0; // Get the budget from the result
$username = $user ? $user['Username'] : 'Guest'; // Get the full name
$email = $user ? $user['Email'] : 'No Email'; // Get the email from the database or use the fallback
$photo = $user && $user['Photo'] ? $user['Photo'] : 'https://img.freepik.com/premium-vector/default-avatar-profile-icon-social-media-user-image-gray-avatar-icon-blank-profile-silhouette-vector-illustration_561158-3383.jpg'; // Set default photo if null

// Fetch today's expenses and calculate total
$totalExpense = 0;
$currentDate = date("Y-m-d"); // Get current date in Y-m-d format
$expenseSql = "SELECT SUM(Cost) as Total FROM expense WHERE User_Id = ? AND DATE(Date) = ?";
$expenseStmt = $conn->prepare($expenseSql);
$expenseStmt->bind_param("is", $user_id, $currentDate);
$expenseStmt->execute();
$expenseResult = $expenseStmt->get_result();

if ($expenseResult->num_rows > 0) {
    $expenseRow = $expenseResult->fetch_assoc();
    $totalExpense = $expenseRow['Total'] ? $expenseRow['Total'] : 0; // Get total or set to 0 if null
}

$expenseStmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/alert.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <img alt="User profile picture" height="80" src="<?php echo htmlspecialchars($photo); ?>">
            <h2><?php echo htmlspecialchars($username); ?></h2>
            <p><?php echo htmlspecialchars($email); ?></p> 
            <ul>
                <li><a href="dashboard.php" class="active">Home Page</a></li>
                <li><a href="#">Account</a></li>
                <li><a href="#">History</a></li>
                <li><a href="#">Settings</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-home"></i> Home Page</h1>
                <div class="icons">
                    <i class="fas fa-bell"></i>
                    <i class="fas fa-cog"></i>
                </div>
            </div>

            <!-- Display alert messages -->
            <?php if ($message): ?>
                <div class='alert alert-success' id="successAlert">
                    <?php echo $message; ?>
                    <button class="close-btn" onclick="closeAlert('successAlert')">&times;</button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class='alert alert-error' id="errorAlert">
                    <?php echo $error; ?>
                    <button class="close-btn" onclick="closeAlert('errorAlert')">&times;</button>
                </div>
            <?php endif; ?>

            <div class="card-container">
                <div class="card balance">
                    <h3><i class="fas fa-wallet icon"></i> Balance</h3>
                    <p>₱ <?php echo number_format($budget, 2); ?></p>
                    <a class="plus-icon" href="budget.php">
                        <i class="fas fa-plus edit-icon"></i>
                    </a>
                </div>
                <div class="card expense">
                    <h3><i class="fas fa-money-bill-wave icon"></i> Total Expense</h3>
                    <p>₱ <?php echo number_format($totalExpense, 2); ?></p>
                    <a class="plus-icon" href="add_expense.php">
                        <i class="fas fa-plus edit-icon"></i>
                    </a>
                </div>
            </div>
            <div class="card-container">
                <div class="card">
                    <h3><i class="fas fa-credit-card icon"></i> Total Money Lent</h3>
                    <p>₱ 999,999</p>
                    <i class="fas fa-edit edit-icon"></i>
                </div>
                <div class="card">
                    <h3><i class="fas fa-chart-line icon"></i> Total Money Loaned</h3>
                    <p>₱ 999,999</p>
                    <i class="fas fa-edit edit-icon"></i>
                </div>
            </div>
            <div class="chart-container">
                <div class="chart">
                    <h3><i class="fas fa-chart-pie icon"></i> Daily Expense</h3>
                    <div class="pie-chart"></div>
                    <div class="legend">
                        <div><span class="food"></span> Food <span>₱ 99</span></div>
                        <div><span class="material"></span> Material <span>₱ 999</span></div>
                        <div><span class="entertainment"></span> Entertainment <span>₱ 9,999</span></div>
                        <div><span class="miscellaneous"></span> Miscellaneous <span>₱ 99,999</span></div>
                        <div><span class="transportation"></span> Transportation <span>₱ 999,999</span></div>
                    </div>
                </div>
                <div class="chart">
                    <h3><i class="fas fa-chart-bar icon"></i> Monthly Expense</h3>
                    <div class="bar-chart"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/dashboard.js"></script>
</body>
</html>
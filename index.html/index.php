<?php
// Database connection
$servername = "localhost";
$username = "root"; // default XAMPP username
$password = "";     // default XAMPP password
$dbname = "student";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add'])) {
        // Add new student
        $name = $_POST['name'];
        $age = $_POST['age'];
        $gender = $_POST['gender'];
        
        $stmt = $conn->prepare("INSERT INTO details (name, age, gender) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $name, $age, $gender);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['update'])) {
        // Update student
        $id = $_POST['id'];
        $name = $_POST['name'];
        $age = $_POST['age'];
        $gender = $_POST['gender'];
        
        $stmt = $conn->prepare("UPDATE details SET name=?, age=?, gender=? WHERE id=?");
        $stmt->bind_param("sisi", $name, $age, $gender, $id);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_GET['delete'])) {
        // Delete student
        $id = $_GET['delete'];
        
        $stmt = $conn->prepare("DELETE FROM details WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch all students
$students = $conn->query("SELECT * FROM details");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: inline-block;
            width: 100px;
        }
        input, select {
            padding: 8px;
            width: 200px;
        }
        button {
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .action-btns a {
            padding: 5px 10px;
            text-decoration: none;
            color: white;
            border-radius: 3px;
        }
        .edit-btn {
            background-color: #2196F3;
        }
        .delete-btn {
            background-color: #f44336;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Student Management System</h1>
        
        <!-- Add/Edit Form -->
        <h2><?php echo isset($_GET['edit']) ? 'Edit Student' : 'Add New Student'; ?></h2>
        <form method="post" action="">
            <?php if (isset($_GET['edit'])): ?>
                <input type="hidden" name="id" value="<?php echo $_GET['edit']; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" 
                    value="<?php echo isset($_GET['edit']) ? $conn->query("SELECT name FROM students WHERE id=".$_GET['edit'])->fetch_assoc()['name'] : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="age">Age:</label>
                <input type="number" id="age" name="age" min="1" 
                    value="<?php echo isset($_GET['edit']) ? $conn->query("SELECT age FROM students WHERE id=".$_GET['edit'])->fetch_assoc()['age'] : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="gender">Gender:</label>
                <select id="gender" name="gender" required>
                    <option value="">Select Gender</option>
                    <option value="Male" <?php echo (isset($_GET['edit']) && $conn->query("SELECT gender FROM students WHERE id=".$_GET['edit'])->fetch_assoc()['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo (isset($_GET['edit']) && $conn->query("SELECT gender FROM students WHERE id=".$_GET['edit'])->fetch_assoc()['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                    <option value="Other" <?php echo (isset($_GET['edit']) && $conn->query("SELECT gender FROM students WHERE id=".$_GET['edit'])->fetch_assoc()['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            
            <button type="submit" name="<?php echo isset($_GET['edit']) ? 'update' : 'add'; ?>">
                <?php echo isset($_GET['edit']) ? 'Update Student' : 'Add Student'; ?>
            </button>
            
            <?php if (isset($_GET['edit'])): ?>
                <a href="index.php" style="padding: 8px 15px; background-color: #ccc; color: black; text-decoration: none;">Cancel</a>
            <?php endif; ?>
        </form>
        
        <!-- Students List -->
        <h2>Student Records</h2>
        <?php if ($students->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Age</th>
                        <th>Gender</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $students->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['name']; ?></td>
                            <td><?php echo $row['age']; ?></td>
                            <td><?php echo $row['gender']; ?></td>
                            <td class="action-btns">
                                <a href="index.php?edit=<?php echo $row['id']; ?>" class="edit-btn">Edit</a>
                                <a href="index.php?delete=<?php echo $row['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this student?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No student records found.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php $conn->close(); ?>
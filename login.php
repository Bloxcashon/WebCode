<?php
session_start();
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin_dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Admin Login
                    </div>
                    <div class="card-body">
                        <div id="error-message" class="alert alert-danger" style="display: none;"></div>
                        <form id="loginForm">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('process_login.php', {
    method: 'POST',
    body: formData
})
.then(response => {
    if (!response.ok) {
        throw new Error('Network response was not ok');
    }
    const contentType = response.headers.get("content-type");
    if (contentType && contentType.indexOf("application/json") !== -1) {
        return response.json();
    } else {
        return response.text().then(text => {
            throw new Error('Received non-JSON response: ' + text);
        });
    }
})
.then(data => {
    if (data.success) {
        window.location.href = 'admin_dashboard.php';
    } else {
        const errorMessage = document.getElementById('error-message');
        errorMessage.textContent = data.message;
        errorMessage.style.display = 'block';
    }
})
.catch((error) => {
    console.error('Error:', error);
    const errorMessage = document.getElementById('error-message');
    errorMessage.textContent = 'An error occurred during login: ' + error.message;
    errorMessage.style.display = 'block';
});
    });
    </script>
</body>
</html>
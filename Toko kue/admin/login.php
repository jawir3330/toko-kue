<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username']);
    $password = clean_input($_POST['password']);
    
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ? AND password = MD5(?)");
    $stmt->execute([$username, $password]);
    $admin = $stmt->fetch();
    
    if ($admin) {
        $_SESSION['admin'] = $admin['id_admin'];
        $_SESSION['admin_nama'] = $admin['nama_lengkap'];
        header('Location: index.php');
        exit;
    } else {
        $error = "Username atau password salah";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f5f2ed;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .login-container {
            background: #ffffff;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.08);
            width: 380px;
        }
        .login-container h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1a1a2e;
            text-align: center;
            margin-bottom: 0.25rem;
        }
        .login-container .sub {
            text-align: center;
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-group label {
            display: block;
            font-weight: 500;
            font-size: 0.85rem;
            color: #4a4a4a;
            margin-bottom: 0.3rem;
        }
        .form-group input {
            width: 100%;
            padding: 0.7rem 1rem;
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s;
            font-family: 'Inter', sans-serif;
        }
        .form-group input:focus {
            border-color: #c0392b;
            outline: none;
            box-shadow: 0 0 0 4px rgba(192,57,43,0.08);
        }
        .btn-login {
            width: 100%;
            padding: 0.75rem;
            background: #1a1a2e;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Inter', sans-serif;
        }
        .btn-login:hover {
            background: #2c2c3e;
        }
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            background: #fdedec;
            color: #922b21;
            font-size: 0.85rem;
            margin-bottom: 1.25rem;
            text-align: center;
        }
        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.8rem;
            color: #bdc3c7;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Admin Panel</h1>
        <p class="sub">Masuk untuk mengelola toko</p>

        <?php if (isset($error)): ?>
            <div class="alert"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn-login">Masuk</button>
        </form>

        <p class="login-footer">Default: admin / admin123</p>
    </div>
</body>
</html>
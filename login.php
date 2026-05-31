<?php
session_start();
require 'koneksi.php';

if (isset($_SESSION['status']) && $_SESSION['status'] === 'login') {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin.php");
        exit;
    } else if ($_SESSION['role'] === 'masyarakat') {
        header("Location: index.php");
        exit;
    }
}

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($result) === 1) {
        $data = mysqli_fetch_assoc($result);
        if (password_verify($password, $data['password'])) {
            $_SESSION['id_user']  = $data['id_user'];
            $_SESSION['username'] = $data['username'];
            $_SESSION['role']     = $data['role'];
            $_SESSION['status']   = "login";

            if ($data['role'] === 'admin') header("Location: admin.php");
            else header("Location: index.php");
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Login - SIPELDA</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 400px;
        }

        .login-container h2 {
            margin-bottom: 5px;
            text-align: center;
            color: #002855;
            font-size: 28px;
        }

        .login-container p {
            text-align: center;
            color: #777;
            margin-top: 0;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: bold;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            outline: none;
        }

        .form-group input:focus {
            border-color: #002855;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background-color: #002855;
            border: none;
            color: white;
            font-size: 16px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-login:hover {
            background-color: #001a3b;
        }

        .alert {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }

        .link-bawah {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }

        .link-bawah a {
            color: #002855;
            text-decoration: none;
            font-weight: bold;
        }

        .toggle-eye {
            position: absolute;
            right: 15px;
            top: 40px;
            cursor: pointer;
            color: #64748b;
            font-size: 16px;
        }
    </style>
</head>

<body>

    <div class="login-container">
        <h2>SIPELDA</h2>
        <p>Layanan Pengaduan Warga Kelurahan</p>

        <?php if (isset($error)) : ?><div class="alert"><?= $error; ?></div><?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Masukkan username Anda" required autocomplete="off">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" id="field-password" name="password" placeholder="Masukkan kata sandi Anda" required>
                <i class="fa-solid fa-eye toggle-eye" id="btn-toggle-eye"></i>
            </div>

            <div style="text-align: right; margin-bottom: 20px;">
                <a href="lupa_password.php" style="font-size: 13px; color: #dc3545; text-decoration: none; font-weight: 500;">Lupa Kata Sandi?</a>
            </div>

            <button type="submit" name="login" class="btn-login">Masuk ke Sistem</button>
        </form>

        <div class="link-bawah">
            Belum punya akun? <a href="register.php">Daftar di sini</a>
        </div>
    </div>

    <script>
        document.getElementById('btn-toggle-eye').addEventListener('click', function() {
            const field = document.getElementById('field-password');
            field.setAttribute('type', field.getAttribute('type') === 'password' ? 'text' : 'password');
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>

</html>

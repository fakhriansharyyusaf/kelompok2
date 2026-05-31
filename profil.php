<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['status']) || $_SESSION['status'] !== 'login') {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// PROSES UPLOAD FOTO
if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == 0) {
    $q_user = mysqli_query($koneksi, "SELECT foto_profil FROM users WHERE id_user = '$id_user'");
    $user_lama = mysqli_fetch_assoc($q_user);

    $ext_diizinkan = array('png', 'jpg', 'jpeg');
    $nama_file     = $_FILES['foto_profil']['name'];
    $x             = explode('.', $nama_file);
    $ekstensi      = strtolower(end($x));
    $ukuran        = $_FILES['foto_profil']['size'];
    $file_tmp      = $_FILES['foto_profil']['tmp_name'];

    if (in_array($ekstensi, $ext_diizinkan) === true) {
        if ($ukuran < 2048000) {
            // Hapus foto lama
            if (!empty($user_lama['foto_profil']) && file_exists('uploads/' . $user_lama['foto_profil'])) {
                unlink('uploads/' . $user_lama['foto_profil']);
            }
            $nama_foto_baru = 'avatar_' . time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "", $nama_file);
            move_uploaded_file($file_tmp, 'uploads/' . $nama_foto_baru);

            mysqli_query($koneksi, "UPDATE users SET foto_profil='$nama_foto_baru' WHERE id_user='$id_user'");
            echo "<script>alert('Foto profil berhasil diperbarui!'); window.location.href='profil.php';</script>";
            exit;
        } else {
            echo "<script>alert('Ukuran gambar maksimal 2MB!');</script>";
        }
    } else {
        echo "<script>alert('Format file wajib JPG/PNG!');</script>";
    }
}

// PROSES HAPUS FOTO
if (isset($_POST['hapus_foto'])) {
    $q_user = mysqli_query($koneksi, "SELECT foto_profil FROM users WHERE id_user = '$id_user'");
    $user_lama = mysqli_fetch_assoc($q_user);

    if (!empty($user_lama['foto_profil']) && file_exists('uploads/' . $user_lama['foto_profil'])) {
        unlink('uploads/' . $user_lama['foto_profil']);
    }
    mysqli_query($koneksi, "UPDATE users SET foto_profil = NULL WHERE id_user = '$id_user'");
    echo "<script>alert('Foto profil berhasil dihapus!'); window.location.href='profil.php';</script>";
    exit;
}

// PROSES UPDATE DATA PROFIL & KATA SANDI
if (isset($_POST['update_profil'])) {
    $nama_lengkap  = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $username_baru = mysqli_real_escape_string($koneksi, $_POST['username']);
    $no_telp       = mysqli_real_escape_string($koneksi, $_POST['no_telp']);

    // Cek apakah username sudah dipakai orang lain
    $cek_user = mysqli_query($koneksi, "SELECT id_user FROM users WHERE username='$username_baru' AND id_user != '$id_user'");
    
    if (mysqli_num_rows($cek_user) > 0) {
        echo "<script>alert('Username sudah digunakan! Pilih yang lain.');</script>";
    } else {
        // Jika form password baru diisi, jalankan prosedur ubah password
        if (!empty($_POST['password_baru'])) {
            $password_lama = $_POST['password_lama'];
            $password_baru = $_POST['password_baru'];

            // Ambil password lama (yang sudah di-hash) dari database
            $q_pass = mysqli_query($koneksi, "SELECT password FROM users WHERE id_user='$id_user'");
            $data_pass = mysqli_fetch_assoc($q_pass);

            // Verifikasi kecocokan kata sandi lama
            if (password_verify($password_lama, $data_pass['password'])) {
                // Jika cocok, buat hash untuk sandi baru
                $password_baru_hash = password_hash($password_baru, PASSWORD_DEFAULT);
                $update = mysqli_query($koneksi, "UPDATE users SET nama_lengkap='$nama_lengkap', username='$username_baru', no_telp='$no_telp', password='$password_baru_hash' WHERE id_user='$id_user'");
                
                if ($update) {
                    $_SESSION['username'] = $username_baru;
                    echo "<script>alert('Data profil dan kata sandi berhasil diperbarui!'); window.location.href='profil.php';</script>";
                    exit;
                }
            } else {
                // Jika sandi lama salah, hentikan proses
                echo "<script>alert('Gagal! Kata sandi lama yang Anda masukkan salah.'); window.history.back();</script>";
                exit;
            }
        } else {
            // Jika form password kosong, update profil saja
            $update = mysqli_query($koneksi, "UPDATE users SET nama_lengkap='$nama_lengkap', username='$username_baru', no_telp='$no_telp' WHERE id_user='$id_user'");
            
            if ($update) {
                $_SESSION['username'] = $username_baru;
                echo "<script>alert('Data profil berhasil disimpan!'); window.location.href='profil.php';</script>";
                exit;
            }
        }
    }
}

// Ambil data untuk ditampilkan
$query = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = '$id_user'");
$user = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Profil Saya - SIPELDA</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            background-color: #f4f7fb;
        }

        .navbar {
            background-color: #002855;
            color: white;
            padding: 25px 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .navbar .logo {
            font-size: 26px;
            font-weight: bold;
            color: white;
            text-decoration: none;
        }

        .nav-center {
            display: flex;
            gap: 40px;
        }

        .nav-center a {
            color: #a9b9cc;
            text-decoration: none;
            font-size: 16px;
            font-weight: 500;
        }

        .nav-center a:hover {
            color: white;
        }

        .user-profile-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 20px;
            border-radius: 30px;
            text-decoration: none;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .nav-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.8);
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            display: flex;
            gap: 30px;
            padding: 0 20px;
            align-items: flex-start; /* Mencegah sidebar meregang mengikuti tinggi konten sebelah */
        }

        .sidebar {
            flex: 1;
            background: #ebf2fa;
            padding: 40px 20px;
            border-radius: 12px;
            text-align: center;
            border: 1px solid #d0e1f9;
            box-sizing: border-box;
        }

        .avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: #002855;
            margin: 0 auto 20px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: white;
            font-weight: bold;
            border: 3px solid white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .sidebar h3 {
            margin: 0 0 25px;
            color: #002855;
            font-size: 22px;
            font-weight: bold;
        }

        .sidebar p {
            margin: 0 0 25px;
            color: #64748b;
            font-size: 14px;
            font-weight: 500;
        }

        .btn-ganti-foto {
            display: block;
            width: 100%;
            padding: 10px;
            margin-bottom: 12px;
            border: 1px solid #002855;
            color: #002855;
            background: white;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            font-size: 13px;
            transition: 0.2s;
        }

        .btn-ganti-foto:hover {
            background: #002855;
            color: white;
        }

        .btn-hapus-foto {
            color: #dc3545;
            background: transparent;
            border: none;
            font-weight: bold;
            cursor: pointer;
            font-size: 13px;
            margin-top: 5px;
        }

        .btn-logout-merah {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            margin-top: 35px;
            background: #dc3545;
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .main-content {
            flex: 2.5;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            box-sizing: border-box;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .form-group-full {
            margin-bottom: 20px;
        }

        .form-group label, .form-group-full label {
            display: block;
            font-size: 13px;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            outline: none;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: #002855;
        }

        .password-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-container .form-control {
            padding-right: 45px;
        }

        .password-container .toggle-eye {
            position: absolute;
            right: 15px;
            cursor: pointer;
            color: #64748b;
            font-size: 16px;
        }

        .password-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
            margin-top: 30px;
        }

        .btn-save {
            background: #002855;
            color: white;
            border: none;
            padding: 14px 35px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            float: right;
            font-size: 14px;
        }

        /* Responsiveness untuk HP/Tablet */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
            }
            .form-grid {
                grid-template-columns: 1fr; /* Jadi satu kolom bertumpuk ke bawah */
                gap: 15px;
            }
            .navbar {
                padding: 20px;
                flex-wrap: wrap;
                gap: 15px;
                justify-content: center;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <a href="index.php" class="logo">SIPELDA</a>
        <div class="nav-center">
            <a href="index.php">Beranda</a>
            <a href="historipengaduan.php">Riwayat</a>
        </div>
        <div>
            <div class="user-profile-btn">
                <span><?= htmlspecialchars($user['username'] ?? ''); ?></span>
                <?php if (!empty($user['foto_profil']) && file_exists('uploads/' . $user['foto_profil'])): ?>
                    <img src="uploads/<?= $user['foto_profil'] ?>" class="nav-avatar">
                <?php else: ?>
                    <i class="fa-solid fa-circle-user" style="font-size: 24px; color: #cbd5e1;"></i>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="sidebar">
            <div class="avatar">
                <?php if (!empty($user['foto_profil']) && file_exists('uploads/' . $user['foto_profil'])): ?>
                    <img src="uploads/<?= $user['foto_profil'] ?>" alt="Foto Profil">
                <?php else: ?>
                    <?= strtoupper(substr($user['username'], 0, 2)) ?>
                <?php endif; ?>
            </div>

            <h3><?= htmlspecialchars($user['username'] ?? '') ?></h3>

            <form method="POST" action="" enctype="multipart/form-data">
                <input type="file" name="foto_profil" id="file-avatar-input" accept="image/png, image/jpeg, image/jpg" style="display: none;" onchange="this.form.submit();">
                <button type="button" class="btn-ganti-foto" onclick="document.getElementById('file-avatar-input').click()">
                    <i class="fa-solid fa-camera"></i> Ganti Foto Profil
                </button>
            </form>

            <?php if (!empty($user['foto_profil'])): ?>
                <form method="POST" action="">
                    <button type="submit" name="hapus_foto" class="btn-hapus-foto" onclick="return confirm('Yakin ingin menghapus foto?')">
                        <i class="fa-solid fa-trash-can"></i> Hapus Foto
                    </button>
                </form>
            <?php endif; ?>

            <a href="logout.php" class="btn-logout-merah" onclick="return confirm('Yakin ingin keluar?')">
                <i class="fa-solid fa-right-from-bracket"></i> Keluar dari Akun
            </a>
        </div>

        <div class="main-content">
            <h2 style="margin-top: 0; color: #002855;">Informasi Pribadi</h2>
            <p style="color: #777; margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 15px;">Perbarui informasi dasar, nama pengguna, dan sandi Anda di sini.</p>

            <form method="POST" action="">
                <div class="form-group-full">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($user['nama_lengkap'] ?? '') ?>" required>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Nomor WhatsApp</label>
                        <input type="text" name="no_telp" class="form-control" value="<?= htmlspecialchars($user['no_telp'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="password-box">
                    <h4 style="margin: 0 0 20px; color:#333;"><i class="fa-solid fa-shield-halved"></i> Pengaturan Sandi</h4>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Kata Sandi Lama</label>
                            <div class="password-container">
                                <input type="password" name="password_lama" id="field-password-lama" class="form-control" style="background: #eef2f6;" placeholder="Ketik sandi saat ini">
                                <i class="fa-solid fa-eye toggle-eye" onclick="togglePassword('field-password-lama', this)"></i>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Kata Sandi Baru</label>
                            <div class="password-container">
                                <input type="password" name="password_baru" id="field-password-baru" class="form-control" style="background: #eef2f6;" placeholder="Ketik sandi baru">
                                <i class="fa-solid fa-eye toggle-eye" onclick="togglePassword('field-password-baru', this)"></i>
                            </div>
                        </div>
                    </div>
    
                </div>

                <div style="border-top: 1px solid #eee; padding-top: 20px; overflow: auto;">
                    <button type="submit" name="update_profil" class="btn-save">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Fungsi fleksibel untuk toggle (show/hide) password di berbagai input
        function togglePassword(fieldId, iconElement) {
            const field = document.getElementById(fieldId);
            const currentType = field.getAttribute('type') === 'password' ? 'text' : 'password';
            
            field.setAttribute('type', currentType);
            iconElement.classList.toggle('fa-eye');
            iconElement.classList.toggle('fa-eye-slash');
        }

        document.querySelector('form').addEventListener('submit', function(e) {
            const passLama = document.getElementById('field-password-lama').value;
            const passBaru = document.getElementById('field-password-baru').value;

            if (passBaru !== "" && passLama === "") {
                e.preventDefault(); // Hentikan form disubmit
                alert('Silakan masukkan Kata Sandi Lama Anda untuk mengonfirmasi perubahan sandi!');
                document.getElementById('field-password-lama').focus();
            }
        });
    </script>
</body>

</html>

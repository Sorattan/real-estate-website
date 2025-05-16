<?php
session_start();
include("baglanti.php");

$username_err = $parola_err = $hata = "";
$username = $parola = "";

// Giriş yapılmışsa, açılış sayfasına git
if (isset($_SESSION["admin_kullanici"], $_SESSION["admin_giris"]) && $_SESSION["admin_giris"] === true) {
    header("Location: acilis.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //Kullanıcı adı doğrulama
    if (empty($_POST["username"])) {
        $username_err = "Kullanıcı adı boş geçilemez.";
    } else {
        $username = $_POST["username"];
    }
    // Parola doğrulama
    if (empty($_POST["password"])) {
        $parola_err = "Parola boş geçilemez.";
    } else {
        $parola = $_POST["password"];
    }

    // Her şey doğruys devam
    if (empty($username_err) && empty($parola_err)) {
        $query = "SELECT * FROM `kullanıcılar` WHERE `kullanıcı_adı` = ?";
        $stmt  = mysqli_prepare($baglanti, $query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $hashlisifre = $row["parola"];

            if (password_verify($parola, $hashlisifre)) {
                $_SESSION["admin_kullanici"] = $row["kullanıcı_adı"];
                $_SESSION["admin_giris"] = true;
                $_SESSION["admin_id"] = $row["id"];
                header("Location: acilis.php");
                exit();
            } else {
                $hata = "Parola yanlış.";
            }
        } else {
            $hata = "Kullanıcı bulunamadı.";
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($baglanti);
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emlakçı Giriş - Tosuncuk Emlak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-bg: #f5f8fa;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 15px;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            text-align: center;
            border-radius: 10px 10px 0 0 !important;
            padding: 20px;
        }
        
        .card-header .logo {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .form-control {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #e1e5eb;
        }
        
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        .btn-primary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .form-label {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .alert {
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            border: none;
        }
        
        .alert-danger {
            background-color: #fee2e2;
            color: #ef4444;
        }
        
        .input-group-text {
            background-color: #f8f9fa;
            border: 1px solid #e1e5eb;
            color: #6c757d;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }
        
        .back-link:hover {
            color: var(--secondary-color);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="card-header">
                <div class="logo mb-2"><i class="fas fa-home me-2"></i>Tosuncuk Emlak</div>
                <h5 class="mb-0">Emlakçı Giriş Paneli</h5>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($hata)) { ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $hata; ?>
                    </div>
                <?php } ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Kullanıcı Adı</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" id="username" name="username" placeholder="Kullanıcı adınızı girin" value="<?php echo $username; ?>" required>
                        </div>
                        <?php if (!empty($username_err)) { ?>
                            <div class="invalid-feedback d-block"><?php echo $username_err; ?></div>
                        <?php } ?>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Şifre</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control <?php echo (!empty($parola_err)) ? 'is-invalid' : ''; ?>" id="password" name="password" placeholder="Şifrenizi girin" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="far fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                        <?php if (!empty($parola_err)) { ?>
                            <div class="invalid-feedback d-block"><?php echo $parola_err; ?></div>
                        <?php } ?>
                    </div>

                    <button type="submit" name="giris" class="btn btn-primary w-100">
                        <i class="fas fa-sign-in-alt me-2"></i>Giriş Yap
                    </button>
                </form>

                <div class="text-center mt-4">
                    <p class="mb-0">Hesabınız yok mu? <a href="kayit.php" class="text-decoration-none fw-bold">Kayıt Ol</a></p>
                </div>
            </div>
        </div>
        
        <a href="index.php" class="back-link">
            <i class="fas fa-arrow-left me-1"></i> İlanlar Sayfasına Geri Dön
        </a>
    </div>

    <script>
        // Şifre göster/gizle fonksiyonu
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>

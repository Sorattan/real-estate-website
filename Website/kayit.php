<?php
session_start();
include("baglanti.php");

// Güvenlik kodu tanımla
define("GUVENLIK_KODU", "1907");

$hata = $basari = "";
$name = $email = $phone = "";
$parola_err = "";

if (isset($_POST["kayit_ol"]))
{
    // Güvenlik kodunu kontrol et
    $girilen_kod = $_POST["guvenlik_kodu"] ?? '';
    
    // Şifre kontrolü
    $parola = $_POST["parola"] ?? '';
    $parola_tekrar = $_POST["parola_tekrar"] ?? '';
    
    if ($parola !== $parola_tekrar) {
        $parola_err = "Girdiğiniz şifreler eşleşmiyor!";
        $hata = "Kayıt işlemi gerçekleştirilemedi.";
    } 
    elseif ($girilen_kod != GUVENLIK_KODU) {
        $hata = "Hatalı güvenlik kodu! Kayıt işlemi gerçekleştirilemedi.";
    } 
    else {
        $name = $_POST["kullanici_adi"];
        $email = $_POST["email"];
        $phone = $_POST["telefon"];
        $password = password_hash($_POST["parola"], PASSWORD_DEFAULT);

        $ekle = "INSERT INTO kullanicilar (kullanici_adi, email, parola, tel) 
                 VALUES ('$name','$email','$password','$phone')";
                 
        $calistirekle = mysqli_query($baglanti, $ekle);

        if ($calistirekle) {
            $basari = "Kayıt Başarılı";
        } else {
            $hata = "Kayıt başarısız";
        }
    }

    mysqli_close($baglanti);
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emlakçı Kayıt - Tosuncuk Emlak</title>
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
            font-size: 14px;
        }
        
        .register-container {
            width: 100%;
            max-width: 400px;
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
            padding: 16px;
        }
        
        .card-header .logo {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        
        .card-header h5 {
            font-size: 16px;
            margin-bottom: 0;
        }
        
        .form-control {
            padding: 8px 12px;
            border-radius: 5px;
            margin-bottom: 12px;
            border: 1px solid #e1e5eb;
            font-size: 13px;
        }
        
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        .btn-primary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            padding: 8px 12px;
            font-weight: 600;
            transition: all 0.3s;
            font-size: 13px;
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
            margin-bottom: 4px;
            font-size: 13px;
        }
        
        .alert {
            border-radius: 5px;
            padding: 12px;
            margin-bottom: 16px;
            border: none;
            font-size: 13px;
        }
        
        .alert-danger {
            background-color: #fee2e2;
            color: #ef4444;
        }
        
        .alert-success {
            background-color: #dcfce7;
            color: #16a34a;
        }
        
        .input-group-text {
            background-color: #f8f9fa;
            border: 1px solid #e1e5eb;
            color: #6c757d;
            font-size: 13px;
            padding: 8px 12px;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 16px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
            font-size: 13px;
        }
        
        .back-link:hover {
            color: var(--secondary-color);
        }
        
        .invalid-feedback {
            color: #ef4444;
            font-size: 12px;
            margin-top: -8px;
            margin-bottom: 8px;
        }
        
        small.text-muted {
            font-size: 11px;
        }
        
        .btn-outline-secondary {
            font-size: 13px;
            padding: 8px;
        }
        
        .card-body {
            padding: 16px;
        }
        
        .text-center p {
            font-size: 13px;
            margin-top: 12px;
        }
        
        .mb-3 {
            margin-bottom: 12px !important;
        }
        
        .mb-4 {
            margin-bottom: 16px !important;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="card">
            <div class="card-header">
                <div class="logo mb-2"><i class="fas fa-home me-2"></i>Tosuncuk Emlak</div>
                <h5 class="mb-0">Emlakçı Kayıt Paneli</h5>
            </div>
            <div class="card-body p-3">
                <?php if (!empty($hata)) { ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $hata; ?>
                    </div>
                <?php } ?>
                
                <?php if (!empty($basari)) { ?>
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $basari; ?>
                    </div>
                <?php } ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">E-posta</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" placeholder="E-posta adresinizi girin" value="<?php echo $email; ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label">Kullanıcı Adı</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="username" name="kullanici_adi" placeholder="Kullanıcı adınızı girin" value="<?php echo $name; ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="telefon" class="form-label">Telefon</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input type="text" class="form-control" id="telefon" name="telefon" placeholder="Telefon numaranızı girin" value="<?php echo $phone; ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Şifre</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control <?php echo (!empty($parola_err)) ? 'is-invalid' : ''; ?>" id="password" name="parola" placeholder="Şifrenizi girin" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="far fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">Şifre Tekrar</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control <?php echo (!empty($parola_err)) ? 'is-invalid' : ''; ?>" id="password_confirm" name="parola_tekrar" placeholder="Şifrenizi tekrar girin" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirm">
                                <i class="far fa-eye" id="eyeIconConfirm"></i>
                            </button>
                        </div>
                        <?php if (!empty($parola_err)) { ?>
                            <div class="invalid-feedback d-block"><?php echo $parola_err; ?></div>
                        <?php } ?>
                    </div>
                    
                    <div class="mb-4">
                        <label for="guvenlik_kodu" class="form-label">Güvenlik Kodu</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-shield-alt"></i></span>
                            <input type="password" class="form-control" id="guvenlik_kodu" name="guvenlik_kodu" placeholder="Güvenlik kodunu girin" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleSecurityCode">
                                <i class="far fa-eye" id="securityEyeIcon"></i>
                            </button>
                        </div>
                        <small class="text-muted">Lütfen sistem yöneticinizden güvenlik kodunu öğrenin.</small>
                    </div>

                    <button type="submit" name="kayit_ol" class="btn btn-primary w-100">
                        <i class="fas fa-user-plus me-2"></i>Kayıt Ol
                    </button>
                </form>

                <div class="text-center mt-3">
                    <p class="mb-0">Zaten hesabınız var mı? <a href="giris.php" class="text-decoration-none fw-bold">Giriş Yap</a></p>
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
        
        // Şifre tekrar göster/gizle fonksiyonu
        document.getElementById('togglePasswordConfirm').addEventListener('click', function() {
            const passwordConfirmInput = document.getElementById('password_confirm');
            const eyeIconConfirm = document.getElementById('eyeIconConfirm');
            
            if (passwordConfirmInput.type === 'password') {
                passwordConfirmInput.type = 'text';
                eyeIconConfirm.classList.remove('fa-eye');
                eyeIconConfirm.classList.add('fa-eye-slash');
            } else {
                passwordConfirmInput.type = 'password';
                eyeIconConfirm.classList.remove('fa-eye-slash');
                eyeIconConfirm.classList.add('fa-eye');
            }
        });
        
        // Güvenlik kodu göster/gizle fonksiyonu
        document.getElementById('toggleSecurityCode').addEventListener('click', function() {
            const securityCodeInput = document.getElementById('guvenlik_kodu');
            const securityEyeIcon = document.getElementById('securityEyeIcon');
            
            if (securityCodeInput.type === 'password') {
                securityCodeInput.type = 'text';
                securityEyeIcon.classList.remove('fa-eye');
                securityEyeIcon.classList.add('fa-eye-slash');
            } else {
                securityCodeInput.type = 'password';
                securityEyeIcon.classList.remove('fa-eye-slash');
                securityEyeIcon.classList.add('fa-eye');
            }
        });
        
        // Şifre eşleşme kontrolü
        document.getElementById('password_confirm').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const passwordConfirm = this.value;
            
            if (password !== passwordConfirm) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    </script>
</body>
</html>
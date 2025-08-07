<?php
ob_start();
session_start();

// Static 6-digit PIN
define('STATIC_PIN', '123456');

if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    showMainPage();
} else {
    if (isset($_POST['pin'])) {
        $enteredPin = $_POST['pin'];

        if ($enteredPin === STATIC_PIN) {
            $_SESSION['authenticated'] = true;
            showMainPage();
        } else {
            showLoginForm("Incorrect PIN. Please try again.");
        }
    } else {
        showLoginForm();
    }
}

function showLoginForm($error = '')
{
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>GOP-Marketing - Authentication</title>
        <link rel="icon" type="image/x-icon" href="icon/icon.png">
        <link rel="stylesheet" href="bootstrap-5.3.6/css/bootstrap.min.css">
        <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
        <style>
            body {
                /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }

            .auth-card {
                background: rgba(255, 255, 255, 0.95);
                border-radius: 20px;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.2);
                padding: 50px 40px;
                text-align: center;
                max-width: 450px;
                width: 100%;
            }

            .logo {
                width: 100px;
                height: 100px;
                margin: 0 auto 25px;
                /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 2.2rem;
                font-weight: bold;
                /* box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3); */
            }
            .logo img {
                max-width: 100%;
                border-radius: 50%;
            }

            .pin-container {
                margin: 30px 0;
            }

            .pin-input-group {
                display: flex;
                gap: 10px;
                justify-content: center;
                margin: 20px 0;
            }

            .pin-digit {
                width: 50px;
                height: 50px;
                font-size: 1.8rem;
                font-weight: bold;
                text-align: center;
                border: 2px solid #e0e0e0;
                border-radius: 12px;
                background: rgba(255, 255, 255, 0.9);
                transition: all 0.3s ease;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            }

            .pin-digit:focus {
                border-color: #667eea;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
                outline: none;
                background: white;
                transform: scale(1.05);
            }

            .pin-digit.filled {
                border-color: #28a745;
                background: rgba(40, 167, 69, 0.1);
            }

            .hidden-input {
                position: absolute;
                left: -9999px;
                opacity: 0;
            }

            .btn-auth {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border: none;
                border-radius: 12px;
                padding: 15px 40px;
                color: white;
                font-weight: 600;
                font-size: 1.1rem;
                transition: all 0.3s ease;
                box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
            }

            .btn-auth:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
                color: white;
            }

            .btn-auth:disabled {
                opacity: 0.6;
                cursor: not-allowed;
                transform: none;
            }

            .error-message {
                color: #dc3545;
                margin-top: 15px;
                font-size: 0.95rem;
                font-weight: 500;
            }

            .security-icon {
                color: #667eea;
                margin-bottom: 10px;
            }

            .pin-label {
                color: #6c757d;
                font-size: 0.9rem;
                margin-bottom: 15px;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
            }

            @keyframes shake {

                0%,
                50%,
                100% {
                    transform: translateX(0);
                }

                25% {
                    transform: translateX(-5px);
                }

                75% {
                    transform: translateX(5px);
                }
            }

            .shake {
                animation: shake 0.5s ease-in-out;
            }
        </style>
    </head>

    <body>
        <div class="auth-card">
            <div class="logo">
                <img src="icon/icon.png" alt="">
            </div>
            <h2 class="mb-3 fw-bold">GOP Marketing</h2>
            <div class="security-icon">
                <span class="iconify" data-icon="solar:shield-check-outline" data-width="32"></span>
            </div>
            <p class="text-muted mb-4">Secure Access Portal</p>

            <form method="POST" id="pinForm">
                <div class="pin-container">
                    <div class="pin-label">
                        <span class="iconify" data-icon="solar:lock-keyhole-outline" data-width="16"></span>
                        Enter your 6-digit PIN
                    </div>

                    <!-- Hidden input for form submission -->
                    <input type="password" name="pin" id="hiddenPin" class="hidden-input" maxlength="6" required>

                    <!-- Visual PIN input boxes -->
                    <div class="pin-input-group">
                        <input type="password" class="form-control pin-digit" maxlength="1" data-index="0">
                        <input type="password" class="form-control pin-digit" maxlength="1" data-index="1">
                        <input type="password" class="form-control pin-digit" maxlength="1" data-index="2">
                        <input type="password" class="form-control pin-digit" maxlength="1" data-index="3">
                        <input type="password" class="form-control pin-digit" maxlength="1" data-index="4">
                        <input type="password" class="form-control pin-digit" maxlength="1" data-index="5">
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="error-message">
                        <span class="iconify" data-icon="solar:danger-triangle-outline" data-width="16"></span>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-auth btn-lg mt-4" id="submitBtn" disabled style="display: none;">
                    <span class="iconify me-2" data-icon="solar:login-outline" data-width="20"></span>
                    Access System
                </button>
            </form>

            <div class="mt-4">
                <small class="text-muted d-block">
                    <span class="iconify me-1" data-icon="solar:info-circle-outline" data-width="14"></span>
                    Secure access to inventory management system
                </small>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const pinDigits = document.querySelectorAll('.pin-digit');
                const hiddenPin = document.getElementById('hiddenPin');
                const submitBtn = document.getElementById('submitBtn');
                const form = document.getElementById('pinForm');

                let pinValue = '';

                // Focus first input on load
                pinDigits[0].focus();

                pinDigits.forEach((digit, index) => {
                    digit.addEventListener('input', function(e) {
                        // Only allow numbers
                        const value = e.target.value.replace(/[^0-9]/g, '');
                        e.target.value = value;

                        if (value) {
                            pinValue = pinValue.substring(0, index) + value + pinValue.substring(index + 1);
                            e.target.classList.add('filled');

                            // Move to next input
                            if (index < pinDigits.length - 1) {
                                pinDigits[index + 1].focus();
                            }
                        } else {
                            pinValue = pinValue.substring(0, index) + pinValue.substring(index + 1);
                            e.target.classList.remove('filled');
                        }

                        updateHiddenInput();
                    });

                    digit.addEventListener('keydown', function(e) {
                        // Handle backspace
                        if (e.key === 'Backspace') {
                            if (!e.target.value && index > 0) {
                                pinDigits[index - 1].focus();
                                pinDigits[index - 1].value = '';
                                pinDigits[index - 1].classList.remove('filled');
                                pinValue = pinValue.substring(0, index - 1) + pinValue.substring(index);
                                updateHiddenInput();
                            }
                        }

                        // Handle arrow keys
                        if (e.key === 'ArrowLeft' && index > 0) {
                            pinDigits[index - 1].focus();
                        }
                        if (e.key === 'ArrowRight' && index < pinDigits.length - 1) {
                            pinDigits[index + 1].focus();
                        }
                    });

                    digit.addEventListener('paste', function(e) {
                        e.preventDefault();
                        const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '');

                        for (let i = 0; i < Math.min(pastedData.length, pinDigits.length - index); i++) {
                            pinDigits[index + i].value = pastedData[i];
                            pinDigits[index + i].classList.add('filled');
                        }

                        pinValue = '';
                        pinDigits.forEach(digit => {
                            pinValue += digit.value || '';
                        });

                        updateHiddenInput();

                        // Focus last filled digit or next empty
                        const lastFilledIndex = Math.min(index + pastedData.length - 1, pinDigits.length - 1);
                        pinDigits[lastFilledIndex].focus();
                    });
                });

                function updateHiddenInput() {
                    hiddenPin.value = pinValue;
                    submitBtn.disabled = pinValue.length !== 6;

                    if (pinValue.length === 6) {
                        submitBtn.classList.add('pulse');
                        setTimeout(() => {
                            form.submit();
                        }, 300);
                    }
                }

                // Add error animation if there's an error
                <?php if ($error): ?>
                    document.querySelector('.auth-card').classList.add('shake');
                    setTimeout(() => {
                        document.querySelector('.auth-card').classList.remove('shake');
                    }, 500);
                <?php endif; ?>
            });
        </script>
    </body>

    </html>
<?php
}

function showMainPage()
{
    header('Location: views/items/items.php');
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

ob_end_flush();
?>
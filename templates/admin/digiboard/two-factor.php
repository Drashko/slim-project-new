<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two Factor Verification | Digiboard</title>
    
    <link rel="shortcut icon" href="favicon.png">
    <link rel="stylesheet" href="/digiboard/assets/vendor/css/all.min.css">
    <link rel="stylesheet" href="/digiboard/assets/vendor/css/OverlayScrollbars.min.css">
    <link rel="stylesheet" href="/digiboard/assets/vendor/css/sweetalert2.min.css">
    <link rel="stylesheet" href="/digiboard/assets/vendor/css/bootstrap.min.css">
    <link rel="stylesheet" href="/digiboard/assets/css/style.css">
    <link rel="stylesheet" id="primaryColor" href="/digiboard/assets/css/blue-color.css">
    <link rel="stylesheet" id="rtlStyle" href="#">
</head>
<body class="light-theme">
    <!-- preloader start -->
    <div class="preloader d-none">
        <div class="loader">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
    <!-- preloader end -->

    <!-- main content start -->
    <div class="main-content login-panel two-factor-panel">
        <div class="container">
            <div class="row g-4 align-items-center">
                <div class="col-lg-6">
                    <div class="text-lg-start text-center logo mb-4">
                        <img src="/digiboard/assets/images/logo-black.png" alt="logo">
                    </div>
                    <p class="text-lg-start text-center mb-lg-0 mb-4">It's the Bright One, it's the Right One, that's Business.</p>
                </div>
                <div class="col-lg-6">
                    <div class="static-body">
                        <div class="panel bg-transparent">
                            <div class="panel-body">
                                <div class="part-img w-25 m-auto mb-lg-5 mb-4 px-lg-4">
                                    <img src="/digiboard/assets/images/phone.png" alt="image">
                                </div>
                                <div class="part-txt text-center">
                                    <h2>Two-Factor Verification</h2>
                                    <p class="mb-2">Enter the verification code we sent to</p>
                                    <p class="fw-semibold fs-5 mb-lg-4 mb-0">********678</p>
                                </div>
                                <div class="verification-area text-center">
                                    <div id="otp_target"></div>
                                    <p class="mb-4">Type your 6 digit security code</p>
                                    <button class="btn btn-primary px-3 security-code-submit">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- footer start -->
        <div class="footer">
            <p>Copyright© <script>document.write(new Date().getFullYear())</script> All Rights Reserved By <span class="text-primary">Digiboard</span></p>
        </div>
        <!-- footer end -->
    </div>
    <!-- main content end -->
    
    <script src="/digiboard/assets/vendor/js/jquery-3.6.0.min.js"></script>
    <script src="/digiboard/assets/vendor/js/jquery.overlayScrollbars.min.js"></script>
    <script src="/digiboard/assets/vendor/js/otpdesigner.min.js"></script>
    <script src="/digiboard/assets/vendor/js/sweetalert2.all.min.js"></script>
    <script src="/digiboard/assets/vendor/js/bootstrap.bundle.min.js"></script>
    <script src="/digiboard/assets/js/main.js"></script>
</body>
</html>
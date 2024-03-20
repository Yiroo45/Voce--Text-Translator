<?php require ("mysql/mysqli_session.php");
$current_page = basename($_SERVER['PHP_SELF']);

function dd($item)
{
    var_dump($item);
    exit();
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!--<link rel="stylesheet" href="styles/style2.css"> -->
    <link rel="stylesheet" href="styles/simplePagination.css">
    <link rel="stylesheet" href="styles/index-style.css">
    <link rel="stylesheet" href="styles/landingpage.css">
    <title>Audio to Text Translation</title>
    <link rel="icon" type="image/x-icon" href="images/icon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

</head>

<body>

    <!-- Navbar -->
    <nav>
        <div class="logo">
            <a href="index.php"> <img src="images/logo.png" alt="Voce logo"></a>
            <span>About Voce</span>
        </div>
    </nav>
    <!-- End of Navbar -->
    <?php include ("sidebar.php") ?>
    <div class="main-content" style="background: url('images/headset.jpg') center/cover no-repeat;">

        <div class="header">
            <a href="text-text.php"><button><img src="images/translator.png" alt="Language Icon"
                        width="30px">Text</button></a>
            <a href="index.php"><button><img src="images/music-file.png" alt="Language Icon" width="30px">Upload
                    a File</button></a>
        </div>
        <!-- ======= Hero Section ======= -->
        <section>

            
                <div class="" style="text-align:left">
                <h1>Voce</h1>

                <div><a href="text-text.php" class="btn-get-started rounded-pill scrollto">Get Started</a></div>
                </div>

        </section>
        <br>

    </div>
    
    <br>
    
    <nav>
        <div class="footer">
            <div>
                <a href="faqs.php">FAQs</a>
                <a href="about.php">About Voce</a>
            </div>
            <div>
                <span>Voce © 2024. All Rights Reserved</span>
            </div>
        </div>
    </nav>


    <!-- for an in-depth walkthrough for pagination, please visit https://bilalakil.me/simplepagination -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/simplePagination.js/1.4/jquery.simplePagination.min.js"
        integrity="sha512-J4OD+6Nca5l8HwpKlxiZZ5iF79e9sgRGSf0GxLsL1W55HHdg48AEiKCXqvQCNtA1NOMOVrw15DXnVuPpBm2mPg=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="scripts/index.js"></script>
    <script src="scripts/translation_process.js"></script>
</body>

</html>
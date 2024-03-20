<?php require ("mysql/mysqli_session.php");
$current_page = basename($_SERVER['PHP_SELF']);
?>
<?php if (!isset ($_SESSION['username'])) {

    header("location: index");
    exit();
} ?>

<?php

function dd($item)
{
    var_dump($item);
    exit();
}

require "utilities/common_languages.php"; // Translator_Functions and Error Handling are alr required in this file
require "utilities/verify_audio_files.php";
$id = is_array($_SESSION['user_id']) ? $_SESSION['user_id']['user_id'] : $_SESSION['user_id'];


// Query for total number of feedback sent
$q = "SELECT COUNT(contact_id) AS total_feedback FROM contacts";
$result = mysqli_query($dbcon, $q);
$num_of_feedback = mysqli_fetch_assoc($result);

// Query for total number of files uploaded
$q = "SELECT COUNT(file_id) AS total_files FROM audio_files";
$result = mysqli_query($dbcon, $q);
$num_of_files = mysqli_fetch_assoc($result);

// Query for total number of text-to-text translations
$q = "SELECT COUNT(from_audio_file) AS total_t2t FROM text_translations WHERE from_audio_file = 0";
$result = mysqli_query($dbcon, $q);
$num_of_t2t = mysqli_fetch_assoc($result);

// Query for total number of audio-to-text translations
$q = "SELECT COUNT(from_audio_file) AS total_a2t FROM text_translations WHERE from_audio_file = 1";
$result = mysqli_query($dbcon, $q);
$num_of_a2t = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="styles/UI-OLDSTYLE.css">

    <title>Admin Dashboard</title>
    <link rel="icon" type="image/x-icon" href="images/icon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">





</head>

<!-- Confirm delete window -->
<div class="delete-window">
    <div class="confirm-div">
        <h4 class="confirm-text"></h4>
        <div class="confirm-btn-div">
            <button class="confirm-btn confirm-yes">Yes</button>
            <button class="confirm-btn confirm-no">No</button>
        </div>
    </div>
</div>

<body>

    <!-- Sidebar -->
    <?php require "sidebar.php" ?>

    <!-- Main Content -->
    <div class="content">
        <!-- Navbar -->
        <nav>
            <i class='bx bx-menu'></i>
            <?= $_SESSION['username']; ?>
        </nav>

        <!-- End of Navbar -->

        <main style="padding: 0;">



            <div class="float-container">
                <div class="donut-container">
                    <h1>Total Translations</h1>
                    <br>
                    <canvas id="donutCanvas"></canvas>
                </div>
                <div class="admin-container">
                    <div class="admin-content content-users">
                        <h1 class="">Total Feedback Given</h1>
                        <h1 class="count">
                            <?= $num_of_feedback['total_feedback'] ?>
                        </h1>

                    </div>
                    <div class="admin-content content-files">
                        <h1>Total Audio Files uploaded</h1>
                        <h1 class="count">
                            <?= $num_of_files['total_files'] ?>
                        </h1>
                    </div>
                    <div class="admin-content content-t2t">
                        <h1>Total Text Translations</h1>
                        <h1 class="count">
                            <?= $num_of_t2t['total_t2t'] ?>
                        </h1>
                    </div>
                    <div class="admin-content content-a2t">
                        <h1>Total Audio Translations</h1>
                        <h1 class="count">
                            <?= $num_of_a2t['total_a2t'] ?>
                        </h1>
                    </div>
                </div>
            </div>
            <div class="graph-container">
                <h1>Usage for the Past Week</h1>
                <canvas id="myChart">
                </canvas>
            </div>
            <br />
            <div class="graph-container">
                <h1>Errors in Text to Text</h1>
                <canvas id="bartext">
                </canvas>
            </div>
            <br />
            <div class="graph-container">
                <h1>Errors in Audio to Text</h1>
                <canvas id="baraudio">
                </canvas>
            </div>
            <br />
            <div class="graph-container">
                <form method="post" action="admin.php">
                    <button style="padding: 5px;" id="verify-files">
                        <h3>Verify Audio Files</h3>
                    </button>
                </form>
                <p>
                    <?= $verify_message; ?>
                </p>
            </div>
            <div class="dlbtns-container">
                <button class="dlpie-btn" href="#">Download Pie Graph</button>
                <button class="dlgraph-btn" href="#">Download Line Graph</button>
                <button class="dlbar1-btn" href="#">Download Text Errors Graph</button>
                <button class="dlbar2-btn" href="#">Download Audio Errors Graph</button>
            </div>


        </main>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>                            
    <script src = "scripts/admin.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/chart.js/dist/chart.umd.min.js"></script>

</body>

</html>
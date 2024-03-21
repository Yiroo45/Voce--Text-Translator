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
    <title>FAQs Voce</title>
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
            <span>Text Translator</span>
        </div>
    </nav>
    <!-- End of Navbar -->
    <?php include ("sidebar.php") ?>
    <div class="main-content">

        <div id="loadingModal" class="modalloading">
            <div class="modal-contentloading">
                <img src="images/duckagain.gif" alt="Loading..." />
                <p id="loadingModalMessage">Loading....</p>
            </div>
        </div>
        <div class="header">
            <a href="text-text.php"><button><img src="images/translator.png" alt="Language Icon"
                        width="30px">Text</button></a>
            <a href="index.php"><button><img src="images/music-file.png" alt="Language Icon" width="30px">Upload
                    a File</button></a>
        </div>
        <div class="header-downloadfile-wrapper">
            <div class="header-downloadfile" dir="rtl" id="download-file">
                <?php if (isset ($_SESSION['recent_audio']) && isset ($_GET['translated']) && $_GET['translated'] == 1): ?>
                    <div class="download button" dir="rtl" id="download-file">
                        <form method="post" action="utilities/download_audio_output.php">
                            <button type="submit" name="text" style="padding:5px;">Download as Text File</button>
                            <button type="submit" name="word" style="padding:5px;">Download as Word File</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <p style="color: red;" id="error-message"><i>
                <?php
                if (isset ($_GET['error'])) {
                    switch ($_GET['error']) {
                        case 1: // user did not choose language
                            echo "Please select a model or source/translated language.";
                            break;
                        case 2:  // user did not upload file
                            echo "No File Upload. Please try again.";
                            break;
                        case 3: // user upload wrong file type
                            echo "File Format Not Supported. Please try again.";
                            break;
                        case 4: // user choose two same language
                            echo "Please choose two different language.";
                            break;
                        case 5: // it's what the programmers say, "WHY IS IT NOT WORKING??"
                            echo "Audio File not processed well. Please try again.";
                            break;
                        case 6: // user added unprovided choices
                            echo "Please choose only on the provided models/languages.";
                            break;
                        case 7: // user added unprovided choices
                            echo "Recorded audio cannot be processed. Please contact Voce team.";
                            break;
                    }
                }
                ?>
            </i></p>
        <div class="header-language selector">
            <div class="language-selector-wrapper">
                <div class="language-selector">
                    <label class="label1">
                        Source language:
                    </label>
                    &nbsp&nbsp
                    <?php if (isset ($_SESSION['recent_audio']) && isset ($_GET['translated']) && $_GET['translated'] == 1): ?>
                        <?php $textid = $_SESSION['recent_audio'];
                        $data = mysqli_query($dbcon, "SELECT * FROM text_translations WHERE text_id = '$textid' AND from_audio_file = 1 ORDER BY translation_date DESC LIMIT 1")->fetch_row();
                        echo $data[4] ?? ''; ?>
                    <?php else: ?>
                        <form enctype="multipart/form-data" id="form" action="utilities/audio_translation.php" method="POST"
                            onsubmit="showLoading()">
                            <select name="src" id="sourceLanguage" class="form-control">
                                <!--  Will display Languages supported by API and Whisper -->
                                <option value="auto">Auto-Detect Language...</option>Auto-Detect
                                <?php foreach ($common_langs as $lang => $code): ?>
                                    <option name="language">
                                        <?= $lang ?>
                                    </option>
                                <?php endforeach ?>
                            </select>

                        <?php endif; ?>
                </div>
            </div>
            <div class="language-selector-wrapper">


                <div class="language-selector">
                    <label class="label1">
                        Target language:
                    </label>
                    &nbsp&nbsp
                    <?php if (isset ($_SESSION['recent_audio']) && isset ($_GET['translated']) && $_GET['translated'] == 1): ?>
                        <?php echo $data[5] ?? ''; ?>
                    <?php else: ?>
                        <select name="target" id="targetLanguage" class="form-control">
                            <!-- Will display languages supported by API only-->
                            <option value="">Select One …</option>
                            <?php foreach ($common_langs as $lang => $code): ?>
                                <option name="language">
                                    <?= $lang ?>
                                </option>
                            <?php endforeach ?>
                        </select>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="container" id="drop-zone" ondrop="fileDropHandler(event);" ondragover="dragOverHandler(event);"
            ondragenter="dragEnterHandler(event);" ondragleave="dragLeaveHandler(event);">

            <?php if (isset ($_SESSION['recent_audio']) && isset ($_GET['translated']) && $_GET['translated'] == 1): ?>
                <div class="box">
                    <div class="text-section">
                        <textarea id="originalText" name="originalText" class="customtextfield" rows="4" readonly><?php
                        if (isset ($_GET['translated']) && $_GET['translated'] == 1) {
                            echo $data[6] ?? '';
                        }
                        ?>

                                                                                                </textarea>
                    </div>
                    <!-- former button before updating ui 
                        <button type="submit" id="yourButtonID" class="custom-button">Translate</button> -->
                </div>
                <div class="box2">
                    <div class="text-section">
                        <textarea id="translatedText" name="translatedText" class="customtextfield" rows="4"
                            readonly><?php
                            if (isset ($_GET['translated']) && $_GET['translated'] == 1) {
                                echo $data[7] ?? '';
                            }
                            ?>
                                                                                                                                                                                                                                                                                                                    </textarea>
                    </div>
                </div>
            <?php else: ?>

                <div class="box">
                    <div class="upload-icon">
                        <img src="images/upload.png" alt="Upload Icon">
                        <input class="file-input" type="file" name="user_file" id="fileInputLabel" for="fileInput"><br>
                        <div>
                            <input class="removeBGM" type="checkbox" name="removeBGM">
                            <label for="removeBGM"><span style="font-style: italic; color: red;">*Remove BGM
                                    before translating audio with music.</span></label>
                        </div>


                    </div>

                    <!-- former button before updating ui 
                            <button type="submit" id="yourButtonID" class="custom-button">Translate</button> -->

                </div>
                <div class="box2">
                    <div class="details">
                        <input type="hidden" name="record" />
                        <button type="button" id="mic" class="mic-toggle hovering">
                            <span class="material-symbols-outlined" style="font-size: 150px;">
                                mic
                            </span>
                        </button>
                        <audio class="playback" controls></audio>
                    </div>
                </div>

                <div class="drop-text">Drop files here</div>
            <?php endif; ?>
            <div class="options">
                <a href=""><img src="images/anti-clockwise.png" alt="Language Icon" width="30px"></a>
                <label>History</label>
                <br>
                <a href=""><img src="images/dictionary-icon.png" alt="Language Icon" width="30px"></a>
                <label>Dictionary</label>
            </div>
        </div>
        <?php if (isset ($_SESSION['recent_audio']) && isset ($_GET['translated']) && $_GET['translated'] == 1): ?>
            <a href="index.php"><button class="tryagain">Translate again</button></a>
        <?php else: ?>
            <button type="submit" class="translate-button" id="audio-translate-btn">Translate</button>
        <?php endif; ?>
        </form>

    </div>
    <!-- Live Recording 
                <div class="container">
                    <input type="hidden" name="record" />
                    <button type="button" id="mic" class="mic-toggle">Record Now</button>
                    <audio class="playback" controls></audio>
                </div> -->


    <!-- <div class="container">
                    <div class="wrapper">
                        <header>Transcribe Now</header>


                        <div class="upload-file" id="drop-zone" ondrop="fileDropHandler(event);"
                            ondragover="dragOverHandler(event);" ondragenter="dragEnterHandler(event);"
                            ondragleave="dragLeaveHandler(event);">
                            <center><i class="bx bx-upload"></i></center>
                            <p>Drag and Drop File to Upload</p>
                        </div>
                        <input class="file-input" type="file" name="user_file" id="fileInputLabel" for="fileInput">
                        <input class="removeBGM" type="checkbox" name="removeBGM">
                        <label for="removeBGM">Remove BGM <br> <span style="font-style: italic; color: red;">*Remove
                                BGM
                                before translating audio with music.</span></label>
                        <br><span style="font-style: italic; color: red;">*Only a maximum of 60MB file is
                            accepted.</span>
                    </div> 



                    <button type="submit" id="yourButtonID" class="custom-button">Translate</button> -->


    <?php if (isset ($_SESSION['recent_audio']) && isset ($_GET['translated']) && $_GET['translated'] == 1): ?>
        <div class="download button" dir="rtl" id="download-file">
            <form method="post" action="utilities/download_audio_output.php">
                <button type="submit" name="text" style="padding:5px;">Download as Text File</button>
                <button type="submit" name="word" style="padding:5px;">Download as Word File</button>
            </form>
        </div>
    <?php endif; ?>

    <br>
    
    <nav>
        <div class="footer">
        <div>
            <a href="faqs.php">FAQs</a>
            <a href="about.php">About Voce</a>
        </div>
        <div >
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
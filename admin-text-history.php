<?php require("mysql/mysqli_session.php"); 
$current_page = basename($_SERVER['PHP_SELF']);



?>
<?php if (!isset($_SESSION['username'])) {
    
  header("location: index.php");
  exit(); 
}?>

<?php


function dd($item){
    var_dump($item);
    exit();
}

require "utilities/Translator_Functions.php";

$q = "SELECT user_id, username, email, registration_date, type FROM users ORDER BY user_id ASC";
$users = mysqli_query($dbcon, $q);
// get session id

$id = is_array($_SESSION['user_id']) ? $_SESSION['user_id']['user_id'] : $_SESSION['user_id'];


// Translation history for text to text 
$history = mysqli_query($dbcon, "SELECT * FROM text_translations WHERE from_audio_file = 0 ORDER BY translation_date DESC");


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="styles/style2.css">
    <link rel = "stylesheet" href = "styles/user-table.css">
    <link rel="stylesheet" href="styles/simplePagination.css">
    <title>Admin - Users</title>
    <link rel="icon" type="image/x-icon" href="images/icon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">





</head>

<!-- Confirm delete window -->
<div class = "delete-window">
    <div class = "confirm-div">
        <h4 class = "confirm-text"></h4>
        <div class = "confirm-btn-div">
            <button class = "confirm-btn confirm-yes">Yes</button>
            <button class = "confirm-btn confirm-no">No</button>
        </div>
    </div>
</div>


<body>

    <!-- Sidebar -->
    <?php require "sidebar.php"?>

    <!-- Main Content -->
    <div class="content">
        <!-- Navbar -->
        <nav>
            <form id = "search-form">
            <i class='bx bx-menu'></i><span id = "nav-name"><?= $_SESSION['username']; ?></span>
                        <input id = "search-user" type="text" placeholder="Search User.." name="search">
            </form>
        </nav>

        <!-- End of Navbar -->

        <main style = "padding: 0;">
        <div class = "table-container">
            <!-- Truncate Text -->
            <div id="myModal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <p id="modalText"></p>
                </div>
            </div>
            
                <div class="orders">
                    <div class = "deleteAllClass">
                        <button type = 'button' class = "deleteSelectedRows" id = "t2t">Delete Selected Rows</button>
                        <button type = 'button' class = "deleteRows-btn" id = "t2t">Select Rows</button>
                        <button type = 'button' class = "deleteAll-btn" id = "t2t">Delete All</button>
                    </div>
                    <div class="header">
                        <h2>Recent Text to Text Translations</h3>
                    </div>
                </div>
                <table class = "users-table">
                <thead>
                            <tr>
                            <th>Id</th>
                            <th>Source Language</th>
                            <th>Original Text</th>
                            <th>Target Language</th>
                            <th>Translated Text</th>
                            <th>Translation Date</th>    
                            <th colspan=2>Delete</th>    
                            </tr>
                        </thead>
                        <tbody class = "history-body">
                        <!-- Displays audio to text history -->
                        <?php Translator::displayHistory($history, "text2text")?>
                        </tbody>
                </table>
                <br />
                
                <div id="page-nav-content">
                    <div id="page-nav"></div>
                </div>

        </div>

    </div>

    <!-- for an in-depth walkthrough for pagination, please visit https://bilalakil.me/simplepagination -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/simplePagination.js/1.4/jquery.simplePagination.min.js" integrity="sha512-J4OD+6Nca5l8HwpKlxiZZ5iF79e9sgRGSf0GxLsL1W55HHdg48AEiKCXqvQCNtA1NOMOVrw15DXnVuPpBm2mPg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="scripts/index.js"></script>
    <script src="scripts/delete.js"></script>
    <script src="scripts/translation_process.js"></script>
</body>

</html>
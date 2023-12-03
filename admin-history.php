<?php require("mysql/mysqli_session.php"); 
$current_page = basename($_SERVER['PHP_SELF']);
?>

<?php if (!isset($_SESSION['username'])) {
        header("location: loginpage.php");
        exit();
} ?>   

<?php
require "utilities/Translator_Functions.php";

//  Get language codes for each language
$languages = Translator::getLangCodes();
$lang_codes = [];

// get session id
$id = is_array($_SESSION['user_id']) ? $_SESSION['user_id']['user_id'] : $_SESSION['user_id'];

// Translation history for text to text 
$history = mysqli_query($dbcon, "SELECT * FROM text_translations WHERE user_id = $id AND from_audio_file = 0 ORDER BY translation_date DESC");

foreach($languages as $language){
  $lang_codes[$language["name"]] = $language["code"];
}
// Language Translation, please check https://rapidapi.com/dickyagustin/api/text-translator2 for more information.

// Translate text input
if($_SERVER["REQUEST_METHOD"] == "POST"){ 

    // Error Handling first before translation 
    if ($_POST["src"] == "" || $_POST['target'] == "") {
        // error, user did not choose language
        logs("error-tt", $_SESSION['username'], $dbcon);
        header("Location: text-text.php?error=1");
        exit();
        
    } 
    if (empty(trim($_POST['text']))) {
        // error, user did not type anything
        logs("error-tt", $_SESSION['username'], $dbcon);
        header("Location: text-text.php?error=2");
        exit();
    }
    
  // translates text, get output
  $translation = Translator::translate($_POST["text"], 
      $lang_codes[$_POST["src"]], 
      $lang_codes[$_POST["target"]]
  );

  // insert into database
  $source_lang = $_POST['src'];
  $target_lang = $_POST['target'];
  $orig_text = $_POST["text"];
  $isFromAudio = False;
  
  // db query
  $query_insert = mysqli_prepare($dbcon, "INSERT INTO text_translations(user_id, from_audio_file, original_language, translated_language,
  translate_from, translate_to, translation_date) VALUES (?, ?, ?, ?, ?, ?, NOW())");
  mysqli_stmt_bind_param($query_insert, 'iissss', $id, $isFromAudio, $source_lang, $target_lang, $orig_text, $translation);
  mysqli_stmt_execute($query_insert);

  logs("text-to-text", $_SESSION['username'], $dbcon);

  
  header("Location: text-text.php?translated=1");
  exit();
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="styles/style2.css">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://kit.fontawesome.com/yourcode.js" crossorigin="anonymous"></script>


</head>
<style> 
.pagination{
    text-align: center;
    margin-left: 600px;
    margin-top: -75px;
    display: flex;
    color: #383838;
    border-radius: 6px;
}
.pagination-list{
    margin: 20px 30px;
}
.pagination-list li{
    display: inline-block;
    margin: 0 10px;
    width: 45px;
    height: 45px;
    border-radius: 50%;
    text-align: center;
    font-size: 22px;
    font-weight: 500;
    line-height: 45px;
    cursor: pointer;
    background-position: 0 -45px;
    transition: background-position 0.5s;
}
.pagination-list li.active{
    color: #fff;
    background-image: linear-gradient(#ff4568,#ff4568);
    background-repeat: no-repeat;
    background-position: 0 0;
}
.btn1, .btn2{
    display: inline-flex;
    align-items: center;
    font-size: 22px;
    font-weight: 500;
    color: #383838;
    background: transparent;
    outline: none;
    border: none;
    cursor: pointer;
}
</style>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="dashboard1.php" class="logo">
            <i class="fa fa-microphone"></i>
            <div class="logo-name"><span>Vo</span>CE</div>
        </a>
        <ul class="side-menu">
            <li><a href="admin-dashboard.php"><i class='bx bxs-dashboard'></i>Dashboard</a></li>
            <li class="<?php echo ($current_page === 'text-text.php') ? 'active' : ''; ?>">
            <a href="admin-users.php"><img src="images/sidebartext.png" alt="scroll icon" width="25" height="25" style="margin-left: 5px;">
            &nbsp Users</a> 
                 

            <li class="<?php echo ($current_page === 'history_audio.php') ? 'active' : ''; ?>">
            <a href="admin-history.php"><img src="images/sidebaraudio.png" alt="scroll icon" width="25" height="25" style="margin-left: 5px;">
            &nbsp History</a> 
            <li><a href="#"><i class='bx bx-cog'></i>Settings</a></li>
        </ul>
        <ul class="side-menu">
            <li>
                <a href="logout.php" class="logout">
                    <i class='bx bx-log-out-circle'></i>
                    Logout
                </a>
            </li>
        </ul>
    </div>
    
    <!-- End of Sidebar -->

    <!-- Main Content -->
    <div class="content">
        <!-- Navbar -->
        <nav>
            <i class='bx bx-menu'></i>
            <form action="#">
                <div class="form-input">
                    <input type="search" placeholder="Search...">
                    <button class="search-btn" type="submit"><i class='bx bx-search'></i></button>
                </div>
            </form>
            <input type="checkbox" id="theme-toggle" hidden>
            <label for="theme-toggle" class="theme-toggle"></label>
            <a href="#" class="notif">
                <i class='bx bx-bell'></i>
                <span class="count">12</span>
            </a>
            <a href="#" class="profile">
                <img src="images/logo.png">
            </a>
        </nav>

        <!-- End of Navbar -->

        <main>
           
            <div class="header">
                <div class="left">
                    <h1>
                    User History
                    </h1>


                    <!-- Error Message: Pabago nalang if may naiisip kang ibang design -->
                    <p style="color: red;"><i>
                    <?php
                        if (isset($_GET['error']) && $_GET['error'] == 1) {
                            echo "Please select a source/translated language.";
                        }
                        if (isset($_GET['error']) && $_GET['error'] == 2) {
                            echo "Please type text to be translated.";
                        }
                    ?> 
                    </i></p>

                    
                </div>
            </div>
         

            <!-- Insights -->

            <!-- End of Insights -->
            <div class="bottom-data">

                <!-- Table -->
                <div class="orders">
                    <div class="header">
                        <h2>Recent Text to Text Translations</h2>
                        <br>
                    </div>
                    <table>
                        <thead>
                            <tr>
                            <th>Original Text</th>
                            <th>Source Language</th>
                            <th>Translated Text</th>
                            <th>Target Language</th>
                            <th>Translation Date</th>  
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($row = mysqli_fetch_assoc($history)) : ?>
                            <tr>
                            <td><?= $row['translate_from'] ?></td>
                            <td><?= $row['original_language'] ?></td>
                            <td><?= $row['translate_to']?></td>
                            <td><?= $row['translated_language']?></td>
                            <td><?= $row['translation_date']?></td>

                            </tr>
                        <?php endwhile ?>
                        </tbody>
                <!-- End of Reminders-->

            </div>
                </table>
                </div>
            

        </main>
        <div class="pagination">
        <button class="btn1" onclick="backBtn()">prev</button>
            <ul class="pagination-list">
                <li class="link active" value="1" onclick="activeLink()">1</li>
                <li class="link" value="2" onclick="activeLink()">2</li>
                <li class="link" value="3" onclick="activeLink()">3</li>
                <li class="link" value="4" onclick="activeLink()">4</li>
                <li class="link" value="5" onclick="activeLink()">5</li>
                <li class="link" value="6" onclick="activeLink()">...</li>
            </ul>
        <button class="btn1" onclick="nextBtn()">next</button>
    </div>
    </div>

<script src="scripts/index.js"></script>
<script>
    let links = document.getElementsByClassName("link");
    let currentValue = 1;
    let maxRows = 10;
    
    function activeLink() {
        for (let l of links) {
            l.classList.remove("active");
        }

        event.target.classList.add("active");
        currentValue = event.target.value;
        showRows();
    }

    function backBtn() {
        if (currentValue > 1) {
            for (let l of links) {
                l.classList.remove("active");
            }
            currentValue--;
            links[currentValue - 1].classList.add("active");
            showRows();
        }
    }

    function nextBtn() {
        if (currentValue < 6) {
            for (let l of links) {
                l.classList.remove("active");
            }
            currentValue++;
            links[currentValue - 1].classList.add("active");
            showRows();
        }
    }

    function showRows() {
        let start = (currentValue - 1) * maxRows;
        let end = start + maxRows;

        for (let i = 0; i < rows.length; i++) {
            if (i >= start && i < end) {
                rows[i].style.display = "";
            } else {
                rows[i].style.display = "none";
            }
        }
    }

    // Initialize the table on page load
    showRows();
</script>
</body>

</html>
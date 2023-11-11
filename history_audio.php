<?php require("mysql/mysqli_session.php"); 
$current_page = basename($_SERVER['PHP_SELF']);
?>
<?php require "translation.php" 
?>

<?php require "translator_functions.php" ?>
<?php if (!isset($_SESSION['username'])) {
    
  header("location: index.php");
  exit(); 
}?>

<?php 
$id = is_array($_SESSION['user_id']) ? $_SESSION['user_id']['user_id'] : $_SESSION['user_id'];

// Translation history for text to text 
$history = mysqli_query($dbcon, "SELECT * FROM text_translations t INNER JOIN audio_files a ON t.file_id = a.file_id WHERE t.user_id = $id AND a.user_id = $id AND t.from_audio_file = 1 ORDER BY translation_date DESC");
foreach($languages as $language){
  $lang_codes[$language["name"]] = $language["code"];
}
// Language Translation, please check https://rapidapi.com/dickyagustin/api/text-translator2 for more information.

// Translate text input
if($_SERVER["REQUEST_METHOD"] == "POST"){

  $source_lang = $_POST['src'];
  $target_lang = $_POST['target'];
  $isFromAudio = TRUE;
  
  
  
  $file_name = $_FILES['user_file']['name'];
  $file_size = round(filesize('audio_files/' . $file_name)/1000000, 2);
  $file_format =  pathinfo('audio_files/' . $file_name, PATHINFO_EXTENSION);
  $query_insert2 = mysqli_prepare($dbcon, "INSERT INTO audio_files(user_id, file_name, file_size, file_format,
  upload_date) VALUES (?, ?, ?, ?, NOW())");
  mysqli_stmt_bind_param($query_insert2, 'isss', $id, $file_name, $file_size, $file_format);
  mysqli_stmt_execute($query_insert2);


  $get_fileid = "SELECT file_id FROM audio_files WHERE user_id = '$id' ORDER BY file_id DESC LIMIT 1";
  $fileresult = mysqli_query($dbcon, $get_fileid);
  $row = mysqli_fetch_assoc($fileresult);

  $query_insert1 = mysqli_prepare($dbcon, "INSERT INTO text_translations(file_id, user_id, from_audio_file, original_language, translated_language,
  translate_from, translate_to, translation_date) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
  mysqli_stmt_bind_param($query_insert1, 'iiissss', $row['file_id'], $id, $isFromAudio, $source_lang, $target_lang, $transcript, $result);
  mysqli_stmt_execute($query_insert1);




  /*
  mysqli_query($dbcon, "INSERT INTO text_translations(user_id, from_audio_file, original_language, translated_language,
  translate_from, translate_to) VALUES 
  ('$id',
   '$isFromAudio',
  '$source_lang', 
  '$target_lang',
  '$orig_text', '$translation')");*/

  logs("audio-to-text", $_SESSION['username'], $dbcon);
  header("Location: history_audio.php?translated=1");
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





</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="dashboard1.php" class="logo">
            <i class="fa fa-microphone"></i>
            <div class="logo-name"><span>Vo</span>CE</div>
        </a>
        <ul class="side-menu">
            <li><a href="dashboard1.php"><i class='bx bxs-dashboard'></i>Dashboard</a></li>
            
            <li><a href="text-text.php"><img src="images/sidebartext.png" alt="scroll icon" width="25" height="25" style="margin-left: 5px;">
            &nbsp Text-Text</a></li>        

            <li class="<?php echo ($current_page === 'history_audio.php') ? 'active' : ''; ?>">
            <a href="history_audio.php"><img src="images/sidebaraudio.png" alt="scroll icon" width="25" height="25" style="margin-left: 5px;">
            &nbsp Audio to Text</a>  
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
        </nav>

        <!-- End of Navbar -->

        <main>

    
            <div class="header">
                <div class="left">
                    <h1>Audio Transcriber</h1>
                  
                    <!-- Error Message: Pabago nalang if may naiisip kang ibang design -->
                    <p style="color: red;"><i>
                    <?php
                        if (isset($_GET['error']) && $_GET['error'] == 1) { // user did not choose language
                            echo "Please select a source/translated language.";
                        }
                        if (isset($_GET['error']) && $_GET['error'] == 2) { // user did not upload file
                            echo "No File Upload. Please try again.";
                        }
                        if (isset($_GET['error']) && $_GET['error'] == 3) { // user upload wrong file
                            echo "Invalid File Format. Please try again.";
                        }
                    ?> 
                    </i></p>
                </div>
            </div>
            <form enctype="multipart/form-data" action = "history_audio.php" method = "POST" onsubmit="showLoading()">
			<label>
			Source language:
			<select name="src" id="sourceLanguage" class="form-control">
				<option value="">Select One …</option>
				<?php foreach($languages as $language): ?>
					<option name = "language"><?= $language["name"]?></option>
				<?php endforeach ?> 	
			</select>
			</label>
            <br>
			<label>
			Target language:
			<select name="target" id="targetLanguage" class="form-control">
				<option value="">Select One …</option>
				<?php foreach($languages as $language): ?>
					<option name = "language"><?= $language["name"]?></option>
				<?php endforeach ?>
			</select>
			</label><br><br>
                    
	
           <div class="container">
            <div class="wrapper">
    <header>Transcribe Now</header>
    
    
        <div class="upload-file">          
      <center><i class="bx bx-upload"></i></center>
      <p>Browse File to Upload</p>
                </div>
      <input class="file-input" type="file" name="user_file" id="fileInputLabel" for="fileInput">
      <!-- accepts only Supported formats: ['m4a', 'mp3', 'webm', 'mp4', 'mpga', 'wav', 'mpeg'] -->
  </div>
 


<button type = "submit" id="yourButtonID" class="custom-button" disabled>Translate</button>

<!-- Loading Div -->
<!-- <div id="loading" class="hidden">
<div class="loader"></div>
        <p>Loading...</p>
    </div> -->
</form>
  <div class="text-section">
        <header>Original text:</header>
        <textarea id="originalText" name="originalText" class="customtextfield" rows="4" readonly><?php
            $data = mysqli_query($dbcon, "SELECT * FROM text_translations WHERE user_id = $id AND from_audio_file = 1 ORDER BY translation_date DESC LIMIT 1")->fetch_row();
            if (isset($_GET['translated']) && $_GET['translated'] == 1) {
                echo $data[6] ?? '';
            }
            ?>
        </textarea>

        <header>Translated text:</header>
        <textarea id="translatedText" name="translatedText" class="customtextfield" rows="4" readonly>`<?php
            if (isset($_GET['translated']) && $_GET['translated'] == 1) {
                echo $data[7] ?? '';
            }
            ?>
        </textarea>
               
             </div>
        </div>
      
<br>


            <div class="bottom-data">
                <div class="orders">
                    <div class="header">
                        <h2>Recent Audio to Text Translations</h3>
                        <i class='bx bx-filter'></i>
                        <i class='bx bx-search'></i>
                    </div>
                    <table>
                        <thead>
                            <tr>
                            <th>File Name</th>
                            <th>File Type</th>
                            <th>File Size</th>  
                            <th>Original Text</th>
                            <th>Source Language</th>
                            <th>Translated Text</th>
                            <th>Target Language</th>
                            <th>Translation Date</th>    
                            <th>Delete</th>    
                            </tr>
                        </thead>
                        <tbody>
                        <!-- Displays audio to text history -->
                        <?php Translator::displayHistory($history, "audio2text")?>
                            <!-- Add more rows for additional files -->
                        </tbody>
                    </table>

                </div>

            </div>

        </main>

    </div>
                                
    <script src="scripts/index.js"></script>
</body>

</html>
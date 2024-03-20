<?php
 require("mysql/mysqli_connect.php"); 
 require("utilities/delete_files.php");

 
if($_POST['clearAll'] == 'true'){
    // Deletes all rows corresponding to user from audio_files table if it's an audio to text translation
    if($_POST['fromAudio'] == 1){

        $deleteQuery = mysqli_prepare($dbcon, "DELETE FROM text_translations WHERE from_audio_file = ?");
        bindAndExec($deleteQuery, "i", $_POST['fromAudio']);

        $deleteQuery = mysqli_prepare($dbcon, "DELETE FROM audio_files");
        mysqli_stmt_execute($deleteQuery);


        deleteAllAudioFiles();
        
    }
    else{            
        // Deletes all rows from text_translations table where from_audio_file is 0
        $deleteQuery = mysqli_prepare($dbcon, "DELETE FROM text_translations WHERE from_audio_file = ?");
        bindAndExec($deleteQuery, "i", $_POST['fromAudio']);
    }
 }

 elseif($_POST['deleteRows'] == 'true'){

    if($_POST['fromAudio'] == 1){
        $rowsToDelete = json_decode($_POST['rowsToDelete']);
        $filesToDelete = json_decode($_POST['filesToDelete']);
        for($i = 0; $i < count($rowsToDelete); $i++){
                $deleteRows = $rowsToDelete[$i];
                $deleteFiles =$filesToDelete[$i];
                

                deleteAudioFile($deleteFiles);

                
                $deleteQuery = mysqli_prepare($dbcon, "DELETE FROM text_translations WHERE text_id = ?");
                bindAndExec($deleteQuery, "s", $deleteRows);

                $deleteQuery = mysqli_prepare($dbcon, "DELETE FROM audio_files WHERE file_id = ?");
                bindAndExec($deleteQuery, "s", $deleteFiles);

                
            }
    }

    else{
        foreach(json_decode($_POST['rowsToDelete']) as $rowNum){
            $deleteQuery = mysqli_prepare($dbcon, "DELETE FROM text_translations WHERE text_id = ?");
            bindAndExec($deleteQuery, "s", $rowNum);        
        }
    }
    
 }

 else{
    //deletes row from database

    $rowId = $_POST['rowId'];
    $fileId = $_POST['fileId'];
    
    $deleteQuery = mysqli_prepare($dbcon, "DELETE FROM text_translations WHERE text_id = ?");
    bindAndExec($deleteQuery, "s", $rowId); 

    //deletes audio file record from database if it's an audio to text translation
    if($_POST['fileId'] != "null"){
        
        deleteAudioFile($fileId);
        
        $deleteQuery = mysqli_prepare($dbcon, "DELETE FROM audio_files WHERE file_id = ?");
        bindAndExec($deleteQuery, "s", $fileId); 
        
    }
 }


//query for text to text history
if($_POST['fromAudio'] == 0)
    $q = "SELECT * FROM text_translations WHERE from_audio_file = 0 ORDER BY translation_date DESC";
//query for audio to text history
else
    $q = "SELECT * FROM text_translations t INNER JOIN audio_files a ON t.file_id = a.file_id WHERE from_audio_file = 1 AND t.from_audio_file = 1 ORDER BY translation_date DESC";

$sql = mysqli_query($dbcon, $q);
$result = mysqli_fetch_all($sql, MYSQLI_ASSOC);

exit(json_encode($result));

function bindAndExec($stmt, $markers, $value){
    mysqli_stmt_bind_param($stmt, $markers, $value);
    mysqli_stmt_execute($stmt);
}


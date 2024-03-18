<?php 

require("error_handling.php");

class Translator{
    static function get_meanings($word){
        $data = [
            "word" => $word
        ];
    
        $json_data = json_encode($data);

        $url = "http://localhost:5000/get_meanings";
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_close($ch);

        // Error handling
        if (!$response = curl_exec($ch)) {
            throw new Exception(curl_error($ch)); // More informative error message
        }
        // Check for JSON decoding errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response");
        }    

        return $response;
    }
    
    static function db_insertAudioFile($path, $pathsize) {
        global $dbcon;
        // prepare file id, filename, filesize, fileformat
        $file_name = $path;
        $file_size = round($pathsize / 1024 / 1024, 2);
            //$file_size = round(filesize('audio_files/' . $file_name)/1000000, 2);
        $file_format =  pathinfo('../audio_files/' . $file_name, PATHINFO_EXTENSION);
        $is_recorded = false;
        // insert audio file into database
          $query_insert2 = mysqli_prepare($dbcon, "INSERT INTO audio_files(file_name, is_recorded, file_size, file_format,
          upload_date) VALUES (?, ?, ?, ?, NOW())");
    
        // execute the query
          mysqli_stmt_bind_param($query_insert2, 'siss', $file_name, $is_recorded, $file_size, $file_format);
          mysqli_stmt_execute($query_insert2);
    }

    static function db_uploadRecordFile($record) {
        global $dbcon;
        // we will only upload the filename created and the file format, we upload since we need the upload date

        // prepare convert audio blob
        $is_recorded = true;
        $temp = "record" . (time() . rand(10000, 99999)) . ".webm";
        
        // temporarily save the audio file
        //file_put_contents("../audio_files/" . $temp, $record);
        //move_uploaded_file( $_FILES['user_file']['tmp_name'],$pathto) or die(ErrorHandling::audioError2());
        
        // prepare file id, filename, filesize, fileformat
        $file_name = $temp;
        $file_size = round($_FILES['record']['size'] / 1024 / 1024, 2);
        $file_format =  pathinfo($temp, PATHINFO_EXTENSION);

        // temporary save in database
          $query_insert2 = mysqli_prepare($dbcon, "INSERT INTO audio_files(file_name, is_recorded, file_size, file_format,
          upload_date) VALUES (?, ?, ?, ?, NOW())");
    
        // execute the query
          mysqli_stmt_bind_param($query_insert2, 'siss', $file_name, $is_recorded, $file_size, $file_format);
          mysqli_stmt_execute($query_insert2);

          return $temp;
    }
    
    static function createNewFilename($path, $is_recorded) {
        global $dbcon;
        // create a new filename with format
        $filename = pathinfo($path, PATHINFO_FILENAME);
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $path = mysqli_real_escape_string($dbcon, $path);

        // get the date of the file from db
        $datequery = "SELECT file_id, DATE_FORMAT(upload_date, '%m%d%Y_%H%i%s') AS formatted_date 
                        FROM audio_files WHERE file_name = '$path' ORDER BY file_id DESC LIMIT 1";
        $dateresult = mysqli_query($dbcon, $datequery);
        $row = mysqli_fetch_assoc($dateresult);
        // create an error handler where in if $datequery possibly resulted into two result
        if (!$is_recorded) {
    
            $newFilename = $row['file_id'] . "_" . $filename . $row['formatted_date'];
            $newFile = $newFilename . "." . $extension;
            
            // audio files folder
            $pathto = "../audio_files/" . $newFile;

            move_uploaded_file($_FILES['user_file']['tmp_name'],$pathto) or die(ErrorHandling::audioError2());
            return $newFile;

        } 
        else {
            
            $newFilename = $row['file_id'] . "_" . "record" . $row['formatted_date'];
            $newFile = $newFilename . "." . $extension;
            
            // audio files folder
            $pathto = "../audio_files/" . $newFile;
            move_uploaded_file($_FILES['record']['tmp_name'], $pathto) or die(ErrorHandling::audioError4());
            

            return $newFile;
        }
  
    }

    static function createNewFolder($filename) {
        // if audio file will not be processed in extracting vocals
        //  create a folder to place the new file with removed audio

        $filename = pathinfo($filename, PATHINFO_FILENAME);
        $directory = "../audio_files/" . $filename . "/";
        if(!is_dir($directory)){
			mkdir($directory, 0777, true);
		}
    }

    static function removeSilence($inputFile, $removeBGM) {
        // if removeBGM is on, do not make a folder, else, create a new folder
        $filename = pathinfo($inputFile, PATHINFO_FILENAME);

        $data = [
            "file" => $inputFile,           // file with extension
            "filename" => $filename,        // filename only
            "removeBGM" => $removeBGM       // ON or OFF
        ];
    
        $json_data = json_encode($data);
        $url = "http://localhost:5000/removesilence";
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Execute api call
        curl_exec($ch);

        curl_close($ch);

        /*if ($removeBGM == "on") { // we will use vocals.wav to remove silence instead
            $output = shell_exec("cd .. && ffmpeg -y -i " . escapeshellarg("audio_files/" . $filename . "/vocals.wav") . 
            " -af  silenceremove=stop_periods=-1:stop_duration=1:stop_threshold=-50dB " . escapeshellarg("audio_files/" . $filename . "/audio_processed.mp3"));
        
        } 
        else {                    // we will use the original file since spleeter isn't used
            $output = shell_exec("cd .. && ffmpeg -y -i " . escapeshellarg("audio_files/". $inputFile) . 
            " -af  silenceremove=stop_periods=-1:stop_duration=1:stop_threshold=-50dB " . escapeshellarg("audio_files/" . $filename . "/audio_processed.mp3"));
        }*/
    }

    static function uploadAndTranscribe($newFile, $removeBGM, $src_lang){

        global $dbcon;      

        $filename = pathinfo($newFile, PATHINFO_FILENAME);
        $extension = pathinfo($newFile, PATHINFO_EXTENSION);
        
                /* make sure to go to php.ini in xampp (config > php.ini) 
                 and set max_execution_time into 600 [10 minutes] or higher (write in seconds), for longer processing
                 you only need to pass the name of file as argument for translation (file extension not needed)
                */
                $data = [
                    "fname" => $filename,
                    "ext" => $extension,
                    "removeBG" => $removeBGM,
                    "src" => $src_lang
                ];
            
                $json_data = json_encode($data);
        
                $url = "http://localhost:5000/transcribe";
            
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_close($ch);
        
                // Error handling - if no response received
                if (!$response = curl_exec($ch)) {
                    throw new Exception(curl_error($ch)); // More informative error message
                }
                
                // Error handling - if invalid json
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception("Invalid JSON response");
                }    

                // Error handling - if text is empty
                $output = json_decode($response, true);
                if ($output["text"]) {
                    return $output;
                } else {
                    ErrorHandling::audioError3($newFile); // pass the filename so it can be deleted, since it's not processed
                }

                //return  json_decode($response, true);
        
                // replace single quotes around language codes and fields with double quotes
                // $outputString = preg_replace('/(?<!\w)\'(.*?)\'/', '"$1"', $outputString);

                //$outputString = preg_replace('/\'(.*?)\'/', '"$1"', $outputString);
                // $output = json_decode($outputString, true);
                
                // if (!isset($output['text'])) { exit(json_encode(['error' => var_dump($outputString)])); }

                // if ($output["text"]) {
                //     return $output;
                //     // the array will be returned so both text and language can be accessed
                // } else {
                //     ErrorHandling::audioError3($newFile); // pass the filename so it can be deleted, since it's not processed
                // }
    }

    static function getVocals($file) {
        # Activate the virtual environment
        #   use spleeter for extracting vocals,
        #   and pass the file as argument 
        #   then, deactivate virtual environment
        
        #   code for Python 3.8 system
        # $output = shell_exec("cd .. && python scripts/separate.py " . escapeshellarg($file) . ");
        
        #   code for Python 3.11 system with py3.8 spleeter_env virtual env
        $output = shell_exec("cd .. && spleeter_env\\Scripts\\activate && python scripts/separate.py " . escapeshellarg($file) . " && deactivate");
       
        # Contains API call on Spleeter Instead
        /*$data = ["file" => $file];
    
        $json_data = json_encode($data);
        $url = "http://localhost:5000/spleeter";
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Execute api call
        curl_exec($ch);

        curl_close($ch);
        */

    }

    //IMPORTANT! $history should contain the query result 
    // translation format should either be text2text
    static function displayHistory($history, $translation_format){
        // Displays text to text history  or audio2text
        if($translation_format == "text2text"){
            while($row = mysqli_fetch_assoc($history)){
                echo               
                "<tr id = ". $row['text_id'] ." class = '" . "t2t" . " paginate" . "'>" .
                "<td class = 'user-td'>" .$row['text_id'] . "</td>" . 
                "<td class = 'user-td'>" .$row['original_language'] . "</td>" .
                "<td class = 'user-td truncate-text'>" .$row['translate_from'] . "</td>" . 
                "<td class = 'user-td'>" .$row['translated_language'] . "</td>" . 
                "<td class = 'user-td truncate-text'>" .$row['translate_to'] . "</td>" .
                "<td class = 'user-td'>" .$row['translation_date'] . "</td>" .  
                "<td class = 'user-td'>"."<button type = 'button' class = 'delete-btn'>Delete</button></td>" .  
                "<td class = 'user-td'>" . "<input type = 'checkbox' class = 'delete-checkbox' id = ". $row['text_id'] ."></td>"   
                . "</tr>";

            }
        }
        // Displays audio to text history
        elseif($translation_format == "audio2text"){
            while($row = mysqli_fetch_assoc($history)){
                echo               
                "<tr id = ". $row['text_id'] ." class = '" . "a2t". " " . $row['file_id'] .  " paginate" . "'>" .
                "<td class = 'user-td'>" .$row['text_id'] . "</td>" .
                "<td class = 'user-td break-word'>" .$row['file_name'] . "</td>" . 
                "<td class = 'user-td'>" .$row['original_language'] . "</td>" . 
                "<td class = 'user-td truncate-text'>" .$row['translate_from'] . "</td>" . 
                "<td class = 'user-td'>" .$row['translated_language'] . "</td>" .
                "<td class = 'user-td truncate-text'>" .$row['translate_to'] . "</td>" .
                "<td class = 'user-td'>" .$row['translation_date'] . "</td>" . 
                "<td class = 'user-td'>" . "<button type = 'button' class = 'delete-btn'>Delete</button></td>" .
                "<td class = 'user-td'>" . "<input type = 'checkbox' class = 'delete-checkbox' id = ". $row['text_id'] ."></td>"   
                . "</tr>";

            }
        }

        else {
            while($row = mysqli_fetch_assoc($history)){
                echo               
                "<tr id = ". $row['text_id'] ." class = '" . $row['file_id'] .  " paginate" . "'>" .
                    "<td class='user-td'>" .$row['text_id'] . "</td>" . 
                    "<td class='user-td break-word'>" .$row['file_name'] . "</td>" . 
                    "<td class='user-td'>" .$row['original_language'] . "</td>" . 
                    "<td class='user-td truncate-text'>" .$row['translate_from'] . "</td>" . 
                    "<td class='user-td'>" .$row['translated_language'] . "</td>" .
                    "<td class='user-td truncate-text'>" .$row['translate_to'] . "</td>" .
                    "<td class='user-td'>" .$row['translation_date'] . "</td>" . 
                    "<td class='user-td'>" . "<button type = 'button' class = 'delete-btn'>Delete</button></td>" .
                    "<td class='user-td'>" . "<input type = 'checkbox' class = 'delete-checkbox' id = ". $row['text_id'] ."></td>"  .
                "</tr>";

            }
        }
    }

    static function getLangCodes(){
        $url = "http://localhost:5000/getlangcodes"; //Python endpoint for language codes.
        $data = ["key1" => "value1", "key2" => "value2"];        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);        
        $response = curl_exec($ch);
        // Close the cURL session
        curl_close($ch);

        // Process the response
        if ($response === false) {
            header("Location: service-unavailable.php");
            exit();
        } else {
            // Decode the JSON response
            $data = json_decode($response, true);

            // Access and use the retrieved data
            return $data;
        }
    }

    static function translate($input = '', $src = '', $target = '') {
        
        $data = [
            "txt" => $input,
            "src" => $src,
            "trg" => $target
        ];
    
        $json_data = json_encode($data);


        $url = "http://localhost:5000/translate";
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_close($ch);

        // Error handling
        if (!$response = curl_exec($ch)) {
            throw new Exception(curl_error($ch)); // More informative error message
        }
        // Check for JSON decoding errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response");
        }    

        return $response;
    }

    static function checkConnection() {
        $url = "http://localhost:5000/testcon"; //Python endpoint for language codes.
        $data = ["key1" => "value1", "key2" => "value2"];        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);        
        $response = curl_exec($ch);
        // Close the cURL session
        curl_close($ch);

        // Process the response, bring user back to index if connection found
        if ($response != false  && $response == "connected") {
            header("Location: index.php");
            exit();
        }
    }
}
?>
// contains the record button and playback audio
const mic_btn = document.querySelector('#mic');
const playback = document.querySelector(".playback");


mic_btn.addEventListener("click", ToggleMic);



let is_recording = false;
let recorder = null;

// store anything we've record in segments in an array 
//  to confine into blob latur on
let chunks = []; 

let silenceTimeout; // Timer for silence detection

// FUNCTION - When user clicks the record button
function ToggleMic() {
    // change the status of recording, if it's recording, stop it
    is_recording = !is_recording;

    if (is_recording) {
        console.log('start');

        // Function to request microphone permission
        const getMicrophonePermission = async () => {
            try {
            const stream = await navigator.mediaDevices
                .getUserMedia({ audio: true })
                .then(setupStream); // set up the recording once permission is granted
            } catch (error) {
            console.error('Microphone access denied:', error);
            is_recording = !is_recording;   // Revert recording status if permission denied
            }
        };
        
        // Call the function to request permission
        
        getMicrophonePermission();
    } else {
        console.log('stop2');
        recorder.stop();
    }
}

// FUNCTION - permission is granted, record the audio through microphone
function setupStream(stream) {

    

    recorder = new MediaRecorder(stream);

    console.log('setup');
    
    // RECORDER START ---------------
    recorder.start();

    // AUDIO DATA AVAILABLE ---------------
    //  this is gonna create a chunk of data so often 
    //  that we can push them into chunks of array to be turned into blob
    recorder.ondataavailable = e => {
        chunks.push(e.data);
    }
    
    // RECORDER STOP ---------------
    // when we stop recording we can create a blob from the chunks
    // audio ogg = format
    // codecs=opus = compression
    recorder.onstop = e => {
        console.log('stopped');
        const blob = new Blob(chunks, { type: "audio/mpeg; codecs=opus"});
        chunks = [];
        const audioURL = window.URL.createObjectURL(blob);
        playback.src = audioURL;
    }
    
    // FOR DETECTING SILENCE
    // minimum decibels to detect silence,
    // Define a constant for the minimum decibels to be used in the audio analysis.
    // This is a threshold below which the audio levels are considered insignificant.
    const MIN_DECIBELS = -45;

    // Create a new AudioContext instance. This is the main component of the Web Audio API,
    // acting as a hub for creating and managing all of the various audio elements known as nodes.
    // AudioContext is essential for working with audio in web applications [0][1].
    const audioContext = new AudioContext();

    // Create a MediaStreamAudioSourceNode from the provided audio stream. This node is part of the audio graph
    // and is responsible for playing the audio stream. It's an input node that feeds audio data into the audio graph [0][1].
    const audioStreamSource = audioContext.createMediaStreamSource(stream);

    // Create an AnalyserNode to perform real-time frequency and time-domain analysis. This node is used to extract
    // data about the audio for visualization or other purposes. It's a processing node that can analyze the audio
    // data in various ways, such as frequency or waveform [0][1].
    const analyser = audioContext.createAnalyser();

    // Set the minimum decibels for the analyser. This value is used as the reference level for the decibel measurements.
    // By setting a minimum decibel level, we ensure that the analyser does not consider very low audio levels as significant [1].
    analyser.minDecibels = MIN_DECIBELS;

    // Connect the audio source to the analyser. This is a crucial step in the audio graph setup. By connecting the source
    // to the analyser, we allow the audio data to flow from the source through the analyser. This setup is part of the
    // modular routing concept of the Web Audio API, where nodes are linked together to form an audio routing graph [0][1].
    audioStreamSource.connect(analyser);

    // Get the number of frequency bins in the analyser. Each bin represents a range of frequencies, and the total number of bins
    // determines the frequency resolution of the analyser. The frequency resolution is the ability to distinguish between
    // different frequencies in the audio signal. A higher number of bins means a higher frequency resolution [1].
    const bufferLength = analyser.frequencyBinCount;

    // Create a Uint8Array to store the frequency data. The size of this array is equal to the number of frequency bins.
    // This array will hold the frequency data that is extracted from the audio stream by the analyser. The Uint8Array is
    // used because it is a typed array that holds 8-bit unsigned integers, which is suitable for storing frequency data [1].
    const domainData = new Uint8Array(bufferLength);

    // Call a function to detect sound using the analyser and the frequency data array. This function is not defined in the provided code
    // but would typically analyze the frequency data to detect the presence of sound. This could involve looking for peaks in the
    // frequency data that exceed a certain threshold, indicating the presence of sound [1].
    detectSound(analyser, domainData, bufferLength);
}

function detectSound(analyser, domainData, bufferLength) {
    let soundDetected = false;

    const checkSound = () => {
        analyser.getByteFrequencyData(domainData);

        for (let i = 0; i < bufferLength; i++) {
            if (domainData[i] > 0) {
                soundDetected = true;
                break;
            }
        }

        if (soundDetected) {
            console.log('Sound detected');
            clearTimeout(silenceTimeout);
            soundDetected = false;
            // Reset the timer for silence detection
            silenceTimeout = setTimeout(() => {
                if (is_recording) {
                    is_recording = false;
                    recorder.stop();
                    console.log("Silence detected, recording stopped.");
                }
            }, 5000); // 5 seconds of silence to stop recording
        }

        if (is_recording) window.requestAnimationFrame(checkSound);
    };

    checkSound();
}
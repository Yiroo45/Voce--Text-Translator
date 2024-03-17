from deep_translator import GoogleTranslator
from flask import Flask, request, jsonify
from PyDictionary import PyDictionary
import whisper
import json


dictionary = PyDictionary()
model = whisper.load_model("medium")
langs_dict = GoogleTranslator().get_supported_languages(as_dict=True) 
app = Flask('lang_codes')

@app.route("/get_meanings", methods=["POST"])
def get_meaning():
    json_data = request.get_json()
    meanings = dictionary.meaning(json_data["word"])
    return json.dumps(meanings)

@app.route("/transcribe", methods=["POST"])
def transcribe():
    json_data = request.get_json()
    print(json_data)

    filePath = "audio_files/" + json_data['fname'] + "/audio_processed.mp3"
    #filePath = "audio_files/" + filename + ("." + extension if removeBGM == "off" else "/vocals.wav")
    if json_data['src'] == "auto":
        result = model.transcribe(filePath)
    else:
        result = model.transcribe(filePath, language=json_data['src'])
        
    output = {"text": result["text"], "language": result["language"]}
    print(str(output))
    return output

@app.route("/getlangcodes", methods=["GET"])
def getlangcodes():
    json_data = json.dumps(langs_dict)
    return json_data

@app.route("/translate", methods=["POST"])
def translate():
    json_data = request.get_json()
    print(json_data)
    src = langs_dict[json_data['src']]
    trg = langs_dict[json_data['trg']]
    translated = GoogleTranslator(source= src, target= trg).translate(json_data['txt'])
    return translated


@app.route("/spleeter", methods=["POST"])
def spleeter():
    # TO BE FIXED
    json_data = request.get_json()
    print(json_data)
    return 0

@app.route("/removesilence", methods=["POST"])
def removesilence():
    # TO BE FIXED
    json_data = request.get_json()
    print(json_data)
    return 0

if __name__ == "__main__":
    app.run(debug=True, port=5000)


# from deep_translator import GoogleTranslator
# from flask import Flask, request
# import json
# import whisper
# model = whisper.load_model("medium")
# langs_dict = GoogleTranslator().get_supported_languages(as_dict=True) 
# app = Flask('lang_codes')


# @app.route("/getlangcodes", methods=["GET"])
# def getlangcodes():
#     json_data = json.dumps(langs_dict)
#     return json_data

# @app.route("/transcribe", methods=["POST"])
# def transcribe():
#     json_data = request.get_json()
#     src = langs_dict[json_data['src']]
#     trg = langs_dict[json_data['trg']]

#     filePath = "audio_files/" + json_data['fname'] + "/audio_processed.mp3"

#     if json_data['removeBGM'] == "auto":
#         result = model.transcribe(filePath)
#     else:
#         result = model.transcribe(filePath, language= json_data['src'])
        
#     transcript = result["text"]

#     translated = GoogleTranslator(source= src, target= trg).translate(transcript)
#     return translated

# @app.route("/translate", methods=["POST"])
# def translate():
#     json_data = request.get_json()

#     src = langs_dict[json_data['src']]
#     trg = langs_dict[json_data['trg']]
#     translated = GoogleTranslator(source= src, target= trg).translate(json_data['txt'])
#     return translated

# if __name__ == "__main__":
#     app.run(debug=True, port=5000)

CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255),
    email VARCHAR(255),
    pword VARCHAR(255),
    registration_date TIMESTAMP,
    type VARCHAR(255)
);

CREATE TABLE user_activity_log (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    activity_description VARCHAR(255),
    activity_date TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (user_id)
);

CREATE TABLE audio_files (
    file_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    file_name VARCHAR(255),
    file_size VARCHAR(255),
    file_format VARCHAR(255),
    upload_date TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (user_id)
);

CREATE TABLE text_translations (
    text_id INT PRIMARY KEY AUTO_INCREMENT,
    file_id INT,
    user_id INT,
    from_audio_file BOOLEAN,
    original_language varchar(255),
    translated_language VARCHAR(255),
    translate_from VARCHAR(255),
    translate_to VARCHAR(255),
    translation_date TIMESTAMP,
    FOREIGN KEY (file_id) REFERENCES audio_files (file_id),
    FOREIGN KEY (user_id) REFERENCES users (user_id)
);
# How to set up MySQL/MariaDB for testing

    CREATE USER 'phpunit'@'localhost' IDENTIFIED BY 'password';
    CREATE DATABASE welwitschi_auth_test;
    GRANT ALL PRIVILEGES ON welwitschi_auth_test . * TO 'phpunit'@'localhost';
    
The phpunit user's password must be stored in the `TEST_DB_PASS` environment
variable.
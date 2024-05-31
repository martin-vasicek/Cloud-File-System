# Cloud File System
  - Private encrypted file system for easy and secure file sharing over the cloud.
## Features
  - Fast upload with progress information.
  - Accessible from anywhere with an Internet connection.
  - High-standard encryption.
  - The only size limit is the size of your server storage 😉.
## Technical information
  - File encryption using the AES-256-CBC algorithm.
  - User credentials stored as Argon2 hashes.
  - The encryption/decryption key is generated only when the user logs in, through 2 x 50,000 iterations of the whirlpool hashing algorithm for username + password.
  - System stores key in cookie encrypted by secret passphrase (statically written in code).
  - The key is not stored on the server, so your files are safe even if your server is hacked.
## Configuration
### You will need to change some variables in your ```php.ini```.
> [!WARNING]
> If you do not set these, the application will not work. This is one reason why this application is good for your own VPS or other type of server you own.
```
session.auto_start = 1
session.cookie_secure = 1
session.cookie_httponly = 1
session.session.use_strict_mode = 1
upload_max_filesize = 0
post_max_size = 0
max_execution_time = 0
```
### Then, you need to configure few things in configuration file
#### DB setup
  - Create a database called ```file```.
  - Import ```SQL_STRUCTURE.sql``` into your newly created database.
#### Configuration in ```includes/php/config.php.example```
> [!IMPORTANT]
> You must remove ```.example``` endcap!
  - ```dbhost```, ```dbname```, ```dbusername```, ```dbpassword``` -> Your DB credentials.
  - ```cookiekey``` -> A random strong secret key for cookie encryption.
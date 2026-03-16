Step 1: Install XAMPP
XAMPP is a free package that installs Apache (your local web server), PHP, and MySQL all at once — it's the easiest way to run PHP locally on your computer.

Go to https://www.apachefriends.org and download the version for your operating system (Windows, Mac, or Linux)
Run the installer. When it asks which components to install, make sure Apache, MySQL, and PHP are all checked
Install it to the default location (C:\xampp on Windows, or /Applications/XAMPP on Mac)


Step 2: Start Apache and MySQL

Open the XAMPP Control Panel (search for it in your Start Menu or Applications folder)
Click Start next to Apache — its status should turn green
Click Start next to MySQL — its status should turn green

Both need to be running any time you want to use your site. If either one shows an error, the most common cause on Windows is that something else is already using port 80 (for Apache) or port 3306 (for MySQL). Skype and IIS are frequent culprits — closing them usually fixes it.

Step 3: Put Your Project Files in the Right Place
XAMPP only serves files from a specific folder called the web root:

Windows: C:\xampp\htdocs\
Mac: /Applications/XAMPP/htdocs/

Create a folder for your project inside htdocs. For example: C:\xampp\htdocs\worldcuisine\
Copy all your project files (the .php, .html, .css files, everything) into that folder. Your file structure should look like:
C:\xampp\htdocs\worldcuisine\
    config.php
    homePage.php
    all.php
    recipe.php
    signup.html
    ...
You access your site by opening a browser and going to http://localhost/worldcuisine/homePage.php — not by double-clicking the HTML file.

Step 4: Set Up the Database

With MySQL running in XAMPP, open your browser and go to http://localhost/phpmyadmin
phpMyAdmin is a visual tool for managing your MySQL databases — it comes bundled with XAMPP
In the left sidebar, click New to create a new database
In the "Database name" field type world_cuisine, leave the collation as utf8mb4_general_ci, and click Create
Now click the SQL tab at the top of the page
Open the database_setup.sql file from your project in any text editor (Notepad, VS Code, etc.), select all the text, and paste it into the SQL box in phpMyAdmin
Click Go — phpMyAdmin will run the script and create all your tables and sample data
You should now see tables like users, recipes, and reviews in the left sidebar under world_cuisine

Step 5: Test Everything
Open your browser and go to http://localhost/worldcuisine/homePage.php. From there you can test the full flow:

Click Sign Up and create a new account
You should be automatically logged in and redirected to the home page, where you'll see your username in the nav bar
Click View All — you should see the 8 sample recipes in the grid
Click a recipe card to open it and try leaving a review
Click + Add Recipe to submit your own recipe
Click Log Out to end your session, then try signing back in

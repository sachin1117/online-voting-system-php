🗳️ Online Voting System (PHP + MySQL)






A secure and lightweight Online Voting System built with PHP & MySQL.
Users can register, log in, and cast votes online, while an admin dashboard provides full control to manage candidates, voters, and election settings. The system also supports real-time results and Excel export of votes.

✨ Features

👤 User Module

Register and log in securely

Cast vote (one person = one vote)

View election results (if enabled by admin)

🛠️ Admin Module

Manage candidates (add, edit, delete)

Manage registered voters

Control election settings (start, stop, hide results)

View & export results in Excel

🔒 Security

Session-based authentication

CSRF protection

Input sanitization

Role-based access (Admin & Voter)

🛠️ Tech Stack

Frontend: HTML, CSS, Bootstrap, JavaScript

Backend: PHP (Core PHP, PDO for DB)

Database: MySQL

Server: Apache (XAMPP / WAMP / LAMP / Docker)

🚀 Installation & Setup Guide

Clone the Repository

git clone https://github.com/yourusername/online-voting-system-php.git
cd online-voting-system-php


Setup Database

Import the provided database.sql file into MySQL:

mysql -u root -p < database.sql


Configure Database

Open config.php and update your DB credentials:

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'online_voting_system');


Run the Project

Place the folder in htdocs (XAMPP) or www (WAMP).

Start Apache & MySQL services.

Open browser and go to:
👉 http://localhost/online-voting-system-php

⚙️ Environment Variables (.env Example)

If you want to use a .env file instead of editing config.php:

DB_HOST=localhost
DB_USER=root
DB_PASSWORD=your_password
DB_NAME=online_voting_system
DB_PORT=3306

SESSION_SECRET=your_random_secret

▶️ Running the Project
Using XAMPP / WAMP
# Start Apache & MySQL
# Access the project in your browser
http://localhost/online-voting-system-php

Using PHP Built-in Server
php -S localhost:8000


👉 Visit: http://localhost:8000

📡 API Endpoints
Endpoint	Method	Description
register.php	POST	Register new voter
login.php	POST	User login
vote.php	POST	Submit a vote
results.php	GET	Show election result
admin.php	GET	Admin dashboard
export.php	GET	Export results (Excel)
🖥️ Example Usage

Voter:

Register at register.php

Login at login.php

Cast vote at vote.php

Check results at results.php (if enabled)

Admin:

Access admin.php

Add/edit/delete candidates

Enable/disable results

Export results in Excel

📂 Folder Structure
📦 online-voting-system-php
 ┣ 📜 index.php         # Landing page
 ┣ 📜 register.php      # Voter registration
 ┣ 📜 login.php         # Login page
 ┣ 📜 logout.php        # Logout script
 ┣ 📜 vote.php          # Voting page
 ┣ 📜 results.php       # Election results
 ┣ 📜 admin.php         # Admin dashboard
 ┣ 📜 create_admin.php  # Script to create initial admin
 ┣ 📜 export.php        # Export results to Excel
 ┣ 📜 config.php        # Database & security config
 ┣ 📜 database.sql      # MySQL schema
 ┗ 📂 assets/           # CSS, JS, images

🤝 Contributing

Contributions are welcome! 🚀

Fork the repo

Create a new branch (feature/your-feature)

Commit changes (git commit -m 'Add feature')

Push & create a Pull Request

📜 License

This project is licensed under the MIT License. You are free to use, modify, and distribute.

👩‍💻 Author / Contact

Developed by [Your Name]
📧 Email: yourname@example.com

🌐 GitHub: @yourusername

🔮 Next Steps

🌍 Deploy to Heroku, Vercel, or cPanel

🐳 Add Docker support for quick setup

🔑 Add JWT-based authentication (API support)

🎨 Upgrade UI with Tailwind / Bootstrap 5

📊 Add analytics dashboard for admins

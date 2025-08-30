🗳️ Online Voting System (PHP + MySQL)






A secure and lightweight Online Voting System built with PHP & MySQL.
Users can register, log in, and cast votes online, while an admin dashboard provides full control to manage candidates, voters, and election settings. The system also supports real-time results and Excel export of votes.

📌 GitHub Repo → online-voting-system-php

✨ Features

👤 User Features

Register and log in securely

Cast vote (one user = one vote)

View election results (if enabled)

🛠️ Admin Features

Manage candidates (add, edit, delete)

Manage registered voters

Control election settings (start, stop, hide results)

Export results in Excel

🔒 Security

Session-based authentication

CSRF protection

Input sanitization

Role-based access (Admin & Voter)

🛠️ Tech Stack

Frontend: HTML, CSS, Bootstrap, JavaScript

Backend: PHP (PDO for DB)

Database: MySQL

Server: Apache (XAMPP / WAMP / LAMP / Docker)

🚀 Installation & Setup

Clone the Repository

git clone https://github.com/sachin1117/online-voting-system-php.git
cd online-voting-system-php


Setup Database

Import the provided database.sql file into MySQL:

mysql -u root -p < database.sql


Configure Database

Open config.php and update DB credentials:

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'online_voting_system');


Run the Project

Place the folder in htdocs (XAMPP) or www (WAMP).

Start Apache & MySQL services.

Open browser → http://localhost/online-voting-system-php

⚙️ Environment Variables (.env Example)

If you prefer .env file instead of editing config.php:

DB_HOST=localhost
DB_USER=root
DB_PASSWORD=your_password
DB_NAME=online_voting_system
DB_PORT=3306

SESSION_SECRET=your_random_secret

▶️ Running the Project
Using XAMPP / WAMP
# Start Apache & MySQL
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
📂 Project Files
📦 online-voting-system-php
 ┣ 📜 index.php         # Landing page
 ┣ 📜 register.php      # Voter registration
 ┣ 📜 login.php         # User login
 ┣ 📜 logout.php        # User logout
 ┣ 📜 vote.php          # Voting page
 ┣ 📜 results.php       # Election results
 ┣ 📜 admin.php         # Admin dashboard
 ┣ 📜 create_admin.php  # Script to create initial admin
 ┣ 📜 export.php        # Export results to Excel
 ┣ 📜 config.php        # DB + security config
 ┣ 📜 database.sql      # MySQL schema
 ┗ 📂 assets/           # CSS, JS, images (if any)

🤝 Contributing

Contributions are welcome 🚀

Fork the repo

Create a new branch (feature/your-feature)

Commit changes (git commit -m 'Add feature')

Push & create a Pull Request

📜 License

This project is licensed under the MIT License.

👨‍💻 Author / Contact

Author: Sachin Kumar

📧 Email: sachinkumar69344@gmail.com

🌐 GitHub: sachin1117

🔮 Next Steps

🌍 Deploy to Heroku / Vercel / cPanel

🐳 Add Docker support

🔑 Add email verification for voters

🎨 Improve UI with Bootstrap 5 / Tailwind

📊 Add admin analytics dashboard

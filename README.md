﻿### Stock Analysis
Hey there!

> Steps to get started

- clone the repo 
  - `git clone https://github.com/DneshP/stock_analysis.git`
- go to ./src folder copy example.env to .env
  - update the DB_DSN=mysql:host=localhost;port=3306;dbname=stock_analysis 
  - DB_USER=root 
  - DB_PASSWORD=password
  - BASE_URL=http://localhost/project => make sure there is a no trailing slash
- create a database and update the name in the DB_DSN
- go to the root of the project 
  - run `php ./setup.php`
- to run tests cd to ./src  and run `.\vendor\bin\phpunit --testdox`

That's it

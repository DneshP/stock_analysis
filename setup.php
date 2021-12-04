<?php

echo "\e[1;36mInstalling dependencies\e[0m\n";
shell_exec("cd ./src && composer install");
echo "\e[1;36mRunning Migrations\e[0m\n";
shell_exec("php ./Migrations.php");
echo "\e[1;36mGood to go!\e[0m\n";

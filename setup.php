<?php

echo "\e[1;36mInstalling dependencies\e[0m\n";
shell_exec("cd ./src && composer install");
echo "\e[1;36mRunning migrations\e[0m\n";
shell_exec("php ./migrations.php");
echo "\e[1;36mGood to go!\e[0m\n";

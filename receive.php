<?php

require_once("config.php");
require_once("statbot.php");

openlog("rstatbot", LOG_PID | LOG_PERROR, LOG_LOCAL0);

$bot = new StatBot($token, $allowed_users, $hosts);
$bot->log_last_query();
$bot->handle_message();

closelog();

?>

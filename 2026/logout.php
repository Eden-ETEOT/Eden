<?php
session_start();
session_unset();
session_destroy();

header("Location: ./Login/loginMockup/index.php");
exit;

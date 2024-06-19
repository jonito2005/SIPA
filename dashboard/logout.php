<?php
session_start();
session_destroy();
header("Location: /SIPANEW/index.html");
?>
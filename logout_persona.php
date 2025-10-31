<?php
session_start();
unset($_SESSION['persona_id']);
header("Location: login_persona.php");

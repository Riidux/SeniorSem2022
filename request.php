<?php
// Grab db.php
include_once "db.php";

// If anyone tries to access the site directly, redirect to main site
if(!isset($_GET['q']) || !isset($_GET['context'])) {
    header('Location: https://modex.dev/chatbot/index.php');
}

// Grab question from URL parameter
$question = $_GET['q'];

// Grab context from URL parameter
$context = $_GET['context'];

// Attempt to get answer
$answer = getAnswer($question, $context);

// Return answer JSON encoded (needed to fetch it correctly)
echo json_encode($answer);
?>
<?php 
    // Grab db.php functions
    include "db.php";
    // Dump the whole thing
    dumpDb(); 

    echo "<hr>";
    // Grab all questions with layer 0 = top layer (starting questions)
    $questions = getQuestions(0);
    // Print them all out
    foreach($questions as $question) {
        echo "Top level questions -> " . $question['question'] . "<br>";
    }
?>
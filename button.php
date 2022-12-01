<?php
include "db.php";
?>

<!DOCTYPE html>
<html>
    <head>
    <script src="./javascript.js"></script>
        <title>Button Input Chatbot</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="container">
            <div class="content" id="shop">
                <h1>Modex</h1>
                <div id='outer'>
                    <img src='./modexsneaker1.png'/>
                </div>
            </div>
            <div class="content" id="chatbot">
                <h1>Button Input Chatbot</h1>
                <div id="chat">
                    <ul id="chatList">
                        <!--
                        <li class="bot">Bot Text</li>
                        <li class="user">User Text</li> 
                        -->
                        <li class="bot">Hello! How can I help you?</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- script -->
     <script>
    // parameters passed to indicate input method
    // buttons = 1 for button input
    // buttons = 0 for type input
        buttons = 1;
     </script>
    </body>
</html>
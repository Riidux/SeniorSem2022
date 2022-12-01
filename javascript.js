
// Keep track of where we are in the "question tree"
var context = 0;
var buttons = 0;

addEventListener('DOMContentLoaded', (event) => {
    getAnswer("");
});

// When "submitting" aka. pressing enter in input, get answer
addEventListener('submit', (event) => {
    // Turn off default form behaviour with redirects
    event.preventDefault();
    var text = document.getElementById("questionBox").value;

    // Add replaceAt function
    // Replaces a character at certain index with another (can also replace with multiple characters)
    String.prototype.replaceAt = function (index, replacement) {
        return this.substring(0, index) + replacement + this.substring(index + replacement.length);
    }

    // Match only letters, numbers, !, . and ?
    const alphaNumericAndSentenceSignsOnly = /^[^A-Za-z0-9.?!]*$/g;
    // Iterate over the string
    for (var i = 0; i < text.length; i++) {
        // If character is invalid (does not match) replace with space
        if (text.charAt(i).match(alphaNumericAndSentenceSignsOnly) != null)
            text = text.replaceAt(i, ' ');
    }

    // Do not execute if nothing or only whitespaces are sent
    if (!text || text.replace(/ /g, '') === "")
        return;

    // Add text to the chat
    addChat(text);

    // Get answer from input value (and add it to the chat)
    getAnswer(text);
    // Reset input field
    document.getElementById("questionBox").value = "";
});

// Adds a text to the "chat"
// user = 1 ... user message
//        0 ... bot message
function addChat(text, user = 1) {
    // Make new li
    var li = document.createElement("li");
    // Set it's text to the message
    li.innerHTML = text;
    // Apply class of user or bot
    if (user)
        li.classList.add("user");
    else
        li.classList.add("bot");
    // Add element to the "chat"
    document.getElementById("chatList").appendChild(li);
}

// Adds a button to the "chat"
function addButton(text) {
    // Create new li
    var li = document.createElement("li");
    // Buttons are ALWAYS bot chats
    li.classList.add("bot");

    // Multiple questions -> Put them all in ONE button message
    if (Array.isArray(text)) {
        // Iterate through questions
        text.forEach(subText => {
            // Create new button
            var button = document.createElement("button");
            button.setAttribute("class", "button1");
            // Put question into button
            button.innerHTML = subText;
            // Add listener to when button is clicked
            button.addEventListener('click', (event) => {
                // Grab text from button
                var text = event.path[0].innerHTML;
                // Write it to the chat as USER
                addChat(text);
                // Get answer for pressed button
                getAnswer(text);
            });
            // Add button to the button message
            li.appendChild(button);
        });
    } else {
        // See above
        var button = document.createElement("button");
        button.innerHTML = text;
        li.appendChild(button);
    }

    // Do NOT add back button in main menu, only when in a sub-menu (category already selected)
    if (context != 0) {
        // Create new button
        var backButton = document.createElement("button");
        backButton.setAttribute("class", "button1");
        // Always have the text be "Back"
        backButton.innerHTML = "Back";
        // Add listener to when button is clicked
        backButton.addEventListener('click', (event) => {
            // If back button is pressed, return to main menu (categories)
            context = 0;
            // Write "Back" to the chat
            addChat("Back");
            // Grab available questions
            getAnswer("");
        });
        li.appendChild(backButton);
    }

    // Append element with buttons to the "chat"
    document.getElementById("chatList").appendChild(li);
}

// Helper function that decides whether button or text input should be used
// Response = Questions fetched from request.php
// buttons = 1 -> use buttons
// buttons = 0 -> use text
function postAvailableQuestions(response, buttons = 1) {
    if (!buttons) {
        // Add each available question to a list and print
        var message = "Following questions are available: <br>";
        response.forEach(resp => {
            message += resp.num + ") " + resp.question + "<br>";
        });
        // Add it to the chat
        addChat(message, 0);
    } else {
        addButton(response.map(resp => resp.question));
    }
}

// Get answer from request.php
async function getAnswer(question) {
    // Fetch result
    let response = await fetch("request.php?q=" + question + "&context=" + context);
    // Parse it from JSON
    // console.log(response);
    response = await response.json();
    // Print it to console
    console.log(response);

    // If response is an array -> List of available questions
    if (Array.isArray(response)) {
        postAvailableQuestions(response, buttons);
    } else {
        // If a valid question is asked, progress in context
        context = response.parent;

        // If response is empty, a category was selected. Get list of available questions instantly.
        if (response.answer.replace(/ /g, '') === "") {
            getAnswer("");
        } else { // Otherwise add response to the chat
            // Add response to the chat
            addChat(response.answer, 0);
            context = 0;
            if (buttons)
                getAnswer("");
        }
    }
}

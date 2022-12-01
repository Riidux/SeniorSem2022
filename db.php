<?php
// Create new database file / open db handle
$db = new SQLite3('database.db');

// Set up table for answers
// Each row resembles a question and its corresponding answer
// id -> Primary Key, Identifier of question
// Answer, Question -> Obvious
// Num -> Number input instead of question
// Parent ->  Points to parent question (since questions can have sub-questions, here we define which parent question child questions belong to)
//            NULL  -> top layer question
//            Value -> ID of parent
$db->exec("CREATE TABLE IF NOT EXISTS answers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    answer TEXT NOT NULL DEFAULT 'answer',
    question TEXT NOT NULL DEFAULT 'question',
    num INTEGER NOT NULL DEFAULT 1,
    parent INTEGER DEFAULT NULL
)");

if(isset($_GET) && isset($_GET['delete'])) {
    clearDatabase();
}

// Yep
function clearDatabase() {
    global $db;
    $db->exec("DROP TABLE answers;");
    $db->exec("CREATE TABLE IF NOT EXISTS answers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        answer TEXT NOT NULL DEFAULT 'answer',
        question TEXT NOT NULL DEFAULT 'question',
        num INTEGER NOT NULL DEFAULT 1,
        parent INTEGER DEFAULT NULL
    )");

    // List of available answers
    // Shipping
    createAnswer("Shipping", "", 1);
    createAnswer("International Shipping available?", "Modex ships in U.S. only and is not available internationally.", 1, 1);
    createAnswer("What shipping carriers are used?", "USPS, UPS, and FedEx are available as options.", 2, 1);
    createAnswer("How long does it take for my shoes to arrive?", "It takes between 7 to 14 days for a delivery to be made.", 3, 1);

    // Sizing
    createAnswer("Sizing", "", 2);
    createAnswer("What sizes are available?", "The sneakers come in sizes between 4.5 to 14 in US men’s, and 6 to 15.5 in women’s. Width sizes are available between B to 2E in women’s.", 1, 2);
    createAnswer("Do you offer custom sizing?", "Yes, custom sizing is offered over our email.", 2, 2);

    // Payment
    createAnswer("Payment", "", 3);
    createAnswer("How much do the sneakers cost?", "Our price for all our regular offered sizes is $150. Custom sizes are between $175 to $200.", 1, 3);
    createAnswer("What payment methods are accepted?", "Credit and debit cards are accepted, as well as various online payment processing tools: Paypal, Shopify, Google Pay, and Amazon Pay.", 2, 3);
    createAnswer("Is payment with cash available?", "Modex is an online business and does not offer any method to accept cash payments.", 3, 3);
}

// Adds an entry to the database
function createAnswer($question, $answer, $num = 1, $parent = NULL) {
    global $db;
    $stmt = "INSERT INTO answers (question, answer, num, parent) VALUES ('$question', '$answer', '$num', ";

    // Parent = NULL -> 'INSERT INTO answers (question, answer, num, parent) VALUES ('question', 'answer', 'num', NULL)' <- quote not there!!!
    //       != NULL -> 'INSERT INTO answers (question, answer, num, parent) VALUES ('question', 'answer', 'num', 'parent')'
    $stmt .= ($parent == NULL) ? "NULL" : "'$parent'";    
    $stmt .= ");";

    $db->exec($stmt);
}

// Get all questions for current layer of program
// Parent -> NULL for top layer question
//           Number for which question was previously answered
function getQuestions($parent) {
    global $db;

    // If parameter is empty or 0, set it to NULL otherwise to its Integer value
    $parent = (!isset($parent) || $parent == 0) ? NULL : intval($parent);
    $results = null;

    // If parent value is not NULL use prepared statement 
    if($parent != NULL) {
        // Prepared statement because SQL Injection is a no-no
        $stmt = $db->prepare("SELECT question, num FROM answers WHERE parent=:parent");
        $stmt->bindValue(":parent", $parent, SQLITE3_INTEGER);

        // Execute statement
        $results = $stmt->execute();
    } else { // If value is NULL, no prepared statement is needed since the statement is static
        $results = $db->query("SELECT question, num FROM answers WHERE parent IS NULL");
    }
    
    // Prepare array to hold results
    $questions = array();

    // Grab all results from the database query and put them into the questions array
    while($row = $results->fetchArray()) {
        array_push($questions, $row);
    }

    // If there is no result, return "No questions found", otherwise return array of questions
    return (count($questions) == 0) ? array("No questions found") : $questions;
}

// Returns answer for a question (request.php)
function getAnswer($question, $context) {
    global $db;

    // Add number selection; check for parent
    $query = "SELECT answer, parent, num FROM answers WHERE (question LIKE :question COLLATE NOCASE OR num=:number) AND ";
    
    // Context (Parent) = NULL -> IS NULL 
    //                  = number -> prepared statement
    $query .= ($context == NULL || $context == 0) ? "parent IS NULL" : "parent=:parent";

    // Prepared statement incase of SQL Injection
    $stmt = $db->prepare($query);
    $stmt->bindValue(":question", ($question === "") ? $question : $question."%", SQLITE3_TEXT);
    $stmt->bindValue(":number", $question, SQLITE3_TEXT);

    if($context != NULL)
        $stmt->bindValue(":parent", $context, SQLITE3_NUM);

    // Execute statement
    $results = $stmt->execute();
    // Fetch result
    $row = $results->fetchArray();

    // If an answer exists -> Wrap it into an object with answer and parent fields
    // If not -> Get sub-questions of categories
    if(isset($row) && isset($row['answer'])) { // Questions
        $answer = new stdClass();
        $answer->answer = $row['answer'];
        $answer->parent = (isset($row['parent']) && $row['parent'] != null) ? $row['parent'] : $row['num'];
        return $answer;
    } else { // Categories
        return getQuestions($context);
    }
}

// Echo everything in the db (check.php)
function dumpDb() {
    global $db;
    $results = $db->query("SELECT * FROM answers");
    while($row = $results->fetchArray()) {
        echo "ID -> " . $row['id'] . "<br>";
        echo "Answer -> " . $row['answer'] . "<br>";
        echo "Question -> " . $row['question'] . "<br>";
        echo "Number -> " . $row['num'] . "<br>";
        echo "Parent -> " . $row['parent'] . "<br><br>";
    }
}
?>
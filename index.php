    <?php

    require 'vendor/autoload.php';

    $dotenv = new Dotenv\Dotenv(__DIR__);
    $dotenv->load();

    use Parse\ParseClient;
    use Parse\ParseObject;
    use Parse\ParseQuery;

    ParseClient::initialize($_ENV['PARSE_ID'], '', $_ENV['PARSE_KEY']);
    ParseClient::setServerURL($_ENV['PARSE_URL']);

    /**
     * Set the Slack app token
     */
    $app_key = $_ENV['KEY'];

    /**
     * Get the response from Slack
     */
    $command = $_POST['command'];
    $text = $_POST['text'];
    $token = $_POST['token'];
    $user_id = $_POST['user_id'];

    /**
     * Validate the Slack token
     */
    if($token != $app_key){
        $msg = "The token `".$token."` : `".$app_key."` for the slash command doesn't match. Check your script.";
        die($msg);
        echo $msg;
    }

    /**
     * Break the text into an array in order to test various keywords
     */
    $textArr = explode(' ', $text);

    if ($textArr[0] == '-list') {
        /**
         * List the user's notes
         */

    	/**
         * Fetch the user's notes
         */
    	$query = new ParseQuery('NoteObject');
    	$query->equalTo('user', $user_id);
    	$results = $query->find();
    	$notes = '';

        /**
    	 * Add each note to the response
         */
    	foreach($results as $result) {
    		$noteId = $result->getObjectId();
    		$noteText = $result->get('text');

    		$notes .= "*#".$noteId.":* ```".$noteText."``` \n";
    	}

    	$response = $notes;
    } else if ($textArr[0] == '-delete') {
        /**
         * Delete a note (or notes) by ID(s)
         */

        $message = '';
        $query = new ParseQuery('NoteObject');

        for ($i = 1; $i < count($textArr); $i++) {
            $query->equalTo('user', $user_id);
            $query->equalTo('objectId', $textArr[$i]);
            $note = $query->first();
            $note->destroy();
            $message .= ">*#".$textArr[$i]."* has been deleted\n";
        }

        $response = $message;
    } else if ($textArr[0] == '-help') {
        /**
         * Show a list of commands
         */

        $commandList = [
            [
                command => '-list',
                description => 'returns a list of notes in ascending order',
                example => '/note -list'
            ],
            [
                command => '-delete {id}',
                description => 'deletes the note(s) specified by id',
                example => '/note -delete SmwWo3kfza'
            ],
            [
                command => '-last',
                description => 'returns the last saved note',
                example => '/note -last'
            ]
        ];
        $commands = '';

        foreach($commandList as $command) {
            $commands .= $command['command']."\n".$command['description']."\n".$command['example']."\n\n";
        }
        $response = "```".$commands."```";
    } else if ($textArr[0] == '-last') {
        /**
         * Display the most recent note
         */

        $query = new ParseQuery('NoteObject');
        $query->descending('createdAt');
        $query->equalTo('user', $user_id);
        $note = $query->first();

        $noteId = $note->getObjectId();
        $noteText = $note->get('text');

        $response = "*#".$noteId.":* ```".$noteText."```";
    } else {
    	/**
         * Save the note
         */

    	$noteObject = ParseObject::create('NoteObject');
    	$noteObject->set("text", $text);
    	$noteObject->set("user", $user_id);
    	$noteObject->save();

    	$response = '_Your note has been saved under ID_ *#'.$noteObject->getObjectId().'*';
    }

    header('Content-type: application/json');

    /**
     * Build the response
     */
    $reply = [
        'text' => $response
    ];

    /**
     * Send the response to the user
     */
    echo json_encode($reply);
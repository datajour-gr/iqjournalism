<?php

//read files per row 

//Accept post reuqests 
// Get the text from the AJAX request
if (isset($_POST['text'])) {
    $text = $_POST['text'];
    $label = $_POST['label']; // true / false
    $row = $_POST['row']; // true / false
    $text = str_replace(array("\n", "\t", ' '), ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    // Escape the received text for HTML
    $escapedText = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

    // Process the text as needed (e.g., save it to a file)
    $filename = 'ail/data_' . $_GET['arthro'] . '_validate.json';
    file_put_contents($filename, serialize(array('row' =>  $row, $label => $escapedText)) . PHP_EOL, FILE_APPEND);

    // Echo the escaped text as a response
    echo "Received and escaped text: " . $escapedText;
    exit();
}



// Read the JSON file into a PHP variable
if (isset($_GET['arthro'])) {
    $file_name  = 'ail/data_' . $_GET['arthro'] . '.json';
    $json_data = file_get_contents($file_name);
    //if file exists check if validate file exists
    $validation_file =  'ail/data_' . $_GET['arthro'] . '_validate.json';
    // var_dump($json_data );
    if (file_exists($validation_file)) {
        $handle = fopen($validation_file, 'r');

        $validation_data = [];
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $data_unseriliaze = unserialize($line);
                // var_dump($data_unseriliaze);
                $validation_data[$data_unseriliaze['row']] = $data_unseriliaze;
            }

            fclose($handle);
        }
    }else{
        $validation_data = [];
    }
} else {
    // echo '<br>';
    // echo '<a href="?arthro=0"><b>Emolex</b> start validation here</a>';
    //  exit;
}




/////////////////////////////////////////////
// Parse the JSON data into a PHP array
if (isset($json_data)) {
    $data = json_decode($json_data, true);
}
$svg_names = ['anger', 'anticipation', 'disgust', 'fear', 'joy','sadness', 'surprise', 'trust'];
$svg = [
    '<span title="angry" class="emoji angry">&#128548;</span>',
    '<span title="anticipation" class="emoji anticipation">&#128559;</span>',
    '<span title="disgust" class="emoji disgust">&#128534;</span>',
    '<span title="fear" class="emoji fear">&#128552;</span>',
    '<span title="joy" class="emoji joy">&#128512;</span>',
    '<span title="sadness" class="emoji sadness">&#128542;</span>',
    '<span title="surprise" class="emoji surprise">&#128562;</span>',
    '<span title="trust" class="emoji trust">&#128525;</span>',
];

function processMultilevelData($data, $svg, $svg_names, $validation_data)
{ ?>

    <table class="table table-sm  table-striped table-hover ">
        <thead>
            <tr>
                <td>Id</td>
                <td>Original Text</td>
                <td>Filtered Tokens</td>
                <td>Token analysis</td>
                <td>Found / Num of tokens</td>
                <td>Emotions</td>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($data as $row => $value) : ?>
                <?php $Id = $row + 2; ?>
                <tr id="article_id" data-article="<?php echo $Id; ?>">
                    <td><?php echo $Id; ?></td>
                    <td><?php echo $value['original_text']; ?></td>
                    <td><?php echo $value['filtered_tokens']; ?></td>
                    <td>
                        <table class="table table-responsive table-sm">
                            <thead>
                                <tr>
                                    <td>Token</td>
                                    <td>Found Token</td>
                                    <td>Token Lemma</td>
                                    <td>Found Lemma</td>
                                    <td>Lev. Original</td>
                                    <td>Lev Lemma</td>

                                    <td>correct</td>
                                    <td>wrong</td>


                                    <td> <span id="smiley-emoji"></span>
                                        <span id="anger"></span><br>
                                        <table class="table table-responsive table-sm emotion-table">
                                            <tr>
                                                <?php foreach ($svg as $key => $row) : ?>
                                                    <td>
                                                        <?php echo $svg_names[$key]; ?>
                                                        <i><?php echo $row; ?></i>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        </table>
                                    </td>


                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($value['matched_words'] as $label => $token) : ?>
                                    <tr data-row="<?php echo $label; ?>">
                                        <td><?php echo $token['token']; ?></td>
                                        <td><?php echo $token['found_token']; ?></td>
                                        <td><?php echo $token['token_lemma']; ?></td>
                                        <td><?php echo $token['found_lemma']; ?></td>
                                        <td><?php echo $token['lev_original']; ?></td>
                                        <td><?php echo $token['lev_lemma']; ?></td>

                                        <?php
                                        $data_correct = false;
                                        $data_wrong = false;
                                        if (isset($validation_data[$label])) {
                                            if (isset($validation_data[$label]['correct'])) {
                                                $data_correct = true;
                                            } elseif (isset($validation_data[$label]['wrong'])) {
                                                $data_wrong = true;
                                            }
                                        }
                                        ?>

                                        <td>
                                            <a href="#" class="correct <?php echo $data_correct ? 'active' : ''; ?>">
                                                <svg fill="#000000" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="800px" height="800px" viewBox="0 0 335.765 335.765" xml:space="preserve">
                                                    <g>
                                                        <g>
                                                            <polygon points="311.757,41.803 107.573,245.96 23.986,162.364 0,186.393 107.573,293.962 335.765,65.795 		" />
                                                        </g>
                                                    </g>
                                                </svg>
                                            </a>
                                        </td>
                                        <td>
                                            <a href="#" class="wrong  <?php echo $data_wrong ? 'active' : ''; ?>">
                                                <svg fill="#000000" width="800px" height="800px" viewBox="0 0 200 200" data-name="Layer 1" id="Layer_1" xmlns="http://www.w3.org/2000/svg">
                                                    <title />
                                                    <path d="M114,100l49-49a9.9,9.9,0,0,0-14-14L100,86,51,37A9.9,9.9,0,0,0,37,51l49,49L37,149a9.9,9.9,0,0,0,14,14l49-49,49,49a9.9,9.9,0,0,0,14-14Z" />
                                                </svg>
                                            </a>
                                        </td>

                                        <td class="">
                                            <table class="table table-responsive table-sm emotion-table">
                                                <tr>
                                                    <?php
                                                    $i = 0;
                                                    //var_dump($token['emotions'] );
                                                    foreach ($token['emotions'] as  $row) : ?>
                                                        <td>
                                                            <div><?php echo $row; ?></div>
                                                            <div><i><?php echo $svg[$i]; ?></i></div>
                                                        </td>
                                                        <?php $i += 1; ?>
                                                    <?php endforeach; ?>
                                                </tr>
                                            </table>
                                        </td>



                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </td>
                    <td><?php echo $value['foundpertotal']; ?></td>
                    <td><?php echo implode(',', $value['emotions']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php
    return $data;
}


// Call the recursive function to process the multilevel data





?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>UOA</title>

    <!-- Add the Bootstrap CSS link -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css?<?php echo time(); ?>">

    <!-- Add the jQuery library link -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Add the Bootstrap JavaScript link (requires jQuery) -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


    <style>
.emotion-table div, .emotion-table i {
    opacity: 1;
}
    </style>
</head>

<body>

    <?php
    $current_url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    // var_dump($_SERVER['HTTP_HOST']);
    // Remove the query string, if it exists
    if (strpos($current_url, '?') !== false) {
        $current_url = substr($current_url, 0, strpos($current_url, '?'));
    }

    // var_dump($current_url); 
    $next_page = isset($_GET['arthro']) ? intval($_GET['arthro']) + 1 : 0;
    $prev_page = isset($_GET['arthro']) ? intval($_GET['arthro']) - 1 : 0;
    //echo $current_url;

    ?>

    <div class="arrows">
        <div class="previous"><a href="<?php echo '?arthro=' . $prev_page; ?>">PREVIOUS</a></div>
        <div class="next"><a href="<?php echo '?arthro=' . $next_page; ?>">NEXT</a></div>
    </div>

    <div class="container-fluid full-height   pt-3">
        <div class="row pt-4">
            <div class="col-md-12">
                <?php if (isset($data) && $svg && isset($validation_data)) : ?>
                    <?php processMultilevelData($data, $svg, $svg_names, $validation_data); ?>
                <?php else : ?>
                    <a href="emotions.php?arthro=0"><b>Emotions Emolex</b> start validation here</a><br>
                    <a href="vad.php?arthro=0"><b>Vad</b> start validation here</a><br>
                    <a href="ail.php?arthro=0"><b>Ail</b> start validation here</a><br>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
    echo '<pre>';
    // var_dump($data);
    echo '</pre>';
    ?>

    <script>
        const $ = jQuery;
        // console.log($('.emotion-table  div').each(index, elem => {

        // }).html());
        $('.emotion-table td >  div').each((index, elem) => {

            if ($(elem).html() == '1') {
                $(elem).css('font-weight', 'bold');
                $(elem).css('opacity', '1');
                $(elem).next('div').css('opacity', '1');
            } else {
                // $(elem).css('font-weight','bold');
            }
        });
        // if($('.emotion-table td div').html() == '1'){
        //     $(this).css('font-weight','bold');
        // }

        //correct , wrong,
        $('.correct').on('click', function(event) {
            $(this).addClass('active');
            console.log($(this).parent().parent().html());
            let row_text = ($(this).parent().parent().text());
            var rowNumber = ($(this).parent().parent().data('row'));
            console.log('Clicked cell is in row ' + (rowNumber));

            $.ajax({
                url: "ail.php?arthro=<?php echo $_GET['arthro']; ?>",
                method: "POST",
                data: {
                    text: row_text,
                    label: 'correct',
                    row: rowNumber
                },
                success: function(response) {
                    // Display the server's response
                    $("#response").html(response);
                },
                error: function() {
                    // Display an error message
                    $("#response").html("Error: Unable to send data to the server.");
                }
            });



        });
        $('.wrong').on('click', function() {
            $(this).addClass('active');
            console.log($(this).parent().parent().html());
            let row_text = ($(this).parent().parent().text());
            var rowNumber = ($(this).parent().parent().data('row'));
            console.log('Clicked cell is in row ' + (rowNumber + 1));

            $.ajax({
                url: "ail.php?arthro=<?php echo $_GET['arthro']; ?>",
                method: "POST",
                data: {
                    text: row_text,
                    label: 'wrong',
                    row: rowNumber
                },
                success: function(response) {
                    // Display the server's response
                    $("#response").html(response);
                },
                error: function() {
                    // Display an error message
                    $("#response").html("Error: Unable to send data to the server.");
                }
            });
        });
    </script>

</body>

</html>
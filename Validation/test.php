<?php
// Read the JSON file into a PHP variable
$json_data = file_get_contents('/home/whiteson/uoa/src/data.json');

// Parse the JSON data into a PHP array
$data = json_decode($json_data, true);
$svg_names = ['anger', 'anticipation', 'disgust', 'fear', 'joy', 'negative', 'positive', 'sadness', 'trust'];
$svg = [
    '<span title="angry" class="emoji angry">&#128548;</span>', 
    '<span title="anticipation" class="emoji anticipation">&#128559;</span>', 
    '<span title="disgust" class="emoji disgust">&#128534;</span>', 
    '<span title="fear" class="emoji fear">&#128552;</span>', 
    '<span title="joy" class="emoji joy">&#128512;</span>', 
    '<span title="negative" class="emoji negative">&#128548;</span>', 
    '<span title="positive" class="emoji positive">&#128521;</span>', 
    '<span title="sadness" class="emoji sadness">&#128542;</span>', 
    '<span title="trust" class="emoji trust">&#128525;</span>',
];

function processMultilevelData($data,$svg)
{ ?>

    <table class="table table-sm table-responsive table-striped table-hover ">
        <thead>
            <tr>
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

                <tr>
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
                                    <td>Emotions <span id="smiley-emoji"></span>
                                        <span id="anger"></span><br>
                                        <table class="table table-responsive table-sm emotion-table">
                                            <tr>
                                                <?php foreach ($svg as $row) : ?>
                                                    <td class="<?php echo $row; ?> emotions-label">
                                                        <?php echo $row; ?>
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
                                    <tr>
                                        <td><?php echo $token['token']; ?></td>
                                        <td><?php echo $token['found_token']; ?></td>
                                        <td><?php echo $token['token_lemma']; ?></td>
                                        <td><?php echo $token['found_lemma']; ?></td>
                                        <td><?php echo $token['lev_original']; ?></td>
                                        <td><?php echo $token['lev_lemma']; ?></td>
                                        <td class="emot12io1212ns1212">
                                            <table class="table table-responsive table-sm emotions">
                                                <tr>
                                                    <?php
                                                    $i = 0;
                                                    foreach ($token['emotions'] as $row) : ?>
                                                        <td class="<?php echo $svg[$i]; ?>">
                                                            <?php echo $row; ?>
                                                            <i><?php echo $svg[$i]; ?></i>
                                                        </td>
                                                        <?php $i+=1; ?>
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

    <!-- Add the jQuery library link -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Add the Bootstrap JavaScript link (requires jQuery) -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
   

    <style>
        .emoji{
            font-size: 20px;
        }
        .full-height {
            height: 100vh;
            overflow: scroll;
        }

        .emotions-label {
            font-size: 10px
        }
    </style>
</head>

<body>

    <div class="container-fluid full-height">
        <div class="row">
            <div class="col-md-12">
                <?php processMultilevelData($data,$svg); ?>
            </div>
        </div>
    </div>
    <?php
    echo '<pre>';
    // var_dump($data);
    echo '</pre>';
    ?>

   
</body>

</html>
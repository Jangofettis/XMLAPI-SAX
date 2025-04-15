<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form method="post" action="res.php">
    <?php
        echo "<select name='tidning'>";
        function starttagg($parser, $NAME, $attributes) {
            if ($NAME == "NEWSPAPER"){
                echo "<option value='".$attributes ['NAME']."'>";
                echo $attributes ['NAME'];
            }
        }

        function sluttagg($parser, $NAME) {
            if ($NAME == "NEWSPAPER"){
                echo "</option>";
            }
        }

        function chardata($parser, $data) {
          
        }


    $parser = xml_parser_create();
    xml_set_element_handler($parser, 'starttagg', 'sluttagg');
    xml_set_character_data_handler($parser, 'chardata');

    $url = "https://wwwlab.webug.se/examples/XML/articleservice/papers";
    $data=file_get_contents($url);
    xml_parse($parser, $data, true);

    xml_parser_free($parser);
    echo "</select>";

    ?>
    <input type="submit" value="Submit">
    </form>
</body>
</html>
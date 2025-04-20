<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respons</title>
    <style>
        /* Grundinställningar för tabellen */
        table {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
        }
        td {
            border: 1px solid #ddd;
            vertical-align: top;
            padding: 0;
        }

        /* Vänsterkolumnen med tidningsinfo */
        .newspaper-info {
            color: #000;
            font-weight: bold;
            padding: 10px;
            width: 180px;
            white-space: pre-line; /* tillåter radbrytningar */
        }

        /* Wrappar varje artikel för marginal och skugga */
        .story {
            margin: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
            background-clip: padding-box;
        }

        /* Olika bakgrund för News vs Review */
        .news {
            background-color: #FBF4E6; /* blekbeige */
        }
        .review {
            background-color: #DDEBF7; /* ljusblå */
        }

        /* Rad allra överst i varje artikel-cell */
        .story-header {
            font-size: 0.9em;
            color: #555;
            margin-bottom: 0.5em;
        }

        /* Rubrik i artikeln */
        .story h3 {
            margin: 0 0 0.5em;
            font-size: 1.2em;
        }
        .story p {
            margin: 0.3em 0;
            line-height: 1.4;
        }
    </style>
</head>
<body>
    <?php
    $paper = $_POST["paper"] ?? "Morning_Edition";

    $url = "https://wwwlab.webug.se/examples/XML/articleservice/articles?paper=" . urlencode($paper);
    $data = file_get_contents($url);

    if ($data === false) {
        die("Kunde inte hämta data från URL:en.");
    }

    $articles = [];
    $currentNewspaper = "";
    $currentArticle = [];
    $currentText = "";

    function starttagg($parser, $NAME, $attributes) {
        global $articles, $currentNewspaper, $currentArticle, $currentText;

        if ($NAME == "NEWSPAPER") {
            $currentNewspaper = $attributes['NAME'] . " (Edition: " . $attributes['TYPE'] . " Subscribers: " . $attributes['SUBSCRIBERS'] . ")";
            $articles[$currentNewspaper] = [];
        } elseif ($NAME == "ARTICLE") {
            $currentArticle = $attributes;
        } elseif ($NAME == "HEADING" || $NAME == "STORY") {
            $currentText = "";
        }
    }

    function sluttagg($parser, $NAME) {
        global $articles, $currentNewspaper, $currentArticle, $currentText;

        if ($NAME == "ARTICLE") {
            $articles[$currentNewspaper][] = $currentArticle;
        } elseif ($NAME == "HEADING" || $NAME == "STORY") {
            $currentArticle[$NAME] = $currentText;
        }
    }

    function chardata($parser, $data) {
        global $currentText;

        $currentText .= $data;
    }

    $parser = xml_parser_create();
    xml_set_element_handler($parser, 'starttagg', 'sluttagg');
    xml_set_character_data_handler($parser, 'chardata');
    xml_parse($parser, $data, true);
    xml_parser_free($parser);

    echo '<table>';
    foreach ($articles as $newspaper => $articleList) {
        // En rad per tidning, med en fast vänstercell + en cell per artikel
        echo '<tr>';
        echo '<td class="newspaper-info">' . $newspaper . '</td>';
        foreach ($articleList as $article) {
            $cls = strtolower($article['DESCRIPTION']); // "news" eller "review"
            echo '<td>';
            echo '<div class="story ' . $cls . '">';
            // ID, datum, typ
            echo '<div class="story-header">'
                . $article['ID'] . ' '
                . $article['TIME'] . ' '
                . $article['DESCRIPTION']
                . '</div>';
            // Rubrik
            echo '<h3>' . $article['HEADING'] . '</h3>';
            // Bruten text i stycken
            $paras = explode("\n", trim($article['STORY']));
            foreach ($paras as $p) {
                echo '<p>' . $p . '</p>';
            }
            echo '</div>';
            echo '</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
    ?>
</body>
</html>

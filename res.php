<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respons</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            vertical-align: top;
        }
        .newspaper-info {
            width: 25%; /* Kolumn för tidningsnamn och edition */
            font-weight: bold;
        }
        .story {
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ccc;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
        }
        .news {
            background-color: #d9f7be; /* Ljusgrön för nyheter */
        }
        .review {
            background-color: #ffd6e7; /* Ljusrosa för recensioner */
        }
        h3 {
            font-size: 1.2em;
            font-weight: bold;
            margin: 0 0 10px;
        }
        p {
            font-size: 1em;
            margin: 5px 0;
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

    echo "<table>";
    foreach ($articles as $newspaper => $articleList) {
        echo "<tr><td colspan='" . count($articleList) . "' style='background-color: #f4b084; color: #000;'><strong>" . $newspaper . "</strong></td></tr>";
        echo "<tr>";
        foreach ($articleList as $article) {
            echo "<td>";
            $class = strtolower($article['DESCRIPTION']);
            echo "<div class='story $class'> news"; // Lägg till klassen här
            // Lägg till en rad för ID, Date och Type
            echo "<p>" . $article['ID'] . " " . $article['TIME'] . " " . $article['DESCRIPTION'] . "</p>";
            // Rubrik
            echo "<h3>" . $article['HEADING'] . "</h3>";
            // Story
            $paragraphs = explode("\n", $article['STORY']);
            foreach ($paragraphs as $paragraph) {
                echo "<p>" . $paragraph . "</p>";
            }
            echo "</div>";
            echo "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    ?>
</body>
</html>

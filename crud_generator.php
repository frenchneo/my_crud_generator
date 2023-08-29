<?php
function createEntity()
{
    $entityName = readline("Quel est le nom de l’entité que vous voulez créer ? ");
    while (true) {
        $field = readline("Quel est le nom du nouveau champ à ajouter à l’entité " . $entityName . " ? (Tapez “done” pour arrêter d’ajouter des champs et générer l’entité)");
        if ($field === "done") {
            break;
        }
        $typeTemp = readline("Quel est le type du champ name ? (string || integer) (string par défaut)");
        $type = $typeTemp === "integer" ? "integer" : "string";
        $fields[] = [
            "entityName" => $entityName,
            "fieldName" => $field,
            "type" => $type
        ];
    }

    echo "Les champs suivants ont été ajoutés à l'entité product : \n";
    print_r($fields);
    foreach ($fields as $field) {
        echo "entityName : " . $field['entityName'] . ", fieldName : " . $field['fieldName'] . ", type : " . $field['type'] . "\n";
    }
    $sqlFileName = $entityName . ".sql";
    writeInfile('src/' . $sqlFileName, createSqlSchema($fields));
    writeInfile('src/create' . ucfirst($entityName) . ".php", generateCreateFile($fields));
    writeInfile('src/get' . ucfirst($entityName) . ".php", generateGetFile($fields));
}

function createFile($name)
{
    $file = fopen($name, "w");
    fclose($file);
}

function createSqlSchema($fields)
{
    $entityName = $fields[0]['entityName'];
    $sql = "CREATE TABLE " . "`$entityName`" . "\n(\n";
    $sql .= "  `id` int PRIMARY KEY," . "\n";
    $count = 0;
    foreach ($fields as $field) {
        $count > 0 ? $sql .= ",\n" : $sql .= "";
        $fieldName = $field['fieldName'];
        $field['type'] = $field['type'] === "integer" ? "int" : "varchar(255)";
        $sql .= "  " . "`$fieldName`" . " " . $field['type'];
        $count++;
    }
    $sql .= ");";
    return $sql;
}

function generateCreateFile($fields)
{
    $content = "<?php\n";
    $content .= '$data = json_decode(file_get_contents("php://input"), true);' . "\n";
    $content .= "var_dump(\$data);\n\n";

    $content .= '$servername = "localhost";' . "\n";
    $content .= '$username = "root";' . "\n";
    $content .= '$password = "Password$13";' . "\n";
    $content .= '$database = "crud_generator";' . "\n\n";
    $content .= '$conn = new mysqli($servername, $username, $password, $database);' . "\n\n";
    $content .= 'if ($conn->connect_error) {' . "\n";
    $content .= '    die("Erreur de connexion : " . $conn->connect_error);' . "\n";
    $content .= '}' . "\n\n";

    $sql = 'INSERT INTO ' . $fields[0]['entityName'] . ' (';
    $fieldNames = array_column($fields, 'fieldName');
    $sql .= implode(', ', $fieldNames);
    $sql .= ") VALUES ('\" . ";

    foreach ($fieldNames as $index => $fieldName) {
        print($index);
        if ($index !== 0) {
            $sql .= '. "\', \'" . $data[\'' . "$fieldName" . '\'] ';
        } else {
            $sql .= "\$data['$fieldName'] ";
        }
    }
    $sql .= '. "\')";' . "\n";
    $content .= '$sql = ' . '"' . $sql;

    $content .= 'if ($conn->query($sql) === TRUE) {' . "\n";
    $content .= '    echo "Nouvel enregistrement créé avec succès";' . "\n";
    $content .= '} else {' . "\n";
    $content .= '    echo "Erreur : " . $sql . "<br>" . $conn->error;' . "\n";
    $content .= '}' . "\n";

    $content .= '$conn->close();' . "\n";

    $content .= '?>';

    return $content;
}

function generateGetFile($fields)
{
    $content = "<?php\n";
    $content .= '$servername = "localhost";' . "\n";
    $content .= '$username = "root";' . "\n";
    $content .= '$password = "Password$13";' . "\n";
    $content .= '$database = "crud_generator";' . "\n\n";

    $content .= '$conn = new mysqli($servername, $username, $password, $database);' . "\n\n";
    $content .= 'if ($conn->connect_error) {' . "\n";
    $content .= '    die("Erreur de connexion : " . $conn->connect_error);' . "\n";
    $content .= '}' . "\n\n";
    // $content .= 'echo "Connexion réussie à la base de données MySQL";' . "\n\n";

    $content .= '$sql = "SELECT * FROM ' . $fields[0]['entityName'] . '";' . "\n";
    $content .= '$result = $conn->query($sql);' . "\n\n";

    $content .= '$' . $fields[0]['entityName'] . 's = array();' . "\n";
    $content .= 'if ($result->num_rows > 0) {' . "\n";
    $content .= '    while ($row = $result->fetch_assoc()) {' . "\n";
    $content .= '        $' . $fields[0]['entityName'] . 's[] = $row;' . "\n";
    $content .= '    }' . "\n";
    $content .= '}' . "\n\n";

    $content .= 'echo json_encode($' . $fields[0]['entityName'] . 's);' . "\n\n";

    $content .= '$conn->close();' . "\n\n";

    $content .= '?>';

    return $content;
}

function writeInfile($file, $content)
{
    $filePath = dirname($file);

    if (!file_exists($filePath)) {
        mkdir($filePath, 0777, true);
    }

    $fileHandle = fopen($file, "w");

    if ($fileHandle) {
        fwrite($fileHandle, $content);
        fclose($fileHandle);
        return true;
    } else {
        return false;
    }
}


createEntity();
?>
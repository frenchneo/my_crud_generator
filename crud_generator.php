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
    foreach ($fields as $field) {
        echo "entityName : " . $field['entityName'] . ", fieldName : " . $field['fieldName'] . ", type : " . $field['type'] . "\n";
    }
    $sqlFileName = $entityName . ".sql";
    createFile($sqlFileName);
    writeInfile($sqlFileName, createSqlSchema($fields));
    writeInfile($entityName . ".php", createEntityClass($fields));
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

function createEntityClass($fields)
{
    $className = $fields[0]['entityName'];
    $class = "<?php\n";
    $class = "class " . $className . " {\n";
    $class .= "    private \$id;\n";
    foreach ($fields as $field) {
        $class .= "    private \$" . $field['fieldName'] . ";\n";
    }
    $class .= "\n";
    $class .= "    public function __construct(";
    $count = 0;
    foreach ($fields as $field) {
        $count > 0 ? $class .= ", " : $class .= "";
        $class .= "$" . $field['fieldName'];
        $count++;
    }
    $class .= ") {\n";
    foreach ($fields as $field) {
        $class .= "        \$this->" . $field['fieldName'] . " = $" . $field['fieldName'] . ";\n";
    }
    $class .= "    }\n";
    $class .= "\n";
    $class .= "    public function save() {\n";
    $class .= "        global \$conn;\n";
    $class .= "\n";
    $class .= "        \$sql = \"INSERT INTO " . $className . " (";
    $count = 0;
    foreach ($fields as $field) {
        $count > 0 ? $class .= ", " : $class .= "";
        $class .= $field['fieldName'];
        $count++;
    }
    $class .= ") VALUES (";

    $count = 0;
    foreach ($fields as $field) {
        $count > 0 ? $class .= ", " : $class .= "";
        $class .= "'\$this->" . $field['fieldName'] . "'";
        $count++;
    }
    $class .= ")\";\n";
    $class .= "\n";
    $class .= "        if (\$conn->query(\$sql) === FALSE) {\n";
    $class .= "            echo \"Erreur lors de l'enregistrement du produit : \" . \$conn->error;\n";
    $class .= "        }\n";
    $class .= "    }\n";
    $class .= "\n";
    $class .= "    public static function index() {\n";
    $class .= "        global \$conn;\n";
    $class .= "\n";
    $class .= "        \$sql = \"SELECT * FROM " . $className . "\";\n";
    $class .= "        \$result = \$conn->query(\$sql);\n";

    $class .= "\n";
    $class .= "        \$" . $className . "s = array();\n";
    $class .= "        if (\$result->num_rows > 0) {\n";
    $class .= "            while (\$row = \$result->fetch_assoc()) {\n";
    $class .= "                \$" . $className . " = new " . $className . "(";
    $count = 0;
    foreach ($fields as $field) {
        $count > 0 ? $class .= ", " : $class .= "";
        $class .= "\$row['" . $field['fieldName'] . "']";
        $count++;
    }
    $class .= ");\n";
    $class .= "                \$" . $className . "->id = \$row['id'];\n";
    $class .= "                \$" . $className . "s[] = \$" . $className . ";\n";
    $class .= "            }\n";
    $class .= "        }\n";
    $class .= "\n";
    $class .= "        return \$" . $className . "s;\n";
    $class .= "    }\n";
    $class .= "\n";
    $class .= "    public static function show(\$id) {\n";
    $class .= "        global \$conn;\n";
    $class .= "\n";
    $class .= "        \$sql = \"SELECT * FROM " . $className . " WHERE id = \$id\";\n";
    $class .= "        \$result = \$conn->query(\$sql);\n";
    $class .= "\n";
    $class .= "        if (\$result->num_rows == 1) {\n";
    $class .= "            \$row = \$result->fetch_assoc();\n";
    $class .= "            \$" . $className . " = new " . $className . "(";
    $count = 0;

    foreach ($fields as $field) {
        $count > 0 ? $class .= ", " : $class .= "";
        $class .= "\$row['" . $field['fieldName'] . "']";
        $count++;
    }
    $class .= ");\n";
    $class .= "            \$" . $className . "->id = \$row['id'];\n";
    $class .= "            return \$" . $className . ";\n";
    $class .= "        } else {\n";
    $class .= "            return null;\n";
    $class .= "        }\n";
    $class .= "    }\n";
    $class .= "\n";

    $class .= "    public function load(\$id) {\n";
    $class .= "        global \$conn;\n";
    $class .= "\n";
    $class .= "        \$sql = \"SELECT * FROM " . $className . " WHERE id = \$id\";\n";
    $class .= "        \$result = \$conn->query(\$sql);\n";
    $class .= "\n";
    $class .= "        if (\$result->num_rows == 1) {\n";
    $class .= "            \$row = \$result->fetch_assoc();\n";
    $class .= "            \$this->id = \$row['id'];\n";
    foreach ($fields as $field) {
        $class .= "            \$this->" . $field['fieldName'] . " = \$row['" . $field['fieldName'] . "'];\n";
    }
    $class .= "        }\n";
    $class .= "    }\n";
    $class .= "\n";
    $class .= "    public function update() {\n";
    $class .= "        global \$conn;\n";
    $class .= "\n";
    $class .= "        \$sql = \"UPDATE " . $className . " SET ";
    $count = 0;

    foreach ($fields as $field) {
        $count > 0 ? $class .= ", " : $class .= "";
        $class .= $field['fieldName'] . " = '\$this->" . $field['fieldName'] . "'";
        $count++;
    }
    $class .= " WHERE id = \$this->id\";\n";

    $class .= "\n";
    $class .= "        if (\$conn->query(\$sql) === FALSE) {\n";
    $class .= "            echo \"Erreur lors de la mise à jour du produit : \" . \$conn->error;\n";
    $class .= "        }\n";
    $class .= "    }\n";
    $class .= "\n";

    $class .= "    public function delete() {\n";
    $class .= "        global \$conn;\n";
    $class .= "\n";
    $class .= "        \$sql = \"DELETE FROM " . $className . " WHERE id = \$this->id\";\n";
    $class .= "\n";
    $class .= "        if (\$conn->query(\$sql) === FALSE) {\n";
    $class .= "            echo \"Erreur lors de la suppression du produit : \" . \$conn->error;\n";
    $class .= "        }\n";
    $class .= "    }\n";
    $class .= "}\n";
    $class .= "?>";

    return $class;
}
function writeInfile($file, $content)
{
    $file = fopen($file, "w");
    fwrite($file, $content);
}

createEntity();
?>
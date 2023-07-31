<?php
class users
{
    private $id;
    private $jo;
    private $jack;
    private $mleh;

    public function __construct($jo, $jack, $mleh)
    {
        $this->jo = $jo;
        $this->jack = $jack;
        $this->mleh = $mleh;
    }

    public function save()
    {
        global $conn;

        $sql = "INSERT INTO users (jo, jack, mleh) VALUES ('$this->jo', '$this->jack', '$this->mleh')";

        if ($conn->query($sql) === FALSE) {
            echo "Erreur lors de l'enregistrement du produit : " . $conn->error;
        }
    }

    public static function index()
    {
        global $conn;

        $sql = "SELECT * FROM users";
        $result = $conn->query($sql);

        $userss = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $users = new users($row['jo'], $row['jack'], $row['mleh']);
                $users->id = $row['id'];
                $userss[] = $users;
            }
        }

        return $userss;
    }

    public static function show($id)
    {
        global $conn;

        $sql = "SELECT * FROM users WHERE id = $id";
        $result = $conn->query($sql);

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $users = new users($row['jo'], $row['jack'], $row['mleh']);
            $users->id = $row['id'];
            return $users;
        } else {
            return null;
        }
    }

    public function load($id)
    {
        global $conn;

        $sql = "SELECT * FROM users WHERE id = $id";
        $result = $conn->query($sql);

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $this->id = $row['id'];
            $this->jo = $row['jo'];
            $this->jack = $row['jack'];
            $this->mleh = $row['mleh'];
        }
    }

    public function update()
    {
        global $conn;

        $sql = "UPDATE users SET jo = '$this->jo', jack = '$this->jack', mleh = '$this->mleh' WHERE id = $this->id";

        if ($conn->query($sql) === FALSE) {
            echo "Erreur lors de la mise à jour du produit : " . $conn->error;
        }
    }

    public function delete()
    {
        global $conn;

        $sql = "DELETE FROM users WHERE id = $this->id";

        if ($conn->query($sql) === FALSE) {
            echo "Erreur lors de la suppression du produit : " . $conn->error;
        }
    }
}
?>
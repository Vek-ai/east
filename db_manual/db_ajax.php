<?php
$host = "localhost";
$user = "benguetf_eastkentucky";                
$password = "O3K9-T6&{oW[";         
$dbname = "benguetf_eastkentucky";  
$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) die("DB Connection failed");
$action = $_POST['action'] ?? '';

if ($action === 'view_table') {
    $table = $conn->real_escape_string($_POST['table']);
    $res = $conn->query("SELECT * FROM `$table` LIMIT 100");
    if ($res && $res->num_rows > 0) {
        $cols = array_keys($res->fetch_assoc());
        $res->data_seek(0);
        echo "<table><tr>";
        foreach ($cols as $col) echo "<th>" . htmlspecialchars($col) . "</th>";
        echo "<th>Action</th></tr>";
        while ($row = $res->fetch_assoc()) {
            $id_col = $cols[0];
            $id_val = htmlspecialchars($row[$id_col]);
            echo "<tr><form>";
            echo "<input type='hidden' name='table' value='" . htmlspecialchars($table) . "'>";
            echo "<input type='hidden' name='id_col' value='" . htmlspecialchars($id_col) . "'>";
            echo "<input type='hidden' name='id_val' value='" . htmlspecialchars($id_val) . "'>";
            foreach ($cols as $col)
                echo "<td><input type='text' name='" . htmlspecialchars($col) . "' value='" . htmlspecialchars($row[$col]) . "'></td>";
            echo "<td><button class='btn save-row'>💾</button> <button class='btn delete-btn delete-row'>🗑️</button></td>";
            echo "</form></tr>";
        }
        echo "</table>";
    } else echo "<p>No rows found.</p>";
    exit;
}

if ($action === 'update_row') {
    $table = $_POST['table'];
    $id_col = $_POST['id_col'];
    $id_val = $_POST['id_val'];
    $updates = [];
    foreach ($_POST as $key => $val)
        if (!in_array($key, ['action','table','id_col','id_val']))
            $updates[] = "`$key`='" . $conn->real_escape_string($val) . "'";
    $sql = "UPDATE `$table` SET " . implode(",", $updates) . " WHERE `$id_col`='$id_val'";
    echo $conn->query($sql) ? "✅ Updated" : "❌ " . $conn->error;
    exit;
}

if ($action === 'delete_row') {
    $table = $_POST['table'];
    $id_col = $_POST['id_col'];
    $id_val = $_POST['id_val'];
    $sql = "DELETE FROM `$table` WHERE `$id_col`='$id_val'";
    echo $conn->query($sql) ? "🗑️ Deleted" : "❌ " . $conn->error;
    exit;
}

if ($action === 'run_query') {
    $sql = trim($_POST['sql_query']);
    if (!$sql) exit("⚠️ Empty query");
    $result = $conn->query($sql);
    if (!$result) exit("<p>❌ " . htmlspecialchars($conn->error) . "</p>");
    if ($result instanceof mysqli_result) {
        echo "<table><tr>";
        while ($field = $result->fetch_field()) echo "<th>" . htmlspecialchars($field->name) . "</th>";
        echo "</tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $val) echo "<td>" . htmlspecialchars($val) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else echo "<p>✅ Query executed</p>";
    exit;
}
?>

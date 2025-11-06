<?php
$host = "localhost";
$user = "benguetf_eastkentucky";                
$password = "O3K9-T6&{oW[";         
$dbname = "benguetf_eastkentucky";  

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$conn->set_charset("utf8mb4");

$tables = [];
$res = $conn->query("SHOW TABLES");
while ($row = $res->fetch_array()) $tables[] = $row[0];

$msg = "";
$query_result = null;

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    if (isset($_POST['update_row'])) {
        $table  = $_POST['table'];
        $id_col = $_POST['id_col'];
        $id_val = $_POST['id_val'];

        $updates = [];
        foreach ($_POST as $key => $val) {
            if (!in_array($key, ['update_row', 'table', 'id_col', 'id_val'])) {
                $updates[] = "`$key`='" . $conn->real_escape_string($val) . "'";
            }
        }

        $sql = "UPDATE `$table` SET " . implode(",", $updates) . " WHERE `$id_col`='$id_val'";
        $msg = $conn->query($sql) ? "✅ Record updated" : "❌ " . $conn->error;
    }

    if (isset($_POST['delete_row'])) {
        $table  = $_POST['table'];
        $id_col = $_POST['id_col'];
        $id_val = $_POST['id_val'];

        $sql = "DELETE FROM `$table` WHERE `$id_col`='$id_val'";
        $msg = $conn->query($sql) ? "🗑️ Record deleted" : "❌ " . $conn->error;
    }

    if (isset($_POST['run_query'])) {
        $sql = trim($_POST['sql_query']);
        $query_result = $conn->query($sql);
        $msg = $query_result ? "✅ Query executed successfully" : "❌ " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Database Manager</title>
<style>
body { font-family: Arial, sans-serif; background: #f5f6f7; margin: 0; padding: 20px; }
.container { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,.1); }
table { border-collapse: collapse; width: 100%; margin-top: 10px; }
th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
th { background: #f0f0f0; }
input[type=text], select, textarea { width: 100%; box-sizing: border-box; padding: 6px; }
.btn { background: #007bff; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; }
.btn:hover { background: #0056b3; }
.delete-btn { background: #dc3545; }
.delete-btn:hover { background: #a71d2a; }
</style>
</head>
<body>
<div class="container">
    <h2>🗄️ Database Manager</h2>

    <form method="get">
        <select name="table" onchange="this.form.submit()">
            <option value="">-- Select Table --</option>
            <?php foreach ($tables as $t): ?>
                <option value="<?= htmlspecialchars($t) ?>" <?= ($t == ($_GET['table'] ?? '')) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($t) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if ($msg): ?>
        <p><?= htmlspecialchars($msg) ?></p>
    <?php endif; ?>

    <?php if (!empty($_GET['table'])):
        $table = $conn->real_escape_string($_GET['table']);
        $res = $conn->query("SELECT * FROM `$table` LIMIT 100");
        if ($res && $res->num_rows > 0):
            $cols = array_keys($res->fetch_assoc());
            $res->data_seek(0);
    ?>
        <table>
            <tr>
                <?php foreach ($cols as $c): ?>
                    <th><?= htmlspecialchars($c) ?></th>
                <?php endforeach; ?>
                <th>Action</th>
            </tr>

            <?php while ($row = $res->fetch_assoc()): ?>
                <?php $id_col = $cols[0]; $id_val = htmlspecialchars($row[$id_col]); ?>
                <tr>
                    <form method="post">
                        <input type="hidden" name="table" value="<?= htmlspecialchars($table) ?>">
                        <input type="hidden" name="id_col" value="<?= htmlspecialchars($id_col) ?>">
                        <input type="hidden" name="id_val" value="<?= htmlspecialchars($id_val) ?>">

                        <?php foreach ($cols as $c): ?>
                            <td><input type="text" name="<?= htmlspecialchars($c) ?>" value="<?= htmlspecialchars($row[$c]) ?>"></td>
                        <?php endforeach; ?>

                        <td>
                            <button name="update_row" class="btn">💾</button>
                            <button name="delete_row" class="btn delete-btn" onclick="return confirm('Delete record?')">🗑️</button>
                        </td>
                    </form>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No rows found in <b><?= htmlspecialchars($table) ?></b>.</p>
    <?php endif; endif; ?>

    <h3>⚙️ Run SQL Query</h3>
    <form method="post">
        <textarea name="sql_query" rows="4" placeholder="Enter SQL query here..."><?= htmlspecialchars($_POST['sql_query'] ?? '') ?></textarea><br>
        <button class="btn" name="run_query">Execute</button>
    </form>

    <?php if ($query_result && $query_result instanceof mysqli_result): ?>
        <table>
            <tr>
                <?php while ($field = $query_result->fetch_field()): ?>
                    <th><?= htmlspecialchars($field->name) ?></th>
                <?php endwhile; ?>
            </tr>
            <?php while ($row = $query_result->fetch_assoc()): ?>
                <tr>
                    <?php foreach ($row as $val): ?>
                        <td><?= htmlspecialchars($val) ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php endif; ?>
</div>
</body>
</html>

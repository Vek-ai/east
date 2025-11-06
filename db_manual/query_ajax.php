<?php
$host = "localhost";
$user = "benguetf_eastkentucky";                
$password = "O3K9-T6&{oW[";         
$dbname = "benguetf_eastkentucky";  
$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$res = $conn->query("SHOW TABLES");
$tables = [];
while ($row = $res->fetch_array()) $tables[] = $row[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Database Manager (AJAX)</title>
<style>
body { font-family: Arial, sans-serif; background: #f5f6f7; margin: 0; padding: 20px; }
.container { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,.1); }
h2 { margin-top: 0; }
select, textarea, input[type=text] { padding: 6px; margin: 4px 0; width: 100%; box-sizing: border-box; }
table { border-collapse: collapse; width: 100%; margin-top: 10px; }
th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
th { background: #f0f0f0; }
.btn { background: #007bff; color: white; border: none; padding: 6px 12px; cursor: pointer; border-radius: 4px; }
.btn:hover { background: #0056b3; }
.delete-btn { background: #dc3545; }
.delete-btn:hover { background: #a71d2a; }
.section { margin-bottom: 40px; }
#table-view { margin-top: 10px; }
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="container">
<h2>🗄️ Database Manager (AJAX)</h2>
<div class="section">
<h3>📋 Select Table</h3>
<select id="tableSelect">
<option value="">-- Select Table --</option>
<?php foreach ($tables as $t): ?>
<option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></option>
<?php endforeach; ?>
</select>
<div id="table-view"></div>
</div>
<div class="section">
<h3>⚙️ Run SQL Query</h3>
<textarea id="sql_query" rows="4"></textarea><br>
<button class="btn" id="runQuery">Execute</button>
<div id="query-result"></div>
</div>
</div>
<script>
$(document).ready(function(){
    $('#tableSelect').change(function(){
        let table = $(this).val();
        if (!table) return $('#table-view').html('');
        $.post('db_ajax.php', {action:'view_table',table:table}, function(data){$('#table-view').html(data);});
    });
    $(document).on('click', '.save-row', function(e){
        e.preventDefault();
        let form = $(this).closest('form');
        $.post('db_ajax.php', form.serialize() + '&action=update_row', function(resp){alert(resp);});
    });
    $(document).on('click', '.delete-row', function(e){
        e.preventDefault();
        if (!confirm('Delete this record?')) return;
        let form = $(this).closest('form');
        $.post('db_ajax.php', form.serialize() + '&action=delete_row', function(resp){alert(resp);form.closest("tr").remove();});
    });
    $('#runQuery').click(function(){
        let sql = $('#sql_query').val().trim();
        if (!sql) return;
        $.post('db_ajax.php', {action:'run_query',sql_query:sql}, function(data){$('#query-result').html(data);});
    });
});
</script>
</body>
</html>

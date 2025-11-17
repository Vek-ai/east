<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

if(isset($_POST['fetch_available'])){
    $color = mysqli_real_escape_string($conn, $_POST['color']);
    $grade = mysqli_real_escape_string($conn, $_POST['grade']);
    $gauge = mysqli_real_escape_string($conn, $_POST['gauge']);

    $checkQuery = "SELECT * FROM coil_product 
                   WHERE 
                       color_sold_as = '$color' AND
                       grade = '$grade' AND
                       gauge = '$gauge'";
    $result = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
        ?>
        <div class="card">
            <div class="card-body">
                <table id="coilProdTable" class="table align-middle text-nowrap mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Coil #</th>
                            <th scope="col">Color</th>
                            <th scope="col">Grade</th>
                            <th scope="col">Gauge</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            while($row = mysqli_fetch_assoc($result)){
                            ?>
                            <td><?= $row['entry_no'] ?></td>
                            <td><?= getColorName($row['color_sold_as']) ?></td>
                            <td><?= getGradeName($row['grade']) ?></td>
                            <td><?= getGaugeName($row['gauge']) ?></td>
                            <?php
                            }                    
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
<?php
    }
}
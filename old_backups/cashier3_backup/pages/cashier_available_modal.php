<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

if(isset($_POST['fetch_available'])){
    $color = mysqli_real_escape_string($conn, $_POST['color']);
    $width = mysqli_real_escape_string($conn, $_POST['width']);

    $checkQuery = "SELECT * FROM coil WHERE width = '$width' AND color = '$color'";
    $result = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
        ?>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Stock Details</h6>
                    <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table id="productTable" class="table align-middle text-nowrap mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Products</th>
                                <th scope="col">Grade</th>
                                <th scope="col">Color</th>
                                <th scope="col">Gauge</th>
                                <th scope="col">Width</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                while($row = mysqli_fetch_assoc($result)){
                                ?>
                                <td><?= $row['coil'] ?></td>
                                <td><?= getGradeName($row['grade']) ?></td>
                                <td><?= getColorName($row['color']) ?></td>
                                <td><?= getGaugeName($row['gauge']) ?></td>
                                <td><?= $row['width'] ?></td>
                                <?php
                                }                    
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
<?php
    }
}
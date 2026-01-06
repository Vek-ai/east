<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

if (isset($_POST['fetch_in_stock_modal'])) {
    $product_id = intval($_POST['id']);
    $color = !empty($_POST['color']) ? intval($_POST['color']) : null;
    $grade = !empty($_POST['grade']) ? intval($_POST['grade']) : null;
    $gauge = !empty($_POST['gauge']) ? intval($_POST['gauge']) : null;

    if (empty($product_id)) {
        exit;
    }

    $product = getProductDetails($product_id);
    $product_category = (int)$product['product_category'];

    $invWhere = ["Product_id = $product_id"];
    if ($color !== null) $invWhere[] = "color_id = $color";
    if ($grade !== null) $invWhere[] = "grade = $grade";
    if ($gauge !== null) $invWhere[] = "gauge = $gauge";

    $invWhereSql = implode(' AND ', $invWhere);

    $coilWhere = ["product_id = $product_id", "hidden = 0"];
    if ($color !== null) $coilWhere[] = "color_sold_as = $color";
    if ($grade !== null) $coilWhere[] = "grade_sold_as = $grade";
    if ($gauge !== null) $coilWhere[] = "gauge_sold_as = $gauge";

    $coilWhereSql = implode(' AND ', $coilWhere);

    $orderProdWhere = ["productid = $product_id"];
    if ($color !== null) $orderProdWhere[] = "custom_color = $color";
    if ($grade !== null) $orderProdWhere[] = "custom_grade = $grade";
    if ($gauge !== null) $orderProdWhere[] = "custom_gauge = $gauge";

    $orderProdWhereSql = implode(' AND ', $orderProdWhere);
    ?>

    <div class="card">
        <div class="card-body">
            <h5 class="mb-3 fs-6 fw-semibold text-center">Inventory</h5>

            <?php
            if (in_array($product_category, [3, 4])) {
                if (empty($color) || empty($grade) || empty($gauge)) {
                    ?>
                    <div class="table-responsive">
                        <?php
                            $grade = $product['grade'];
                            $gauge = $product['gauge'];
                            $color = $product['color'];

                            $gradeArr = [];
                            if (!empty($product['grade'])) {
                                $decoded = json_decode($product['grade'], true);
                                if (is_array($decoded)) $gradeArr = $decoded;
                            }

                            $gaugeArr = [];
                            if (!empty($product['gauge'])) {
                                $decoded = json_decode($product['gauge'], true);
                                if (is_array($decoded)) $gaugeArr = $decoded;
                            }

                            $colorArr = [];
                            if (!empty($product['color'])) {
                                $decoded = json_decode($product['color'], true);
                                if (is_array($decoded)) $colorArr = $decoded;
                            }

                            $gradeList = !empty($gradeArr) ? implode(',', array_map('intval', $gradeArr)) : '0';
                            $gaugeList = !empty($gaugeArr) ? implode(',', array_map('intval', $gaugeArr)) : '0';
                            $colorList = !empty($colorArr) ? implode(',', array_map('intval', $colorArr)) : '0';

                            $sql = "SELECT * FROM coil_product
                                    WHERE color_sold_as IN ($colorList)
                                    AND grade_sold_as IN ($gradeList)
                                    AND gauge_sold_as IN ($gaugeList)
                                    AND remaining_feet > 0
                                    ORDER BY entry_no ASC";

                            $result = mysqli_query($conn, $sql);

                            if ($result && mysqli_num_rows($result) > 0) {
                                echo '<table class="table table-bordered table-striped align-middle text-center">';
                                echo '<thead>
                                        <tr>
                                            <th>Coil #</th>
                                            <th>Color</th>
                                            <th>Grade</th>
                                            <th>Gauge</th>
                                            <th>Current Length</th>
                                        </tr>
                                    </thead>
                                    <tbody>';
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $coil_no = $row['entry_no'];
                                    $coil_color = getColorName($row['color_sold_as'] ?? $row['color_close'] ?? '');
                                    $coil_grade = getGradeName($row['grade_sold_as']);
                                    $coil_gauge = getGaugeName($row['gauge_sold_as']);
                                    $current_length = $row['remaining_feet'];

                                    echo "<tr>
                                            <td>{$coil_no}</td>
                                            <td>{$coil_color}</td>
                                            <td>{$coil_grade}</td>
                                            <td>{$coil_gauge}</td>
                                            <td>{$current_length}</td>
                                        </tr>";
                                }
                                echo '</tbody></table>';
                            } else {
                                echo '<p class="text-center">No available coil products found.</p>';
                            }
                        ?>
                    </div>
                    <?php
                } else {

                    $onHandRes = mysqli_query($conn, "
                        SELECT IFNULL(SUM(remaining_feet),0) AS on_hand
                        FROM coil_product
                        WHERE color_sold_as = $color
                        AND grade_sold_as = $grade
                        AND gauge_sold_as = $gauge
                        AND hidden = 0
                    ");
                    $onHand = (float)mysqli_fetch_assoc($onHandRes)['on_hand'];

                    $committedRes = mysqli_query($conn, "
                        SELECT IFNULL(SUM(remaining_feet),0) AS committed
                        FROM coil_product
                        WHERE color_sold_as = $color
                        AND grade_sold_as = $grade
                        AND gauge_sold_as = $gauge
                        AND status IN (0,1)
                        AND hidden = 0
                    ");
                    $committed = (float)mysqli_fetch_assoc($committedRes)['committed'];

                    $available = max(0, $onHand - $committed);
                    ?>

                    <div class="p-3 border rounded bg-light mb-2">
                        <div class="row mb-2">
                            <div class="col fw-bold fs-6">
                                ON HAND
                            </div>
                            <div class="col text-end fs-6 fw-bold">
                                <?= $onHand ?> FT
                            </div>
                        </div>
                    </div>
                    <div class="p-3 border rounded bg-light mb-2">
                        <div class="row mb-2">
                            <div class="col fw-bold fs-6">
                                COMMITTED
                            </div>
                            <div class="col text-end fs-6 fw-bold">
                                <?= $committed ?> FT
                            </div>
                        </div>
                    </div>
                    <div class="p-3 border rounded bg-light mb-2">
                        <div class="row">
                            <div class="col fw-bold fs-6">
                                AVAILABLE
                            </div>
                            <div class="col text-end fs-6 fw-bold">
                                <?= $available ?> FT
                            </div>
                        </div>
                    </div>

                <?php }
            } else {

                $onHandRes = mysqli_query($conn, "
                    SELECT IFNULL(SUM(quantity_ttl),0) AS on_hand
                    FROM inventory
                    WHERE $invWhereSql
                ");
                $onHand = (float)mysqli_fetch_assoc($onHandRes)['on_hand'];

                $committedRes = mysqli_query($conn, "
                    SELECT IFNULL(SUM(quantity),0) AS committed
                    FROM order_product
                    WHERE $orderProdWhereSql
                    AND status IN (0,1)
                ");
                $committed = (float)mysqli_fetch_assoc($committedRes)['committed'];

                $available = max(0, $onHand - $committed);
                ?>

                <div class="p-3 border rounded bg-light mb-2">
                    <div class="row mb-2">
                        <div class="col fw-bold fs-6">
                            ON HAND
                        </div>
                        <div class="col text-end fs-6 fw-bold">
                            <?= $onHand ?> PCS
                        </div>
                    </div>
                </div>
                <div class="p-3 border rounded bg-light mb-2">
                    <div class="row mb-2">
                        <div class="col fw-bold fs-6">
                            COMMITTED
                        </div>
                        <div class="col text-end fs-6 fw-bold">
                            <?= $committed ?> PCS
                        </div>
                    </div>
                </div>
                <div class="p-3 border rounded bg-light mb-2">
                    <div class="row">
                        <div class="col fw-bold fs-6">
                            AVAILABLE
                        </div>
                        <div class="col text-end fs-6 fw-bold">
                            <?= $available ?> PCS
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
<?php
}

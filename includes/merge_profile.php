<?php
require 'dbconn.php';

$query = "SELECT * FROM profile_type WHERE hidden = 0 ORDER BY profile_type_id ASC";
$result = mysqli_query($conn, $query);

$merged = [];

while ($row = mysqli_fetch_assoc($result)) {
    $type = $row['profile_type'];
    $id = $row['profile_type_id'];
    $category = json_decode($row['product_category'], true);
    $category = is_array($category) ? $category : [$category];

    if (!isset($merged[$type])) {
        $merged[$type] = [
            'keep_id' => $id,
            'categories' => $category,
            'rows_to_delete' => []
        ];
    } else {
        $merged[$type]['categories'] = array_unique(array_merge($merged[$type]['categories'], $category));
        $merged[$type]['rows_to_delete'][] = $id;
    }
}

foreach ($merged as $type => $data) {
    $json_cat = json_encode(array_values($data['categories']));
    $keep_id = $data['keep_id'];

    $update = "UPDATE profile_type SET product_category = '" . mysqli_real_escape_string($conn, $json_cat) . "' WHERE profile_type_id = $keep_id";
    mysqli_query($conn, $update);

    if (!empty($data['rows_to_delete'])) {
        $ids_to_delete = implode(",", array_map('intval', $data['rows_to_delete']));
        $delete = "DELETE FROM profile_type WHERE profile_type_id IN ($ids_to_delete)";
        mysqli_query($conn, $delete);
    }
}

echo "Merging complete.";
?>

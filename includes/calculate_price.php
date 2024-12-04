<?php
function calculateUnitPrice($basePrice, $lengthFeet, $lengthInch, $panelType, $soldByFeet, $bends, $hems) {
    global $conn;

    $pricePerBend = getSetting('price_per_bend');
    $pricePerHem = getSetting('price_per_hem');
    $ventedPrice = 0.50;

    $totalLength = $lengthFeet + ($lengthInch / 12);
    $extraCostPerFoot = ($panelType === 'vented') ? $ventedPrice : 0;

    if ($soldByFeet == 1) {
        $computedPrice = $totalLength * ($basePrice + $extraCostPerFoot);
    } else {
        $computedPrice = $basePrice + $extraCostPerFoot;
    }

    $bendCost = $bends * $pricePerBend;
    $hemCost = $hems * $pricePerHem;

    $totalPrice = $computedPrice + $bendCost + $hemCost;

    return round($totalPrice, 2);
}
?>
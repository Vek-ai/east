<?php
function calculateUnitPrice($basePrice, $lengthFeet, $lengthInch, $panelType, $soldByFeet, $bends, $hems) {
    global $conn;

    $pricePerBend = getPaymentSetting('price_per_bend');
    $pricePerHem = getPaymentSetting('price_per_hem');
    $extraCostPerFoot = 0;
    if ($panelType === 'vented') {
        $extraCostPerFoot = getPaymentSetting('vented');
    } elseif ($panelType === 'drip_stop') {
        $extraCostPerFoot = getPaymentSetting('drip_stop');
    }

    $totalLength = $lengthFeet + ($lengthInch / 12);

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
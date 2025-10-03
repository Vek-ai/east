<?php
function calculateUnitPrice($basePrice = 0, $lengthFeet = 0, $lengthInch = 0, $panelType = '', $soldByFeet = 0, $bends = 0, $hems = 0, $color = '', $grade = '', $gauge = '') {
    global $conn;

    $basePrice    = is_numeric($basePrice) ? floatval($basePrice) : 0;
    $lengthFeet   = is_numeric($lengthFeet) ? floatval($lengthFeet) : 0;
    $lengthInch   = is_numeric($lengthInch) ? floatval($lengthInch) : 0;
    $soldByFeet   = ($soldByFeet == 1) ? 1 : 0;
    $bends        = is_numeric($bends) ? intval($bends) : 0;
    $hems         = is_numeric($hems) ? intval($hems) : 0;
    $panelType    = $panelType ?? '';
    $color        = $color ?? '';
    $grade        = $grade ?? '';
    $gauge        = $gauge ?? '';

    $pricePerBend = floatval(getPaymentSetting('price_per_bend') ?? 0);
    $pricePerHem  = floatval(getPaymentSetting('price_per_hem') ?? 0);

    $extraCostPerFoot = 0;
    if ($panelType === 'vented') {
        $extraCostPerFoot = floatval(getPaymentSetting('vented') ?? 0);
    } elseif ($panelType === 'drip_stop') {
        $extraCostPerFoot = floatval(getPaymentSetting('drip_stop') ?? 0);
    }

    $totalLength = $lengthFeet + ($lengthInch / 12);
    if ($totalLength <= 0) $totalLength = 1;

    if ($soldByFeet == 1) {
        $computedPrice = $totalLength * ($basePrice + $extraCostPerFoot);
    } else {
        $computedPrice = $basePrice + $extraCostPerFoot;
    }

    $bendCost = $bends * $pricePerBend;
    $hemCost  = $hems * $pricePerHem;

    $computedPrice = $totalLength * $basePrice;

    $totalPrice = $computedPrice + $bendCost + $hemCost;

    $multiplier = getMultiplierValue($color, $grade, $gauge) ?? 1;
    $totalPrice *= $multiplier;

    return round($totalPrice, 2);
}

?>
<?php
function calculateUnitPrice(
        $basePrice = 0,
        $lengthFeet = 0, 
        $lengthInch = 0, 
        $panelType = '', 
        $soldByFeet = 0, 
        $bends = 0, 
        $hems = 0, 
        $color = '', 
        $grade = '', 
        $gauge = '', 
        $width = ''
    ) {
    global $conn;

    $basePrice  = is_numeric($basePrice) ? floatval($basePrice) : 0;
    $lengthFeet = is_numeric($lengthFeet) ? floatval($lengthFeet) : 0;
    $lengthInch = is_numeric($lengthInch) ? floatval($lengthInch) : 0;
    $soldByFeet = ($soldByFeet == 1) ? 1 : 0;
    $bends      = is_numeric($bends) ? intval($bends) : 0;
    $hems       = is_numeric($hems) ? intval($hems) : 0;
    $width      = is_numeric($width) ? intval($width) : 12;

    $pricePerBend = floatval(getPaymentSetting('price_per_bend') ?? 0);
    $pricePerHem  = floatval(getPaymentSetting('price_per_hem') ?? 0);

    $panelExtra = 0;
    if ($panelType === 'Vented') {
        $panelExtra = floatval(getPaymentSetting('vented') ?? 0);
    } elseif ($panelType === 'Drip Stop') {
        $panelExtra = floatval(getPaymentSetting('drip_stop') ?? 0);
    }

    $totalLength = $lengthFeet + ($lengthInch / 12);
    if ($totalLength <= 0) $totalLength = 1;
    if ($width <= 0) $width = 12;

    //$baseTotal = ($soldByFeet == 1) ? $basePrice * $totalLength : $basePrice;
    $baseTotal = $basePrice * $totalLength * ($width / 12);

    $multiplier = getMultiplierValue($color, $grade, $gauge) ?? 1;
    $priceWithMultipliers = $baseTotal * $multiplier;

    $panelExtraCost = $baseTotal * $panelExtra;

    $bendCost = $bends * $pricePerBend;
    $hemCost  = $hems * $pricePerHem;

    $totalPrice = $priceWithMultipliers + $panelExtraCost + $bendCost + $hemCost;

    //return round($totalPrice, 2);
    return round($totalPrice, 3);
}

?>
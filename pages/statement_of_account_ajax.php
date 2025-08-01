<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/send_email.php';

$emailSender = new EmailTemplates();

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "send_email") {
        $customerid = mysqli_real_escape_string($conn, $_POST['customerid']);
        $customer_details = getCustomerDetails($customerid);
        $customer_name = $customer_details['customer_first_name'] . ' ' . $customer_details['customer_last_name'];
        $customer_email = $customer_details['contact_email'];
        $customer_phone = $customer_details['contact_phone'];

        $send_option = mysqli_real_escape_string($conn, $_POST['send_option']);
        $balance = number_format(getCustomerCreditTotal($customerid), 2);
        $statement_url = "https://metal.ilearnwebtech.com/print_statement_account.php?id=$customerid";
        $subject = "Customer Outstanding Balance.";

        $sms_message = "Hi $customer_name,\n\n$subject\nThis is to remind you of your outstanding balance of $balance. Click this link to view additional details:\n$statement_url";

        $email_success = false;
        $sms_success = false;
        $email_error = '';
        $sms_error = '';

        if ($send_option === 'email' || $send_option === 'both') {
            if (!empty($customer_email)) {
                $email_result = $emailSender->sendStatement($customer_email, $subject, $balance, $statement_url);
                $email_success = $email_result['success'];
                $response['email_success'] = $email_success;

                if (!$email_success) {
                    $email_error = $email_result['error'] ?? 'Unknown email error';
                    $response['email_error'] = $email_error;
                }
            } else {
                $response['email_success'] = false;
                $response['email_error'] = 'Missing email';
            }
        }

        if ($send_option === 'sms' || $send_option === 'both') {
            if (!empty($customer_phone)) {
                $sms_result = sendPhoneMessage($customer_phone, $subject, $sms_message);
                $sms_success = $sms_result['success'];
                $response['sms_success'] = $sms_success;

                if (!$sms_success) {
                    $sms_error = $sms_result['error'] ?? 'Unknown SMS error';
                    $response['sms_error'] = $sms_error;
                }
            } else {
                $response['sms_success'] = false;
                $response['sms_error'] = 'Missing phone number';
            }
        }

        if ($email_success || $sms_success) {
            $response['message'] = "Successfully sent to $customer_name.";
        } else {
            $response['message'] = "Message could not be sent to $customer_name.";
        }

        $response['success'] = true;
        echo json_encode($response);
    }

    mysqli_close($conn);
}
?>

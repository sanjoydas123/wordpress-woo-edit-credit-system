<?php


// custom mail send 
function send_custom_email($client_id, $project_name, $footage_link, $type = 'free_test')
{
    $to = 'aminadavfeigenboum+suwgzb0bzmzm5upcnmmh@boards.trello.com';
    $subject = $type . " " . $client_id;
    $message = '<Table><Tr>Project Name<Td></Td><Td>' . $project_name . '</Td></Tr><Tr>Link<Td></Td><Td>' . $footage_link . '</Td></Tr></Table>';
    $headers = array('Content-Type: text/html; charset=UTF-8');

    // Send the email.
    $is_sent = wp_mail($to, $subject, $message, $headers);


    if ($is_sent) {
        // echo 'Email sent successfully.';
        return true;
    } else {
        //   echo 'Failed to send email.';
        return true;
    }
}

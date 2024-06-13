<?php
class Fonnte {
    public static function send($target_number, $text) {

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.fonnte.com/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array(
        'target' => $target_number,
        'message' => $text, 
        'countryCode' => '62', //optional
        ),
        CURLOPT_HTTPHEADER => array(
            "Authorization: " . FONNTE_TOKEN //change TOKEN to your actual token
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    public static function check($target_number) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.fonnte.com/validate',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array(
        'target' => $target_number,
        'countryCode' => '62'
        ),
        CURLOPT_HTTPHEADER => array(
            'Authorization: '. FONNTE_TOKEN
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
}
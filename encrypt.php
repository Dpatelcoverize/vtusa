<?php

function encryptData($data)
{
    $iv = openssl_random_pseudo_bytes(ENC_Bytes);
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', ENC_KEY, 0, $iv);
    $encrypted_string = base64_encode($iv . $encrypted);
    $encrypted_string = str_replace('/', '__SLASH__', $encrypted_string);
    $encrypted_string = str_replace('+', '__PLUS__', $encrypted_string);
    return $encrypted_string;
}

function decryptData($encryptedData)
{
    $encryptedData = str_replace('__SLASH__', '/', $encryptedData);
    $encryptedData = str_replace('__PLUS__', '+', $encryptedData);
    $data = base64_decode($encryptedData);
    $iv = substr($data, 0, ENC_Bytes);
    $encrypted = substr($data, ENC_Bytes);
    $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', ENC_KEY, 0, $iv);
    return $decrypted;
}

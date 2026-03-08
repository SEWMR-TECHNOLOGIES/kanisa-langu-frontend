<?php
// Define constants for the encryption key and IV
define('ENCRYPTION_KEY', '05fa9146a77a2817fd7448498a5eaac9'); // 32 bytes key for AES-256
define('ENCRYPTION_IV', '58f310cb97bb3ce6'); // 16 bytes initialization vector

/**
 * Encrypt data using AES-256-CBC with predefined key and IV.
 *
 * @param string $data The data to encrypt.
 * @return string Encrypted data in base64 format.
 */
function encryptData($data) {
    // Ensure key and IV are of appropriate length
    if (strlen(ENCRYPTION_KEY) !== 32 || strlen(ENCRYPTION_IV) !== 16) {
        throw new Exception('Invalid key or IV length.');
    }

    // Encrypt data
    $encryptedData = openssl_encrypt($data, 'aes-256-cbc', ENCRYPTION_KEY, 0, ENCRYPTION_IV);

    if ($encryptedData === false) {
        throw new Exception('Encryption failed.');
    }

    // Return base64 encoded encrypted data
    return base64_encode($encryptedData);
}

/**
 * Decrypt data using AES-256-CBC with predefined key and IV.
 *
 * @param string $encryptedData The encrypted data in base64 format.
 * @return string Decrypted data.
 */
function decryptData($encryptedData) {
    // Ensure key and IV are of appropriate length
    if (strlen(ENCRYPTION_KEY) !== 32 || strlen(ENCRYPTION_IV) !== 16) {
        throw new Exception('Invalid key or IV length.');
    }

    // Decode base64 encoded encrypted data
    $encryptedData = base64_decode($encryptedData);

    // Decrypt data
    $decryptedData = openssl_decrypt($encryptedData, 'aes-256-cbc', ENCRYPTION_KEY, 0, ENCRYPTION_IV);

    if ($decryptedData === false) {
        throw new Exception('Decryption failed.');
    }

    return $decryptedData;
}

/**
 * Compare a decrypted value to a given plaintext value.
 *
 * @param string $encryptedData The encrypted data in base64 format.
 * @param string $plainText The plaintext value to compare with.
 * @return bool True if the decrypted data matches the plaintext value, false otherwise.
 */
function compareEncryptedData($encryptedData, $plainText) {
    // Decrypt the data
    $decryptedData = decryptData($encryptedData);

    // Compare the decrypted data with the given plaintext value
    return hash_equals($decryptedData, $plainText);
}

?>

<?php
/**
 * TOTP (Time-based One-Time Password) Library
 * RFC 6238 compatible - Google Authenticator, Microsoft Authenticator, Authy
 */

require_once __DIR__ . '/config.php';

class TOTP {
    /**
     * Genera secret key per TOTP
     */
    public static function generateSecret($length = 16) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; // Base32
        $secret = '';

        for ($i = 0; $i < $length; $i++) {
            $secret .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $secret;
    }

    /**
     * Genera codice TOTP dal secret
     */
    public static function generateCode($secret, $timestamp = null, $digits = null, $period = null) {
        $timestamp = $timestamp ?? time();
        $digits = $digits ?? (int) Config::get('MFA_DIGITS', 6);
        $period = $period ?? (int) Config::get('MFA_PERIOD', 30);

        $key = self::base32Decode($secret);
        $time = pack('N*', 0) . pack('N*', floor($timestamp / $period));
        $hash = hash_hmac('sha1', $time, $key, true);
        $offset = ord($hash[19]) & 0xf;

        $code = (
            ((ord($hash[$offset + 0]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        ) % pow(10, $digits);

        return str_pad($code, $digits, '0', STR_PAD_LEFT);
    }

    /**
     * Verifica codice TOTP
     */
    public static function verifyCode($secret, $code, $discrepancy = 1, $timestamp = null) {
        $timestamp = $timestamp ?? time();
        $period = (int) Config::get('MFA_PERIOD', 30);

        // Verifica finestra temporale (±discrepancy periodi)
        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $checkTime = $timestamp + ($i * $period);
            $validCode = self::generateCode($secret, $checkTime);

            if (hash_equals($validCode, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Genera QR Code URL per Google Authenticator
     */
    public static function getQRCodeURL($secret, $email, $issuer = null) {
        $issuer = $issuer ?? Config::get('MFA_ISSUER', 'Finch-AI');
        $label = urlencode($issuer . ':' . $email);

        $params = [
            'secret' => $secret,
            'issuer' => urlencode($issuer),
            'algorithm' => 'SHA1',
            'digits' => Config::get('MFA_DIGITS', 6),
            'period' => Config::get('MFA_PERIOD', 30),
        ];

        $query = http_build_query($params);
        $otpauthURL = "otpauth://totp/{$label}?{$query}";

        // Google Charts QR Code API (alternative: generare QR lato server)
        return 'https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=' . urlencode($otpauthURL);
    }

    /**
     * Ottieni provisioning URI (per app authenticator)
     */
    public static function getProvisioningURI($secret, $email, $issuer = null) {
        $issuer = $issuer ?? Config::get('MFA_ISSUER', 'Finch-AI');
        $label = urlencode($issuer . ':' . $email);

        $params = [
            'secret' => $secret,
            'issuer' => urlencode($issuer),
            'algorithm' => 'SHA1',
            'digits' => Config::get('MFA_DIGITS', 6),
            'period' => Config::get('MFA_PERIOD', 30),
        ];

        $query = http_build_query($params);
        return "otpauth://totp/{$label}?{$query}";
    }

    /**
     * Base32 Decode
     */
    private static function base32Decode($secret) {
        $base32chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $base32charsFlipped = array_flip(str_split($base32chars));

        $paddingCharCount = substr_count($secret, '=');
        $allowedValues = [6, 4, 3, 1, 0];

        if (!in_array($paddingCharCount, $allowedValues)) {
            return false;
        }

        for ($i = 0; $i < 4; $i++) {
            if ($paddingCharCount == $allowedValues[$i] &&
                substr($secret, -($allowedValues[$i])) != str_repeat('=', $allowedValues[$i])) {
                return false;
            }
        }

        $secret = str_replace('=', '', $secret);
        $secret = str_split($secret);
        $binaryString = '';

        for ($i = 0; $i < count($secret); $i = $i + 8) {
            $x = '';
            if (!in_array($secret[$i], $base32charsFlipped)) {
                return false;
            }

            for ($j = 0; $j < 8; $j++) {
                $x .= str_pad(base_convert(@$base32charsFlipped[@$secret[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
            }

            $eightBits = str_split($x, 8);

            for ($z = 0; $z < count($eightBits); $z++) {
                $binaryString .= (($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) == 48) ? $y : '';
            }
        }

        return $binaryString;
    }
}

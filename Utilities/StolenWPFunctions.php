<?php
/**
 * Funzioni copiate malamente da WP
 */

namespace Engine\Utilities;



class StolenWPFunctions {



    /**
     * Generates a random password drawn from the defined set of characters.
     *
     * @since 2.5.0
     *
     * @param int  $length              Optional. The length of password to generate. Default 12.
     * @param bool $special_chars       Optional. Whether to include standard special characters.
     *                                  Default true.
     * @param bool $extra_special_chars Optional. Whether to include other special characters.
     *                                  Used when generating secret keys and salts. Default false.
     * @return string The random password.
     */
    function wp_generate_password( $length = 12, $special_chars = true, $extra_special_chars = false ) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        if ( $special_chars )
            $chars .= '!@#$%^&*()';
        if ( $extra_special_chars )
            $chars .= '-_ []{}<>~`+=,.;:/?|';

        $password = '';
        for ( $i = 0; $i < $length; $i++ ) {
            $password .= substr($chars, rand(0, strlen($chars) - 1), 1);
        }

        return $password;
    }










}
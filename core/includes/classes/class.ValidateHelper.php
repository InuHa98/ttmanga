<?php

class ValidateHelper
{
    private const EMAIL_REGEX       = '/^[A-Za-z0-9._+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/';
    private const PHONE_REGEX       = '/^0[0-9]{9,10}$/';
    private const FULLNAME_REGEX    = '/^[\p{L}#]+(\s[\p{L}#]+)+$/u';
    private const NAME_REGEX        = '/^[\p{L}\d\s_#-]+$/u';
    private const URL_REGEX         = '/^(https?):\/\/[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)+([\/?].*)?$/';
    private const DOMAIN_REGEX      = '/^(?=.{1,253}$)([a-zA-Z0-9-]{1,63}\.)+[a-zA-Z]{2,}$/';

    public static function isValidPhoneNumber(?string $phoneNumber): bool {
        return $phoneNumber !== null && preg_match(self::PHONE_REGEX, $phoneNumber);
    }

    public static function isValidEmail(?string $email): bool {
        return $email !== null && preg_match(self::EMAIL_REGEX, $email);
    }

    public static function isValidFullname(?string $name): bool {
        return $name !== null && preg_match(self::FULLNAME_REGEX, $name);
    }

    public static function isValidName(?string $name): bool {
        return $name !== null && preg_match(self::NAME_REGEX, $name);
    }

    public static function isValidDomain(?string $domain): bool {
        return $domain !== null && preg_match(self::DOMAIN_REGEX, $domain);
    }

    public static function isValidURL(?string $url): bool {
        if ($url === null) return false;
        return filter_var($url, FILTER_VALIDATE_URL) !== false
            && preg_match(self::URL_REGEX, $url);
    }

}

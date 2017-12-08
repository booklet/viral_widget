<?php
class ViralForm
{
    const TIME_KEEP_COOKIES = 86400 * 30; // 86400 = 1 day
    const REGISTRATION_KEY = 'ref_id'; // use to form and cookies keys
    const RECOMMENDATION_KEY = 'ref_coupon'; // use to form and cookies keys

    private $viral_campaign_hash_id;
    private $routing_match;
    private $get;
    private $cookie;
    private $cookie_ref_id_name;
    private $cookie_ref_coupon_name;
    private $registration_code;
    private $recommendation_code;

    public function __construct(string $viral_campaign_hash_id, array $params)
    {
        $this->viral_campaign_hash_id = $viral_campaign_hash_id;
        $this->routing_match = $params['routing_match'] ?? [];
        $this->get = $params['get'] ?? [];
        $this->cookie = $params['cookie'] ?? [];

        $this->setCookiesKeys();
        $this->setCodesValues();
        $this->setCookies();
    }

    public function form(array $params = [])
    {
        if ($this->isSigningUpMember()) {
            return $this->recommendationForm($params);
        } else {
            return $this->registrationForm($params);
        }
    }

    private function setCookiesKeys()
    {
        $pefix = $this->getKeysPrefix();
        $this->cookie_ref_id_name = $pefix . '_' . self::REGISTRATION_KEY;
        $this->cookie_ref_coupon_name = $pefix . '_' . self::RECOMMENDATION_KEY;
    }

    private function setCodesValues()
    {
        $this->registration_code = $this->routing_match[self::REGISTRATION_KEY] ?? $this->cookie[$this->cookie_ref_id_name] ?? null;
        $this->recommendation_code = $this->get[self::RECOMMENDATION_KEY] ?? $this->cookie[$this->cookie_ref_coupon_name] ?? null;
    }

    private function setCookies()
    {
        // Utwórz lub aktualizuj cookies, tylko jeśli wartość się zmieniła
        // aby nie odświeżać daty wygaśnięcia cookies za każdym wyświetleniem.
        if ($this->registration_code and !self::isTestMode()) {
            $this->setCookieWhenValueChange($this->cookie_ref_id_name, $this->registration_code, true);
        }

        if ($this->recommendation_code and !self::isTestMode()) {
            $this->setCookieWhenValueChange($this->cookie_ref_coupon_name, $this->recommendation_code);
        }
    }

    private function recommendationForm($params)
    {
        $member_data = (new ViralFormRequest())->getMemberData($this->recommendation_code);
        if ($member_data) {
            return (new ViralFormHtml($params))->recommendationForm($member_data);
        } else {
            return $this->registrationForm($params);
        }
    }

    private function registrationForm($params)
    {
        $data = [
            'viral_campaign_hash_id' => $this->viral_campaign_hash_id,
            'registration_code' => $this->registration_code,
        ];

        return (new ViralFormHtml($params))->registrationForm($data);
    }

    private function setCookieWhenValueChange($name, $value)
    {
        if (isset($this->cookie[$name]) and $this->cookie[$name] == $value) {
            return;
        }

        setcookie($name, $value, time() + self::TIME_KEEP_COOKIES, '/');
    }

    private function getKeysPrefix()
    {
        return substr($this->viral_campaign_hash_id, 0, 6);
    }

    private function isSigningUpMember()
    {
        return $this->recommendation_code;
    }

    public static function isTestMode()
    {
        return defined('IS_TEST_ENV') and IS_TEST_ENV == true;
    }

    // For tests
    public function getParams()
    {
        return [
            'viral_campaign_hash_id' => $this->viral_campaign_hash_id,
            'routing_match' => $this->routing_match,
            'get' => $this->get,
            'cookie' => $this->cookie,
            'cookie_ref_id_name' => $this->cookie_ref_id_name,
            'cookie_ref_coupon_name' => $this->cookie_ref_coupon_name,
            'registration_code' => $this->registration_code,
            'recommendation_code' => $this->recommendation_code,
        ];
    }
}

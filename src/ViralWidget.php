<?php
class ViralWidget
{
    const TIME_KEEP_COOKIES = 86400 * 30; // 86400 = 1 day
    const REGISTRATION_KEY = 'ref_id'; // for registration  code
    const RECOMMENDATION_KEY = 'ref_coupon'; // for recommendation code

    private $viral_campaign_hash_id;
    private $routing_match;
    private $get;
    private $cookie;
    private $cookie_registration_key_name;
    private $cookie_recommendation_key_name;
    private $registration_code_value;
    private $recommendation_code_value;

    public function __construct(string $viral_campaign_hash_id, array $params = [])
    {
        $this->viral_campaign_hash_id = $viral_campaign_hash_id;
        $this->routing_match = $params['routing_match'] ?? [];
        $this->get = $params['get'] ?? [];
        $this->cookie = $params['cookie'] ?? [];

        $this->setCookiesKeys();
        $this->setCodesValues();
        $this->setCookies();
    }

    public function widget(array $params = [])
    {
        if ($this->isSigningUpMember()) {
            return $this->recommendationWidget($params);
        } else {
            return $this->registrationForm($params);
        }
    }

    private function setCookiesKeys()
    {
        // Need to use cookies names prefix in case when two viral widgets in this same domain
        $pefix = $this->getKeysPrefix();
        $this->cookie_registration_key_name = $pefix . '_' . self::REGISTRATION_KEY;
        $this->cookie_recommendation_key_name = $pefix . '_' . self::RECOMMENDATION_KEY;
    }

    private function setCodesValues()
    {
        $this->registration_code_value = $this->routing_match[self::REGISTRATION_KEY] ?? $this->cookie[$this->cookie_registration_key_name] ?? null;
        $this->recommendation_code_value = $this->get[self::RECOMMENDATION_KEY] ?? $this->cookie[$this->cookie_recommendation_key_name] ?? null;
    }

    private function setCookies()
    {
        // Create or update cookies only if the value has changed
        // not to refresh the expiration date of cookies for each widget display.
        if ($this->registration_code_value and !self::isTestMode()) {
            $this->setCookieWhenValueChange($this->cookie_registration_key_name, $this->registration_code_value);
        }

        if ($this->recommendation_code_value and !self::isTestMode()) {
            $this->setCookieWhenValueChange($this->cookie_recommendation_key_name, $this->recommendation_code_value);
        }
    }

    private function recommendationWidget($params)
    {
        $member_data = (new ViralWidgetRequest())->getMemberData($this->recommendation_code_value);
        if ($member_data) {
            return (new ViralWidgetHtml($params))->recommendationWidget($member_data);
        } else {
            return $this->registrationForm($params);
        }
    }

    private function registrationForm($params)
    {
        $data = [
            'viral_campaign_hash_id' => $this->viral_campaign_hash_id,
            'registration_code_value' => $this->registration_code_value,
        ];

        return (new ViralWidgetHtml($params))->registrationForm($data);
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
        return $this->recommendation_code_value;
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
            'cookie_registration_key_name' => $this->cookie_registration_key_name,
            'cookie_recommendation_key_name' => $this->cookie_recommendation_key_name,
            'registration_code_value' => $this->registration_code_value,
            'recommendation_code_value' => $this->recommendation_code_value,
        ];
    }
}

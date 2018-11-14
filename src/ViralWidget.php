<?php
// Opis modułu dostępny w API: app/modules/viral/docs/Viral.md

class ViralWidget
{
    const TIME_KEEP_COOKIES = 86400 * 30; // 86400 = 1 day
    const REGISTRATION_KEY = 'registration_code'; // for registration code
    const RECOMMENDATION_KEY = 'recommendation_code'; // for recommendation code

    private $viral_campaign_hash_id;
    private $routing_match;
    private $get;
    private $cookies;
    private $cookie_registration_key_name;
    private $cookie_recommendation_key_name;
    private $registration_code_value;
    private $recommendation_code_value;
    private $cookies_to_set;

    public function __construct(string $viral_campaign_hash_id, array $params = [])
    {
        $this->viral_campaign_hash_id = $viral_campaign_hash_id;
        $this->routing_match = $params['routing_match'] ?? [];
        $this->get = $params['get'] ?? [];
        $this->cookies = $params['cookie'] ?? [];
        // W przypadku braku możliwości ustawienia odpowiedniego routingu zamiast
        // http://www.test.pl/viral/KUPON używany linków http://www.test.pl/viral?registration_cod=KUPON
        $this->use_get_urls = $params['use_get_urls'] ?? false;

        $this->setCookiesKeys();
        $this->setCodesValues();
        $this->generateCookiesToSet();
        $this->setCookies();
    }

    public function widget(array $params = [])
    {
        // Preview mode to display all forms and alerts to easy css style
        if (isset($params['preview_mode'])) {
            $h = $this->recommendationWidget($params);
            $h .= $this->registrationForm($params);
            return $h;
        }

        // User activate
        if (isset($this->get['activate'])) {
            header('Location: ' . ViralWidgetApi::getApiUrl() . 'viral_member_activation/' . $this->get['activate']);
            exit;
        }

        // We remove recommendation_code from url that user will not be able to share link with it.
        // So no one can take other summary page (account)
        if ($this->isRecommendationCodeParameterWasPassed()) {

            $url_witout_recommendation_code = (new ViralWidgetUrl())->createCurrentUrlWitoutRecommendationCodeParam(null, $this->routing_match);
            header('Location: ' . $url_witout_recommendation_code);
            exit;
        }

        if ($this->isSigningUpMember()) {
            return $this->recommendationWidget($params);
        } else {
            return $this->registrationForm($params);
        }
    }

    public function isSigningUpMember()
    {
        return $this->recommendation_code_value;
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
        $this->registration_code_value = $this->routing_match[self::REGISTRATION_KEY] ?? $this->cookies[$this->cookie_registration_key_name] ?? null;
        $this->recommendation_code_value = $this->routing_match[self::RECOMMENDATION_KEY] ?? $this->get[self::RECOMMENDATION_KEY] ?? $this->cookies[$this->cookie_recommendation_key_name] ?? null;
    }

    private function generateCookiesToSet()
    {
        $this->cookies_to_set = [];
        // Create or update cookies only if the value has changed
        // not to refresh the expiration date of cookies for each widget display.
        if ($this->registration_code_value) {
            $this->cookies_to_set[] = [
                'name' => $this->cookie_registration_key_name,
                'value' => $this->registration_code_value,
            ];
        }

        if ($this->recommendation_code_value) {
            $this->cookies_to_set[] = [
                'name' => $this->cookie_recommendation_key_name,
                'value' => $this->recommendation_code_value,
            ];
        }
    }

    private function setCookies()
    {
        foreach ($this->cookies_to_set as $cookie) {
            $this->setCookieWhenValueChange($cookie['name'], $cookie['value']);
        }
    }

    private function recommendationWidget($params)
    {
        $member_data = (new ViralWidgetRequest())->getMemberData($this->recommendation_code_value);

        if ($member_data) {
            $member_data = array_merge($member_data, ['use_get_urls' => $this->use_get_urls]);
            return (new ViralWidgetHtml($params, $this->get))->recommendationWidget($member_data);
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

        return (new ViralWidgetHtml($params, $this->get))->registrationForm($data);
    }

    private function setCookieWhenValueChange($name, $value)
    {
        if ($this->isCookieExistsWithEqualValue($name, $value) or self::isTestMode()) {
            return;
        }

        setcookie($name, $value, time() + self::TIME_KEEP_COOKIES, '/');
    }

    private function getKeysPrefix()
    {
        return substr($this->viral_campaign_hash_id, 0, 6);
    }

    private function isCookieExistsWithEqualValue($name, $value)
    {
        return isset($this->cookies[$name]) and $this->cookies[$name] == $value;
    }

    private function isRecommendationCodeParameterWasPassed()
    {
        // $_SERVER['REQUEST_URI'] - wszystko, razem z parametrami, po domenie .pl/[...]

        return (isset($this->get[self::RECOMMENDATION_KEY]) or isset($this->routing_match[self::RECOMMENDATION_KEY]))
                and isset($_SERVER['REQUEST_URI']);
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
            'cookies' => $this->cookies,
            'cookie_registration_key_name' => $this->cookie_registration_key_name,
            'cookie_recommendation_key_name' => $this->cookie_recommendation_key_name,
            'registration_code_value' => $this->registration_code_value,
            'recommendation_code_value' => $this->recommendation_code_value,
            'cookies_to_set' => $this->cookies_to_set,
        ];
    }
}

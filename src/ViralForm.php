<?php
class ViralForm
{
    private $viral_campaign_hash_id;
    private $routing_match;
    private $get;
    private $cookie;

    // dwa ref
    // jeden z ktorego uzytkownik sie zapisal
    // drugi jego wlasny do polecen
    private $registration_code;
    private $recommendation_code;

    public function __construct(array $params)
    {
        $this->viral_campaign_hash_id = $params['viral_campaign_hash_id'];
        $this->routing_match = $params['routing_match'];
        $this->get = $params['get'];
        $this->cookie = $params['cookie'];

        // registration_code
        $this->registration_code = $this->routing_match['ref_id'] ?? $this->cookie[substr($this->viral_campaign_hash_id, 0, 6) . '_ref_id'] ?? null;
        // set cookie if not exists
        if ($this->registration_code and !$this->isTestMode()) {
            setcookie(substr($this->viral_campaign_hash_id, 0, 6) . '_ref_id', $this->registration_code, time() + (86400 * 30), '/'); // 86400 = 1 day
        }

        // $recommendation_code_cookie
        $recommendation_code_get = $this->get['ref_coupon'] ?? null;
        $recommendation_code_cookie = $this->cookie[substr($this->viral_campaign_hash_id, 0, 6) . '_ref_coupon'] ?? null;
        $this->recommendation_code = $recommendation_code_get ?? $recommendation_code_cookie ?? null;
        // set cookie if not exists
        if ($this->recommendation_code and !$this->isTestMode()) {
            setcookie(substr($this->viral_campaign_hash_id, 0, 6) . '_ref_coupon', $this->recommendation_code, time() + (86400 * 30), '/'); // 86400 = 1 day
        }
    }

    public function form(array $params = [])
    {
        if ($this->isRegisteredUser()) {
            return $this->recommendationForm($params);
        } else {
            return $this->registrationForm($params);
        }
    }

    private function recommendationForm($params)
    {
        // $params
        // lead-text  {{pinkty}}

        $data = $this->requestUserData();
        if ($data) {
            return '
            <div id="viral-recommendation">
              <div class="lead-text">
                Poleceń do tej pory: <span class="points">' . $data['points'] . '</span>.
              </div>
              <div class="recommendation-link">
                <input type="text" value="' . $data['source_url'] . '/' . $data['reference_coupon'] . '">
              </div>
              <div class="recommendation-buttons-text">
                Kliknij, aby udostępnić.
              </div>
              <div class="recommendation-buttons">
                <a href="#" class="facebook">Facebook</a>
              </div>
            </div>';
        } else {
            // no data
            return $this->registrationForm($params);
        }
    }

    private function requestUserData()
    {
        if ($this->isTestMode()) {
            return $this->testMemberData();
        }

        try {
            $data = @file_get_contents('http://api.booklet.dev/v1/viral_member_data/' . $this->recommendation_code);
            if ($data !== false) {
                $data = json_decode($data);
                return (array) $data->data[0]->attributes;
            }
        } catch (Throwable $t) {

        }

        return null;
    }

    private function registrationForm()
    {
        return '
        <form id="viral-form" action="http://api.booklet.dev/v1/viral_signing_up/' . $this->viral_campaign_hash_id . '" method="post">
          <input type="hidden" name="member[ref_id]" value="' . $this->registration_code . '">
          <div class="form-group form-group-email">
            <label for="viral-member-email">E-mail:</label>
            <input type="email" name="member[email]" class="form-control" id="viral-member-email">
          </div>
          <div class="form-group form-group-name">
            <label for="viral-member-name">Imie:</label>
            <input type="text" name="member[name]" class="form-control" id="viral-member-name">
          </div>
          <button type="submit" class="btn btn-primary">Zapisz się</button>
        </form>';
    }

    private function isRegisteredUser()
    {
        return $this->recommendation_code;
    }

    private function isTestMode()
    {
        return defined('IS_TEST_ENV') and IS_TEST_ENV == true;
    }

    private function testMemberData()
    {
        return [
            'source_url' => 'http://booklet.dev/viral',
            'target_url' => 'http://booklet.dev/viral-dziekujemy',
            'email' => 'adam@test.com',
            'name' => 'Adam',
            'status' => 'confirmed',
            'points' => 0,
            'reference_coupon' => 'xyz123_recommendation',
        ];
    }


}

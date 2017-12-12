<?php
class ViralWidgetHtml
{
    private $params;
    private $get;

    public function __construct(array $params, array $get)
    {
        $this->params = $params; // data from ->widget($params)
        $this->share_buttons = $params['share_buttons'] ?? ['facebook', 'twitter', 'email'];
        $this->get = $get;
    }

    public function registrationForm($data)
    {
        if ($this->isPreviewMode()) {
            $data['viral_campaign_hash_id'] = 'CusT0mH4sH';
            $data['registration_code_value'] = 'xyz123';
        }

        $html = $this->generateInformationAlerts();

        $html .= '
        <form id="viral-form" action="' . ViralWidgetApi::getApiUrl() . 'viral_signing_up/' . $data['viral_campaign_hash_id'] . '" method="post">
          <input type="hidden" name="member[registration_code]" value="' . $data['registration_code_value'] . '">
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

        return $html;
    }

    public function recommendationWidget($data)
    {
        if ($this->isPreviewMode()) {
            $data['source_url'] = 'http://booklet.pl/viral';
            $data['recommendation_code'] = 'abc123';
        }

        $html = $this->generateInformationAlerts();

        $html .= '
        <div id="viral-recommendation">
          <div class="lead-text">' . $this->getLeadText($data) .'</div>
          <div class="recommendation-link">
            <input type="text" value="' . $data['source_url'] . '/' . $data['recommendation_code'] . '">
          </div>
          <div class="recommendation-buttons-text">
            Kliknij, aby udostępnić.
          </div>
          <div class="recommendation-buttons">
            ' . $this->shareButtons($data) . '
          </div>
        </div>';

        return $html;
    }

    public function getLeadText($data)
    {
        $text = $params['lead_text'] ?? 'Poleceń do tej pory: <span class="points">{points}</span>.';
        $text = str_replace('{points}', $data['points'], $text);

        return $text;
    }

    private function generateInformationAlerts()
    {
        $html = '';

        $email_error = $this->get['email'][0] ?? '';
        if ($email_error == 'jest już wykorzystany w tej kampanii' or $this->isPreviewMode()) {
            $html .= '<div class="viral-alert">Ten email został już wykorzystany w tej kampanii.</div>';
        }

        if ($email_error == 'is required.' or $email_error == 'email is not valid.' or $this->isPreviewMode()) {
            $html .= '<div class="viral-alert">Podany e-mail jest niepoprawny.</div>';
        }

        $show = $this->get['show'] ?? '';
        if ($show == 'activation_successfully' or $this->isPreviewMode()) {
            $html .= '<div class="viral-info">Dziękujemy, Twoje konto zostało aktywowane.</div>';
        }

        if ($show == 'activation_error' or $this->isPreviewMode()) {
            $html .= '<div class="viral-error">Aktywacja nie powiodła się.</div>';
        }

        $campaign = $this->get['campaign'] ?? '';
        if ($campaign == 'suspended' or $this->isPreviewMode()) {
            $html .= '<div class="viral-alert">Kampanie została zawieszona. Rejestracja nie jest aktualnie możliwa.</div>';
        }

        return $html;
    }

    private function isPreviewMode()
    {
       return isset($this->params['preview_mode']);
    }

    private function shareButtons($data)
    {
        $html = '';

        if (in_array('facebook', $this->share_buttons)) {
            $html .= $this->facebookButton($data);
        }

        if (in_array('twitter', $this->share_buttons)) {
            $html .= $this->twitterButton($data);
        }

        if (in_array('email', $this->share_buttons)) {
            $html .= $this->emailButton($data);
        }

        return $html;
    }

    private function facebookButton($data)
    {
        $url = $data['source_url'] . '/' . $data['recommendation_code'];
        $share_url = 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($url);

        return '<a class="facebook-button" href="' . $share_url . '" ' . $this->newWindowPopupScript($share_url, 'Udostępnij na Facebooku') . '">
                  Udostępnij na Facebook
                </a>';
    }

    private function twitterButton($data)
    {
        $text = $data['source_url'] . '/' . $data['recommendation_code'];
        $share_url = 'https://twitter.com/home?status=' . urlencode($text);

        return '<a class="twitter-button" href="' . $share_url . '" ' . $this->newWindowPopupScript($share_url, 'Udostępnij na Twitter') . '>
                  Udostępnij na Twitter
                </a>';
    }

    private function emailButton($data)
    {
        $subject = 'Sprawdz';
        $body = 'https%3A//booklet.pl/';

        return '<a class="email-button" href="mailto:?&subject=' . $subject . '&body=' . $body . '">Udostępnij przez E-mail</a>';
    }

    private function newWindowPopupScript($share_url, $text)
    {
        return 'onclick="window.open(\'' . $share_url . '\',\'' . $text . '\',\'width=600,height=400\'); return false;" target="popup"';
    }

}

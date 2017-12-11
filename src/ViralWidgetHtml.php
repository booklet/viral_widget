<?php
class ViralWidgetHtml
{
    private $params;
    private $get;

    public function __construct(array $params, array $get)
    {
        $this->params = $params;
        $this->get = $get;
    }

    public function registrationForm($data)
    {
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
        $html = $this->generateInformationAlerts();

        $html .= '
        <div id="viral-recommendation">
          <div class="lead-text">' . $this->getLeadText($data) .'</div>
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
        if ($email_error == 'jest już wykorzystany w tej kampanii') {
            $html .= '<div class="viral-alert">Ten email został już wykorzystany w tej kampanii.</div>';
        }

        if ($email_error == 'is required' or $email_error == 'email is not valid') {
            $html .= '<div class="viral-alert">Podany e-mail jest niepoprawny.</div>';
        }

        $show = $this->get['show'] ?? '';
        if ($show == 'activation_successfully') {
            $html .= '<div class="viral-info">Dziękujemy, Twoje konto zostało aktywowane.</div>';
        }

        if ($show == 'activation_error') {
            $html .= '<div class="viral-error">Aktywacja nie powiodła się.</div>';
        }

        $campaign = $this->get['campaign'] ?? '';
        if ($campaign == 'suspended') {
            $html .= '<div class="viral-alert">Kampanie została zawieszona. Rejestracja nie jest aktualnie możliwa.</div>';
        }

        return $html;
    }
}

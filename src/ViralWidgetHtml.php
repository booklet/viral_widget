<?php
class ViralWidgetHtml
{
    private $params;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function registrationForm($data)
    {
        return '
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
    }

    public function recommendationWidget($data)
    {
        return '
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
    }

    public function getLeadText($data)
    {
        $text = $params['lead_text'] ?? 'Poleceń do tej pory: <span class="points">{points}</span>.';
        $text = str_replace('{points}', $data['points'], $text);

        return $text;
    }
}

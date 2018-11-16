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
            <input type="email" name="member[email]" class="form-control" id="viral-member-email" required>
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
        $registration_code_url  = $this->getRegistrationCodeLink($data);

        $html .= '
        <div id="viral-recommendation">
          <div class="lead-text">' . $this->getLeadText($data) .'</div>
          <div class="recommendation-link-text">
            Twój link polecający
          </div>
          <div class="recommendation-link">
            <input type="text" value="' . $registration_code_url . '" id="viral-recommendation-url">
            <button class="copy-to-clipboard-button" onclick="viralCopyToClipboard()"><i class="copy-icon"></i>Kopiuj</button>
          </div>
          <div class="recommendation-buttons-text">
            Kliknij, aby udostępnić link.
          </div>
          <div class="recommendation-buttons">
            ' . $this->shareButtons($data) . '
          </div>
        </div>
        ' . $this->copyToClipboardScript();

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
        if ($show == 'signingup_successfully' or $this->isPreviewMode()) {
            $html .= '<div class="viral-info">Dziękujemy za dołączenie do zabawy, sprawdź swoją pocztę i aktywuj konto.</div>';
        }

        if ($show == 'activation_successfully' or $this->isPreviewMode()) {
            $html .= '<div class="viral-info">Dziękujemy, Twoje konto zostało aktywowane.</div>';
        }

        if ($show == 'activation_error' or $this->isPreviewMode()) {
            $html .= '<div class="viral-error">Aktywacja nie powiodła się.</div>';
        }

        $campaign = $this->get['campaign'] ?? '';
        if ($campaign == 'suspended' or $this->isPreviewMode()) {
            $html .= '<div class="viral-alert">Kampania została zawieszona. Rejestracja nie jest aktualnie możliwa.</div>';
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

        // show button in order from share_buttons array
        foreach ($this->share_buttons as $button_name) {
            if ($button_name == 'facebook') {
                $html .= $this->facebookButton($data);
            }

            if ($button_name == 'twitter') {
                $html .= $this->twitterButton($data);
            }

            if ($button_name == 'email') {
                $html .= $this->emailButton($data);
            }

            if ($button_name == 'fb_messenger') {
                $html .= $this->fbMessenger($data);
            }
        }

        return $html;
    }

    private function copyToClipboardScript()
    {
        return '<script type="text/javascript">
                  function viralCopyToClipboard() {
                    var copyText = document.getElementById("viral-recommendation-url");
                    copyText.select();
                    document.execCommand("Copy");
                    alert("Twój link polecający został skopiowany.");
                  }
                </script>';
    }

    private function facebookButton($data)
    {
        $url = $this->getRegistrationCodeLink($data);
        $share_url = 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($url);

        return '<a class="facebook-button" href="' . $share_url . '" ' . $this->newWindowPopupScript($share_url, 'Udostępnij na Facebooku') . '">
                  <i class="facebook-icon"></i>Udostępnij na Facebook
                </a>';
    }

    private function twitterButton($data)
    {
        $url = $this->getRegistrationCodeLink($data);
        $text = $this->params['twitter_text'] ?? null;
        if ($text) {
            $text = str_replace('{url}', $url, $text);
        } else {
            $text = $url;
        }

        $share_url = 'https://twitter.com/home?status=' . urlencode($text);

        return '<a class="twitter-button" href="' . $share_url . '" ' . $this->newWindowPopupScript($share_url, 'Udostępnij na Twitter') . '>
                  <i class="twitter-icon"></i>Udostępnij na Twitter
                </a>';
    }

    private function emailButton($data)
    {
        $url = $this->getRegistrationCodeLink($data);
        $mail_subject = $this->params['mail_subject'] ?? 'Sprawdz';
        $mail_body = $this->params['mail_body'] ?? $url;
        $mail_body = str_replace('{url}', $url, $mail_body);

        return '<a class="email-button" href="mailto:?&subject=' . $this->formatTextForMailtoParam($mail_subject) . '&body=' . $this->formatTextForMailtoParam($mail_body) . '"><i class="email-icon"></i>Udostępnij przez E-mail</a>';
    }

    private function fbMessenger($data)
    {
        if (ViralUserDevice::isNotMobileDevice() and !ViralWidget::isTestMode()) {
            return;
        }

        $url = $this->params['fb_messenger_link'] ?? $this->getRegistrationCodeLink($data);
        $app_id = $this->params['fb_messenger_app_id'] ?? 'missing_app_id';

        return '<a class="messenger-button" href="fb-messenger://share/?link=' . urlencode($url) . '&app_id=' . $app_id . '"><i class="messenger-icon"></i>Udostępnij przez Messengera</a>';
    }

    private function newWindowPopupScript($share_url, $text)
    {
        return 'onclick="window.open(\'' . $share_url . '\',\'' . $text . '\',\'width=600,height=400\'); return false;" target="popup"';
    }

    private function formatTextForMailtoParam($text) {
        return rawurlencode(htmlspecialchars_decode($text));
    }

    private function getRecommendationCodeLink($data)
    {
        if (isset($data['use_get_urls']) and $data['use_get_urls']) {
            return $data['source_url'] . '?recommendation_code=' . $data['recommendation_code'];
        } else {
            return $data['source_url'] . '/' . $data['recommendation_code'];
        }
    }

    private function getRegistrationCodeLink($data)
    {
        if (isset($data['use_get_urls']) and $data['use_get_urls']) {
            return $data['source_url'] . '?registration_code=' . $data['recommendation_code'];
        } else {
            return $data['source_url'] . '/' . $data['recommendation_code'];
        }
    }
}

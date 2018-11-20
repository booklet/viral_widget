<?php
class ViralWidgetHtmlTest extends TesterCase
{
    public function testRegistrationForm()
    {
        $params = []; // dane z ->widget($params)
        $data = [
            'viral_campaign_hash_id' => 'abcdefghijklmnopqrst',
            'registration_code_value' => null,
        ];

        $html = (new ViralWidgetHtml($params, []))->registrationForm($data);

        Assert::expect($html)->to_include_string('<form id="viral-form" action="http://api.booklet.dev/v1/viral_signing_up/abcdefghijklmnopqrst" method="post">');
        Assert::expect($html)->to_include_string('<input type="hidden" name="member[registration_code]" value="">');
        Assert::expect($html)->to_include_string('<input type="email" name="member[email]" class="form-control" id="viral-member-email" required>');
        Assert::expect($html)->to_include_string('<input type="text" name="member[name]" class="form-control" id="viral-member-name">');
        Assert::expect($html)->to_include_string('<button type="submit" class="btn btn-primary">Zapisz się</button>');
    }

    public function testRegistrationFormWithRegulations()
    {
        $params = [
            'requires_acceptance_of_regulations' => true,
            'regulations_link' => 'https://page.test/regulamin',
        ]; // dane z ->widget($params)

        $data = [
            'viral_campaign_hash_id' => 'abcdefghijklmnopqrst',
            'registration_code_value' => null,
        ];

        $html = (new ViralWidgetHtml($params, []))->registrationForm($data);

        Assert::expect($html)->to_include_string('<input type="checkbox" name="member[regulations]" class="form-control" id="viral-regulations" required>');
        Assert::expect($html)->to_include_string('Oświadczam, że zapoznałem się z <a href="https://page.test/regulamin" target="_blank">Regulaminem</a>');
    }

    public function testRecommendationWidget()
    {
        $params = []; // dane z ->widget($params)
        $data = MemberDataFactory::testMemberData();  // dane z klasy

        $html = (new ViralWidgetHtml($params, []))->recommendationWidget($data);

        Assert::expect($html)->to_include_string('Poleceń do tej pory: <span class="points">0</span>.');
        Assert::expect($html)->to_include_string('<input type="text" value="http://booklet.dev/viral/xyz123_recommendation" id="viral-recommendation-url">');
    }

    public function testRecommendationWidgetWitUseGetUrls()
    {
        $params = []; // dane z ->widget($params)
        $data = MemberDataFactory::testMemberData();  // dane z klasy
        $data = array_merge($data, ['use_get_urls' => true]);

        $html = (new ViralWidgetHtml($params, []))->recommendationWidget($data);

        Assert::expect($html)->to_include_string('<input type="text" value="http://booklet.dev/viral?registration_code=xyz123_recommendation" id="viral-recommendation-url">');
    }

    // ?email[0]=jest+już+wykorzystany+w+tej+kampanii
    public function testInformationAlertsEmailUsed()
    {
        $params = [];
        $data = [
            'viral_campaign_hash_id' => 'abcdefghijklmnopqrst',
            'registration_code_value' => null,
        ];
        $get = ['email' =>
            [
                'jest już wykorzystany w tej kampanii',
            ]
        ];

        $html = (new ViralWidgetHtml($params, $get))->registrationForm($data);

        Assert::expect($html)->to_include_string('<div class="viral-alert">Ten email został już wykorzystany w tej kampanii.</div>');
        Assert::expect($html)->to_include_string('<form id="viral-form" action="http://api.booklet.dev/v1/viral_signing_up/abcdefghijklmnopqrst" method="post">');
    }

    // ?email[0]=is+required.&email[1]=email+is+not+valid.
    public function testInformationAlertsWrongEmail()
    {
        $params = [];
        $data = [
            'viral_campaign_hash_id' => 'abcdefghijklmnopqrst',
            'registration_code_value' => null,
        ];
        $get = ['email' =>
            [
                'is required.',
                'email is not valid.',
            ]
        ];

        $html = (new ViralWidgetHtml($params, $get))->registrationForm($data);

        Assert::expect($html)->to_include_string('<div class="viral-alert">Podany e-mail jest niepoprawny.</div>');
        Assert::expect($html)->to_include_string('<form id="viral-form" action="http://api.booklet.dev/v1/viral_signing_up/abcdefghijklmnopqrst" method="post">');
    }

    // ?show=signingup_successfully
    public function testInformationAlertsSigningupSuccessfully()
    {
        $params = [];
        $data = [
            'viral_campaign_hash_id' => 'abcdefghijklmnopqrst',
            'registration_code_value' => null,
        ];
        $get = ['show' => 'signingup_successfully'];

        $html = (new ViralWidgetHtml($params, $get))->registrationForm($data);

        Assert::expect($html)->to_include_string('<div class="viral-info">Dziękujemy za dołączenie do zabawy, sprawdź swoją pocztę i aktywuj konto.</div>');
        Assert::expect($html)->to_include_string('<form id="viral-form" action="http://api.booklet.dev/v1/viral_signing_up/abcdefghijklmnopqrst" method="post">');
    }

    // ?show=activation_successfully
    public function testInformationAlertsActivationSuccessfully()
    {
        $params = [];
        $data = [
            'viral_campaign_hash_id' => 'abcdefghijklmnopqrst',
            'registration_code_value' => null,
        ];
        $get = ['show' => 'activation_successfully'];

        $html = (new ViralWidgetHtml($params, $get))->registrationForm($data);

        Assert::expect($html)->to_include_string('<div class="viral-info">Dziękujemy, Twoje konto zostało aktywowane.</div>');
        Assert::expect($html)->to_include_string('<form id="viral-form" action="http://api.booklet.dev/v1/viral_signing_up/abcdefghijklmnopqrst" method="post">');
    }

    // ?show=activation_successfully
    public function testInformationAlertsActivationError()
    {
        $params = [];
        $data = [
            'viral_campaign_hash_id' => 'abcdefghijklmnopqrst',
            'registration_code_value' => null,
        ];
        $get = ['show' => 'activation_error'];

        $html = (new ViralWidgetHtml($params, $get))->registrationForm($data);

        Assert::expect($html)->to_include_string('<div class="viral-error">Aktywacja nie powiodła się.</div>');
        Assert::expect($html)->to_include_string('<form id="viral-form" action="http://api.booklet.dev/v1/viral_signing_up/abcdefghijklmnopqrst" method="post">');
    }

    // ?recommendation_code=a8j13h
    public function testInformationAlertsSignupSuccess()
    {
    }

    // ?campaign=suspended
    public function testInformationAlertsCampaignSuspended()
    {
        $params = [];
        $data = [
            'viral_campaign_hash_id' => 'abcdefghijklmnopqrst',
            'registration_code_value' => null,
        ];
        $get = ['campaign' => 'suspended'];

        $html = (new ViralWidgetHtml($params, $get))->registrationForm($data);

        Assert::expect($html)->to_include_string('<div class="viral-alert">Kampania została zawieszona. Rejestracja nie jest aktualnie możliwa.</div>');
        Assert::expect($html)->to_include_string('<form id="viral-form" action="http://api.booklet.dev/v1/viral_signing_up/abcdefghijklmnopqrst" method="post">');
    }

    public function testFacebookShareButton()
    {
        $params = [];
        $data = MemberDataFactory::testMemberData();
        $html = (new ViralWidgetHtml($params, []))->recommendationWidget($data);

        Assert::expect($html)->to_include_string('<a class="facebook-button" href="https://www.facebook.com/sharer/sharer.php?u=http%3A%2F%2Fbooklet.dev%2Fviral%2Fxyz123_recommendation"');
        Assert::expect($html)->to_include_string('onclick="window.open(\'https://www.facebook.com/sharer/sharer.php?u=http%3A%2F%2Fbooklet.dev%2Fviral%2Fxyz123_recommendation\',\'Udostępnij na Facebooku\',\'width=600,height=400\'); return false;"');
        Assert::expect($html)->to_include_string('Udostępnij na Facebook');
    }

    public function testTwitterShareButton()
    {
        $params = [];
        $data = MemberDataFactory::testMemberData();
        $html = (new ViralWidgetHtml($params, []))->recommendationWidget($data);

        Assert::expect($html)->to_include_string('<a class="twitter-button" href="https://twitter.com/home?status=http%3A%2F%2Fbooklet.dev%2Fviral%2Fxyz123_recommendation"');
        Assert::expect($html)->to_include_string('onclick="window.open(\'https://twitter.com/home?status=http%3A%2F%2Fbooklet.dev%2Fviral%2Fxyz123_recommendation\',\'Udostępnij na Twitter\',\'width=600,height=400\'); return false;"');
        Assert::expect($html)->to_include_string('Udostępnij na Twitter');
    }

    public function testTwitterShareButtonWithCustomText()
    {
        $params = [
            'twitter_text' => 'Custom text for twitter {url} check it.'
        ];
        $data = MemberDataFactory::testMemberData();
        $html = (new ViralWidgetHtml($params, []))->recommendationWidget($data);

        Assert::expect($html)->to_include_string('<a class="twitter-button" href="https://twitter.com/home?status=Custom+text+for+twitter+http%3A%2F%2Fbooklet.dev%2Fviral%2Fxyz123_recommendation+check+it."');
        Assert::expect($html)->to_include_string('onclick="window.open(\'https://twitter.com/home?status=Custom+text+for+twitter+http%3A%2F%2Fbooklet.dev%2Fviral%2Fxyz123_recommendation+check+it.\',\'Udostępnij na Twitter\',\'width=600,height=400\'); return false;"');
        Assert::expect($html)->to_include_string('Udostępnij na Twitter');
    }

    public function testEmailShareButton()
    {
        $params = [];
        $data = MemberDataFactory::testMemberData();
        $html = (new ViralWidgetHtml($params, []))->recommendationWidget($data);

        Assert::expect($html)->to_include_string('</a><a class="email-button" href="mailto:?&subject=Sprawdz&body=http%3A%2F%2Fbooklet.dev%2Fviral%2Fxyz123_recommendation"><i class="email-icon"></i>Udostępnij przez E-mail</a>');
    }

    public function testEmailShareButtonWithCustomText()
    {
        $params = [
            'mail_subject' => 'Check it!',
            'mail_body' => 'Custom text for email {url} check it.',
        ];
        $data = MemberDataFactory::testMemberData();
        $html = (new ViralWidgetHtml($params, []))->recommendationWidget($data);

        Assert::expect($html)->to_include_string('<a class="email-button" href="mailto:?&subject=Check%20it%21&body=Custom%20text%20for%20email%20http%3A%2F%2Fbooklet.dev%2Fviral%2Fxyz123_recommendation%20check%20it."><i class="email-icon"></i>Udostępnij przez E-mail</a>');
    }

    public function testFbMessengerShareButton()
    {
        $params = ['share_buttons' => ['fb_messenger']];
        $data = MemberDataFactory::testMemberData();
        $html = (new ViralWidgetHtml($params, []))->recommendationWidget($data);

        Assert::expect($html)->to_include_string('<a class="messenger-button" href="fb-messenger://share/?link=http%3A%2F%2Fbooklet.dev%2Fviral%2Fxyz123_recommendation&app_id=missing_app_id"><i class="messenger-icon"></i>Udostępnij przez Messengera</a>');
    }
}

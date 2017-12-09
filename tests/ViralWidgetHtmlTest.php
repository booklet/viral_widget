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

        $html = (new ViralWidgetHtml($params))->registrationForm($data);

        Assert::expect($html)->to_include_string('<form id="viral-form" action="http://api.booklet.dev/v1/viral_signing_up/abcdefghijklmnopqrst" method="post">');
        Assert::expect($html)->to_include_string('<input type="hidden" name="member[registration_code]" value="">');
        Assert::expect($html)->to_include_string('<input type="email" name="member[email]" class="form-control" id="viral-member-email">');
        Assert::expect($html)->to_include_string('<input type="text" name="member[name]" class="form-control" id="viral-member-name">');
        Assert::expect($html)->to_include_string('<button type="submit" class="btn btn-primary">Zapisz się</button>');
    }


    public function testRecommendationWidget()
    {
        $params = []; // dane z ->widget($params)
        $data = MemberDataFactory::testMemberData();  // dane z klasy

        $html = (new ViralWidgetHtml($params))->recommendationWidget($data);

        Assert::expect($html)->to_include_string('Poleceń do tej pory: <span class="points">0</span>.');
        Assert::expect($html)->to_include_string('<input type="text" value="http://booklet.dev/viral/xyz123_recommendation">');
    }
}

<?php
class ViralWidgetTest extends TesterCase
{
    public function testGetWidget()
    {
        $match = [];
        $get = [];
        $cookie = [];

        $viral = new ViralWidget('c51a44ce318175d3c68214f6d5111111', [
            'routing_match' => $match,
            'get' => $get,
            'cookie' => $cookie,
        ]);
        $html = $viral->widget();

        Assert::expect($html)->to_include_string('<form id="viral-form" action="http://api.booklet.dev/v1/viral_signing_up/c51a44ce318175d3c68214f6d5111111" method="post">');
        Assert::expect($html)->to_include_string('<input type="hidden" name="member[registration_code]" value="">');
        Assert::expect($html)->to_include_string('<input type="email" name="member[email]" class="form-control" id="viral-member-email">');
        Assert::expect($html)->to_include_string('<input type="text" name="member[name]" class="form-control" id="viral-member-name">');
        Assert::expect($html)->to_include_string('<button type="submit" class="btn btn-primary">Zapisz się</button>');
    }

    public function testGetWidgetWithRefId()
    {
        $match['registration_code'] = 'abc123_registration';
        $get = [];
        $cookie = [];

        $viral = new ViralWidget('c51a44ce318175d3c68214f6d5111111', [
            'routing_match' => $match,
            'get' => $get,
            'cookie' => $cookie,
        ]);
        $html = $viral->widget();

        Assert::expect($html)->to_include_string('<form id="viral-form" action="http://api.booklet.dev/v1/viral_signing_up/c51a44ce318175d3c68214f6d5111111" method="post">');
        Assert::expect($html)->to_include_string('<input type="hidden" name="member[registration_code]" value="abc123_registration">');
        Assert::expect($html)->to_include_string('<input type="email" name="member[email]" class="form-control" id="viral-member-email">');
        Assert::expect($html)->to_include_string('<input type="text" name="member[name]" class="form-control" id="viral-member-name">');
        Assert::expect($html)->to_include_string('<button type="submit" class="btn btn-primary">Zapisz się</button>');
    }

    public function testGetWidgetWhenFirstVisits()
    {

        $viral = new ViralWidget('c51a44ce318175d3c68214f6d5111111');

        $data = $viral->getParams();
        $expect = [
            'viral_campaign_hash_id' => 'c51a44ce318175d3c68214f6d5111111',
            'routing_match' => [],
            'get' => [],
            'cookies' => [],
            'cookie_registration_key_name' => 'c51a44_registration_code',
            'cookie_recommendation_key_name' => 'c51a44_recommendation_code',
            'registration_code_value' => null,
            'recommendation_code_value' => null,
            'cookies_to_set' => [],
        ];
        Assert::expect($data)->to_equal($expect);

        $html = $viral->widget();

        Assert::expect($html)->to_include_string('<form id="viral-form" action="http://api.booklet.dev/v1/viral_signing_up/c51a44ce318175d3c68214f6d5111111" method="post">');
        Assert::expect($html)->to_include_string('<input type="hidden" name="member[registration_code]" value="">');
    }

    public function testGetWidgetWhenFirstVisitsWithRegistrationCode()
    {
        $match['registration_code'] = 'abc123_registration';
        $get = [];
        $cookie = [];

        $viral = new ViralWidget('c51a44ce318175d3c68214f6d5111111', [
            'routing_match' => $match,
            'get' => $get,
            'cookie' => $cookie,
        ]);

        $data = $viral->getParams();
        $expect = [
            'viral_campaign_hash_id' => 'c51a44ce318175d3c68214f6d5111111',
            'routing_match' => ['registration_code' => 'abc123_registration'],
            'get' => [],
            'cookies' => [],
            'cookie_registration_key_name' => 'c51a44_registration_code',
            'cookie_recommendation_key_name' => 'c51a44_recommendation_code',
            'registration_code_value' => 'abc123_registration',
            'recommendation_code_value' => null,
            'cookies_to_set' => [
                [
                    'name' => 'c51a44_registration_code',
                    'value' => 'abc123_registration',
                ],
            ],
        ];
        Assert::expect($data)->to_equal($expect);

        $html = $viral->widget();

        Assert::expect($html)->to_include_string('<form id="viral-form" action="http://api.booklet.dev/v1/viral_signing_up/c51a44ce318175d3c68214f6d5111111" method="post">');
        Assert::expect($html)->to_include_string('<input type="hidden" name="member[registration_code]" value="abc123_registration">');
    }

    public function testGetWidgetWhenUserRegistredByGetParam()
    {
        $match['registration_code'] = 'abc123_registration';
        $get['recommendation_code'] = 'xyz123_recommendation';
        $cookie = [];

        $viral = new ViralWidget('c51a44ce318175d3c68214f6d5111111', [
            'routing_match' => $match,
            'get' => $get,
            'cookie' => $cookie,
        ]);

        $data = $viral->getParams();
        $expect = [
            'viral_campaign_hash_id' => 'c51a44ce318175d3c68214f6d5111111',
            'routing_match' => ['registration_code' => 'abc123_registration'],
            'get' => ['recommendation_code' => 'xyz123_recommendation'],
            'cookies' => [],
            'cookie_registration_key_name' => 'c51a44_registration_code',
            'cookie_recommendation_key_name' => 'c51a44_recommendation_code',
            'registration_code_value' => 'abc123_registration',
            'recommendation_code_value' => 'xyz123_recommendation',
            'cookies_to_set' => [
                [
                    'name' => 'c51a44_registration_code',
                    'value' => 'abc123_registration',
                ],
                [
                    'name' => 'c51a44_recommendation_code',
                    'value' => 'xyz123_recommendation',
                ]
            ],
        ];
        Assert::expect($data)->to_equal($expect);

        $html = $viral->widget();

        Assert::expect($html)->to_include_string('Poleceń do tej pory: <span class="points">0</span>.');
        Assert::expect($html)->to_include_string('<input type="text" value="http://booklet.dev/viral/xyz123_recommendation">');
    }

    public function testGetWidgetWhenUserRegistredByCookie()
    {
        $match['registration_code'] = 'abc123_registration';
        $get = [];
        $cookie['c51a44_recommendation_code'] = 'xyz123_recommendation';

        $viral = new ViralWidget('c51a44ce318175d3c68214f6d5111111', [
            'routing_match' => $match,
            'get' => $get,
            'cookie' => $cookie,
        ]);

        $data = $viral->getParams();
        $expect = [
            'viral_campaign_hash_id' => 'c51a44ce318175d3c68214f6d5111111',
            'routing_match' => ['registration_code' => 'abc123_registration'],
            'get' => [],
            'cookies' => ['c51a44_recommendation_code' => 'xyz123_recommendation'],
            'cookie_registration_key_name' => 'c51a44_registration_code',
            'cookie_recommendation_key_name' => 'c51a44_recommendation_code',
            'registration_code_value' => 'abc123_registration',
            'recommendation_code_value' => 'xyz123_recommendation',
            'cookies_to_set' => [
                [
                    'name' => 'c51a44_registration_code',
                    'value' => 'abc123_registration',
                ],
                [
                    'name' => 'c51a44_recommendation_code',
                    'value' => 'xyz123_recommendation',
                ]
            ],
        ];
        Assert::expect($data)->to_equal($expect);

        $html = $viral->widget();

        Assert::expect($html)->to_include_string('Poleceń do tej pory: <span class="points">0</span>.');
        Assert::expect($html)->to_include_string('<input type="text" value="http://booklet.dev/viral/xyz123_recommendation">');
    }

    public function testPreviewMode()
    {
        $match = [];
        $get = [];
        $cookie = [];

        $viral = new ViralWidget('c51a44ce318175d3c68214f6d5111111', [
            'routing_match' => $match,
            'get' => $get,
            'cookie' => $cookie,
        ]);
        $html = $viral->widget(['preview_mode' => true]);

        Assert::expect($html)->to_include_string('<form id="viral-form" action="http://api.booklet.dev/v1/viral_signing_up/CusT0mH4sH" method="post">');
        Assert::expect($html)->to_include_string('<input type="email" name="member[email]" class="form-control" id="viral-member-email">');
        Assert::expect($html)->to_include_string('<input type="text" name="member[name]" class="form-control" id="viral-member-name">');
        Assert::expect($html)->to_include_string('<button type="submit" class="btn btn-primary">Zapisz się</button>');
        Assert::expect($html)->to_include_string('<div class="viral-alert">Podany e-mail jest niepoprawny.</div>');
        Assert::expect($html)->to_include_string('<div class="viral-info">Dziękujemy, Twoje konto zostało aktywowane.</div>');
    }
}

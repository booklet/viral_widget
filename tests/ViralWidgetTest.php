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
        Assert::expect($html)->to_include_string('<input type="hidden" name="member[ref_id]" value="">');
        Assert::expect($html)->to_include_string('<input type="email" name="member[email]" class="form-control" id="viral-member-email">');
        Assert::expect($html)->to_include_string('<input type="text" name="member[name]" class="form-control" id="viral-member-name">');
        Assert::expect($html)->to_include_string('<button type="submit" class="btn btn-primary">Zapisz się</button>');
    }

    public function testGetWidgetWithRefId()
    {
        $match['ref_id'] = 'abc123_registration';
        $get = [];
        $cookie = [];

        $viral = new ViralWidget('c51a44ce318175d3c68214f6d5111111', [
            'routing_match' => $match,
            'get' => $get,
            'cookie' => $cookie,
        ]);
        $html = $viral->widget();

        Assert::expect($html)->to_include_string('<form id="viral-form" action="http://api.booklet.dev/v1/viral_signing_up/c51a44ce318175d3c68214f6d5111111" method="post">');
        Assert::expect($html)->to_include_string('<input type="hidden" name="member[ref_id]" value="abc123_registration">');
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
            'cookie' => [],
            'cookie_registration_key_name' => 'c51a44_ref_id',
            'cookie_recommendation_key_name' => 'c51a44_ref_coupon',
            'registration_code_value' => null,
            'recommendation_code_value' => null,
        ];
        Assert::expect($data)->to_equal($expect);

        $html = $viral->widget();

        Assert::expect($html)->to_include_string('<form id="viral-form" action="http://api.booklet.dev/v1/viral_signing_up/c51a44ce318175d3c68214f6d5111111" method="post">');
        Assert::expect($html)->to_include_string('<input type="hidden" name="member[ref_id]" value="">');
    }

    public function testGetWidgetWhenUserRegistredByGetParam()
    {
        $match['ref_id'] = 'abc123_registration';
        $get['ref_coupon'] = 'xyz123_recommendation';
        $cookie = [];

        $viral = new ViralWidget('c51a44ce318175d3c68214f6d5111111', [
            'routing_match' => $match,
            'get' => $get,
            'cookie' => $cookie,
        ]);

        $data = $viral->getParams();
        $expect = [
            'viral_campaign_hash_id' => 'c51a44ce318175d3c68214f6d5111111',
            'routing_match' => ['ref_id' => 'abc123_registration'],
            'get' => ['ref_coupon' => 'xyz123_recommendation'],
            'cookie' => [],
            'cookie_registration_key_name' => 'c51a44_ref_id',
            'cookie_recommendation_key_name' => 'c51a44_ref_coupon',
            'registration_code_value' => 'abc123_registration',
            'recommendation_code_value' => 'xyz123_recommendation',
        ];
        Assert::expect($data)->to_equal($expect);

        $html = $viral->widget();

        Assert::expect($html)->to_include_string('Poleceń do tej pory: <span class="points">0</span>.');
        Assert::expect($html)->to_include_string('<input type="text" value="http://booklet.dev/viral/xyz123_recommendation">');
    }

    public function testGetWidgetWhenUserRegistredByCookie()
    {
        $match['ref_id'] = 'abc123_registration';
        $get = [];
        $cookie['c51a44_ref_coupon'] = 'xyz123_recommendation';

        $viral = new ViralWidget('c51a44ce318175d3c68214f6d5111111', [
            'routing_match' => $match,
            'get' => $get,
            'cookie' => $cookie,
        ]);

        $data = $viral->getParams();
        $expect = [
            'viral_campaign_hash_id' => 'c51a44ce318175d3c68214f6d5111111',
            'routing_match' => ['ref_id' => 'abc123_registration'],
            'get' => [],
            'cookie' => ['c51a44_ref_coupon' => 'xyz123_recommendation'],
            'cookie_registration_key_name' => 'c51a44_ref_id',
            'cookie_recommendation_key_name' => 'c51a44_ref_coupon',
            'registration_code_value' => 'abc123_registration',
            'recommendation_code_value' => 'xyz123_recommendation',
        ];
        Assert::expect($data)->to_equal($expect);

        $html = $viral->widget();

        Assert::expect($html)->to_include_string('Poleceń do tej pory: <span class="points">0</span>.');
        Assert::expect($html)->to_include_string('<input type="text" value="http://booklet.dev/viral/xyz123_recommendation">');
    }
}
